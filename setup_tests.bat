@echo off
REM Quick Setup Script for MyRemind Unit Tests
REM Run this script to setup testing environment

echo ========================================
echo MyRemind Unit Testing Setup
echo ========================================
echo.

REM Check if Composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer not found!
    echo Please install Composer first: https://getcomposer.org/
    pause
    exit /b 1
)

echo [1/4] Installing PHPUnit via Composer...
composer install
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer install failed!
    pause
    exit /b 1
)
echo [OK] PHPUnit installed successfully
echo.

echo [2/4] Creating test database...
echo Please ensure MySQL is running in Laragon
echo.
echo Importing test schema...
mysql -u root -e "SOURCE tests/fixtures/test_schema.sql"
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] Database import failed. Please import manually:
    echo   1. Open phpMyAdmin
    echo   2. Import tests/fixtures/test_schema.sql
    echo.
) else (
    echo [OK] Test database created successfully
    echo.
)

echo [3/4] Verifying test configuration...
if exist phpunit.xml (
    echo [OK] phpunit.xml found
) else (
    echo [ERROR] phpunit.xml not found!
    pause
    exit /b 1
)

if exist tests\bootstrap.php (
    echo [OK] tests\bootstrap.php found
) else (
    echo [ERROR] tests\bootstrap.php not found!
    pause
    exit /b 1
)
echo.

echo [4/4] Running tests...
echo.
php vendor\bin\phpunit --testdox
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo To run tests again:
echo   php vendor\bin\phpunit
echo.
echo To run specific test:
echo   php vendor\bin\phpunit tests\Unit\Model\AuthModelTest.php
echo.
echo To generate coverage report:
echo   php vendor\bin\phpunit --coverage-html coverage
echo.
pause
