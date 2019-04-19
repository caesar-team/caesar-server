
# Application Environment  
## Required variables  
> The application uses a database as a data storage
> and should be connected using the following variables
### Database (postgres v9.6) variables  

    DATABASE_HOST  
 > The host name of the database to which the application will be connected.  

    DATABASE_DRIVER

> The type of database driver that will be used by the database.  

    POSTGRES_DB  
  
> The name of the database to be used by the application.  

    POSTGRES_USER  
    POSTGRES_PASSWORD  
  
> The user's credentials on whose behalf the application will access the database.
  
	DATABASE_URL  
  
Example|preset: `DATABASE_URL=${DATABASE_DRIVER}://${POSTGRES_USER}:${POSTGRES_PASSWORD}@${DATABASE_HOST}:${DATABASE_PORT}/${POSTGRES_DB}`  
The database url should be compiled as recommended in the official instruction doctrine.  
[https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url)

> Also was realized background processes like user notifications.
> RabbitMQ is used for this.

### RabbitMQ (v3.7.14) variables
	RABBITMQ_HOST
> The host name of the rabbitMQ server to which the application will be connected.  

	RABBITMQ_DEFAULT_USER
> RabbitMQ user which to be used to connect

	RABBITMQ_DEFAULT_PASS 
> RabbitMQ user password to connect

	#UID=1001
> Run docker build under a current user  

### App environment  
	APP_ENV  
> The application can work in three modes.
> dev  - for development server  
> prod - for production server  
> test - for tests
 
	OAUTH_ALLOWED_DOMAINS 
> Restriction by domains.

APP_SECRET
> The most popular way to protect an application is to add more entropy to encryption.
>  APP_SECRET contain an immutable random string to add more entropy.
>  Private signature to encrypt application data (Example: APP_SECRET=af21c49e35f01c1ec4d465daf75098e07a04c7ed)  

	BACKUP_CODE_SALT
> Secret hash to encrypt backup codes stored in application data base  
> (Example: BACKUP_CODE_SALT=af21c49e35f01c1ec4d465daf75098e07a04c7ed)  

	INVITATION_SALT
> Secret hash to encrypt invitations stored in application data base  
> (Example: INVITATION_SALT=af21c49e35f01c1ec4d465daf75098e07a04c7ed)  
 
	JWT_PASSPHRASE
> Is a text used to control access to a jwt token  
> Any word or random characters to add more entropy
 