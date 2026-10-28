#!/usr/bin/env bash

# Evidencia Development Environment Startup Script
# Usage: ./start-dev.sh or bash start-dev.sh

echo "=========================================="
echo "   Starting Evidencia Development Suite   "
echo "=========================================="

# 1. Launch DBngin
echo "[1/4] Launching DBngin..."
DBNGIN_EXE="$APPDATA/DBngin/dbngin-go.exe"
if [ -f "$DBNGIN_EXE" ]; then
    cmd.exe /c start "" "$DBNGIN_EXE" 2>/dev/null || true
else
    cmd.exe /c start dbngin 2>/dev/null || echo "DBngin executable not found at $DBNGIN_EXE."
fi

# 2. Launch HeidiSQL
echo "[2/4] Launching HeidiSQL..."
HEIDISQL_EXE="/c/Program Files/HeidiSQL/heidisql.exe"
if [ -f "$HEIDISQL_EXE" ]; then
    cmd.exe /c start "" "C:\Program Files\HeidiSQL\heidisql.exe" 2>/dev/null || true
else
    cmd.exe /c start heidisql 2>/dev/null || echo "HeidiSQL not found at default location."
fi

# 3. Launch Python NLP Engine in a dedicated window
echo "[3/4] Launching Python NLP Engine (Port 8001)..."
cmd.exe /c start "Evidencia - Python NLP Engine (Port 8001)" cmd.exe /k "python python-engine/main.py"

# 4. Launch Laravel Dev Suite (Server :8000 + Vite :5173 + Queue Worker)
echo "[4/4] Launching Laravel & Vite (php artisan dev)..."
cmd.exe /c start "Evidencia - Laravel Server & Vite (Port 8000)" cmd.exe /k "php artisan dev"

echo ""
echo "=========================================="
echo " All services launched in separate windows!"
echo " - Laravel App:     http://localhost:8000"
echo " - Vite Dev Server: http://localhost:5173"
echo " - Python NLP API:  http://127.0.0.1:8001/docs"
echo " - Database:        DBngin (Port 3306) & HeidiSQL"
echo "=========================================="
