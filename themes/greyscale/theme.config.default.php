<?php //theme configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v8 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'theme.config.php' and customise to your liking,
       don’t delete or modify 'theme.config.default.php'! --- */


/* required options: (used by NoNonsense Forum itself, all themes must provide these)
   ====================================================================================================================== */
//prepended to the thread title for each reply (like in e-mail)
//(the "&__NO__;" template tag is for the number of the reply)
@define ('THEME_RE',		'RE[&__NO__;]: ');

//HTML used when appending to a post:
//"&__AUTHOR__;"		- username of who posted
//"&__DATETIME__;"		- timestamp formatted for use with HTML5 `<time>`
//"&__TIME__;"			- human-readable time, as per `DATE_FORMAT`
@define ('THEME_APPEND',	'<p class="appended"><b>&__AUTHOR__;</b> added on <time datetime="&__DATETIME__;">&__TIME__;</time></p>');

//HTML that replaces a post when it's deleted (this is not rectroactive)
@define ('THEME_DEL_USER',	'<p>This post was deleted by its owner</p>');
@define ('THEME_DEL_MOD', 	'<p>This post was deleted by a moderator</p>');


/* optional: (options unique to this theme)
   ====================================================================================================================== */


?>