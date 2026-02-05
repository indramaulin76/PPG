#!/bin/bash

echo "🐳 Setting up Laravel with Docker..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Copy .env file if not exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker..."
    cp .env.docker .env
    echo "✅ .env file created"
else
    echo "ℹ️  .env file already exists"
fi

# Build and start containers
echo ""
echo "🔨 Building Docker images..."
docker-compose build

echo ""
echo "🚀 Starting containers..."
docker-compose up -d

echo ""
echo "⏳ Waiting for database to be ready..."
sleep 10

# Install Composer dependencies
echo ""
echo "📦 Installing Composer dependencies..."
docker-compose exec app composer install

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo ""
    echo "🔑 Generating application key..."
    docker-compose exec app php artisan key:generate
fi

# Run migrations
echo ""
echo "🗃️  Running database migrations..."
docker-compose exec app php artisan migrate --force

# Cache configuration
echo ""
echo "⚡ Caching configuration..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache

# Set permissions
echo ""
echo "🔐 Setting permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo ""
echo "📊 Application is running at: http://localhost:8000"
echo "🗄️  Database: MySQL (localhost:3306)"
echo "🔴 Redis: localhost:6379"
echo "⚡ Vite: http://localhost:5173"
echo ""
echo "Useful commands:"
echo "  docker-compose up -d       # Start containers"
echo "  docker-compose down        # Stop containers"
echo "  docker-compose logs -f     # View logs"
echo "  docker-compose exec app bash  # Access app container"
echo ""
