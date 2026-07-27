# Script de Instalación Ecommerce
Write-Host "Instalando Ecommerce System..." -ForegroundColor Green

# Crear directorios
Write-Host "1. Creando directorios..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path "storage/logs" -Force | Out-Null
New-Item -ItemType Directory -Path "storage/cache" -Force | Out-Null
New-Item -ItemType Directory -Path "storage/sessions" -Force | Out-Null
New-Item -ItemType Directory -Path "storage/uploads" -Force | Out-Null
New-Item -ItemType Directory -Path "public/uploads" -Force | Out-Null

# Generar claves
Write-Host "2. Generando claves de seguridad..." -ForegroundColor Yellow
php cli/generate-key.php

Write-Host ""
Write-Host "Instalación completada!" -ForegroundColor Green
Write-Host "Siguiente: Importa la base de datos desde phpMyAdmin" -ForegroundColor Cyan
