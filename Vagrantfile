# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # This requires a password on your private RSA key. To add a password, use the command below.
  # ssh-keygen -p -f /path/to/private
  config.ssh.forward_agent = true
  config.ssh.insert_key    = false
  config.vm.box            = "ubuntu/trusty64"
  
  # Disable the default /vagrant synced folder
  config.vm.synced_folder ".", "/vagrant", disabled: true
  
  config.vm.provider "virtualbox" do |provider, override|
      #provider.gui        = true
      provider.memory      = 2048
      provider.cpus        = 2;
      
      provider.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant", "1"]
      
      override.vm.hostname = "stage"
      override.vm.provision :shell, path: "provision/env/stage/bootstrap.sh"
      override.vm.network "forwarded_port", guest: 80, host: 8080
      override.vm.network "private_network", ip: "192.168.33.14"
      override.vm.synced_folder ".", "/var/www", {:mount_options => ['dmode=777','fmode=777']}
  end
  
  config.vm.provider :digital_ocean do |provider, override|
      override.ssh.private_key_path = '~/.ssh/rsa_id'
      override.ssh.username         = "ashinpaugh"
      override.vm.box               = "digital_ocean"
      override.vm.box_url           = "https://github.com/smdahlen/vagrant-digitalocean/raw/master/box/digital_ocean.box"
      
      provider.setup  = true
      provider.token  = "1914b60fed8f26195a9427a2206a2391de88a5f1e4b783b9a8a283dfc99a66b4"
      provider.image  = "ubuntu-14-04-x64"
      provider.region = "nyc2"
      provider.size   = "512mb"
      
      provider.vm.provision :shell, path: "provision/env/prod/bootstrap.sh"
  end
end
