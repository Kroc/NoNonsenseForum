<?php //theme configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v12 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'theme.config.php' and customise to your liking,
       don’t delete or modify 'theme.config.default.php'! --- */


/* required options: (used internally by NoNonsense Forum itself, all themes must provide these)
   ====================================================================================================================== */
//(the template replacements are done using `sprintf`, see <php.net/manual/en/function.sprintf.php> for details)

//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
@define ('DATE_FORMAT',		'd M ’y · H:i');

//title format for each reply (like in e-mail)
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
@define ('THEME_LOGO',		'title.png');


/* functions: (you might want to do some particular formatting in your theme)
   ====================================================================================================================== */
//produces a truncated list of page numbers around the current page:
//(you might want to do something different, like a combo box with a button)
if (!function_exists ('theme_pageList')) { function theme_pageList ($page, $pages) {
	//always include the first page
	$list[] = 1;
	//more than one page?
	if ($pages > 1) {
		//if previous page is not the same as 2, include ellipses
		//(there’s a gap between 1, and current-page minus 1, e.g. "1, …, 54, 55, 56, …, 100")
		if ($page-1 > 2) $list[] = '';
		//the page before the current page
		if ($page-1 > 1) $list[] = $page-1;
		//the current page
		if ($page != 1) $list[] = $page;
		//the page after the current page (if not at end)
		if ($page+1 < $pages) $list[] = $page+1;
		//if there’s a gap between page+1 and the last page
		if ($page+1 < $pages-1) $list[] = '';
		//last page
		if ($page != $pages) $list[] = $pages;
	}
	
	//turn it into HTML
	foreach ($list as &$item) if ($item == $page) {
		$item = "<li><em>$item</em></li>";
	} elseif ($item) {
		$item = "<li><a href=\"?page=$item#threads\">$item</a></li>";
	} else {
		$item = '<li>…</li>';
	}
	return implode ('', $list);
}}

//produce an HTML list of names. used for the mods/members list
if (!function_exists ('theme_nameList')) { function theme_nameList ($names) {
	foreach ($names as &$name) $name = '<b'.(isMod ($name) ? ' class="mod"' : '').'>'.safeHTML ($name).'</b>';
	return implode (', ', $names);
}}

?>