param([switch]$Json)
$Failed = 0; $Results = @()

function Check-Http($Name, $Url) {
    try {
        $sw = [System.Diagnostics.Stopwatch]::StartNew()
        $r  = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
        $sw.Stop()
        $ok = $r.StatusCode -in 200,204
        if (!$Json) { Write-Host "  $(if($ok){'[OK]'}else{'[!!]'}) $Name -> HTTP $($r.StatusCode) ($($sw.ElapsedMilliseconds)ms)" -ForegroundColor $(if($ok){'Green'}else{'Red'}) }
        if (!$ok) { $script:Failed++ }
        return @{service=$Name;status=if($ok){"healthy"}else{"unhealthy"};http=$r.StatusCode}
    } catch {
        if (!$Json) { Write-Host "  [!!] $Name -> UNREACHABLE" -ForegroundColor Red }
        $script:Failed++
        return @{service=$Name;status="down";http=0}
    }
}

if (!$Json) { Write-Host "`n[*] Health Check $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')`n" -ForegroundColor Cyan }
$Results += Check-Http "Prometheus"        "http://localhost:9090/-/healthy"
$Results += Check-Http "Grafana"           "http://localhost:3000/api/health"
$Results += Check-Http "Loki"              "http://localhost:3100/ready"
$Results += Check-Http "Alertmanager"      "http://localhost:9093/-/healthy"
$Results += Check-Http "cAdvisor"          "http://localhost:8080/healthz"
$Results += Check-Http "Windows Exporter"  "http://localhost:9182/metrics"

$svc = Get-Service "windows_exporter" -ErrorAction SilentlyContinue
if ($svc) {
    $ok = $svc.Status -eq "Running"
    if (!$Json) { Write-Host "  $(if($ok){'[OK]'}else{'[!!]'}) windows_exporter service: $($svc.Status)" -ForegroundColor $(if($ok){'Green'}else{'Red'}) }
    if (!$ok) { $Failed++ }
}

if ($Json) {
    @{timestamp=(Get-Date -Format "o");failed=$Failed;status=if($Failed-eq 0){"healthy"}else{"degraded"};checks=$Results} | ConvertTo-Json -Depth 3
} else {
    Write-Host "`n$(if($Failed-eq 0){'[OK] All checks passed'}else{"[$Failed] check(s) FAILED"})" -ForegroundColor $(if($Failed-eq 0){'Green'}else{'Red'})
}
exit $Failed
