=== WP PostNow ===
Contributors: tmtoy(TakumaMorikawa)
Donate link: http://blog.duffytoy.com/
Tags: mail, email, post, posts, plugin, automatic
Requires at least: 3.1.0
Tested up to: 3.5.2
Stable tag: 1.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP PostNow can encourage posts in the automated email on a regular schedule.

== Description ==

WP PostNow can encourage posts in the automated email on a regular schedule!  

It is possible setting various.  
* Delivery terms of email alerts  
* Check and Mail delivery interval  
* Mail delivery time  
* Select the FuturePosts option(on/off)  
* Send mail user  
* Add Carbon copy mail  
* Mail sender display name  
* Sender address  
* Enter the subject  
* Enter the body  
    
If you are not setting anything, a lower setting is assigned.  
(You can use it right now without any configuration)  
* Mail delivery interval (One week)  
* Check and Mail delivery interval (1day)  
* Mail delivery time (AM10:00)  
* Select the FuturePosts option(on)  
* Send mail user (Administrator)  
* Add Carbon copy mail (None)  
* Mail sender display name (WP-PostNow)  
* Sender address (postnow@yourdomain)  
* Enter the subject ('your SiteName' post is stagnated.)  
* Enter the body ('your SiteName'[ 'your siteAddress' ] Let's post the article right now)  
  
Please note that there is a possibility that can not receive the mail site address and spf record and DNS settings are not set.

= Translators =

* Japanese(ja) - [Takuma Morikawa](http://blog.duffytoy.com)

== Installation ==

1. Upload the `wp-postnow` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Open 'Settings->WP-PostNow' menu.

== Screenshots ==

1. Settings(Japanese)
2. Settings(English)

== Changelog ==

= 1.5.0 =
* Add Check and Mail delivery interval.
* Add interval display.
* Change Setting Text.
* Appended to the readme.txt

= 1.4.1 =
* Add set the time of mail send.
* Appended to the readme.txt

= 1.4 =
* English version release.
* Add Languages files.
* Add English version screenshot.
* Bug fix for localtime.

= 1.0 =
* The first release.

== Upgrade Notice ==

= 1.4 =
This version fixes a localtime related bug. Upgrade immediately.
