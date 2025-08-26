# Docker Development Environment

This Docker setup provides a complete development environment for the WhatsNext Real Estate application.

## Services

### Web Server (Apache + PHP)
- **Port**: 8080
- **Features**: 
  - PHP 8.1 with Apache
  - mod_rewrite enabled
  - .htaccess support
  - All necessary PHP extensions installed
- **URL**: http://localhost:8080

### MySQL Database
- **Port**: 3306
- **Database**: whatsnext_dev
- **User**: whatsnext_user
- **Password**: whatsnext_pass
- **Root Password**: root

### phpMyAdmin
- **Port**: 8081
- **URL**: http://localhost:8081
- **Login**: whatsnext_user / whatsnext_pass

## Getting Started

### 1. Start the Environment
```bash
docker-compose up -d
```

### 2. Access Your Application
- **Main App**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### 3. Stop the Environment
```bash
docker-compose down
```

### 4. View Logs
```bash
docker-compose logs web
docker-compose logs mysql
```

## Configuration Files

- **docker-compose.yml**: Main orchestration file
- **docker/apache/Dockerfile**: Custom PHP+Apache image
- **docker/apache/000-default.conf**: Apache virtual host configuration
- **docker/apache/php.ini**: PHP configuration
- **docker/mysql/init.sql**: Database initialization script

## Features

✅ **URL Rewriting**: Your .htaccess rules will work perfectly  
✅ **PHP Processing**: All PHP files will render correctly  
✅ **Database**: MySQL with persistent data storage  
✅ **Development Tools**: phpMyAdmin for database management  
✅ **Hot Reload**: Code changes are immediately available  

## Troubleshooting

### Port Conflicts
If you get port conflicts, stop MAMP first:
```bash
# Stop MAMP Apache and MySQL
# Then run:
docker-compose up -d
```

### Rebuild Containers
If you make changes to Dockerfile or configs:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database Reset
To reset the database:
```bash
docker-compose down -v
docker-compose up -d
```

