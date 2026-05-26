# Copy frontend from Desktop into repo `frontend` folder (PowerShell)
# Adjust the source path if your frontend lives elsewhere.

$source = "C:\Users\aghaa\Desktop\academy_clean\*"
$dest = Join-Path (Get-Location) 'frontend'

Write-Host "Copying from $source to $dest"
New-Item -ItemType Directory -Path $dest -Force | Out-Null
Copy-Item -Path $source -Destination $dest -Recurse -Force
Write-Host "Copy complete. Next: cd $dest\artifacts\academy && pnpm install && pnpm dev"
