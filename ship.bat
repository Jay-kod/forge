@echo off
setlocal enabledelayedexpansion

echo ===================================================
echo   FORGE Code Shipping Pipeline
echo   Target: https://github.com/Jay-kod/forge.git
echo ===================================================
echo.
echo Shipping code in 5 seconds... (Press Ctrl+C to cancel)
timeout /t 5 /nobreak >nul

echo.
echo [1/3] Staging changes...
git add .

set COMMIT_MSG=%*
if "%COMMIT_MSG%"=="" (
    set COMMIT_MSG=feat: update and ship FORGE platform changes
)

echo [2/3] Committing changes: "%COMMIT_MSG%"...
git commit -m "%COMMIT_MSG%"

echo [3/3] Shipping to GitHub (origin main)...
git push -u origin main

if %ERRORLEVEL% equ 0 (
    echo.
    echo ===================================================
    echo   [SUCCESS] Code shipped to GitHub successfully!
    echo ===================================================
) else (
    echo.
    echo ===================================================
    echo   [NOTICE] If a GitHub login window opened,
    echo   please authorize and re-run ship.bat.
    echo ===================================================
)

echo.
pause
