# Prepare Your Workstation
1. [Download Virtualbox](https://www.virtualbox.org/wiki/Downloads)

2. Download Vagrant:
    - [Vagrant for Windows]

    - Vagrant for OSX and [Brew]:
        - You may have to tap the keg before you're able to install.
        
                $ brew install Caskroom/cask/vagrant
    
    - Vagrant for Linux
    
            $ sudo apt-get vagrant

3. Enable SSH Agent Forwarding
    - Example `~/.ssh/config` entry
    
                Host vagrant
                    User vagrant
                    HostName 127.0.0.1
                    Port 2222
                    IdentityFile ~/.ssh/id_rsa

    - You must use a SSH key that has a passphrase for forwarding to work. To modify an existing key use
    
            $ ssh-keygen -p -f /path/to/key
    
    - An SSH Agent must remain [open](http://www.phase2technology.com/blog/running-an-ssh-agent-with-vagrant/) while interacting with the VM.

4. Clone the Repository
    - Navigate to the folder you want to have the project in (the `ROOT`).
    - Clone the project:
    
            $ git clone git@bitbucket.org:oumishealthandfitness/php-backend.git .

    - Now you're ready to setup your Vagrant machine.
        - This README assumes that you'll be developing on a vagrant machine.
        - If you would rather test on your local host machine (without vagrant) note that
        the domains listed here are going to differ from what comes standard.
        - Check out the Environments section for more details.

# Vagrant Setup
Used to setup up consistent environments for local testing and deployment to production.

Definitions:

    - ROOT   = the projects root directory.
    - DOMAIN = the domain you've configured to test the project.

1. Configure
    - In the project's root directory open `ROOT/Vagrantfile`
        - Ensure that the settings for *vb.memory*, *vb.cpus*, and *config.vm.network* won't conflict with your current setup.
    - Open `ROOT/provision/bootstrap.sh`
        - Note the *SQL_PASSWORD* sh variable at the top. You may change as needed. This will later be used when configuring symfony.
    - Open `ROOT/provision/apache-vhost.conf`
        - The line that has *ServerName* followed by a domain name is what this document refers to as DOMAIN.
        - Change *DOMAIN* as needed.
        - Copy *DOMAIN*, open your 'hosts' file (location varies based on operating system), and point *DOMAIN* to the IP listed next to *config.vm.network "private_network"* in `ROOT/Vagrantfile`

2. Create the VM

    While in ROOT type
    
        $ vagrant up

    
    This process may take some time to complete depending on your processor and internet speeds as it is downloading a Ubuntu image and installing the project's OS dependencies.

3. Connect to the VM

    While in ROOT type:

        $ vagrant ssh

    After you're connect to the VM open:
    
        $ sudo vim /etc/apache2/apache2.conf
    
    
    Find the following block
    
        <Directory /var/www/>
            Options Indexes FollowSymLinks
            AllowOverride None
            Require all granted
        </Directory>

    Comment out the AllowOverride directive
    
        <Directory /var/www/>
            Options Indexes FollowSymLinks
            # AllowOverride None
            Require all granted
        </Directory>
    
        
    Restart apache for the changes to take effect
    
        $ sudo service apache2 restart

4. Setup Symfony
    - Go into the symlink-ed folder on the VM:
    
            $ cd /var/www && composer update
    
    - After downloading/updating the project's dependencies, the symfony setup script will run.
    - Pay attention to the prompts. If you intend on the program using root's credentials, when prompted for *database_password*, provide the value *SQL_PASSWORD* from the `ROOT/provision/bootstrap.sh` file mentioned earlier.
    - Setup program specific configurations using

            $ php app/console moop:health:setup
    
      Which will setup the API system; creating the database tables along with basic goals.

# Setting up the Project

1. Make an API call.
    - At this point you should be able to test the [API](http://api.health.moop.stage/app_stage.php/v1/group.json). You will know if you were successful if JSON is printed on the screen.
    - If you receive a white screen with a message *You are not allowed to access this file. Check...*:
        - Open `ROOT/web/app_dev.php`
        - Perform some debugging to determine your `REMOTE_ADDR`.
        - Replace `192.168.33.1` with your own REMOTE_ADDR`.

2. Changing Project Variables:
    - At this point it's time to setup the FatSecret APIs.
        Open `ROOT/app/config/config.yml` and find:
        
                parameters:
                    domain:   ~
                    moop.fat_secret.consumer_key:    ~
                    moop.fat_secret.consumer_secret: ~
        
    - Fill in the domain the project is using in production. IE: healthawarenesscoalition.org
    - You need to [register](http://platform.fatsecret.com/api/Default.aspx?screen=r) an account with FatSecret in order to obtain your application's *consumer\_key/consumer\_secret*. Fill in these values with the ones listed in the developer portal.
    - If you do not plan on using Redis as a caching service change:
    
        `cache_provider_type: redis` to `cache_provider_type: array`



# Environments
Definitions:

    - Dev   = Developing on your local machine without vagrant.
    - Stage = Development using a vagrant box.
    - Prod  = The application in the wild.

Knowing which environment you wish to use is key, as they all use different
hostnames to distinguish themselves from one another. You can find which
environment uses what hostname in the various `config.yml` files.

For example to determine the hostname used on dev I'll look in

        ROOT/app/config/config_dev.yml

Stage

        ROOT/app/config/config_stage.yml

And Production

        ROOT/app/config/config.yml

The URLs you are using should match up with the URLs found in the mobile app's
app-botstrap.js file. If you decide to swap development environments,
the mobile app's `ApiEndpoint` must point to the new environment's hostname.



# Knowledge Bases:
- [Cordova](http://cordova.apache.org/docs/en/5.0.0/)
- [Vagrant](http://docs.vagrantup.com/v2/getting-started/)
- [GulpJs](http://gulpjs.com/)
- [Ripple Emulator](https://www.npmjs.com/package/ripple-emulator) Browser based emulation.
- [Weinre](http://people.apache.org/~pmuellr/weinre-docs/latest/) Hardware based emulation.
- [Less](http://lesscss.org/)
- [FatSecret API](http://platform.fatsecret.com/api/Default.aspx?screen=rapih)
- [AngularJS](https://angularjs.org/)
- [jQuery](https://api.jquery.com/)
- [Symfony](https://symfony.com/)
- [Apache](http://httpd.apache.org/)
- [Bootstrap](http://getbootstrap.com/getting-started/)




[Vagrant for Windows]:http://www.vagrantup.com/downloads.html
[Brew]:http://brew.sh/
[Composer]:https://getcomposer.org/download/
