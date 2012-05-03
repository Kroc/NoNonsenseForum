<?php //theme configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v19 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'theme.config.php' and customise to your liking,
       DON’T DELETE OR MODIFY 'theme.config.default.php'! --- */


/* required options: (used internally by NoNonsense Forum itself, all themes must provide these)
   ====================================================================================================================== */
//a space-delimited list of theme translations the user can choose. each item should be a standard language code
//(see <w3.org/International/questions/qa-choosing-language-tags> for information on choosing a language code)
//which therefore refers to the matching 'lang.*.php' file, e.g. 'fr de es it' for French, German, Spanish & Italian.
//see 'lang.example.php' for info on translations
@define ('THEME_LANGS',		'');
//(if you change the text in the theme ['*.html' files], you might want to change this option to blank '' so that users
//can’t use the other translations--they may no longer match up with your default language's text--unless you intend to
//update the additonal translations too!)

//the translation to use by default. leave blank to use the theme's default language (which is what is written directly
//into the HTML files), otherwise, enter the translation's language code to change the default language of your forum
@define ('THEME_LANG',		'');

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

?>