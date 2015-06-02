#!/usr/bin/env bash

ENVIRONMENT="stage"
MOUNT_POINT="/var/www"
PROVISION_PATH="$MOUNT_POINT/provision/env/$ENVIRONMENT"

echo ">> Starting VM provisioner..."
sudo apt-get -qq update

echo ">>> Copying custom cli commands..."
cp ${MOUNT_POINT}/provision/bin/* /usr/local/bin/.

echo ">>> Installing server dependencies..."
sudo apt-get install -y make curl wget vim htop unzip autoconf automake git > /dev/null 2>&1

#echo ">>> Installing MySQL dependencies..."
#sudo apt-get install -y debconf-utils > /dev/null 2>&1

# Sets the MySQL password.
#debconf-set-selections <<< "mysql-server mysql-server/root_password password $SQL_PASSWORD"
#debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $SQL_PASSWORD"

echo ">>> Installing Apache / Redis / PHP.."
sudo apt-get install -y apache2 redis-server php5 > /dev/null 2>&1

echo ">>> Installing PHP mods..."
sudo apt-get install -y php5-redis php5-curl php5-gd php5-mcrypt php5-intl > /dev/null 2>&1

if ! [ -L /etc/apache2/sites-enabled/apache-vhost.conf ]; then
  echo ">>> Creating Apache Virtual Host..."
  
  sudo rm -rf /var/www/html
  sudo rm -f  /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf > /dev/null 2>&1
  sudo cp ${PROVISION_PATH}/apache-vhost.conf /etc/apache2/sites-available/apache-vhost.conf > /dev/null 2>&1
  
  sudo a2enmod rewrite         > /dev/null 2>&1
  sudo a2ensite apache-vhost   > /dev/null 2>&1
  sudo service apache2 restart > /dev/null 2>&1
fi

if ! [ -L /usr/bin/composer.phar ]; then 
  echo ">>> Installing Composer..."
  
  sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin > /dev/null 2>&1
  
  echo ">>> Updating PHP Libraries..."
  cd ${MOUNT_POINT}
  composer.phar update
fi

echo ">> Provisioning complete.\n\n"

echo "Run the following yourself:"
echo "  sudo apt-get install mysql-server php5-mysql"
