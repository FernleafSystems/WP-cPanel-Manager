=== cPanel Manager (from iControlWP) ===
Contributors: paultgoodchild, dlgoodchild
Donate link: http://www.icontrolwp.com/
Tags: cPanel, manage
Requires at least: 3.2.0
Tested up to: 4.9
Stable tag: 1.8.2

== Description ==

The cPanel Manager plugin from [iControlWP: Secure Multiple WordPress Management](http://icwp.io/70 "iControlWP: Secure Multiple WordPress Management")
offers you the ability to connect to your Web Hosting cPanel account.

Currently you can:

= Email Accounts =

*	View a list of all email accounts for all domains.
*	Add a new email account to any domain
*	Delete multiple email accounts.

= MySQL =

*	View a list of all MySQL database and their attached users
*	Download any MySQL database in your cPanel account directly from this plugin
*	Add a new MySQL database and a new MySQL user to that database
*	Add a new MySQL user
*	Delete MySQL databases from your cPanel account IN BULK - take GREAT CARE
*	Delete MySQL users from your cPanel account IN BULK - take GREAT CARE

= FTP =

*	View a list of all FTP users
*	Add a new FTP user
*	Add new FTP users IN BULK
*	Delete FTP users IN BULK (with option to remove ftp directories)

= Sub Domains =

*	View all sub domains along with their full document root path and their redirection status
*	Add a new sub domain
*	Delete sub domains IN BULK

= Parked Domains =

*	View all Parked Domains along with their full document root path and their redirection status
*	Add a new Parked Domain
*	Delete Parked Domains IN BULK

= Addon Domains =

*	View all Addon Domains along with their full document root path and their redirection status
*	Add a new Addon Domain and associated FTP user.
*	Delete Addon Domains IN BULK

With it (in time) you will be able to perform many convenient functions from within your WordPress site that
you would otherwise need to log into your cPanel to do.

Currently, with the initial release it will list your databases, your database users, yours parked and addon domains
and also your crons.

== Frequently Asked Questions ==

= Where can I find my Security Access Key? =

This is a secret key/password that YOU CREATE. It protects and restricts access to your cPanel username/password.

= Is it secure to have my cPanel login credentials in my WordPress? =

Normally, no. But with version 1.1 of the plugin, if you have the 'mcrypt' PHP library available on your web hosting
your cPanel credentials will always be encrypted before being stored in your WordPress database.

= What if I don't have the 'mcrypt' PHP extensions? =

You will have a permanent notice on the plugin's admin pages telling you of this. The plugin will function as normal
but your details will not be encrypted.

= What is the Security Access Key? =

This is basically an encryption salt/password. We use this to encrypt and decrypt your cPanel username and password.

= What if I forget the Security Access Key? =

Simply click the orange 'Reset' button on the plugin's security page. This will delete the current security access key,
the stored cpanel username and the stored cpanel password.

You will then need to supply a new Security Access Key (as you would have at the beginning) before adding any new cPanel information. 

= What is the CONFIRM box all about? =

As you can imagine with great power comes great responsibility. This plugin lets you delete databases and users in bulk.

So, before I can let you do that, you must type in the word CONFIRM exactly as it is, in capital letters, each time you want
to perform a task. If you don't, the task will fail.

This is a small protection against accidental clicks etc. If you accidently delete all your databases and
you want to blame someone, you know that it could only have been done by typing CONFIRM and submitting the task. There's NO
way around this.

= Can I undo my delete of databases and users? =

No!

Use the MySQL database delete and MySQL user delete functionality with GREAT CARE. You are wholly responsible for any mess you create. 

= Where is all the documentation? =

The cPanel Manager is very easy to use right now because there isn't much functionality.

But, documentation is coming. I wanted to get this work out to the public first, [get feedback](http://www.icontrolwp.com/help-support/?src=wporg "iControlWP: Manage Muliple WordPress Sites Better")
and then move on from there.

= Do you make any other plugins? =

Yes, we created the only [Twitter Bootstrap WordPress](http://www.icontrolwp.com/wordpress-twitter-bootstrap-css-plugin-home/ "Twitter Bootstrap WordPress Plugin")
plugin with over 20,000 downloads so far.

We also created the [Manage Multiple WordPress Site Better Tool: iControlWP](http://www.icontrolwp.com/?src=wporg) for people with multiple WordPress sites to manage.

== Changelog ==

= TODO =

* Remove some hardcoding for Jump-links and from the cPanel API itself (server port)
* add cron job management
* add file handling and file manager jump links
* add ability to perform FULL cPanel backup and FTP offsite
* add ability to perform cPanel backup of MySQL databases and FTP offsite
* use ajax calls to load only necessary data to reduce API calls.

= 1.8.2 =

* FIX: Compatibility with PHP >= 5.6

= 1.8.1 =

* FIX: Compatibility with WordPress 4.5

= 1.8 =

* FIX: Compatibility with PHP 5.4 and other errors
* FIX: Compatibility with WordPress 3.9+

= 1.7 =

* ADDED: Addon Email management - lists Emails / add new email accounts / delete email accounts.

= 1.6 =

* ADDED: Addon Domain management - lists Addon domains / add new Addon domains / delete Addon domains.

= 1.5 =

* ADDED: Parked Domain management - lists parked domains / add new parked domains / delete parked domains.
* CHANGED: Combined the parked domains and the sub domains into 1 tab (fewer cPanel API calls)

= 1.4 =

* ADDED: Sub Domain management - lists sub domains / add new sub domains / delete sub domains.

= 1.3 =

* ADDED: Ability to add new FTP users in Bulk using comma-separate list of 'username, password, disk-quota'

= 1.2 =

* ADDED: Ability to add new FTP Users
* ADDED: Ability to delete multiple FTP users account.
* ADDED: Jump link to phpMyAdmin for your hosting account.
* REMOVED: Parked/Addon Domains and Crons tabs until their ready.

= 1.1 =

* ADDED: Encryption mechanism of sensitive cPanel data through use of a Security Access Key. REQUIRES: PHP mcrypt library extension to be loaded.
* ADDED: Permanent warning message if you don't have the mcrypt library extension loaded and your data isn't encrypted.
* CHANGED: Regardless of whether you can encrypt your data or not, cPanel username and password are serialized before being stored to WP DB.

= 1.0 =

* First Release.

== Upgrade Notice ==

= 1.7 =

* ADDED: Addon Email management - lists Emails / add new email accounts / delete email accounts.
