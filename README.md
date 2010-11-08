# eve-corp-portal

This is a CMS for the MMORPG [EVE Online](http://www.eveonline.com/). It is meant to be a web portal for EVE Online in-game corporations. It authorizes users with the EVE Online API, greatly reducing the time required for managing corporation members. *Important:* I no longer maintain this CMS and I will not provide support for it. I am merely posting it here hoping it may help someone.

# Features

* Authorization and access rights according to in-game roles
* Forums
* News portal
* Calendar for planning operations
* Messaging system
* Operation submission and evaluation plug-in for salvage operations
* Production management plug-in for industrial corporations
* In-game-browser friendly
* Plug-in support

# Installation

You will need a http server, a MySQL server and PHP installed. A basic LAMP stack will do just fine.

First off clone the repository. Your home directory would be a good place.

    cd ~
	git clone git://github.com/oozcitak/eve-corp-portal.git

Let us now import the database schema. First create three databases. One named `portal_core`, another named `portal_plugins` and the last one named `portal_eve`. Now import the database dumps in the `sql` directory:

	mysql portal_core < sql/core.sql
	mysql portal_plugins < sql/plugins.sql

The database dump of EVE Online is not included. The [official database](http://www.eveonline.com/community/toolkit.asp) is in MSSQL format. You need to either convert it yourself or search a bit for a community MySQL dump. Once you get the MySQL dump, import that too:

	mysql portal_eve < sql/eve.sql

Next, you need to configure PHP with the database settings. Open `core.class.php` in the `core` directory and change the database settings between lines 62-78.

Now set up your http server. For Apache your configuration will look like this. (Obviously, you need to replace server name and root path to your settings.)

    <VirtualHost *:80>
      ServerName  example.com
      DirectoryIndex index.php
      DocumentRoot /path/to/eve-corp-portal/

      <Directory /path/to/eve-corp-portal/>
        Options FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
      </Directory>
    </VirtualHost>

You can now restart your http server and if all goes well, you can log in to the portal. For your first login use the username `admin` and `password `admin`. Once you log in, go to your profile page and change your password.
	
# Disclaimer

# License
