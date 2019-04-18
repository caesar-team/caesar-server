# Application Environment
## Required variables
### Database variables
> DATABASE_HOST

The host name of the database to which the application will be connected.
> DATABASE_DRIVER

The type of database driver that will be used by the database.
> POSTGRES_DB

The name of the database to be used by the application.
> POSTGRES_USER
> POSTGRES_PASSWORD

The user's credentials on whose behalf the application will access the database

> DATABASE_URL

Example|preset: `DATABASE_URL=${DATABASE_DRIVER}://${POSTGRES_USER}:${POSTGRES_PASSWORD}@${DATABASE_HOST}:${DATABASE_PORT}/${POSTGRES_DB}`
The database url should be compiled as recommended in the official instruction doctrine.
[https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url)
