# Crea (o reemplaza) la tarea de Windows que genera la copia de seguridad automática
# todos los días a las 21:00. Si la PC está apagada a esa hora, se ejecuta al encenderla.
#
# Uso (PowerShell, desde la carpeta del proyecto):
#   powershell -ExecutionPolicy Bypass -File scripts\crear-tarea-backup.ps1
#
# Para quitarla:
#   Unregister-ScheduledTask -TaskName "Disfraces Elizabeth - Copia de seguridad" -Confirm:$false

$proyecto = Split-Path -Parent $PSScriptRoot
$php      = "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php-win.exe"   # php-win.exe: no abre ventana de consola
$nombre   = "Disfraces Elizabeth - Copia de seguridad"

if (-not (Test-Path $php)) {
    Write-Error "No se encontró PHP en $php. Ajuste la ruta en este script."
    exit 1
}

$accion   = New-ScheduledTaskAction -Execute $php -Argument "artisan backup:generar --automatico" -WorkingDirectory $proyecto
$disparo  = New-ScheduledTaskTrigger -Daily -At "21:00"
$ajustes  = New-ScheduledTaskSettingsSet -StartWhenAvailable -DontStopIfGoingOnBatteries -AllowStartIfOnBatteries

Register-ScheduledTask -TaskName $nombre -Action $accion -Trigger $disparo -Settings $ajustes -Description "Copia de seguridad diaria de la base de datos disfraces_elizabeth" -Force | Out-Null

Write-Host "Tarea '$nombre' creada: todos los días a las 21:00." -ForegroundColor Green
