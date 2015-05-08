# Vagrant Setup
Used to setup up consistent environments for local testing and deployment to production.

Definitions:

    - ROOT   = the projects root directory.
    - DOMAIN = the domain you've configured to test the project.

1. [Download Virtualbox](https://www.virtualbox.org/wiki/Downloads)

2. Download Vagrant
   - [Vagrant for Windows]
   - Vagrant for OSX and [Brew]:
       - You may have to tap the keg before you're able to install.

        `brew install Caskroom/cask/vagrant`

   - Vagrant for Linux
   
        `sudo apt-get vagrant`
        
3. Configure
    - In the project's root directory open `ROOT/Vagrantfile`
        - Ensure that the settings for *vb.memory*, *vb.cpus*, and *config.vm.network* won't conflict with your current setup.
    - Open `ROOT/provision/bootstrap.sh`
        - Note the *SQL_PASSWORD* sh variable at the top. You will use this later when configuring symfony. Change as needed.
    - Open `ROOT/provision/apache-vhost.conf`
        - The line that has *ServerName* followed by a domain name is what this document reffers to as DOMAIN.
        - Change *DOMAIN* as needed.
        - Copy *DOMAIN*, open your 'hosts' file (location varies based on operating system), and point *DOMAIN* to the IP listed next to *config.vm.network "private_network"* in `ROOT/Vagrantfile`

4. Create the VM

    While in ROOT type
    
    ```bash
    $ vagrant up
    ```

    
    This process may take some time to complete depending on your processor and internet speeds as it is download the Ubuntu image and all the project's dependencies.

5. Connect to the VM

    While in ROOT type:

    ```bash
    $ vagrant ssh
    ```

    After you're connect to the VM open:
    
    ```bash
    $ sudo vim /etc/apache2/apache2.conf
    ```
    
    Find the following block
        
    ```apache
    <Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
    ```

    Comment out the AllowOverride directive
        
    ```apache
    <Directory /var/www/>
        Options Indexes FollowSymLinks
        # AllowOverride None
        Require all granted
    </Directory>
    ```
        
    Restart apache for the changes to take effect

    ```bash        
    $ sudo service apache2 restart
    ```

6. Setup Symfony
    - Using [Composer] type while in ROOT:

        ```bash        
        $ composer update
        ```
        
    - After downloading/updating the project's dependencies, the symfony setup script will run.
    - Pay attention to the prompts. If you intend on the programming using root's credentials, when prompted for *database_password*, provide the value *SQL_PASSWORD* from the `ROOT/provision/bootstrap.sh` file mentioned earlier.
    - Seteup program specific configurations using

        ```bash    
        $ php app/console moop:health:setup
        ```
    
    
        Which will setup the API system with some basic goals as well as create the database tables.

# Setting up the Project

1. Make an API call.
    - At this point you should be able to test the [API](http://api.mis-health.dev/v1/group.json). You will know if you were successful if JSON is printed on the screen.
    - If you receive a white screen with a message *You are not allowed to access this file. Check...*:
        - Open `ROOT/web/app_dev.php`
        - Perform some debugging to determine your `REMOTE_ADDR`.
        - Replace `192.168.33.1` with your own REMOTE_ADDR`.

2. Changing Project Variables:
    - At this point it's time to setup the FatSecret APIs.
        Open `ROOT/app/config/config.yml` and find:
        
        >       parameters:
        >           domain:   ~
        >           moop.fat_secret.consumer_key:    ~
        >           moop.fat_secret.consumer_secret: ~
    - Fill in the domain the project is using in production. IE: healthawarenesscoalition.org
    - You need to [register](http://platform.fatsecret.com/api/Default.aspx?screen=r) an account with FatSecret in order to obtain your application's consumer_key/consumer_secret. Fill in these values with the ones listed in the developer portal.
    - In the same file, find the line `cache_provider_type: redis`
        - If you do not plan on using Redis as a caching service change this value to `array`.









[Vagrant for Windows]:http://www.vagrantup.com/downloads.html
[Brew]:http://brew.sh/
[Composer]:https://getcomposer.org/download/