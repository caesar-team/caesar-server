Caesar
==========
## Requirements

* [Docker and Docker Compose](https://docs.docker.com/engine/installation)
* [MacOS Only]: Docker Sync (run `gem install docker-sync` to install it)

## Installation

### 1. Start Containers and install dependencies 
On Linux:
```bash
docker-compose up -d
```
On MacOS:
```bash
docker-sync-stack start
```
### 2. Install vendors
```bash
docker-compose exec php composer install
```
### 3. Run migrations, install fixtures
```bash
docker-compose exec php bin/console doctrine:migrations:migrate
docker-compose exec php bin/console doctrine:fixtures:load
```

### 4. Generate the SSH keys for JWT: 
```bash
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

### 5. Update .env:
- Create a config file .env by .env.dist
- Fill required values by instruction inside .env

### 6. Open project
Just go to [http://localhost](http://localhost)

####Run tests:
Reveal `TEST_DATABASE_URL` from .env
```bash
APP_ENV=test vendor/bin/phpunit -d memory_limit=-1 #Phpunit
```

