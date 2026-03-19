#!/usr/bin/env python3
"""
DevOps Monitoring — Status Page Backend
Real-time API server that queries Prometheus and Loki and exposes
a single /api/status endpoint consumed by index.html

Run: python3 backend.py
Serves: http://localhost:8888
"""

import asyncio
import json
import time
import re
from datetime import datetime, timezone
from typing import Optional

import httpx
from fastapi import FastAPI, WebSocket, WebSocketDisconnect
from fastapi.staticfiles import StaticFiles
from fastapi.responses import JSONResponse, HTMLResponse
from fastapi.middleware.cors import CORSMiddleware

# ─────────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────────
PROMETHEUS_URL = "http://localhost:9090"
LOKI_URL       = "http://localhost:3100"
ALERTMANAGER_URL = "http://localhost:9093"
SERVE_PORT     = 8888
POLL_INTERVAL  = 10   # seconds between background metric refreshes
LOG_POLL_INTERVAL = 5 # seconds between log polls

app = FastAPI(title="DevOps Status Backend", version="1.0.0")
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_methods=["*"], allow_headers=["*"])

# ─────────────────────────────────────────────
# SHARED STATE  (updated by background task)
# ─────────────────────────────────────────────
state = {
    "metrics": {},
    "services": [],
    "logs": [],
    "alerts": [],
    "threats": [],
    "updated_at": None,
}

# WebSocket connections for push updates
ws_clients: list[WebSocket] = []


# ─────────────────────────────────────────────
# PROMETHEUS HELPERS
# ─────────────────────────────────────────────
async def prom_query(client: httpx.AsyncClient, query: str) -> Optional[float]:
    """Instant query — returns single float or None."""
    try:
        r = await client.get(
            f"{PROMETHEUS_URL}/api/v1/query",
            params={"query": query},
            timeout=5.0,
        )
        data = r.json()
        if data["status"] == "success" and data["data"]["result"]:
            return float(data["data"]["result"][0]["value"][1])
    except Exception:
        pass
    return None


async def prom_check_up(client: httpx.AsyncClient) -> bool:
    try:
        r = await client.get(f"{PROMETHEUS_URL}/-/healthy", timeout=3.0)
        return r.status_code == 200
    except Exception:
        return False


async def fetch_metrics(client: httpx.AsyncClient) -> dict:
    """Fetch all system metrics from Prometheus in parallel."""
    queries = {
        "cpu_percent":   '100 - (avg(rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100)',
        "memory_percent": "(1 - (node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes)) * 100",
        "memory_used_bytes": "node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes",
        "memory_total_bytes": "node_memory_MemTotal_bytes",
        "disk_percent":  '100 - ((node_filesystem_avail_bytes{mountpoint="/",fstype!="tmpfs"} / node_filesystem_size_bytes{mountpoint="/",fstype!="tmpfs"}) * 100)',
        "disk_used_bytes":  'node_filesystem_size_bytes{mountpoint="/",fstype!="tmpfs"} - node_filesystem_avail_bytes{mountpoint="/",fstype!="tmpfs"}',
        "net_rx_bytes_s": 'sum(rate(node_network_receive_bytes_total{device!~"lo|docker.*|veth.*"}[5m]))',
        "net_tx_bytes_s": 'sum(rate(node_network_transmit_bytes_total{device!~"lo|docker.*|veth.*"}[5m]))',
        "load_1":  "node_load1",
        "load_5":  "node_load5",
        "load_15": "node_load15",
        "uptime_seconds": "node_time_seconds - node_boot_time_seconds",
        "cpu_cores": "count(node_cpu_seconds_total{mode='idle'})",
        "containers_running": 'count(container_last_seen{name!=""})',
        "net_errors": "sum(rate(node_network_receive_errs_total[5m]))",
        "disk_io_read":  "sum(rate(node_disk_read_bytes_total[5m]))",
        "disk_io_write": "sum(rate(node_disk_written_bytes_total[5m]))",
    }
    tasks = {k: prom_query(client, q) for k, q in queries.items()}
    results = {}
    for k, coro in tasks.items():
        results[k] = await coro

    # Format human-readable
    def fmt_bytes(b):
        if b is None: return "—"
        for u in ["B","KB","MB","GB","TB"]:
            if b < 1024: return f"{b:.1f} {u}"
            b /= 1024
        return f"{b:.1f} PB"

    def fmt_uptime(s):
        if s is None: return "—"
        s = int(s)
        d, r = divmod(s, 86400)
        h, r2 = divmod(r, 3600)
        m = r2 // 60
        return f"{d}d {h}h {m}m" if d else f"{h}h {m}m"

    return {
        "cpu_percent":        round(results["cpu_percent"] or 0, 1),
        "memory_percent":     round(results["memory_percent"] or 0, 1),
        "memory_used":        fmt_bytes(results["memory_used_bytes"]),
        "memory_total":       fmt_bytes(results["memory_total_bytes"]),
        "disk_percent":       round(results["disk_percent"] or 0, 1),
        "disk_used":          fmt_bytes(results["disk_used_bytes"]),
        "net_rx":             fmt_bytes(results["net_rx_bytes_s"]) + "/s",
        "net_tx":             fmt_bytes(results["net_tx_bytes_s"]) + "/s",
        "net_rx_raw":         round(results["net_rx_bytes_s"] or 0, 0),
        "load_1":             round(results["load_1"] or 0, 2),
        "load_5":             round(results["load_5"] or 0, 2),
        "load_15":            round(results["load_15"] or 0, 2),
        "uptime":             fmt_uptime(results["uptime_seconds"]),
        "cpu_cores":          int(results["cpu_cores"] or 0),
        "containers_running": int(results["containers_running"] or 0),
        "net_errors":         round(results["net_errors"] or 0, 2),
        "disk_io_read":       fmt_bytes(results["disk_io_read"]) + "/s",
        "disk_io_write":      fmt_bytes(results["disk_io_write"]) + "/s",
    }


# ─────────────────────────────────────────────
# SERVICE HEALTH CHECKS
# ─────────────────────────────────────────────
SERVICES_CONFIG = [
    {"name": "Prometheus",    "url": f"{PROMETHEUS_URL}/-/healthy",    "port": 9090},
    {"name": "Grafana",       "url": "http://localhost:3000/api/health","port": 3000},
    {"name": "Loki",          "url": f"{LOKI_URL}/ready",              "port": 3100},
    {"name": "Alertmanager",  "url": f"{ALERTMANAGER_URL}/-/healthy",  "port": 9093},
    {"name": "Node Exporter", "url": "http://localhost:9100/metrics",   "port": 9100},
    {"name": "cAdvisor",      "url": "http://localhost:8080/healthz",   "port": 8080},
    {"name": "Promtail",      "url": "http://localhost:9080/ready",     "port": 9080},
]

async def check_service(client: httpx.AsyncClient, svc: dict) -> dict:
    t0 = time.monotonic()
    try:
        r = await client.get(svc["url"], timeout=4.0)
        latency_ms = round((time.monotonic() - t0) * 1000)
        status = "up" if r.status_code in (200, 204) else "degraded"
    except Exception as e:
        latency_ms = None
        status = "down"
    return {
        "name":    svc["name"],
        "port":    svc["port"],
        "status":  status,
        "latency": latency_ms,
    }

async def fetch_services(client: httpx.AsyncClient) -> list:
    tasks = [check_service(client, s) for s in SERVICES_CONFIG]
    return list(await asyncio.gather(*tasks))


# ─────────────────────────────────────────────
# LOKI LOG FETCHING
# ─────────────────────────────────────────────
THREAT_PATTERNS = {
    "crit": re.compile(r"(?i)(failed password|invalid user|authentication failure|brute|attack|exploit|malware|ransomware|Out of memory|oom.kill|BIOS.*corrupt|EXT4-fs error|recv\(\) failed)", re.I),
    "warn": re.compile(r"(?i)(sudo.*fail|permission denied|UFW BLOCK|port scan|SYN flood|error|exception|timeout|refused|denied|hrtimer|ntpd.*step)", re.I),
}

def classify_log(line: str) -> str:
    if THREAT_PATTERNS["crit"].search(line): return "crit"
    if THREAT_PATTERNS["warn"].search(line): return "warn"
    return "info"

def detect_source(labels: dict) -> str:
    job = labels.get("job", "")
    fname = labels.get("filename", "")
    container = labels.get("container", labels.get("name", ""))
    if "auth" in job or "auth" in fname:       return "auth"
    if "kernel" in job or "kern" in fname:      return "kernel"
    if "nginx" in job or "nginx" in fname:      return "nginx"
    if container and container not in ("", "/"):return "docker"
    return "syslog"

async def fetch_logs(client: httpx.AsyncClient, minutes: int = 5, limit: int = 100) -> list:
    """Pull recent log lines from Loki."""
    try:
        end_ns   = int(time.time() * 1e9)
        start_ns = end_ns - minutes * 60 * int(1e9)
        r = await client.get(
            f"{LOKI_URL}/loki/api/v1/query_range",
            params={
                "query": '{job=~"auth|syslog|kernel|nginx|docker_containers"}',
                "start": start_ns,
                "end":   end_ns,
                "limit": limit,
                "direction": "backward",
            },
            timeout=8.0,
        )
        data = r.json()
        entries = []
        for stream in data.get("data", {}).get("result", []):
            labels = stream.get("stream", {})
            src    = detect_source(labels)
            prog   = labels.get("unit", labels.get("container", labels.get("job", "system")))
            prog   = prog.replace(".service", "").strip("/")[:12]
            for ts_ns, line in stream.get("values", []):
                ts_dt = datetime.fromtimestamp(int(ts_ns) / 1e9, tz=timezone.utc)
                entries.append({
                    "ts":  ts_dt.strftime("%H:%M:%S"),
                    "src": src,
                    "pg":  prog,
                    "lv":  classify_log(line),
                    "msg": line[:300],
                })
        # Sort newest first, deduplicate
        entries.sort(key=lambda x: x["ts"], reverse=True)
        return entries[:limit]
    except Exception as e:
        return []


# ─────────────────────────────────────────────
# ALERTMANAGER — ACTIVE ALERTS
# ─────────────────────────────────────────────
async def fetch_alerts(client: httpx.AsyncClient) -> list:
    try:
        r = await client.get(f"{ALERTMANAGER_URL}/api/v2/alerts", timeout=4.0)
        raw = r.json()
        alerts = []
        for a in raw:
            labels = a.get("labels", {})
            ann    = a.get("annotations", {})
            alerts.append({
                "name":     labels.get("alertname", "Unknown"),
                "severity": labels.get("severity", "info"),
                "summary":  ann.get("summary", ""),
                "instance": labels.get("instance", ""),
                "state":    a.get("status", {}).get("state", "active"),
                "started":  a.get("startsAt", ""),
            })
        return alerts
    except Exception:
        return []


# ─────────────────────────────────────────────
# THREAT DETECTION FROM LOGS
# ─────────────────────────────────────────────
def extract_threats(logs: list) -> list:
    threats = []
    ip_failures: dict[str, int] = {}

    for entry in logs:
        msg = entry["msg"]

        # SSH brute force — count per IP
        m = re.search(r"Failed password.*from ([\d.]+)", msg, re.I)
        if m:
            ip = m.group(1)
            ip_failures[ip] = ip_failures.get(ip, 0) + 1

        # Root login
        if re.search(r"ROOT LOGIN|Accepted.*for root from", msg, re.I):
            threats.append({"sev":"crit","msg":f"Direct root login detected","meta":f"auth · {entry['ts']}"})

        # OOM
        if re.search(r"Out of memory|oom.kill", msg, re.I):
            threats.append({"sev":"crit","msg":"OOM kill event — process terminated","meta":f"kernel · {entry['ts']}"})

        # Disk error
        if re.search(r"EXT4-fs error|I/O error|Buffer I/O error", msg, re.I):
            threats.append({"sev":"crit","msg":"Disk I/O error detected","meta":f"kernel · {entry['ts']}"})

        # Port scan
        if re.search(r"UFW BLOCK.*DPT", msg, re.I):
            threats.append({"sev":"warn","msg":f"Firewall block: {msg[:80]}","meta":f"kernel · {entry['ts']}"})

        # Attack tools
        if re.search(r"nmap|sqlmap|hydra|masscan|nikto", msg, re.I):
            threats.append({"sev":"crit","msg":f"Attack tool detected: {msg[:80]}","meta":f"{entry['src']} · {entry['ts']}"})

        # Path traversal in nginx
        if re.search(r"\.\./\.\./|/etc/passwd|/etc/shadow", msg, re.I):
            threats.append({"sev":"warn","msg":f"Path traversal probe: {msg[:80]}","meta":f"nginx · {entry['ts']}"})

    # Emit brute-force alerts for IPs with multiple failures
    for ip, count in ip_failures.items():
        if count >= 5:
            threats.append({"sev":"crit","msg":f"Brute force: {count} SSH failures from {ip}","meta":f"auth · last 5 min"})
        elif count >= 3:
            threats.append({"sev":"warn","msg":f"Repeated SSH failures ({count}x) from {ip}","meta":f"auth · last 5 min"})

    # Deduplicate by message prefix
    seen = set()
    unique = []
    for t in sorted(threats, key=lambda x: {"crit":0,"warn":1,"info":2}.get(x["sev"],2)):
        key = t["msg"][:50]
        if key not in seen:
            seen.add(key)
            unique.append(t)

    return unique[:20]


# ─────────────────────────────────────────────
# BACKGROUND POLLING TASK
# ─────────────────────────────────────────────
async def poll_loop():
    """Continuously refresh state from Prometheus and Loki."""
    async with httpx.AsyncClient() as client:
        while True:
            try:
                metrics, services, logs, alerts = await asyncio.gather(
                    fetch_metrics(client),
                    fetch_services(client),
                    fetch_logs(client, minutes=10, limit=120),
                    fetch_alerts(client),
                )
                state["metrics"]    = metrics
                state["services"]   = services
                state["logs"]       = logs
                state["alerts"]     = alerts
                state["threats"]    = extract_threats(logs)
                state["updated_at"] = datetime.now(timezone.utc).isoformat()

                # Push update to all connected WebSocket clients
                payload = json.dumps({"type": "update", "data": state})
                dead = []
                for ws in ws_clients:
                    try:
                        await ws.send_text(payload)
                    except Exception:
                        dead.append(ws)
                for ws in dead:
                    ws_clients.remove(ws)

            except Exception as e:
                print(f"[poll_loop error] {e}")

            await asyncio.sleep(POLL_INTERVAL)


@app.on_event("startup")
async def startup():
    asyncio.create_task(poll_loop())


# ─────────────────────────────────────────────
# REST ENDPOINTS
# ─────────────────────────────────────────────
@app.get("/api/status")
async def get_status():
    """Full status snapshot — metrics, services, recent logs, alerts, threats."""
    return JSONResponse(content=state)


@app.get("/api/metrics")
async def get_metrics():
    return JSONResponse(content=state.get("metrics", {}))


@app.get("/api/services")
async def get_services():
    return JSONResponse(content=state.get("services", []))


@app.get("/api/logs")
async def get_logs(src: str = "all", level: str = "all", q: str = "", limit: int = 100):
    """Filtered log endpoint."""
    logs = state.get("logs", [])
    if src != "all":
        logs = [l for l in logs if l.get("src") == src]
    if level != "all":
        logs = [l for l in logs if l.get("lv") == level]
    if q:
        q_lower = q.lower()
        logs = [l for l in logs if q_lower in l.get("msg","").lower() or q_lower in l.get("pg","").lower()]
    return JSONResponse(content=logs[:limit])


@app.get("/api/alerts")
async def get_alerts():
    return JSONResponse(content=state.get("alerts", []))


@app.get("/api/threats")
async def get_threats():
    return JSONResponse(content=state.get("threats", []))


@app.get("/api/health")
async def health():
    return {"status": "ok", "updated_at": state.get("updated_at")}


# ─────────────────────────────────────────────
# WEBSOCKET — LIVE PUSH
# ─────────────────────────────────────────────
@app.websocket("/ws")
async def websocket_endpoint(ws: WebSocket):
    await ws.accept()
    ws_clients.append(ws)
    # Send current state immediately on connect
    try:
        await ws.send_text(json.dumps({"type": "update", "data": state}))
        while True:
            await ws.receive_text()  # keep alive; client sends pings
    except WebSocketDisconnect:
        pass
    finally:
        if ws in ws_clients:
            ws_clients.remove(ws)


# ─────────────────────────────────────────────
# SERVE STATIC STATUS PAGE
# ─────────────────────────────────────────────
app.mount("/", StaticFiles(directory=".", html=True), name="static")


# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────
if __name__ == "__main__":
    import uvicorn
    print(f"""
╔══════════════════════════════════════════════════╗
║   DevOps Monitoring — Status Page Backend        ║
║                                                  ║
║   Status Page:  http://localhost:{SERVE_PORT}          ║
║   API:          http://localhost:{SERVE_PORT}/api/status ║
║   Logs API:     http://localhost:{SERVE_PORT}/api/logs   ║
║   WebSocket:    ws://localhost:{SERVE_PORT}/ws           ║
╚══════════════════════════════════════════════════╝
    """)
    uvicorn.run("backend:app", host="0.0.0.0", port=SERVE_PORT, reload=False, log_level="warning")
