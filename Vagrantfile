Vagrant.configure("2") do |config|
  	config.vm.box = "precise64"
  	config.vm.provision :shell, :path => "bootstrap.sh"
  	config.vm.network :forwarded_port, host: 4567, guest: 80
	#config.vm.synced_folder "/Users/mikejohnclarke/Dropbox", "/vagrant/dropbox"
	config.vm.synced_folder "C:\\Users\\The Hiddddddddddddde\\Dropbox", "/vagrant/dropbox"
	config.vm.provider "virtualbox" do |v|
    	v.customize ['modifyvm', :id, "--memory", "1024"]
  	end
end
