# Installation of composer on Debian

## Update
First of all, execute the following command :
```
sudo apt update
```

## Dependencies
You need to install dependencies :
```
sudo apt install curl php8.1-cli php8.1-mbstring git unzip
```

## Installation
Download the installer :
```
curl -sS https://getcomposer.org/installer -o composer-setup.php
```

Download the hash :
```
HASH=`curl -sS https://composer.github.io/installer.sig`
```

Execute the installation :
```
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
```

Install globally :
```
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

## Composer is now installed in your machin !
You can take the following command to verify :
```
composer
```