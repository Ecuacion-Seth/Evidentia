@echo off
title Evidencia Dev Stop
echo Stopping Evidencia development servers...

taskkill /FI "WINDOWTITLE eq Evidencia - Python NLP Engine*" /T /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Evidencia - Laravel Server & Vite*" /T /F >nul 2>&1

echo Stopped!
timeout /t 2 >nul
