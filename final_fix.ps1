$path = "C:\xampp\php\php.ini"
$lines = Get-Content $path
$targetLineIndex = 983 # Line 984 (0-indexed)

if ($lines[$targetLineIndex] -match "extension=php_openssl.dll") {
    $lines[$targetLineIndex] = ";extension=php_openssl.dll"
    $lines | Set-Content $path
    Write-Host "Commented out line 984"
} else {
    Write-Host "Line 984 does not match expected content. Content is: $($lines[$targetLineIndex])"
}
