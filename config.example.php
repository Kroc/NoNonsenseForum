<?php //configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v7 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- rename this file 'config.php' and customise to your liking --- */

//timezone to use for all datetimes
//this must be a string from this list: <php.net/manual/en/timezones.php>, e.g. "Europe/London"
define ('FORUM_TIMEZONE',	'UTC');

//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
define ('DATE_FORMAT',		'd M ’y · H:i');

//if posting is allowed. change to false and nobody will be able to post new threads or reply
define ('FORUM_ENABLED',	true);

//folder name of the theme to use, in "/themes/*"
define ('FORUM_THEME',		'greyscale');

//forum’s title. used in theme, and in RSS feeds
define ('FORUM_NAME',		'NoNonsense Forum');

//number of threads and posts to show per page
//WARNING: changing these will inadvertadely invalidate post permalinks, decide on these numbers in the beginning
define ('FORUM_THREADS',	50);
define ('FORUM_POSTS',		25);

//maximum allowed size (number of characters) of input fields
define ('SIZE_NAME',		20);		//user name
define ('SIZE_PASS',		20);		//password
define ('SIZE_TITLE',		100);		//post title
define ('SIZE_TEXT',		50000);		//post message

//prepended to the thread title for each reply (like in e-mail)
//(the "&__NO__;" template tag is for the number of the reply)
define ('TEMPLATE_RE',		'RE[&__NO__;]: ');

//HTML used when appending to a post:
//"&__AUTHOR__;"	- username of who posted
//"&__DATETIME__;"	- timestamp formatted for use with HTML5 `<time>`
//"&__TIME__;"		- human-readable time, as per `DATE_FORMAT`
define ('TEMPLATE_APPEND',	'<p class="appended"><b>&__AUTHOR__;</b> added on <time datetime="&__DATETIME__;">&__TIME__;</time></p>');

//HTML that replaces a post when it's deleted
define ('TEMPLATE_DEL_USER', '<p>This post was deleted by its owner</p>');
define ('TEMPLATE_DEL_MOD',  '<p>This post was deleted by a moderator</p>');


?>
