@echo off
title HRMate System Launcher
color 0A

echo.
echo  =======================================================
echo               HRMate - HR Management System
echo  =======================================================
echo.

:: Check MySQL
echo  [1/2] Checking MySQL...
tasklist /fi "imagename eq mysqld.exe" 2>NUL | find /i "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="1" (
    echo        Starting MySQL...
    start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"
    timeout /t 3 /nobreak >nul
)
echo        MySQL is ACTIVE!

echo  [2/2] Starting HRMate on port 8000...
echo.
echo  =======================================================
echo   SYSTEM READY!
echo.
echo   HRMate Portal   : http://localhost:8000
echo   Database Viewer : http://localhost/phpmyadmin
echo  =======================================================
echo.
echo   [ADMIN]    admin / AdminPassword2026!
echo   [EMPLOYEE] bareera.imam / Employee#2026
echo.
echo  =======================================================
echo   DATABASE - HOW TO OPEN
echo     Open XAMPP first, make sure Apache + MySQL running
echo     Then visit: http://localhost/phpmyadmin
echo     Username: root     Password: (empty)
echo     Click "hrm_system" in left side panel
echo  =======================================================
echo.
echo   Press Ctrl+C to stop.
echo  =======================================================
echo.

start "" "http://localhost:8000"

cd /d "%~dp0"
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
    )
)
php -S localhost:8000 -t public

pause
