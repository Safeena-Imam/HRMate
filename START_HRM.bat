@echo off
title HRMate System Launcher
color 0A

echo.
echo  =======================================================
echo               HRMate - HR Management System
echo  =======================================================
echo.

:: Start MySQL if not running
echo  [1/2] Checking MySQL Database Service...
tasklist /fi "imagename eq mysqld.exe" 2>NUL | find /i "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="1" (
    echo        Starting MySQL Server...
    start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"
    timeout /t 3 /nobreak >nul
)
echo        MySQL is ACTIVE!

:: Start PHP Server
echo  [2/2] Starting PHP Server on localhost:8000...
echo.
echo  =======================================================
echo   SYSTEM IS ONLINE & READY!
echo   Open your browser at: http://localhost:8000
echo  =======================================================
echo.
echo   [ADMIN CREDENTIALS]
echo     Username: admin
echo     Password: AdminPassword2026!
echo.
echo   [EMPLOYEE TEST CREDENTIALS]
echo     Username: bareera.imam
echo     Password: Employee#2026
echo.
echo   Press Ctrl+C to STOP the server.
echo  =======================================================
echo.

cd /d "c:\Users\safee\Desktop\HRM SYSTEM"
php -S localhost:8000 -t public

pause
