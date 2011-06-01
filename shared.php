<?php //reduce some duplication
/* ====================================================================================================================== */
/* NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//let me know when I’m being stupid
error_reporting (-1);

/* constants: some stuff we don’t expect to change
   ---------------------------------------------------------------------------------------------------------------------- */
define ('START', 		microtime (true));			//record how long the page takes to generate
define ('FORUM_ROOT',		dirname (__FILE__));			//full path for absolute references
define ('FORUM_URL',		'http://'.$_SERVER['HTTP_HOST']);	//todo: https support

//these are just some enums for templates to react to
define ('ERROR_NONE',		0);
define ('ERROR_NAME',		1);					//name entered is invalid / blank
define ('ERROR_PASS',		2);					//password is invalid / blank
define ('ERROR_TITLE',		3);					//the title is invalid / blank
define ('ERROR_TEXT',		4);					//post text is invalid / blank
define ('ERROR_AUTH',		5);					//name / password did not match

//set the forum owner’s personal config
@include './config.php';

/* default config:
   ---------------------------------------------------------------------------------------------------------------------- */
//*don’t* change these values here, instead rename 'config.example.php' to 'config.php' and customise
//these are here so that if I add a new value, the forum won’t break if you don’t update your config file

//see 'config.example.php' for description of these
defined ('FORUM_TIMEZONE')	or define ('FORUM_TIMEZONE',	'UTC');
defined ('DATE_FORMAT')		or define ('DATE_FORMAT',	'd M ’y · H:i');
defined ('FORUM_ENABLED')	or define ('FORUM_ENABLED',	true);
defined ('FORUM_THEME')		or define ('FORUM_THEME',	'greyscale');
defined ('FORUM_NAME')		or define ('FORUM_NAME',	'NoNonsense Forum');
defined ('FORUM_THREADS')	or define ('FORUM_THREADS',	50);
defined ('FORUM_POSTS')		or define ('FORUM_POSTS',	25);
defined ('SIZE_NAME')		or define ('SIZE_NAME',		20);
defined ('SIZE_PASS')		or define ('SIZE_PASS',		20);
defined ('SIZE_TITLE')		or define ('SIZE_TITLE',	100);
defined ('SIZE_TEXT')		or define ('SIZE_TEXT',		50000);
defined ('TEMPLATE_RE')		or define ('TEMPLATE_RE',	'RE[&__NO__;]: ');
defined ('TEMPLATE_APPEND')	or define ('TEMPLATE_APPEND',	'<p class="appended"><b>&__AUTHOR__;</b> added on <time datetime="&__DATETIME__;">&__TIME__;</time></p>');
defined ('TEMPLATE_DEL_USER')	or define ('TEMPLATE_DEL_USER',	'<p>This post was deleted by its owner</p>');
defined ('TEMPLATE_DEL_MOD')	or define ('TEMPLATE_DEL_MOD', 	'<p>This post was deleted by a moderator</p>');

//PHP 5.3 issues a warning if the timezone is not set when using date commands
date_default_timezone_set (FORUM_TIMEZONE);


/* get input
   ====================================================================================================================== */
//all pages can accept a name / password when committing actions (new thread / post &c.)
define ('NAME', mb_substr (trim (@$_POST['username']), 0, SIZE_NAME, 'UTF-8'));
define ('PASS', mb_substr (      @$_POST['password'],  0, SIZE_PASS, 'UTF-8'));

//if name & password are provided, validate them
if (
	NAME && PASS &&
	//the email check is a fake hidden field in the form to try and fool spam bots
	isset ($_POST['email']) && @$_POST['email'] == 'example@abc.com' &&
	//I wonder what this does? ...
	((isset ($_POST['x']) && isset ($_POST['y'])) || (isset ($_POST['submit_x']) && isset ($_POST['submit_y'])))
) {
	//users are stored as text files based on the hash of the given name
	$name = hash ('sha512', strtolower (NAME));
	$user = FORUM_ROOT."/users/$name.txt";
	//create the user, if new
	if (!file_exists ($user)) file_put_contents ($user, hash ('sha512', $name.PASS));
	//does password match?
	define ('AUTH', file_get_contents ($user) == hash ('sha512', $name.PASS));
} else {
	define ('AUTH', false);
}

//whilst page number is not used everywhere (like 'action.php'), it does no harm to get it here because it can simply be
//ignored on 'action.php' &c. whilst avoiding duplicated code on the scripts that do use it
define ('PAGE', preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1);

//all our pages use path (often optional) so this is done here
define ('PATH', preg_match ('/[^.\/&]+/', @$_GET['path']) ? $_GET['path'] : '');
//these two get used an awful lot
define ('PATH_URL', !PATH ? '/' : safeURL ('/'.PATH.'/', false));	//when outputting as part of a URL
define ('PATH_DIR', !PATH ? '/' : '/'.PATH.'/');			//when using serverside, like `chdir` / `unlink`

//we have to change directory for `is_dir` to work, see <uk3.php.net/manual/en/function.is-dir.php#70005>
//being in the right directory is also assumed for reading 'mods.txt' and when generating the RSS (`indexRSS`)
//(oddly with `chdir` the path must end in a slash)
chdir (FORUM_ROOT.PATH_DIR);

//get the list of moderators:
$MODS = array (
	//mods.txt on root for mods on all sub-forums
	'GLOBAL'=> file_exists (FORUM_ROOT.'/mods.txt')
		? file (FORUM_ROOT.'/mods.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES)
		: array (),
	//if in a sub-forum, the local mods.txt
	'LOCAL'	=> PATH && file_exists ('mods.txt')
		? file ('mods.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES)
		: array ()
);


/* ---------------------------------------------------------------------------------------------------------------------- */

//stop browsers caching, so you don’t have to refresh every time to see changes
header ('Cache-Control: no-cache', true);
header ('Expires: 0', true);


/* ====================================================================================================================== */

//<stackoverflow.com/questions/2092012/simplexml-how-to-prepend-a-child-in-a-node/2093059#2093059>
//we could of course do all the XML manipulation in DOM proper to save doing this…
class allow_prepend extends SimpleXMLElement {
	public function prependChild ($name, $value=null) {
		$dom = dom_import_simplexml ($this);
		$new = $dom->insertBefore (
			$dom->ownerDocument->createElement ($name, $value),
			$dom->firstChild
		);
		return simplexml_import_dom ($new, get_class ($this));
	}
}

/* ====================================================================================================================== */

//replace markers (“&__TAG__;”) in the template with some other text
function template_tags ($template, $values) {
	foreach ($values as $key=>&$value) $template = str_replace ("&__${key}__;", $value , $template);
	return $template;
}

//santise output:
function safeHTML ($text) {
	//encode a string for insertion into an HTML element
	return htmlspecialchars ($text, ENT_NOQUOTES, 'UTF-8');
}
function safeString ($text) {
	//encode a string for insertion between quotes in an HTML attribute (like `value` or `title`)
	return htmlspecialchars ($text, ENT_QUOTES,   'UTF-8');
}
function safeURL ($text, $is_HTML=true) {
	//encode a string to be used in a URL, keeping path separators
	$text = str_replace ('%2F', '/', rawurlencode ($text));
	//will the URL be output into HTML? (rather than, say, the HTTP headers)
	//if so, encode for HTML too, e.g. "&" must be "&amp;" within URLs when in HTML
	return $is_HTML ? safeHTML ($text) : $text;
}

//produces a truncated list of pages around the current page
function pageList ($current, $total) {
	//always include the first page
	$PAGES[] = 1;
	//more than one page?
	if ($total > 1) {
		//if previous page is not the same as 2, include ellipses
		//(there’s a gap between 1, and current-page minus 1, e.g. "1, …, 54, 55, 56, …, 100")
		if ($current-1 > 2) $PAGES[] = '';
		//the page before the current page
		if ($current-1 > 1) $PAGES[] = $current-1;
		//the current page
		if ($current != 1) $PAGES[] = $current;
		//the page after the current page (if not at end)
		if ($current+1 < $total) $PAGES[] = $current+1;
		//if there’s a gap between page+1 and the last page
		if ($current+1 < $total-1) $PAGES[] = '';
		//last page
		if ($current != $total) $PAGES[] = $total;
	}
	return $PAGES;
}

//take the author's message, process markup, and encode it safely for the RSS feed
function formatText ($text) {
	//unify carriage returns between Windows / UNIX
	$text = preg_replace ('/\r\n?/', "\n", $text);
	
	//sanitise HTML against injection
	$text = safeHTML ($text);
	
	/* preformatted text (code spans / blocks):
	   -------------------------------------------------------------------------------------------------------------- */
	//we will have to remove the code chunks from the source text to avoid the other markup processing from munging it
	//and then restore the chunks back later
	$code = array ();
	//find code blocks:
	while (preg_match ('/^(?-s:\s*([%$])(.*?))\n(.*?)\n(?-s:\s*)\1(["”»]?)$/msu', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$code[] = "<pre><span class=\"ct\">{$m[1][0]}{$m[2][0]}</span>\n{$m[3][0]}\n<span class=\"cb\">{$m[1][0]}</span></pre>";
		//replace the code block with a placeholder
		$text = substr_replace ($text, "\n&__CODE__;".$m[4][0], $m[0][1], strlen ($m[0][0]));
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
		'"<a href=\"".("$5"?"mailto:$5":("$1"?"$1":"http://")."$2$3$4")."\">$0</a>"',
	$text);
	
	/* blockquotes:
	   -------------------------------------------------------------------------------------------------------------- */
	do $text = preg_replace (array (
		//you would think that you could combine these. you really would
		'/(?:^\n|\A)(?-s:\s*)("((?>(?1)|.)*?)")(?-s:\s*)(?:\n?$|\Z)/msu',
		'/(?:^\n|\A)(?-s:\s*)(“((?>(?1)|.)*?)”)(?-s:\s*)(?:\n?$|\Z)/msu',
		'/(?:^\n|\A)(?-s:\s*)(«((?>(?1)|.)*?)»)(?-s:\s*)(?:\n?$|\Z)/msu'
	),	//extra quote marks are inserted in the spans for both themeing, and so that when you copy a quote, the
		//nesting is preserved for you. there must be a line break between spans and the text otherwise it prevents
		//the regex from finding quote marks at the ends of lines (these extra linebreaks are removed next)
		"\n<blockquote>\n\n<span class=\"ql\">&ldquo;</span>\n$2\n<span class=\"qr\">&rdquo;</span>\n\n</blockquote>\n",
		$text, -1, $c
	); while ($c);
	
	//remove the extra linebreaks addeded between our theme quotes
	//(required so that extra `<br />`s don’t get added!)
	$text = preg_replace (
		array ('/&ldquo;<\/span>\n/ms', '/\n<span class="qr">/ms'),
		array ('&ldquo;</span>', 	'<span class="qr">'),
	$text);
	
	/* finalise:
	   -------------------------------------------------------------------------------------------------------------- */
	//add paragraph tags between blank lines
	foreach (preg_split ('/\n{2,}/', trim ($text), -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
		//if not a blockquote, wrap in a paragraph
		if (!preg_match ('/^<\/?b|^&_/', $chunk)) $chunk = "<p>\n".str_replace ("\n", "<br />\n", $chunk)."\n</p>";
		$text = @$result .= "\n$chunk";
	}
	
	//restore code blocks
	foreach ($code as &$html) $text = preg_replace ('/&__CODE__;/', $html, $text, 1);
	
	return $text;
}

/* ====================================================================================================================== */

//check to see if a name is a known moderator in mods.txt
function isMod ($name) {
	global $MODS;
	return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}

/* ====================================================================================================================== */

//regenerate a folder's RSS file (all changes happening in a folder)
function indexRSS () {
	//get list of threads
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);	//look ma, no loop!
	
	//get the last post made in each thread as an RSS item
	foreach (array_slice ($threads, 0, FORUM_THREADS) as $thread) {
		$xml  = simplexml_load_file ($thread) or die ("$thread is malformed.");
		$item = $xml->channel->item[0];
		
		@$rss .= template_tags (<<<XML
<item>
	<title>&__TITLE__;</title>
	<link>&__URL__;</link>
	<author>&__NAME__;</author>
	<pubDate>&__DATE__;</pubDate>
	<description>&__TEXT__;</description>
</item>
XML
		, array (
			'TITLE'	=> safeHTML ($item->title),
			'URL'	=> $item->link,
			'NAME'	=> safeHTML ($item->author),
			'DATE'	=> gmdate ('r', strtotime ($item->pubDate)),
			'TEXT'	=> safeHTML ($item->description),
		));
	}
	
	file_put_contents ('index.xml', template_tags (<<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="http://${_SERVER['HTTP_HOST']}&__PATH__;index.xml" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>http://${_SERVER['HTTP_HOST']}/</link>

&__ITEMS__;

</channel>
</rss>
XML
	, array (
		'PATH'	=> PATH_URL,
		'TITLE'	=> safeHTML (FORUM_NAME.(PATH ? ' / '.PATH : '')),
		//if all threads are deleted, there won’t be any <item>s
		'ITEMS'	=> @$rss ? $rss : ""
	)));
	
	/* sitemap
	   -------------------------------------------------------------------------------------------------------------- */
	chdir (FORUM_ROOT);
	
	//we’re going to use the RSS files as sitemaps
	$folders = array ('');
	//get list of sub-forums
	foreach (array_filter (
		//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
		preg_grep ('/^(\.|users$|themes$)/', scandir (FORUM_ROOT.'/'), PREG_GREP_INVERT), 'is_dir'
	) as $folder) $folders[] = $folder;
	
	//generate a sitemap index file, to point to each index RSS file in the forum:
	//<https://www.google.com/support/webmasters/bin/answer.py?answer=71453>
	foreach ($folders as $folder) {
		//get the time of the latest item in the RSS feed
		//(the RSS feed may be missing as they are not generated in new folders until something is posted)
		if (
			@$xml = simplexml_load_file (FORUM_ROOT.($folder ? "/$folder" : '').'/index.xml')
		) @$sitemaps .= template_tags (<<<XML
<sitemap>
	<loc>http://${_SERVER['HTTP_HOST']}&__FILE__;/index.xml</loc>
	<lastmod>&__DATE__;</lastmod>
</sitemap>

XML
		, array (
			'FILE'	=> $folder ? safeURL ("/$folder", false) : '',
			'DATE'	=> gmdate ('r', strtotime ($xml->channel->item[0]->pubDate))
		));
	}
	
	file_put_contents (
		FORUM_ROOT."/sitemap.xml",
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
		"<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n".
		@$sitemaps.
		"</sitemapindex>"
	);
	
	//you saw nothing, right?
	clearstatcache ();
}

?>