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

We now need to setup cron jobs for the portal. Those jobs are used to periodically fetch information from EVE Online API. Edit your cron jobs with:

    crontab -e

Add the following lines. Remember to change the paths.

    01 *  *   *   *     php /path/to/eve-portal/core/cron/H00.cron.php > /dev/null 2>&1
    31 *  *   *   *     php /path/to/eve-portal/core/cron/H30.cron.php > /dev/null 2>&1
    01 00 *   *   *     php /path/to/eve-portal/core/cron/D00.cron.php > /dev/null 2>&1
    01 11 *   *   *     php /path/to/eve-portal/core/cron/D11.cron.php > /dev/null 2>&1
    01 12 *   *   *     php /path/to/eve-portal/core/cron/D12.cron.php > /dev/null 2>&1
    01 03 *   *   1     php /path/to/eve-portal/core/cron/W1.cron.php > /dev/null 2>&1
    01 03 *   *   3     php /path/to/eve-portal/core/cron/W2.cron.php > /dev/null 2>&1
    01 03 *   *   5     php /path/to/eve-portal/core/cron/W3.cron.php > /dev/null 2>&1
    01 03 *   *   6     php /path/to/eve-portal/core/cron/W4.cron.php > /dev/null 2>&1
    01 03 *   *   7     php /path/to/eve-portal/core/cron/W5.cron.php > /dev/null 2>&1

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

You can now restart your http server and if all goes well, you can log in to the portal. For your first login enter username `admin` and password `admin`. Once you log in, go to your profile page and change your password.

You now need to configure the director API key. First create a character in game and give it director rights. Now go to the [EVE Online API page](http://www.eveonline.com/api/default.asp) and copy the API key for this character. Go to the `Portal Administration` page, select `Settings` and paste your API key here. Your corporation and member information will be fetched using this API key. It may take up to 24 hours for the changes to take effect. While here, also set your corporation name and links to killboard and alliance pages.

As a last step you will probably want to change the default logo in `css/logo.jpg` to your corporation's logo.

You can now announce your brand new corp portal to your members. Once they login with their API keys, they will have access to the portal with their in-game roles, character names and portraits fetched from the API.

# Disclaimer

I am not affiliated with [EVE Online](http://www.eveonline.com/) or [CCP Games](http://www.ccpgames.com/). Also, I no longer maintain this project and will not provide support for it. If you are stuck, you are on your own.

# License

This CMS is licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php). Some text and images used in the project are properties of [CCP Games](http://www.ccpgames.com/). Please read and abide by the EVE Online [terms of service](http://www.eveonline.com/pnp/terms.asp), [EULA](http://www.eveonline.com/pnp/eula.asp) and [website terms of use](http://www.eveonline.com/pnp/termsofuse.asp).