@echo off
echo 🐳 Setting up Laravel with Docker...
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not running. Please start Docker Desktop and try again.
    pause
    exit /b 1
)

REM Copy .env file if not exists
if not exist .env (
    echo 📝 Creating .env file from .env.docker...
    copy .env.docker .env
    echo ✅ .env file created
) else (
    echo ℹ️  .env file already exists
)

REM Build and start containers
echo.
echo 🔨 Building Docker images...
docker-compose build

echo.
echo 🚀 Starting containers...
docker-compose up -d

echo.
echo ⏳ Waiting for database to be ready...
timeout /t 10 /nobreak >nul

REM Install Composer dependencies
echo.
echo 📦 Installing Composer dependencies...
docker-compose exec app composer install

REM Generate application key if not set
findstr /C:"APP_KEY=base64:" .env >nul
if %errorlevel% neq 0 (
    echo.
    echo 🔑 Generating application key...
    docker-compose exec app php artisan key:generate
)

REM Run migrations
echo.
echo 🗃️  Running database migrations...
docker-compose exec app php artisan migrate --force

REM Cache configuration
echo.
echo ⚡ Caching configuration...
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache

REM Set permissions (Windows handles this differently)
echo.
echo 🔐 Setting permissions...
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache

echo.
echo ✅ Setup complete!
echo.
echo 📊 Application is running at: http://localhost:8000
echo 🗄️  Database: MySQL (localhost:3306)
echo 🔴 Redis: localhost:6379
echo ⚡ Vite: http://localhost:5173
echo.
echo Useful commands:
echo   docker-compose up -d       # Start containers
echo   docker-compose down        # Stop containers
echo   docker-compose logs -f     # View logs
echo   docker-compose exec app bash  # Access app container
echo.
pause
