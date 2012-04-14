<?php //theme-specific template functions
/* ====================================================================================================================== */
/* NoNonsense Forum v19 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//this function is called just before a templated page is outputted so that you have an opportunity to do any extra
//templating of your own. the `$template` object passed in is a DOMTemplate class, see '/lib/domtemplate/' for code
//or <camendesign.com/dom_templating> for documentation on how to template with it
function theme_custom ($template) {
	//set the logo
	$template->setValue ('img#logo@src', FORUM_PATH.'themes/'.FORUM_THEME.'/img/'.THEME_LOGO);
}

//produce an HTML list of names (used for the mods/members list)
function theme_nameList ($names) {
	foreach ($names as &$name) $name = '<b'.(isMod ($name) ? ' class="mod"' : '').'>'.safeHTML ($name).'</b>';
	return implode (', ', $names);
}

//produces a truncated list of page numbers around the current page:
//(you might want to do something different, like a combo box with a button)
function theme_pageList ($url_slug, $page, $pages) {
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
	foreach ($list as &$item) switch (true) {
		case $item == $page:	$item = "<li><em>$item</em></li>"; 				break;
		case $item:		$item = "<li><a href=\"$url_slug+$item\">$item</a></li>";	break;
		default:		$item = '<li>…</li>';
	}
	//insert the previous / next links
	if ($pages > 1 && $page > 1)	array_unshift ($list, "<li><a href=\"$url_slug+".($page-1)."\">«</a></li>");
	if ($page < $pages)		array_push    ($list, "<li><a href=\"$url_slug+".($page+1)."\">»</a></li>");
	
	return implode ('', $list);
}

?>