@echo off
title Evidencia Dev Launcher
echo ==========================================
echo    Starting Evidencia Development Suite   
echo ==========================================

:: 1. Launch DBngin
echo [1/4] Launching DBngin...
if exist "%APPDATA%\DBngin\dbngin-go.exe" (
    start "" "%APPDATA%\DBngin\dbngin-go.exe"
) else (
    start "" dbngin 2>nul || echo DBngin not found in %APPDATA%\DBngin.
)

:: 2. Launch HeidiSQL
echo [2/4] Launching HeidiSQL...
if exist "%ProgramFiles%\HeidiSQL\heidisql.exe" (
    start "" "%ProgramFiles%\HeidiSQL\heidisql.exe"
) else (
    start "" heidisql 2>nul || echo HeidiSQL not found in %ProgramFiles%\HeidiSQL.
)

:: 3. Launch Python NLP Engine in a dedicated window
echo [3/4] Launching Python NLP Engine (Port 8001)...
start "Evidencia - Python NLP Engine (Port 8001)" cmd /k "python python-engine/main.py"

:: 4. Launch Laravel Dev Suite in a dedicated window
:: "php artisan dev" concurrently runs: php artisan serve (:8000), npm run dev (:5173), and queue worker
echo [4/4] Launching Laravel Server, Vite, and Queue Worker (Port 8000)...
start "Evidencia - Laravel & Vite (Port 8000)" cmd /k "php artisan dev"

echo.
echo ==========================================
echo  All services have been started!
echo  - Laravel App:     http://localhost:8000
echo  - Vite Dev Server: http://localhost:5173
echo  - Python NLP API:  http://127.0.0.1:8001/docs
echo  - Database:        DBngin (Port 3306) ^& HeidiSQL
echo ==========================================
timeout /t 5 >nul
