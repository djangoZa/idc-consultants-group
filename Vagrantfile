Vagrant.configure("2") do |config|
  config.vm.box = "precise64"
  config.vm.provision :shell, :path => "bootstrap.sh"
  config.vm.synced_folder "/Users/mikejohnclarke/Dropbox", "/vagrant/dropbox"
  config.vm.network :forwarded_port, host: 4567, guest: 80
end
