<?php //shared functions
/* ====================================================================================================================== */
/* NoNonsense Forum v11 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//check to see if a name is a known moderator
function isMod ($name) {
	global $MODS; return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}

function isMember ($name) {
	global $MEMBERS; return in_array (strtolower ($name), array_map ('strtolower', $MEMBERS));
}

/* ====================================================================================================================== */

//sanitise input:
function safeGet ($data, $len, $trim=true) {
	//remove PHP’s auto-escaping of text (depreciated, but still on by default in PHP5.3)
	if (get_magic_quotes_gpc ()) $data = stripslashes ($data);
	//remove useless whitespace. can be skipped (i.e for passwords)
	if ($trim) $data = trim ($data);
	//clip the length in case of a fake crafted request
	return $len ? mb_substr ($data, 0, $len) : $data;
}

//sanitise output:
function safeHTML ($text) {
	//encode a string for insertion into an HTML element
	return htmlspecialchars ($text, ENT_NOQUOTES);
}
function safeString ($text) {
	//encode a string for insertion between quotes in an HTML attribute (like `value` or `title`)
	return htmlspecialchars ($text, ENT_QUOTES);
}
function safeURL ($text, $is_HTML=true) {
	//encode a string to be used in a URL, keeping path separators
	$text = str_replace ('%2F', '/', rawurlencode ($text));
	//will the URL be outputted into HTML? (rather than, say, the HTTP headers)
	//if so, encode for HTML too, e.g. "&" should be "&amp;" within URLs when in HTML
	return $is_HTML ? safeHTML ($text) : $text;
}

//take the author's message, process markup, and encode it safely for the RSS feed
function formatText ($text) {
	//unify carriage returns between Windows / UNIX, and sanitise HTML against injection
	$text = safeHTML (preg_replace ('/\r\n?/', "\n", $text));
	
	/* preformatted text (code blocks):
	   -------------------------------------------------------------------------------------------------------------- */
	/* example:			or: (latex in partiular since it uses % as a comment marker)
	
		% title 		$ title
		⋮			⋮
		%			$
	*/
	$pre = array ();
	while (preg_match ('/^(?-s:(\h*)([%$])(.*?))\n(.*?)\n\h*\2(["”»]?)$/msu', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$pre[] = "<pre><span class=\"ct\">{$m[2][0]}{$m[3][0]}</span>\n"
			 //unindent code blocks that have been quoted
		         .(strlen ($m[1][0]) ? preg_replace ("/^\s{1,".strlen ($m[1][0])."}/m", '', $m[4][0]) : $m[4][0])
		         ."\n<span class=\"cb\">{$m[2][0]}</span></pre>"
		;
		//replace the code block with a placeholder:
		//(we will have to remove the code chunks from the source text to avoid the other markup processing from
		//munging it and then restore the chunks back later)
		$text = substr_replace ($text, "\n&__PRE__;".$m[5][0], $m[0][1], strlen ($m[0][0]));
	}
	
	/* inline code / teletype text:
	   -------------------------------------------------------------------------------------------------------------- */
	// example: `code` or ``code``
	$code = array ();
	while (preg_match ('/(?<=\s|^)(`+)(.*?)(?<!`)\1(?!`)/m', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$code[] = '<code>'.$m[1][0].$m[2][0].$m[1][0].'</code>';
		//same as with normal code blocks, replace them with a placeholder
		$text = substr_replace ($text, "&__CODE__;", $m[0][1], strlen ($m[0][0]));
	}
	
	/* hyperlinks:
	   -------------------------------------------------------------------------------------------------------------- */
	//find full URLs and turn into HTML hyperlinks. we also detect e-mail addresses automatically
	$text = preg_replace (
		'/(?:
			((?:(?:http|ftp)s?|irc)?:\/\/)				# $1 = protocol
			(							# $2 = friendly URL (no protocol)
				[a-z0-9\.\-]{1,}(?:\.[a-z]{2,6})+		# domain name
			)(\/)?							# $3 = slash is excluded from friendly URL
			(?(3)(							# $4 = folders and filename, relative URL
				(?>						# folders and filename
					\)(?![\.,]?(?:\s|$))|			# ignore brackets on end with dot or comma
					[:\.,"”»](?!\s|$)|			# ignore various characters on the end
					[^\s:)\.,"”»]				# the rest, including bookmark
				)*
			)?)
		|
			([a-z0-9\._%+\-]+@[a-z0-9\.\-]{1,}(?:\.[a-z]{2,6})+)	# $5 = e-mail
		)/exiu',
		'"<a href=\"".("$5"?"mailto:$5":("$1"?"$1":"http://")."$2$3$4")."\" rel=\"nofollow\">$0</a>"',
	$text);
	
	/* inline formatting:
	   -------------------------------------------------------------------------------------------------------------- */
	$text = preg_replace (
		//example: _italic_ & *bold*
		array ('/(?<!\w)_(?!_)(.*?)(?<!_)_(?!\w)/',	'/(?<![*\w])\*(?!\*)(.*?)(?<!\*)\*(?![*\w])/'),
		array ('<em>_$1_</em>',				'<strong>*$1*</strong>'),
	$text);
	
	/* titles and dividers
	   -------------------------------------------------------------------------------------------------------------- */
	/* example: (titles)	/	(dividers)
		
		:: title		----
	*/
	$text = preg_replace(
		array ('/(?:\n|\A)(::.*)(?:\n?$|\Z)/mu',	'/(?:\n|\A)\h*(----+)\h*(?:\n?$|\Z)/m'),
		array ("\n\n<h2>$1</h2>\n",			"\n\n<p class=\"hr\">$1</p>\n"),
	$text);
	
	/* blockquotes:
	   -------------------------------------------------------------------------------------------------------------- */
	/* example:
	
		“this is the first quote level.
		
		“this is the second quote level.”
		
		back to the first quote level.”
	*/
	do $text = preg_replace (array (
		//you would think that you could combine these. you really would
		'/(?:\n|\A)\h*("(?!\s+)((?>(?1)|.)*?)\s*")\h*(?:\n?$|\Z)/msu',
		'/(?:\n|\A)\h*(“(?!\s+)((?>(?1)|.)*?)\s*”)\h*(?:\n?$|\Z)/msu',
		'/(?:\n|\A)\h*(«(?!\s+)((?>(?1)|.)*?)\s*»)\h*(?:\n?$|\Z)/msu'
	),	//extra quote marks are inserted in the spans for both themeing, and so that when you copy a quote, the
		//nesting is preserved for you. there must be a line break between spans and the text otherwise it prevents
		//the regex from finding quote marks at the ends of lines (these extra linebreaks are removed next)
		"\n\n<blockquote>\n\n<span class=\"ql\">&ldquo;</span>\n$2\n<span class=\"qr\">&rdquo;</span>\n\n</blockquote>\n",
		$text, -1, $c
	); while ($c);
	//remove the extra linebreaks addeded between our theme quotes
	//(required so that extra `<br />`s don’t get added!)
	$text = preg_replace (
		array ('/&ldquo;<\/span>\n/',	'/\n<span class="qr">/'),
		array ('&ldquo;</span>',	'<span class="qr">'),
	$text);
	
	/* finalise:
	   -------------------------------------------------------------------------------------------------------------- */
	//add paragraph tags between blank lines
	foreach (preg_split ('/\n{2,}/', trim ($text), -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
		//if not a blockquote, title or hr, wrap in a paragraph
		if (!preg_match ('/^<\/?(?:bl|h2|p)|^&_/', $chunk))
			$chunk = "<p>\n".str_replace ("\n", "<br />\n", $chunk)."\n</p>"
		;
		$text = @$result .= "\n$chunk";
	}
	
	//restore code blocks
	foreach ($pre  as $html) $text = preg_replace ('/&__PRE__;/',  $html, $text, 1);
	foreach ($code as $html) $text = preg_replace ('/&__CODE__;/', $html, $text, 1);
	
	return $text;
}

/* ====================================================================================================================== */

//the shared template stuff for all pages
function prepareTemplate ($filepath, $title) {
	$template = new DOMTemplate ($filepath);
	
	/* HTML <head>
	   -------------------------------------------------------------------------------------------------------------- */
	$template->set (array (
		//HTML title (= forum / sub-forum name and page number)
		'xpath:/html/head/title'				=> $title,
		//application title (= forum / sub-forum name):
		//used for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
		'xpath://meta[@name="application-name"]/@content'	=> PATH ? PATH : FORUM_NAME,
		//application URL (where the pinned site opens at)
		'xpath://meta[@name="msapplication-starturl"]/@content'	=> FORUM_URL.PATH_URL
	));
	//remove 'custom.css' stylesheet if 'custom.css' is missing
	if (!file_exists (FORUM_ROOT.FORUM_PATH.'themes/'.FORUM_THEME.'/custom.css'))
		$template->remove ('xpath://link[contains(@href,"custom.css")]')
	;
	
	/* site header
	   -------------------------------------------------------------------------------------------------------------- */
	$template->set (array (
		'forum-name'	=> FORUM_NAME,
		'img:logo@src'	=> FORUM_PATH.'themes/'.FORUM_THEME.'/img/'.THEME_LOGO
	));
	//search form:
	$template->set (array (
		//if you're using a Google search, change it to HTTPS if enforced
		'xpath://form[@action="http://google.com/search"]/@action'	=> FORUM_HTTPS	? 'https://encrypted.google.com/search'
												: 'http://google.com/search',
		//set the forum URL for Google search-by-site
		'xpath://input[@name="as_sitesearch"]/@value'			=> $_SERVER['HTTP_HOST']
	));
	//are we in a sub-folder?
	if (PATH) {
		//if so, add the sub-forum name to the breadcrumb navigation,
		$template->setValue ('subforum-name', PATH);
	} else {
		//otherwise -- remove the breadcrumb navigation
		$template->remove ('subforum');
	}
	
	/* site footer
	   -------------------------------------------------------------------------------------------------------------- */
	//are there any local mods?	create the list of local mods
	if (!empty ($MODS['LOCAL'])):	$template->setHTML ('mods-local-list', theme_nameList ($MODS['LOCAL']));
				else:	$template->remove ('mods-local');	//remove the local mods list section
	endif;
	//are there any site mods?	create the list of mods
	if (!empty ($MODS['GLOBAL'])):	$template->setHTML ('mods-list', theme_nameList ($MODS['GLOBAL']));
				 else:	$template->remove ('mods');		//remove the mods list section
	endif;
	//are there any members?	create the list of members
	if (!empty ($MEMBERS)):		$template->setHTML ('members-list', theme_nameList ($MEMBERS));
			  else:		$template->remove ('members');		//remove the members list section
	endif;
	//is a user signed in?
	if (HTTP_AUTH) {
		//yes: remove the signed-out section and set the name of the signed-in user
		$template->remove ('signed-out')->setValue ('signed-in-name', HTTP_AUTH_NAME);
	} else {
		//no: remove the signed-in section
		$template->remove ('signed-in');
	}
	
	return $template;
}

/* ====================================================================================================================== */

//regenerate a folder's RSS file (all changes happening in a folder)
function indexRSS () {
	/* create an RSS feed
	   -------------------------------------------------------------------------------------------------------------- */
	$rss  = new SimpleXMLElement (
		'<?xml version="1.0" encoding="UTF-8"?>'.
		'<rss version="2.0" />'
	);
	$chan = $rss->addChild ('channel');
	//RSS feed title and URL to this forum / sub-forum
	$chan->addChild ('title',	safeHTML (FORUM_NAME.(PATH ? ' / '.PATH : '')));
	$chan->addChild ('link',	FORUM_URL);
	
	//get list of threads, sort by date; most recently modified first
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	//get the last post made in each thread as an RSS item
	foreach (array_slice ($threads, 0, FORUM_THREADS) as $thread)
		if ($xml = @simplexml_load_file ($thread))	//if the RSS feed is valid
		if ($item = $xml->channel->item[0])		//if the feed has any items
	{
		$new = $chan->addChild ('item');
		$new->addChild ('title',	safeHTML ($item->title));
		$new->addChild ('link',		$item->link);
		$new->addChild ('author',	safeHTML ($item->author));
		$new->addChild ('pubDate',	gmdate ('r', strtotime ($item->pubDate)));
		$new->addChild ('description',	safeHTML ($item->description));
	}
	//save to disk
	$rss->asXML ('index.xml');
	
	/* sitemap
	   -------------------------------------------------------------------------------------------------------------- */
	//we’re going to use the RSS files as sitemaps
	chdir (FORUM_ROOT);
	
	//start the XML file
	$xml = new SimpleXMLElement (
		'<?xml version="1.0" encoding="UTF-8"?>'.
		'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />'
	);
	
	//get list of sub-forums
	$folders = array ('');
	foreach (array_filter (
		//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
		preg_grep ('/^(\.|users$|themes$|lib$)/', scandir (FORUM_ROOT.'/'), PREG_GREP_INVERT), 'is_dir'
	) as $folder) $folders[] = $folder;
	
	//generate a sitemap index file, to point to each index RSS file in the forum:
	//<https://www.google.com/support/webmasters/bin/answer.py?answer=71453>
	foreach ($folders as $folder) if (
		//get the time of the latest item in the RSS feed
		//(the RSS feed may be missing as they are not generated in new folders until something is posted)
		@$rss = simplexml_load_file (FORUM_ROOT.($folder ? "/$folder" : '').'/index.xml')
	) {
		$map = $xml->addChild ('sitemap');
		$map->addChild ('loc',		FORUM_URL.($folder ? safeURL ("/$folder", false) : '').'/index.xml');
		$map->addChild ('lastmod',	gmdate ('r', strtotime ($rss->channel->item[0]->pubDate)));
	}
	$xml->asXML (FORUM_ROOT.'/sitemap.xml');
	
	//you saw nothing, right?
	clearstatcache ();
}

/* ====================================================================================================================== */

//SimpleXML (what we use for all RSS creation / manipulation) is great, but doesn’t support adding a node before another;
//(in RSS feeds, the newest item comes first), so here we add this functionality in
class DXML extends SimpleXMLElement {
	//this concept modifed from:
	//<stackoverflow.com/questions/2092012/simplexml-how-to-prepend-a-child-in-a-node/2093059#2093059>
	public function insertBefore ($name, $value=NULL) {
		//import the SimpleXML into DOM proper, which does have an `insertBefore` method
		$dom = dom_import_simplexml ($this);
		//add the item
		$new = $dom->parentNode->insertBefore ($dom->ownerDocument->createElement ($name, $value), $dom);
		//convert back to SimpleXML and return
		return simplexml_import_dom ($new, get_class ($this));
	}
}

?>