Caesar
===========
## Requirements

* [Docker and Docker Compose](https://docs.docker.com/engine/installation)
* [MacOS Only]: Docker Sync (run `gem install docker-sync` to install it)

## Stack
* PHP 7.4
* PostgreSQL 9
* RabbitMQ 3
* Redis 

## Installation

### 1. Update .env:
- Create a config file .env by .env.dist
```bash 
cp .env.dist .env
```
- Fill required values by instruction inside .env

### 2. Generate the RSA keys for JWT: 
```bash
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

Update JWT_PASSPHRASE setting in .env file

### 3. Start Containers and install dependencies 
On Linux/Windows:
```bash
docker-compose up -d
```
On MacOS:
```bash
docker-sync-stack start
```

### 4. Install vendors
```bash
docker-compose exec php composer install
```

### 5. Run migrations, install required default fixtures
```bash
docker-compose exec php bin/console doctrine:migrations:migrate
docker-compose exec php bin/console doctrine:fixtures:load
```

### 6. Access to the admin panel:
Create and promote super admin user: 
```bash
docker-compose exec php bin/console app:user:create admin@domain.com admin@domain.com password --super-admin
```

Promote an existing user: 
```bash
docker-compose exec php bin/console fos:user:promote --super username ROLE_ADMIN
```

Available roles: 
- ROLE_ADMIN
- ROLE_READ_ONLY_USER
- ROLE_SUPER_ADMIN

### 7. Open project
Just go to [http://localhost](http://localhost)

### 8. Open API DOC
Auth by admin and go to [http://localhost/api/doc](http://localhost/api/doc)

## Tests
Run migrations
```bash
docker-compose exec php bin/console doctrine:migrations:migrate --env=test
```

```bash
docker-compose exec php bin/codecept build
docker-compose exec php bin/codecept run unit
docker-compose exec php bin/codecept run api
```

## Contribution

#### PHP Static Analysis Tool

```bash
docker-compose exec php vendor/bin/phpstan analyse   
docker-compose exec php vendor/bin/psalm --show-info=false
``` 

#### Coding standard

Using php-cs-fixer

```bash
docker-compose exec php vendor/bin/php-cs-fixer fix 
```
