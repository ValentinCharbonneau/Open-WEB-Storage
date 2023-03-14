# Installation of PHP 8.1 for Apache2 on Debian

## Update
First of all, execute the following command :
```
sudo apt update
```

## Dependencies
You need to install dependencies :
```
sudo apt install ca-certificates apt-transport-https software-properties-common wget curl lsb-release
```

## Add repository
To add repository of PHP 8.1 to apt :
```
curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
sudo apt update
```

## Installation
Install PHP 8.1
```
sudo apt install php8.1
```

Install apache2 module :
```
sudo apt install libapache2-mod-php8.1
```

Install extentions :
```
sudo apt install php8.1-fpm php8.1-cli php8.1-common php8.1-curl php8.1-bcmath php8.1-intl php8.1-mbstring php8.1-xmlrpc php8.1-mcrypt php8.1-mysql php8.1-gd php8.1-xml php8.1-zip libapache2-mod-fcgid
```

Enable apache modules :
```
sudo a2enmod php8.1
sudo a2enmod proxy_fcgi setenvif 
sudo a2enconf php8.1-fpm
```

Restart apache :
```
sudo systemctl restart apache2
```