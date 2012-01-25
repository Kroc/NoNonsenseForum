<?php //shared functions
/* ====================================================================================================================== */
/* NoNonsense Forum v14 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//the shared template stuff for all pages
function prepareTemplate ($filepath, $title) {
	//load the template into DOM for manipulation. see 'domtemplate.php' for code and
	//<camendesign.com/dom_templating> for documentation
	$template = new DOMTemplate ($filepath);
	
	//fix all absolute URLs (i.e. if NNF is running in a folder):
	//(this also fixes the forum-title home link "/" when NNF runs in a folder)
	foreach ($template->query ('//*/@href, //*/@src') as $node) if ($node->nodeValue[0] == '/')
		//prepend the base path of the forum ('/' if on root, '/folder/' if running in a sub-folder)
		$node->nodeValue = FORUM_PATH.ltrim ($node->nodeValue, '/')
	;
	
	/* HTML <head>
	   -------------------------------------------------------------------------------------------------------------- */
	$template->set (array (
		//HTML title (= forum / sub-forum name and page number)
		'/html/head/title'					=> $title,
		//application title (= forum / sub-forum name):
		//used for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
		'//meta[@name="application-name"]/@content'		=> SUBFORUM ? SUBFORUM : FORUM_NAME,
		//application URL (where the pinned site opens at)
		'//meta[@name="msapplication-starturl"]/@content'	=> FORUM_URL.PATH_URL
	));
	//remove 'custom.css' stylesheet if 'custom.css' is missing
	if (!file_exists (FORUM_ROOT.FORUM_PATH.'themes/'.FORUM_THEME.'/custom.css'))
		$template->remove ('//link[contains(@href,"custom.css")]')
	;
	
	/* site header
	   -------------------------------------------------------------------------------------------------------------- */
	$template->set (array (
		//site title
		'.nnf_forum-name' => FORUM_NAME,
		//set the forum URL for Google search-by-site
		'//input[@name="as_sitesearch"]/@value' => $_SERVER['HTTP_HOST'],
		//if you're using a Google search, change it to HTTPS if enforced
		'//form[@action="http://google.com/search"]/@action'
			=> FORUM_HTTPS	? 'https://encrypted.google.com/search'
					: 'http://google.com/search'
	));
	
	//are we in a sub-folder? if so, build the breadcrumb navigation
	if (PATH) for (
		//split the path by '/' to get each sub-forum
		$items = explode ('/', trim (PATH, '/')), $item = $template->repeat ('.nnf_breadcrumb'),
		$i = 0; $i < count ($items); $i++
	) $item->set (array (
		'a.nnf_subforum-name'      => $items[$i],
		//reconstruct the URL from each sub-forum up to the current one
		'a.nnf_subforum-name@href' => FORUM_PATH.implode ('/', array_map ('safeURL', array_slice ($items, 0, $i+1))).'/'
	))->next ();
	//not in a sub-folder? remove the breadcrumb navigation
	if (!PATH) $template->remove ('.nnf_breadcrumb');
	
	/* site footer
	   -------------------------------------------------------------------------------------------------------------- */
	//are there any local mods?	create the list of local mods
	if (!empty ($MODS['LOCAL'])):	$template->setHTML ('#nnf_mods-local-list', theme_nameList ($MODS['LOCAL']));
				else:	$template->remove  ('#nnf_mods-local');	//remove the local mods list section
	endif;
	//are there any site mods?	create the list of mods
	if (!empty ($MODS['GLOBAL'])):	$template->setHTML ('#nnf_mods-list', theme_nameList ($MODS['GLOBAL']));
				 else:	$template->remove  ('#nnf_mods');	//remove the mods list section
	endif;
	//are there any members?	create the list of members
	if (!empty ($MEMBERS)):		$template->setHTML ('#nnf_members-list', theme_nameList ($MEMBERS));
			  else:		$template->remove  ('#nnf_members');	//remove the members list section
	endif;
	
	//set the name of the signed-in user
	$template->setValue ('.nnf_signed-in-name', NAME)->remove (
		//removed the relevant section for signed-in / out
		HTTP_AUTH ? '.nnf_signed-out' : '.nnf_signed-in'
	);
	
	return $template;
}

/* ====================================================================================================================== */

//check to see if a name is a known moderator
function isMod ($name) {
	global $MODS;    return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}
//a member of a locked forum?
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
					\)(?![:\.,"”»]?(?:\s|$))|		# ignore brackets on end with punctuation
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
		
		:: title		---
	*/
	$text = preg_replace(
		array ('/(?:\n|\A)(::.*)(?:\n?$|\Z)/mu',	'/(?:\n|\A)\h*(---+)\h*(?:\n?$|\Z)/m'),
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
	
	//restore code blocks/spans
	foreach ($pre  as $html) $text = preg_replace ('/&__PRE__;/',  $html, $text, 1);
	foreach ($code as $html) $text = preg_replace ('/&__CODE__;/', $html, $text, 1);
	
	return $text;
}

/* ====================================================================================================================== */

//regenerate a folder's RSS file (all changes happening in a folder)
function indexRSS () {
	/* create an RSS feed
	   -------------------------------------------------------------------------------------------------------------- */
	$rss = new DOMTemplate (FORUM_ROOT.'/lib/rss-template.xml');
	$rss->set (array (
		'/rss/channel/title'	=> FORUM_NAME.(PATH ? str_replace ('/', ' / ', PATH) : ''),
		'/rss/channel/link'	=> FORUM_URL.PATH_URL
	));
	
	//get list of threads, sort by date; most recently modified first
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	$items = $rss->repeat ('/rss/channel/item');
	//get the last post made in each thread as an RSS item
	foreach (array_slice ($threads, 0, FORUM_THREADS) as $thread)
		if ($xml  = @simplexml_load_file ($thread))	//if the RSS feed is valid
		if ($item = @$xml->channel->item[0])		//if the feed has any items
		$items->set (array (
			'./title'	=> $item->title,
			'./link'	=> $item->link,
			'./author'	=> $item->author,
			'./pubDate'	=> gmdate ('r', strtotime ($item->pubDate)),
			'./description'	=> $item->description
		))->next ();
	;
	file_put_contents ('index.xml', $rss->html ());
	
	/* sitemap
	   -------------------------------------------------------------------------------------------------------------- */
	//we’re going to use the RSS files as sitemaps
	chdir (FORUM_ROOT);
	
	//get list of sub-forums and include the root too
	$folders = array ('') + array_filter (
		//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
		preg_grep ('/^(\.|users$|themes$|lib$)/', scandir (FORUM_ROOT.'/'), PREG_GREP_INVERT), 'is_dir'
	);
	
	//start the XML file. this template has an XMLNS, so we have to prefix all our XPath queries :(
	$xml = new DOMTemplate (FORUM_ROOT.'/lib/sitemap-template.xml', 'x', 'http://www.sitemaps.org/schemas/sitemap/0.9');
	
	//generate a sitemap index file, to point to each index RSS file in the forum:
	//<https://www.google.com/support/webmasters/bin/answer.py?answer=71453>
	$sitemap = $xml->repeat ('//x:sitemap');
	foreach ($folders as $folder)
		//get the time of the latest item in the RSS feed
		//(the RSS feed may be missing as they are not generated in new folders until something is posted)
		if (@$rss = simplexml_load_file (FORUM_ROOT.($folder ? "/$folder" : '').'/index.xml'))
		//if you delete the last thread in a folder, there won’t be anything in the RSS index file!
		if (@$rss->channel->item[0])
	 	$sitemap->set (array (
			'./x:loc'	=> FORUM_URL.($folder ? safeURL ("/$folder", false) : '').'/index.xml',
			'./x:lastmod'	=> gmdate ('r', strtotime ($rss->channel->item[0]->pubDate))
		))->next ()
	;
	file_put_contents (FORUM_ROOT.'/sitemap.xml', $xml->html ());
	
	//you saw nothing, right?
	clearstatcache ();
}

?>