### Scope

This is a script design to parse an RSS feed (specifically [http://www.romatsa.ro](http://www.romatsa.ro)'s) crawl the latest article, and if it's a new article (different title from the last) then save the new data and write an email informing people about it.

This script was designed to run as a cron job from the command-line. It is not intended to be from from a web server / accessed from a browser. If you need it though, make sure to remove the `deny` rule from `.htaccess`

This was designed as to keep up with ROMATSA's new job openings and scheduling updates.

---------------------------------------

### File Permisssions

As it is intended to run from the command line, it is assumed that you have write access to all folders. In the event of running this from a browser, make sure your webserver user / group has write access to the following folder

	data/

---------------------------------------

### Config

The `config.php` file contains some variables / CONSTANTS that need to be changed to adapt to your needs. You can set the json file location and name, RSS feed url, and contact emails.

If you need to add multiple emails, just continue adding arrays, as the script will loop though.

---------------------------------------

### Gmail Warning

Gmail really hates when you send html format email to it's accounts, especially if it's from a poorly configured email server, or a server which is not suposed to handle email at all (eg: web server).

So if you're mail doesn't show up, first thing you can try is to make the mailer send plaintext instead. (`Content-Type: text/plain;`)