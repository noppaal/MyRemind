# PowerShell Script to Setup Test Database for MyRemind
# Run this script to create and populate test database

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MyRemind Test Database Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Laragon MySQL path
$mysqlPath = "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe"

# Check if MySQL exists
if (-not (Test-Path $mysqlPath)) {
    Write-Host "[ERROR] MySQL not found at: $mysqlPath" -ForegroundColor Red
    Write-Host "Please update the path in this script or add MySQL to PATH" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Alternative: Import manually via phpMyAdmin" -ForegroundColor Yellow
    Write-Host "  1. Open http://localhost/phpmyadmin" -ForegroundColor Yellow
    Write-Host "  2. Create database 'db_myremind_test'" -ForegroundColor Yellow
    Write-Host "  3. Import tests/fixtures/test_schema.sql" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "[1/3] Creating test database..." -ForegroundColor Yellow
& $mysqlPath -u root -e "CREATE DATABASE IF NOT EXISTS db_myremind_test;"

if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Database created successfully" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Failed to create database" -ForegroundColor Red
    pause
    exit 1
}

Write-Host ""
Write-Host "[2/3] Importing schema..." -ForegroundColor Yellow

# Read SQL file and execute
$sqlContent = Get-Content "tests\fixtures\test_schema.sql" -Raw
& $mysqlPath -u root db_myremind_test -e $sqlContent

if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Schema imported successfully" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Failed to import schema" -ForegroundColor Red
    pause
    exit 1
}

Write-Host ""
Write-Host "[3/3] Verifying tables..." -ForegroundColor Yellow
& $mysqlPath -u root db_myremind_test -e "SHOW TABLES;"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Now run tests with:" -ForegroundColor Yellow
Write-Host "  php vendor\bin\phpunit" -ForegroundColor White
Write-Host ""
pause
