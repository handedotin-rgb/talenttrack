@echo off
title TalentTrack - Recruitment Platform Server
echo ==========================================================
echo        TalentTrack Recruitment Platform Launcher
echo ==========================================================
echo.

REM Always run from the project folder, no matter where this bat file is launched from
cd /d "c:\Users\Dev\Documents\antigravity\intelligent-bardeen"
echo Working directory set to: %CD%
echo.

REM Find php.exe - check WinGet install location first, then PATH
set PHP_EXE=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe

if not exist "%PHP_EXE%" (
    echo WinGet PHP not found. Trying system PHP...
    set PHP_EXE=php.exe
)

echo Using PHP: %PHP_EXE%
echo.

REM Initialize the database if SQLite file doesn't exist
if not exist "database\talenttrack.sqlite" (
    echo First-time setup: Initializing database...
    "%PHP_EXE%" database\init_db.php
    echo Database initialized!
    echo.
)

echo Server is starting at: http://localhost:8000
echo.
echo Open your browser and navigate to: http://localhost:8000
echo.
echo Pre-seeded Administrator and Test Accounts:
echo   Admin     : admin@talenttrack.com     / Admin@123
echo   Recruiter : recruiter@techcorp.com    / Recruiter@123
echo   Candidate : candidate@example.com     / Candidate@123
echo.
echo Press Ctrl+C to stop the server.
echo ==========================================================
echo.

"%PHP_EXE%" -S localhost:8000 index.php
pause
