#!/usr/bin/env bash

# Evidencia Development Environment Shutdown Script
# Usage: ./stop-dev.sh or bash stop-dev.sh

echo "Stopping Evidencia development servers..."

cmd.exe /c 'taskkill /FI "WINDOWTITLE eq Evidencia - Python NLP Engine*" /T /F' >/dev/null 2>&1 || true
cmd.exe /c 'taskkill /FI "WINDOWTITLE eq Evidencia - Laravel Server & Vite*" /T /F' >/dev/null 2>&1 || true

echo "All development server windows stopped."
