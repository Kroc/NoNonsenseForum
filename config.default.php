<?php //configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- rename this file 'config.php' and customise to your liking --- */


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

//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
define ('DATE_FORMAT',		'd M ’y · H:i');

//prepended to the thread title for each reply (like in e-mail)
define ('TEMPLATE_RE',		'RE: ');

//HTML used when appending to a post
define ('TEMPLATE_APPEND',	'<p class="appended"><b>&__AUTHOR__;</b> added on <time datetime="&__DATETIME__;">&__TIME__;</time></p>');

//HTML that replaces a post when it's deleted
define ('TEMPLATE_DELETE_USER', '<p>This post was deleted by its owner</p>');
define ('TEMPLATE_DELETE_MOD',  '<p>This post was deleted by a moderator</p>');


?>