Caesar
===========
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

### 2. Update .env:
- Create a config file .env by .env.dist
- Fill required values by instruction inside .env

### 3. Install vendors
```bash
docker-compose exec php composer install
```

### 4. Run migrations, install fixtures
```bash
docker-compose exec php bin/console doctrine:migrations:migrate
docker-compose exec php bin/console doctrine:fixtures:load
```

### 5. Generate the SSH keys for JWT: 
```bash
mkdir -p var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

Update JWT_PASSPHRASE setting in .env file


### 6. Open project
Just go to [http://localhost](http://localhost)

#### Run tests:
Run migrations
```bash
docker-compose exec php bin/console doctrine:migrations:migrate --env=test
```

```bash
docker-compose exec php bin/codecept build
docker-compose exec php bin/codecept run unit
docker-compose exec php bin/codecept run api
```

#### Access to the admin panel:
Create and promote admin user: `bin/console app:user:create`

Promote an existing user: `bin/console fos:user:promote`

Available roles: 
- ROLE_ADMIN
- ROLE_READ_ONLY_USER
- ROLE_SUPER_ADMIN

Ex: `bin/console fos:user:promote username ROLE_ADMIN`

### 7. Contribution

#### PHP Static Analysis Tool

```bash
docker-compose exec php vendor/bin/phpstan analyse   
docker-compose exec php vendor/bin/psalm --show-info=false
```
