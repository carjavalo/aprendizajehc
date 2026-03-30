$path = "C:\xampp\php\php.ini"
$lines = Get-Content $path
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match "openssl") {
        Write-Host "Line $($i+1): $($lines[$i])"
    }
}
