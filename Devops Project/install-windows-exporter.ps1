#Requires -RunAsAdministrator
# Downloads and installs windows_exporter as a Windows service
# Run: powershell -ExecutionPolicy Bypass -File scripts\install-windows-exporter.ps1

$Version     = "0.25.1"
$InstallDir  = "C:\Program Files\windows_exporter"
$ServiceName = "windows_exporter"
$ExePath     = "$InstallDir\windows_exporter.exe"
$DownloadUrl = "https://github.com/prometheus-community/windows_exporter/releases/download/v$Version/windows_exporter-$Version-amd64.exe"

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  Windows Exporter Installer v$Version"
Write-Host "================================================" -ForegroundColor Cyan

# 1. Create install dir
if (!(Test-Path $InstallDir)) { New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null }
Write-Host "[*] Install dir: $InstallDir" -ForegroundColor Cyan

# 2. Download
Write-Host "[*] Downloading..." -ForegroundColor Cyan
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri $DownloadUrl -OutFile $ExePath -UseBasicParsing
    Write-Host "[OK] Downloaded" -ForegroundColor Green
} catch {
    Write-Host "[X] Download failed: $_" -ForegroundColor Red
    Write-Host "    Manual: $DownloadUrl  ->  $ExePath"
    exit 1
}

# 3. Remove old service
$old = Get-Service -Name $ServiceName -ErrorAction SilentlyContinue
if ($old) {
    Stop-Service $ServiceName -Force -ErrorAction SilentlyContinue
    & sc.exe delete $ServiceName | Out-Null
    Start-Sleep 2
}

# 4. Install service
$Collectors = "cpu,memory,logical_disk,net,os,service,process,system,cs,tcp"
$BinPath = "`"$ExePath`" --collectors.enabled=`"$Collectors`" --web.listen-address=`":9182`""
& sc.exe create $ServiceName binPath= $BinPath start= auto DisplayName= "Windows Exporter (Prometheus)" | Out-Null
& sc.exe description $ServiceName "Prometheus metrics exporter for Windows" | Out-Null

# 5. Firewall rule
$rule = Get-NetFirewallRule -DisplayName "Windows Exporter 9182" -ErrorAction SilentlyContinue
if (!$rule) {
    New-NetFirewallRule -DisplayName "Windows Exporter 9182" -Direction Inbound -Protocol TCP -LocalPort 9182 -Action Allow | Out-Null
    Write-Host "[OK] Firewall port 9182 opened" -ForegroundColor Green
}

# 6. Start
Start-Service $ServiceName
Start-Sleep 3
$svc = Get-Service $ServiceName
if ($svc.Status -eq "Running") {
    Write-Host "[OK] Service RUNNING" -ForegroundColor Green
} else {
    Write-Host "[X] Service failed to start" -ForegroundColor Red; exit 1
}

# 7. Verify
try {
    $r = Invoke-WebRequest -Uri "http://localhost:9182/metrics" -UseBasicParsing -TimeoutSec 5
    Write-Host "[OK] Metrics endpoint live: http://localhost:9182/metrics" -ForegroundColor Green
} catch {
    Write-Host "[!] Endpoint not responding yet — try again in a moment" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  windows_exporter ready!" -ForegroundColor Green
Write-Host "  Prometheus scrapes via host.docker.internal:9182"
Write-Host "================================================" -ForegroundColor Green
