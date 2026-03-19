# log-exporter.ps1 — Export Windows Event Logs for Promtail
# Run as Administrator: powershell -ExecutionPolicy Bypass -File scripts\log-exporter.ps1

$LogDir = Join-Path (Split-Path -Parent $PSScriptRoot) "logs"
if (!(Test-Path $LogDir)) { New-Item -ItemType Directory -Path $LogDir -Force | Out-Null }

$LookbackMinutes = 2

function Export-WinLog {
    param([string]$LogName, [string]$OutFile)
    $since = (Get-Date).AddMinutes(-$LookbackMinutes)
    try {
        $events = Get-WinEvent -LogName $LogName -ErrorAction SilentlyContinue |
            Where-Object { $_.TimeCreated -ge $since } | Sort-Object TimeCreated
        foreach ($ev in $events) {
            $level = switch ($ev.Level) { 1{"CRITICAL"} 2{"ERROR"} 3{"WARNING"} 4{"INFORMATION"} default{"INFO"} }
            $ts    = $ev.TimeCreated.ToString("yyyy-MM-dd HH:mm:ss")
            $src   = $ev.ProviderName -replace '\s+','_'
            $msg   = ($ev.Message -replace "`r`n|`n"," ").Trim()
            if ($msg.Length -gt 300) { $msg = $msg.Substring(0,300)+"..." }
            Add-Content -Path $OutFile -Value "[$ts] [$level] [$src] [Id:$($ev.Id)] $msg" -Encoding UTF8
        }
    } catch {}
    # Rotate if >10MB
    if ((Get-Item $OutFile -ErrorAction SilentlyContinue).Length -gt 10MB) {
        $bak = "$OutFile.$(Get-Date -Format 'yyyyMMdd-HHmmss').bak"
        Move-Item $OutFile $bak
        Get-ChildItem "$OutFile.*.bak" | Sort-Object LastWriteTime -Desc | Select-Object -Skip 3 | Remove-Item -Force
    }
}

Write-Host "Log Exporter running. Ctrl+C to stop. Logs -> $LogDir" -ForegroundColor Cyan

while ($true) {
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Exporting..." -NoNewline
    Export-WinLog "Security"    "$LogDir\security.log"
    Export-WinLog "System"      "$LogDir\system.log"
    Export-WinLog "Application" "$LogDir\application.log"
    Write-Host " Done."
    Start-Sleep 60
}
