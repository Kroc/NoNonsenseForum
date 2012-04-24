<?php //site configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v19 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'config.php' and customise to your liking,
       DON’T DELETE OR MODIFY 'config.default.php'! --- */


//uncomment this if you want to show PHP errors in the browser
#error_reporting (-1);

//if the forum should use--and force--HTTPS connections and URLs:
//if set to true, RSS feeds will be saved with HTTPS URLs, and HTTP connections will automatically be redirected to HTTPS.
//"HSTS" <en.wikipedia.org/wiki/HTTP_Strict_Transport_Security> will be used to tell clients to use HTTPS by default
//NOTE: If you change this setting, old RSS feeds will still contain HTTP URLs, but this won’t pose a problem as the HSTS
//      header will tell browsers to automatically redirect these to HTTPS
@define ('FORUM_HTTPS',		false);

//forum’s title. used in theme, and in RSS feeds
//WARNING: changing this won’t update the index RSS feed containing this name; delete 'index.xml' and then post/delete
//         a thread to regenerate the 'index.xml' file so as to see the change
@define ('FORUM_NAME',		'NoNonsense Forum');

//timezone to use for all datetimes
//this must be a string from this list: <php.net/manual/en/timezones.php>, e.g. "Europe/London"
@define ('FORUM_TIMEZONE',	'UTC');

//folder name of the theme to use, in "/themes/*"
@define ('FORUM_THEME',		'greyscale');

//if posting is allowed. change to false and nobody will be able to post new threads or reply
@define ('FORUM_ENABLED',	true);
//if new users are allowed to register. set to false and only existing registered users will be allowed to post
@define ('FORUM_NEWBIES',	true);

//number of threads and posts to show per page
//WARNING: changing these will inadvertadely invalidate post permalinks, decide on these numbers in the beginning
@define ('FORUM_THREADS',	50);
@define ('FORUM_POSTS',		25);

//maximum allowed size (number of characters) of input fields
@define ('SIZE_NAME',		20);		//user name
@define ('SIZE_PASS',		20);		//password
@define ('SIZE_TITLE',		100);		//post title
@define ('SIZE_TEXT',		50000);		//post message


?>