#!/usr/bin/env bash

#TMP_DIR="/home/vagrant"

SQL_PASSWORD="m1sW1n"

echo ">> Starting VM provisioner..."
sudo apt-get update > /dev/null

echo ">>> Installing server dependencies..."
sudo apt-get install -y curl wget vim htop unzip autoconf automake git npm > /dev/null

echo ">>> Installing emulator depenencies..."
sudo npm -g install cordova less ripple-emulator

echo ">>> Installing MySQL dependencies..."
sudo apt-get install -y debconf-utils > /dev/null

# Sets the password to SQL_PASSWORD.
debconf-set-selections <<< "mysql-server mysql-server/root_password password $SQL_PASSWORD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $SQL_PASSWORD"

echo ">>> Installing Apache / Redis / MySQL / PHP.."
sudo apt-get install -y apache2 redis-server mysql-server php5 > /dev/null

echo ">>> Installing PHP mods..."
sudo apt-get install -y php5-mysql php5-redis php5-curl php5-gd php5-mcrypt  > /dev/null

if ! [ -L /etc/apache2/sites-enabled/apache-vhost.conf ]; then
  echo ">> Creating Apache Virtual Host..."
  
  sudo rm -rf /var/www/html
  sudo rm -f  /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf > /dev/null
  sudo cp /var/www/project/provision/apache-vhost.conf /etc/apache2/sites-available/. > /dev/null
  
  sudo a2enmod rewrite         > /dev/null
  sudo a2ensite apache-vhost   > /dev/null
  sudo service apache2 restart > /dev/null
fi

echo ">>> Installing Composer..."
sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin
sudo cp /var/www/project/provision/composer /usr/bin/.
sudo chmod 777 /usr/bin/composer.phar /usr/bin/composer

echo ">> Provisioning complete."
