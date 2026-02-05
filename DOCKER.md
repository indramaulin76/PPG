# 🐳 Docker Setup - SI-JEMAAH

Docker configuration untuk menjalankan SI-JEMAAH di berbagai platform (Windows, macOS, Linux).

## 📋 Prerequisites

1. **Docker Desktop** terinstall:
   - Windows: [Download Docker Desktop](https://www.docker.com/products/docker-desktop)
   - macOS: [Download Docker Desktop](https://www.docker.com/products/docker-desktop)
   - Linux: Install Docker Engine & Docker Compose

2. **Git** (untuk clone repository)

---

## 🚀 Quick Start

### Windows

```bash
# 1. Clone repository (jika belum)
git clone <your-repo-url>
cd PPG

# 2. Jalankan setup script
docker-setup.bat
```

### macOS / Linux

```bash
# 1. Clone repository (jika belum)
git clone <your-repo-url>
cd PPG

# 2. Buat script executable
chmod +x docker-setup.sh

# 3. Jalankan setup script
./docker-setup.sh
```

---

## 📦 What's Included?

Docker setup ini mencakup:

| Service | Image | Port | Deskripsi |
|---------|-------|------|-----------|
| **app** | PHP 8.4-FPM | - | Laravel application |
| **web** | Nginx Alpine | 8000 | Web server |
| **db** | MySQL 8.0 | 3306 | Database |
| **redis** | Redis Alpine | 6379 | Cache & Session |
| **vite** | Node 20 | 5173 | Frontend dev server |

---

## 🛠️ Manual Setup (Alternative)

Jika script otomatis tidak jalan, ikuti langkah manual:

### 1. Copy Environment File

```bash
cp .env.docker .env
```

### 2. Build & Start Containers

```bash
docker-compose build
docker-compose up -d
```

### 3. Install Dependencies

```bash
docker-compose exec app composer install
```

### 4. Generate App Key

```bash
docker-compose exec app php artisan key:generate
```

### 5. Run Migrations

```bash
docker-compose exec app php artisan migrate
```

### 6. Create Super Admin (Optional)

```bash
docker-compose exec app php artisan tinker
```

```php
User::create([
    'name' => 'Super Admin',
    'email' => 'admin@jemaah.com',
    'password' => Hash::make('password'),
    'role' => 'super_admin',
    'is_active' => true,
]);
exit
```

---

## 📱 Access Application

After setup, access:

- **Web Application:** http://localhost:8000
- **Vite Dev Server:** http://localhost:5173
- **MySQL:** localhost:3306
  - Username: `root`
  - Password: `secret`
  - Database: `laravel`

---

## 🎮 Docker Commands

### Start/Stop Containers

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Stop and remove volumes (CAUTION: deletes database!)
docker-compose down -v
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f web
docker-compose logs -f db
```

### Access Container Shell

```bash
# PHP App container
docker-compose exec app bash

# MySQL
docker-compose exec db mysql -uroot -psecret

# Redis
docker-compose exec redis redis-cli
```

### Laravel Artisan Commands

```bash
# Run migration
docker-compose exec app php artisan migrate

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# Create migration
docker-compose exec app php artisan make:migration create_something_table

# Run tinker
docker-compose exec app php artisan tinker
```

### Composer & NPM

```bash
# Composer install
docker-compose exec app composer install

# Composer update
docker-compose exec app composer update

# NPM install (via vite service)
docker-compose exec vite npm install

# NPM build production
docker-compose exec vite npm run build
```

---

## 🔧 Configuration

### Change Ports

Edit `docker-compose.yml`:

```yaml
web:
  ports:
    - "9000:80"  # Change 8000 to 9000

vite:
  ports:
    - "5174:5173"  # Change 5173 to 5174
```

### Change Database Credentials

Edit `.env`:

```env
DB_DATABASE=my_custom_db
DB_USERNAME=my_user
DB_PASSWORD=my_password
```

Then recreate containers:

```bash
docker-compose down
docker-compose up -d
docker-compose exec app php artisan migrate
```

---

## 🐛 Troubleshooting

### 1. Port Already in Use

**Error:** `Bind for 0.0.0.0:8000 failed: port is already allocated`

**Solution:**
```bash
# Check apa yang pakai port 8000
# Windows
netstat -ano | findstr :8000

# macOS/Linux
lsof -i :8000

# Stop process atau change port di docker-compose.yml
```

### 2. Database Connection Failed

**Check:**
```bash
# See database logs
docker-compose logs db

# Test connection
docker-compose exec db mysql -uroot -psecret -e "SHOW DATABASES;"
```

**Restart database:**
```bash
docker-compose restart db
```

### 3. Permission Denied (Linux/macOS)

```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### 4. Vite Not Working

```bash
# Rebuild vite container
docker-compose up -d --force-recreate vite

# Check vite logs
docker-compose logs -f vite
```

### 5. Complete Reset

```bash
# Stop and remove everything
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Start fresh
./docker-setup.sh  # or docker-setup.bat on Windows
```

---

## 📂 File Structure

```
PPG/
├── docker/
│   └── nginx/
│       └── default.conf          # Nginx configuration
├── docker-compose.yml             # Docker services definition
├── Dockerfile                     # PHP application image
├── .env.docker                    # Docker environment template
├── docker-setup.sh                # Linux/macOS setup script
└── docker-setup.bat               # Windows setup script
```

---

## 🔐 Security Notes

**For Production:**

1. **Change default passwords:**
   ```env
   DB_PASSWORD=your_strong_password_here
   ```

2. **Set APP_DEBUG to false:**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

3. **Use proper Redis password:**
   ```env
   REDIS_PASSWORD=your_redis_password
   ```

4. **Enable HTTPS** via reverse proxy (Nginx, Caddy, Traefik)

5. **Set proper file permissions**

---

## 🆘 Getting Help

**Common Issues:**
- Docker Desktop not starting → Restart computer
- Slow on Windows → Enable WSL2 backend
- Database not ready → Increase healthcheck interval

**Check Status:**
```bash
docker-compose ps
docker stats
```

---

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Laravel Docker Best Practices](https://laravel.com/docs/deployment#docker)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)

---

**Last Updated:** 2026-02-04  
**Docker Version:** 24.0+  
**Docker Compose Version:** 2.0+
