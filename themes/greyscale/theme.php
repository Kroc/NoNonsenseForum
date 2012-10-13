<?php //theme-specific template functions
/* ====================================================================================================================== */
/* NoNonsense Forum v22 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//this function is called just before a templated page is outputted so that you have an opportunity to do any extra
//templating of your own. the `$template` object passed in is a DOMTemplate class, see '/lib/domtemplate/' for code
//or <camendesign.com/dom_templating> for documentation on how to template with it
function theme_custom ($template) {
	$template->set (array (
		//metadata for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
		//application title (= forum / sub-forum name):
		'//meta[@name="application-name"]/@content'          => SUBFORUM ? SUBFORUM : FORUM_NAME,
		//application URL (where the pinned site opens at)
		'//meta[@name="msapplication-starturl"]/@content'    => FORUM_URL.url ('index', PATH_URL),
		//pinned site / metro colour to use
		'//meta[@name="msapplication-navbutton-color"]/@content, //meta[@name="msapplication-TileColor"]/@content'
		                                                     => METRO_COLOUR, 
		//set the site logo
		'img#nnf_logo@src'                                   => FORUM_PATH.'themes/'.FORUM_THEME.'/img/'.THEME_LOGO,
		
		//set the forum URL for Google search-by-site
		'//input[@name="as_sitesearch"]/@value'              => $_SERVER['HTTP_HOST'],
		//if you're using a Google search, change it to HTTPS if enforced
		'//form[@action="http://google.com/search"]/@action' => FORUM_HTTPS ? 'https://encrypted.google.com/search'
		                                                                    : 'http://google.com/search'
	));
}

//produce an HTML list of names (used for the mods/members list)
function theme_nameList ($names) {
	foreach ($names as &$name) $name = '<b'.(isMod ($name) ? ' class="nnf_mod"' : '').'>'.safeHTML ($name).'</b>';
	return implode (', ', $names);
}

//produces a truncated list of page numbers around the current page:
//(you might want to do something different, like a combo box with a button)
function theme_pageList ($template, $file, $page, $pages) {
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
	
	//manipulate the page list in the template
	$node = $template->repeat ('.nnf_pages/li');
	//add a previous page link
	if ($pages > 1 && $page > 1) $node->set (array (
		'a@href'	=> url ($file ? 'thread' : 'index', PATH_URL, $file, $page-1),
		'a'		=> '«'
	))->next ();
	//generate the list of pages,
	foreach ($list as &$item) {
		//create the link
		$node->setValue ('a@href', url ($file ? 'thread' : 'index', PATH_URL, $file, $item));
		switch (true) {
			//determine if this is the current page, a regular page number, or the “…” gap
			case $item == $page:	$node->setValue ('a/em', $item)->next ();	break;
			case $item:		$node->setValue ('a', $item)->next ();		break;
			default:		$node->setValue ('.', '…')->next ();
		}
	}
	//add a next page link
	if ($page < $pages) $node->set (array (
		'a@href'	=> url ($file ? 'thread' : 'index', PATH_URL, $file, $page+1),
		'a'		=> '»'
	))->next ();
}

?>