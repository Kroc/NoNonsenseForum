<?php //theme configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v19 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'theme.config.php' and customise to your liking,
       don’t delete or modify 'theme.config.default.php'! --- */


/* required options: (used internally by NoNonsense Forum itself, all themes must provide these)
   ====================================================================================================================== */
//the default written langauge of the your template files (i.e. `<html lang="en">`). this will be a standard language code,
//see this web page for information on choosing a language code: <w3.org/International/questions/qa-choosing-language-tags>
//of course, don’t actually change this value for an existing theme! this only applies if you are creating a new theme that
//uses a different language by default! changing this value will *not* change the default language shown!
@define ('THEME_LANG',		'en');

//a space-delimited list of theme translations the user can choose, excluding the theme's default language (above).
//each item in the list should be a standard language code, which therefore refers to the matching 'lang.*.php' file,
//e.g. `fr de es it` for French, German, Spanish & Italian. See 'lang.example.php' for info on translations
@define ('THEME_LANGS',		'x-pig-latin');
//(if you change the text in the theme ['*.html' files], you might want to change this option to blank '' so that users
//can’t use the other translations, which may no longer match up with your default language's text, unless you intend to
//update the additonal translations too!)

//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
@define ('DATE_FORMAT',		'd M ’y · H:i');

//the following template replacements are done using `sprintf`,
//see <php.net/manual/en/function.sprintf.php> for details

//the HTML title for index and thread pages
//"%1$s" - the title
//"%2$s" - if on page 2 or greater, `THEME_TITLE_PAGENO` will be inserted here, otherwise it will be removed
@define ('THEME_TITLE',		'%1$s%2$s');

//the page number, added to the titles of index pages and threads
//"%1$u" - the page number
@define ('THEME_TITLE_PAGENO',	' + %1$u');

//the title for append pages
//"%1$s" - the post title (will come from `THEME_RE` for replies)
@define ('THEME_TITLE_APPEND',	'Append to %1$s');

//the title for delete pages
//"%1$s" - the post title (will come from `THEME_RE` for replies)
@define ('THEME_TITLE_DELETE',	'Delete %1$s?');

//reply number shown in threads as a permalink
//"%1$u" - the number of the reply
@define ('THEME_REPLYNO',	'#%1$u.');

//title format for each reply
//"%1$u" - number of the reply
//"%2$s" - the thread title
@define ('THEME_RE',		'RE[%1$u]: %2$s');

//HTML used when appending to a post:
//"%1$s" - username of who posted
//"%2$s" - timestamp formatted for use with HTML5 `<time>`
//"%3$s" - human-readable time, as per `DATE_FORMAT`
@define ('THEME_APPEND',	'<p class="appended"><b>%1$s</b> added on <time datetime="%2$s">%3$s</time></p>');

//HTML that replaces a post when it's deleted (this is not rectroactive)
@define ('THEME_DEL_USER',	'<p>This post was deleted by its owner</p>');
@define ('THEME_DEL_MOD', 	'<p>This post was deleted by a moderator</p>');


/* optional: (options unique to this theme)
   ====================================================================================================================== */
//filename of the image to use as the site logo (assumed to be within the theme's folder)
//- for this theme, it should be 32x32 px
@define ('THEME_LOGO',		'logo.png');


/* functions: (you might want to do some particular formatting in your theme)
   ====================================================================================================================== */
if (!function_exists ('theme_custom')) { function theme_custom ($template) {
	//this function is called just before a templated page is outputted so that you have an opportunity to do any
	//extra templating of your own. the `$template` object passed in is a DOMTemplate class, see '/lib/domtemplate.php'
	//for code or <camendesign.com/dom_templating> for documentation on how to template with it
	
	//set the logo
	$template->setValue ('img#logo@src', FORUM_PATH.'themes/'.FORUM_THEME.'/img/'.THEME_LOGO);
}}

//produce an HTML list of names. used for the mods/members list
if (!function_exists ('theme_nameList')) { function theme_nameList ($names) {
	foreach ($names as &$name) $name = '<b'.(isMod ($name) ? ' class="mod"' : '').'>'.safeHTML ($name).'</b>';
	return implode (', ', $names);
}}

?>