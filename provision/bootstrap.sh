#!/usr/bin/env bash

SQL_PASSWORD="pass"

echo ">> Starting VM provisioner..."
sudo apt-get -qq update

echo ">>> Installing server dependencies..."
sudo apt-get install -y make curl wget vim htop unzip autoconf automake git > /dev/null 2>&1

echo ">>> Installing MySQL dependencies..."
sudo apt-get install -y debconf-utils > /dev/null 2>&1

# Sets the MySQL password.
debconf-set-selections <<< "mysql-server mysql-server/root_password password $SQL_PASSWORD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $SQL_PASSWORD"

echo ">>> Installing Apache / Redis / MySQL / PHP.."
sudo apt-get install -y apache2 redis-server mysql-server php5 > /dev/null 2>&1

echo ">>> Installing PHP mods..."
sudo apt-get install -y php5-mysql php5-redis php5-curl php5-gd php5-mcrypt > /dev/null 2>&1

if ! [ -L /etc/apache2/sites-enabled/apache-vhost.conf ]; then
  echo ">>> Creating Apache Virtual Host..."
  
  sudo rm -rf /var/www/html
  sudo rm -f  /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf > /dev/null 2>&1
  sudo cp /var/www/provision/apache-vhost.conf /etc/apache2/sites-available/apache-vhost.conf > /dev/null 2>&1
  
  sudo a2enmod rewrite         > /dev/null 2>&1
  sudo a2ensite apache-vhost   > /dev/null 2>&1
  sudo service apache2 restart > /dev/null 2>&1
fi

if ! [ -L /usr/bin/composer.phar ]; then 
  echo ">>> Installing Composer..."
  
  sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin > /dev/null 2>&1
  cp /var/www/provision/composer /usr/local/bin/composer
fi

echo ">> Provisioning complete."
