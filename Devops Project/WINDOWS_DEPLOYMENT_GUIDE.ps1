# DevOps Monitoring Stack — Windows Deployment Guide
# =====================================================
# Tested on: Windows 10/11 (64-bit)
# Prerequisites: Run PowerShell as Administrator


# ════════════════════════════════════════════
# STEP 1 — INSTALL DOCKER DESKTOP
# ════════════════════════════════════════════

# 1. Download Docker Desktop for Windows:
#    https://www.docker.com/products/docker-desktop/

# 2. Run the installer → follow the wizard
#    - Enable WSL 2 when prompted (recommended)
#    - Enable "Use WSL 2 based engine"

# 3. After install, restart your PC

# 4. Open Docker Desktop — wait for it to show "Engine Running"

# 5. Verify in PowerShell:
docker --version
docker compose version
# Should print version numbers, not errors


# ════════════════════════════════════════════
# STEP 2 — ENABLE WSL2 (if not already done)
# ════════════════════════════════════════════

# Run in PowerShell as Administrator:
wsl --install
# Restart PC after this completes

# Set WSL2 as default:
wsl --set-default-version 2

# In Docker Desktop Settings:
#   General → "Use WSL 2 based engine" → Apply & Restart


# ════════════════════════════════════════════
# STEP 3 — INSTALL PYTHON 3
# ════════════════════════════════════════════

# Download Python 3.11+ from:
#   https://www.python.org/downloads/windows/

# IMPORTANT: During install, tick "Add Python to PATH"

# Verify:
python --version
pip --version


# ════════════════════════════════════════════
# STEP 4 — COPY THE PROJECT
# ════════════════════════════════════════════

# Place the devops-monitoring-windows folder anywhere, e.g.:
#   C:\devops-monitoring\

# Open PowerShell in that folder:
cd C:\devops-monitoring

# Allow PowerShell scripts to run (run as Admin):
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser


# ════════════════════════════════════════════
# STEP 5 — INSTALL WINDOWS EXPORTER
# ════════════════════════════════════════════

# This replaces node_exporter (Linux-only) for Windows.
# It collects real CPU, Memory, Disk, Network, Service metrics.

# Run as Administrator:
powershell -ExecutionPolicy Bypass -File scripts\install-windows-exporter.ps1

# Verify it's running:
Get-Service windows_exporter
# Status should show: Running

# Test metrics in browser:
#   http://localhost:9182/metrics
# You should see hundreds of lines like:
#   windows_cpu_time_total{...} 0.5


# ════════════════════════════════════════════
# STEP 6 — START THE MONITORING STACK
# ════════════════════════════════════════════

.\scripts\manage.ps1 start

# This starts 6 containers:
#   prometheus, grafana, loki, promtail, alertmanager, cadvisor

# Wait 30 seconds then check:
.\scripts\manage.ps1 status

# All containers should show "running"


# ════════════════════════════════════════════
# STEP 7 — SHIP WINDOWS EVENT LOGS TO LOKI
# ════════════════════════════════════════════

# Open a NEW PowerShell window as Administrator and run:
powershell -ExecutionPolicy Bypass -File scripts\log-exporter.ps1

# This exports Windows Security, System, and Application
# event logs every 60 seconds to the .\logs\ folder.
# Promtail picks them up and ships to Loki automatically.

# You'll see output like:
#   [14:22:01] Exporting... Done.
#   [14:23:01] Exporting... Done.

# Keep this window open while monitoring.
# To run it in background (optional):
Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -File scripts\log-exporter.ps1" -WindowStyle Minimized


# ════════════════════════════════════════════
# STEP 8 — VERIFY PROMETHEUS IS SCRAPING
# ════════════════════════════════════════════

# Open in browser: http://localhost:9090/targets
# You should see these jobs with status "UP":
#   windows-exporter   UP
#   prometheus         UP
#   grafana            UP
#   alertmanager       UP
#   loki               UP
#   cadvisor           UP (may show limited data on Windows)

# If windows-exporter shows DOWN:
#   1. Check windows_exporter service is running
#   2. Check firewall allows port 9182
#   3. Try: curl http://localhost:9182/metrics


# ════════════════════════════════════════════
# STEP 9 — OPEN GRAFANA DASHBOARDS
# ════════════════════════════════════════════

# URL:   http://localhost:3000
# Login: admin / admin123

# 3 dashboards are auto-provisioned:
#   - DevOps System Overview       (Windows CPU/Memory/Disk/Network)
#   - Security & Threat Detection  (Windows Event Log analysis)
#   - Container Monitoring         (Docker container metrics)

# Import the best Windows dashboard from Grafana community:
# Dashboards → Import → Enter ID: 20763 → Load
# Select Prometheus → Import
# This gives a full Windows-specific dashboard.


# ════════════════════════════════════════════
# STEP 10 — START THE STATUS PAGE (OPTIONAL)
# ════════════════════════════════════════════

cd status-page

# Create virtual environment
python -m venv venv
.\venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Run backend
python backend.py

# Open: http://localhost:8888
# Shows real metrics, real logs, real alerts — all live from your Windows system

# To run in background (new window):
Start-Process powershell -ArgumentList "-Command cd '$PWD'; .\venv\Scripts\activate; python backend.py" -WindowStyle Minimized

cd ..


# ════════════════════════════════════════════
# STEP 11 — RUN HEALTH CHECK
# ════════════════════════════════════════════

.\scripts\healthcheck.ps1

# Expected output:
#   [OK] Prometheus       -> HTTP 200
#   [OK] Grafana          -> HTTP 200
#   [OK] Loki             -> HTTP 200
#   [OK] Alertmanager     -> HTTP 200
#   [OK] cAdvisor         -> HTTP 200
#   [OK] Windows Exporter -> HTTP 200
#   [OK] windows_exporter service: Running
#   All checks passed


# ════════════════════════════════════════════
# ACCESS URLS SUMMARY
# ════════════════════════════════════════════
#
#  Grafana:              http://localhost:3000   (admin/admin123)
#  Status Page:          http://localhost:8888
#  Prometheus:           http://localhost:9090
#  Alertmanager:         http://localhost:9093
#  Windows Exporter:     http://localhost:9182/metrics
#  cAdvisor:             http://localhost:8080
#  Loki:                 http://localhost:3100
#
# ════════════════════════════════════════════
# MANAGEMENT COMMANDS
# ════════════════════════════════════════════

.\scripts\manage.ps1 start      # Start all containers
.\scripts\manage.ps1 stop       # Stop containers
.\scripts\manage.ps1 restart    # Restart
.\scripts\manage.ps1 status     # Show status
.\scripts\manage.ps1 logs       # Follow logs
.\scripts\manage.ps1 backup     # Backup volumes
.\scripts\manage.ps1 reload     # Reload Prometheus config
.\scripts\manage.ps1 clean      # Remove everything


# ════════════════════════════════════════════
# TROUBLESHOOTING
# ════════════════════════════════════════════

# "Docker not running"
#   → Open Docker Desktop, wait for "Engine running" in taskbar

# "Port already in use"
netstat -ano | findstr :3000
netstat -ano | findstr :9090
#   → Kill the process using that port, or change port in docker-compose.yml

# "windows-exporter target DOWN in Prometheus"
Get-Service windows_exporter            # check running
curl http://localhost:9182/metrics      # test endpoint
#   → Restart: Restart-Service windows_exporter
#   → Check firewall: port 9182 must be allowed inbound

# "No logs appearing in Loki/Grafana"
#   → Make sure log-exporter.ps1 is running
#   → Check .\logs\ folder has .log files with content
#   → Restart promtail: docker restart promtail

# "Grafana shows No Data"
#   → Verify Prometheus targets are UP: http://localhost:9090/targets
#   → Check datasource: Grafana → Configuration → Data Sources → Test

# "Permission denied running .ps1"
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser


# ════════════════════════════════════════════
# AUTO-START ON WINDOWS BOOT (OPTIONAL)
# ════════════════════════════════════════════

# Create a scheduled task to start the stack on login:
$trigger  = New-ScheduledTaskTrigger -AtLogOn
$action   = New-ScheduledTaskAction -Execute "powershell.exe" `
              -Argument "-ExecutionPolicy Bypass -File C:\devops-monitoring\scripts\manage.ps1 start" `
              -WorkingDirectory "C:\devops-monitoring"
$settings = New-ScheduledTaskSettingsSet -ExecutionTimeLimit 0

Register-ScheduledTask `
    -TaskName "DevOps Monitoring Stack" `
    -Trigger $trigger `
    -Action $action `
    -Settings $settings `
    -RunLevel Highest `
    -Force

# To remove auto-start:
Unregister-ScheduledTask -TaskName "DevOps Monitoring Stack" -Confirm:$false
