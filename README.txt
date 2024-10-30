=== Plugin Name ===
Book a Room Event Calendar
Contributors: chuhpl, Maniacalv
Donate link: http://heightslibrary.org/support-your-library/
Tags: meeting room, calendar, library, bookaroom, event calendar, meetings
Requires at least: 3.0.1
Tested up to: 6.0.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Book a Room Event Calendar is a small plugin that works with the Book a Room Plugin to display the event calendar. 

== Description ==

**IMPORTANT!! I've added a new page requirement. Please create a new page on your site and add the shortcode [showReg] to it. In the settings, please add the URL to this new page.**

**Note: You need the [Book a Room plugin](https://wordpress.org/plugins/book-a-room/) to use the Event Calendar.**

This is a small plugin that works with the Book a Room plugin. Once configured, it provides the event calendar view using shortcodes. 

Use a shortcode to display a list of upcoming events with:

[showEvents start_offset="0" end_offset="7" num_offset="15"]

Start offset is how many days to start from today. Default is 0.
End offset if how many days to show after the start offset. Default is 7.
Num Offset is the number of items to display. Default is 0 (all).

[showCalendar]

This shows the full calendar.

== Installation ==

**First, upload and install the [Book a Room plugin](https://wordpress.org/plugins/book-a-room/).**

Upload and install this plugin.

Enter the database settings from the server that *Book a Room* is running on in the settings for the Book a Room Event Calendar. You can find these settings in Settings > Event Settings.

== Frequently Asked Questions ==

= Do I need to run both plugins? =

No. The Event Calendar plugin just shows the event calendar. All of the configuration and work is done in the main Book a Room plugin. We separated them in case you want to install the public facing Event Calendar on a separate subdomain or host.

== Screenshots ==

== Changelog ==
= 1.9 =
* BUGFIX: Changed how scripts were being called so that jquery works again and the popups don't error out, causing the page to fail to allow anyone to reserve.

= 1.8 =
* Added in empty checking for basedir

= 1.7 =
* Found one tag missing a php. SHould be all better.

= 1.6 =
* FOr some reason we are missing all the templates in the latest version. Trying to replace them.

= 1.5 =
* Keeping the containers but removing the clearfix css. IT was causing issues.

= 1.4 =
* Added in a container with clearfix to make things work with more themes.

= 1.3 =
* Redid the code to allow i18n and lots of bug fixes.

= 1.2 =
* Fixed the jquery css include URL which was causing mixed content issues with HTTPS

= 1.0.19 =
* Added a new option to the shortcode called num_offset. This limits the number of events shown.

= 1.0.18 = 
* Modified the event view so every event shows the new Calendar links, regardless if it needs regisration or not.

= 1.0.16 = 
* Added links on the registration success page that allow a user to add the event to iCal, Outlook or the Google Calendar.

= 1.0.15 = 
* Typo was causing the Clear button on the category area of the search page appear in the wrong place.

= 1.0.14 =
* I added a new page requirement. Please create a new page and add the shortcode [showReg] to it. In the settings, please add the URL to this new page. This fixes a bug that kept some logged out users from submitting the registration form. I also added some help text to the setting page to explain what the different options mean.

= 1.0.13 =
* Hid a few things that got shown that are for future features.

= 1.0.12 =
* Fixed some problems with certain permalink styles not working right.

= 1.0.11 =
* Added sort to the search results page (Thanks Banks!) Fixed an error when submitting an email for registration list.

= 1.0.10 =
* Fixed a small error in the new code that fixes the missing 'action' warning.

= 1.0.9 =
* Links to events on front page weren't working. Added capture for this.

= 1.0.8 =
* Fixed the missing calendar pop ups on the search page.

= 1.0.7b =
* Wordpress Repository isn't updating the version number. Trying a force fix.

= 1.0.7 =
* Changed function definitions to fix a strict standards error.

= 1.0.6 =
* Fixed problem with mcrypt error. Removed the small encrypt function and will replace with something that works with stock PHP.

= 1.0.5 =
* Added an additional check and message to the settings page. It not only checks that you can connect, it looks for a table and lets you know if it's found. (If not, the main plugin is not installed or the prefix is wrong).

= 1.0.4 =
* Added back the pop-up calendar on the Event Search page and fixed the clear buttons so they work again.

= 1.0.3 =
* Fixed the URLs that were forcing you to name your page's permalink "eventcalendar"

= 1.0 =
* First official release!
