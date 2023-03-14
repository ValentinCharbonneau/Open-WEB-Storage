# Open WEB Storage V1.0.0
Open WEB Storage is an open source project to store and encrypt your documents.

## Prerequisites
- Designed for Debian 11 Bullseye and another distribution based on Debian
- OpenSSL
- MariaDB/MySQL, PostgreSQL, or SQLite database
- Apache 2
- [PHP 8.1](https://github.com/ValentinCharbonneau/Open-WEB-Storage/tree/v1.0.0/readme/php81.md) and the following modules:
    - curl
    - xml
    - intl
    - mbstring
    - pdo_mysql, pdo_pgsql, or pdo_sqlite depending on your database server
- [Composer 2.4](https://github.com/ValentinCharbonneau/Open-WEB-Storage/tree/v1.0.0/readme/composer.md) or higher

## PHP extensions
To install Open WEB Storage, you need to install the PHP8 extensions with the following commands
```
sudo apt update
```
```
sudo apt install php8.1 php8.1-curl php8.1-intl php8.1-mbstring php8.1-xml php8.1-opcache
```
If you use MariaDB/MySQL
```
sudo apt install php8.1-pdo-mysql
```
If you use PostreSQL
```
sudo apt install php8.1-pdo-pgsql
```
If you use SQLite
```
sudo apt install php8.1-pdo-sqlite
```

## Apache modules
To install Open WEB Storage, you need to enable the Apache negotiation and rewrite modules with the following commands :
```
sudo a2enmod negotiation
sudo a2enmod rewrite
```

## Apache vhost
Open WEB Storage has its own .htaccess file.
In your vhost configuration file you need to precise ("/path/to/project/public" correspond to path of folder "public" in project) :
```
DocumentRoot /path/to/project/public
DirectoryIndex /index.php

<Directory /path/to/project/public>
    AllowOverride None
    Order Allow,Deny
    Allow from All

    FallbackResource /index.php
</Directory>
```

## Install dependencies
To install multiples dependencies of OpenWEB Storage, we just need to execute the following command :
```
composer install
```

## Database connexion and build
By default, Open WEB Storage is configured to work with a SQLite database, but that wrong on production environment.
User need to have the following right on database :
```
CREATE, ALTER, SELECT, INSERT, UPDATE, DELETE
```

If you use MariaDB/MySQL or PostreSQL, you first need to create a database and a user.
After that, you can configure the database connexion of Open WEB Storage on .env file :
First of all, comment the following line, its line to use SQLite database :
```
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

### MariaDB/MySQL database
Then, if you use MariaDB/MySQL, you need to uncomment and modify the following line :
```
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/db?serverVersion=8&charset=utf8mb4"
```
Replace :
- "app" by the username of the MariaDB/MySQL user
- "!ChangeMe!" by the password of the MariaDB/MySQL user
- "127.0.0.1" by the ip address or domain name of your MariaDB/MySQL server
- "3306" by the port of your MariaDB/MySQL server
- "db" by the database name

### PostgreSQL database
Else, if you use PostgreSQL, you need to uncomment and modify the following line :
```
# DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/db?serverVersion=15&charset=utf8"
```
Replace :
- "app" by the username of the PostgreSQL user
- "!ChangeMe!" by the password of the PostgreSQL user
- "127.0.0.1" by the ip address or domain name of your PostgreSQL server
- "5432" by the port of your PostgreSQL server
- "db" by the database name

Now you can build the database with the following commande :
```
php bin/console doctrine:schema:update --force
```

## Generate keypair
To generate key for bearer token authentication, you need to execute this command :
```
php bin/console lexik:jwt:generate-keypair
```

## Warmup cache
To warmup cache, execute the following command :
```
php bin/console cache:warmup
```

## Build storage system
Now Open WEB Storage is installed, but first of all you need to build folders used by Open WEB Storage to store documents.
For this you juste need to execute the following command :
```
php bin/console ows:build-tree
```

## Fixtures
If you want to build fixtures and sample of data and documents in project, you can use the following command :
```
php bin/console ows:build-fixtures
```

## Create an admin user
If you want to create an user, you can use the following command :
```
php bin/console ows:create-user
```
This command asks you if you want to assign administrator role to new user.

## Installation was finished !
Now you can use the Open WEB Storage application. If you need more information, go to the root url of your recent installation of Open WEB Storage to consult the documentation.
