param(
    [Parameter(Position=0)]
    [ValidateSet("start","stop","restart","status","logs","backup","reload","clean","help","")]
    [string]$Action = "help"
)

$ProjectDir  = Split-Path -Parent $PSScriptRoot
$ComposeFile = Join-Path $ProjectDir "docker-compose.yml"

function Write-Green($m)  { Write-Host $m -ForegroundColor Green }
function Write-Yellow($m) { Write-Host $m -ForegroundColor Yellow }
function Write-Red($m)    { Write-Host $m -ForegroundColor Red }
function Write-Cyan($m)   { Write-Host $m -ForegroundColor Cyan }

function Show-Banner {
    Write-Cyan "============================================="
    Write-Cyan "  DevOps Monitoring Stack  (Windows)"
    Write-Cyan "  Real-Time System Monitoring & Threat Detection"
    Write-Cyan "============================================="
    Write-Host ""
}

function Check-Requirements {
    if (!(Get-Command docker -ErrorAction SilentlyContinue)) {
        Write-Red "[X] Docker not found. Install Docker Desktop:"
        Write-Host "    https://www.docker.com/products/docker-desktop/"
        exit 1
    }
    $info = docker info 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Red "[X] Docker Desktop is not running. Please start it first."
        exit 1
    }
    Write-Green "[OK] Docker Desktop is running"
}

function Start-Stack {
    Show-Banner
    Check-Requirements
    Write-Host "[*] Starting stack..." -ForegroundColor Cyan
    Set-Location $ProjectDir
    docker compose -f $ComposeFile up -d
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Green "[OK] Stack started!"
        Write-Host ""
        Write-Cyan "  Grafana:       http://localhost:3000  (admin/admin123)"
        Write-Cyan "  Prometheus:    http://localhost:9090"
        Write-Cyan "  Alertmanager:  http://localhost:9093"
        Write-Cyan "  cAdvisor:      http://localhost:8080"
        Write-Host ""
        Write-Yellow "[!] Run windows_exporter for real host metrics:"
        Write-Yellow "    powershell -ExecutionPolicy Bypass -File scripts\install-windows-exporter.ps1"
    }
}

function Stop-Stack {
    Write-Yellow "[*] Stopping stack..."
    Set-Location $ProjectDir
    docker compose -f $ComposeFile stop
    Write-Green "[OK] Stopped."
}

function Show-Status {
    Set-Location $ProjectDir
    docker compose -f $ComposeFile ps
    Write-Host ""
    Write-Host "Windows Exporter Service:"
    $svc = Get-Service "windows_exporter" -ErrorAction SilentlyContinue
    if ($svc) {
        $color = if ($svc.Status -eq "Running") { "Green" } else { "Red" }
        Write-Host "  windows_exporter: $($svc.Status)" -ForegroundColor $color
    } else {
        Write-Host "  windows_exporter: NOT INSTALLED" -ForegroundColor Yellow
    }
}

function Show-Logs {
    Set-Location $ProjectDir
    docker compose -f $ComposeFile logs --tail=50 -f
}

function Backup-Data {
    $BackupDir = Join-Path $ProjectDir "backups\$(Get-Date -Format 'yyyyMMdd_HHmmss')"
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
    Write-Host "[*] Backing up to $BackupDir..." -ForegroundColor Cyan
    docker run --rm -v devops-monitoring-windows_prometheus_data:/data -v "${BackupDir}:/backup" alpine sh -c "tar czf /backup/prometheus.tar.gz -C /data ."
    docker run --rm -v devops-monitoring-windows_grafana_data:/data -v "${BackupDir}:/backup" alpine sh -c "tar czf /backup/grafana.tar.gz -C /data ."
    Write-Green "[OK] Backup saved to $BackupDir"
}

function Reload-Prometheus {
    Invoke-RestMethod -Uri "http://localhost:9090/-/reload" -Method Post
    Write-Green "[OK] Prometheus reloaded."
}

function Clean-Stack {
    Write-Red "[!] This will DELETE all containers and volumes!"
    $c = Read-Host "Type 'yes' to confirm"
    if ($c -eq "yes") {
        Set-Location $ProjectDir
        docker compose -f $ComposeFile down -v --remove-orphans
        Write-Green "[OK] Cleaned."
    }
}

switch ($Action) {
    "start"   { Start-Stack }
    "stop"    { Stop-Stack }
    "restart" { Stop-Stack; Start-Sleep 3; Start-Stack }
    "status"  { Show-Status }
    "logs"    { Show-Logs }
    "backup"  { Backup-Data }
    "reload"  { Reload-Prometheus }
    "clean"   { Clean-Stack }
    default   {
        Show-Banner
        Write-Host "Usage: .\scripts\manage.ps1 <command>"
        Write-Host ""
        Write-Host "  start    Start all services"
        Write-Host "  stop     Stop all services"
        Write-Host "  restart  Restart all services"
        Write-Host "  status   Show status"
        Write-Host "  logs     Follow logs"
        Write-Host "  backup   Backup volumes"
        Write-Host "  reload   Reload Prometheus config"
        Write-Host "  clean    Remove everything"
    }
}
