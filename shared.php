<?php //reduce some duplication
/* ====================================================================================================================== */
/* NoNonsense Forum v8 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//default UTF-8 throughout
mb_internal_encoding ('UTF-8');
mb_regex_encoding    ('UTF-8');

/* default config:
   ---------------------------------------------------------------------------------------------------------------------- */
//try set the forum owner’s personal config ('config.php') if it exists
@include './config.php';

//*don’t* change these values here, copy 'config.example.php' into a 'config.php' file and customise.
//these are here so that if I add a new value, the forum won’t break if you don’t update your config file

//see 'config.example.php' for descriptions of these
@define ('FORUM_HTTPS',		false);
@define ('FORUM_NAME',		'NoNonsense Forum');
@define ('FORUM_TIMEZONE',	'UTC');
@define ('DATE_FORMAT',		'd M ’y · H:i');
@define ('FORUM_THEME',		'greyscale');
@define ('FORUM_ENABLED',	true);
@define ('FORUM_NEWBIES',	true);
@define ('FORUM_THREADS',	50);
@define ('FORUM_POSTS',		25);
@define ('SIZE_NAME',		20);
@define ('SIZE_PASS',		20);
@define ('SIZE_TITLE',		100);
@define ('SIZE_TEXT',		50000);
@define ('TEMPLATE_RE',		'RE[&__NO__;]: ');
@define ('TEMPLATE_APPEND',	'<p class="appended"><b>&__AUTHOR__;</b> added on <time datetime="&__DATETIME__;">&__TIME__;</time></p>');
@define ('TEMPLATE_DEL_USER',	'<p>This post was deleted by its owner</p>');
@define ('TEMPLATE_DEL_MOD', 	'<p>This post was deleted by a moderator</p>');

/* constants: some stuff we don’t expect to change
   ---------------------------------------------------------------------------------------------------------------------- */
define ('FORUM_ROOT',		dirname (__FILE__));			//full server-path for absolute references
define ('FORUM_PATH', 		str_replace (				//relative from webroot--if running in a folder
	array ('\\', '//'), '/',					//- replace Windows forward-slash with backslash
	dirname ($_SERVER['SCRIPT_NAME']).'/'				//- always starts with a slash and ends in one
));
define ('FORUM_URL',
	'http'.(FORUM_HTTPS || @$_SERVER['HTTPS'] == 'on' ? 's' : '').	//base URL
	'://'.$_SERVER['HTTP_HOST']
);

//these are just some enums for templates to react to
define ('ERROR_NONE',		0);
define ('ERROR_NAME',		1);					//name entered is invalid / blank
define ('ERROR_PASS',		2);					//password is invalid / blank
define ('ERROR_TITLE',		3);					//the title is invalid / blank
define ('ERROR_TEXT',		4);					//post text is invalid / blank
define ('ERROR_AUTH',		5);					//name / password did not match

//PHP 5.3 issues a warning if the timezone is not set when using date commands
date_default_timezone_set (FORUM_TIMEZONE);

//if enabled, enforce HTTPS
if (FORUM_HTTPS) if (@$_SERVER['HTTPS'] != 'off') {
	//if forced-HTTPS is on and a HTTPS connection is being used, send the 30-day HSTS header
	//see <en.wikipedia.org/wiki/Strict_Transport_Security> for more details
	header ('Strict-Transport-Security: max-age=2592000');
} else {
	//if forced-HTTPS is on and a HTTPS connection is not being used, redirect to the HTTPS version of the current page
	//(we don’t die here so that should the redirect be ignored, the HTTP version of the page will still be given)
	header ('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
}


/* common input
   ====================================================================================================================== */
//all our pages use path (often optional) so this is done here
define ('PATH',     preg_match ('/[^.\/&]+/', @$_GET['path']) ? $_GET['path'] : '');
//these two get used an awful lot
define ('PATH_URL', !PATH ? FORUM_PATH : safeURL (FORUM_PATH.PATH.'/', false));	//when outputting as part of a URL
define ('PATH_DIR', !PATH ? '/' : '/'.PATH.'/');				//serverside, like `chdir` / `unlink`

//we have to change directory for `is_dir` to work, see <uk3.php.net/manual/en/function.is-dir.php#70005>
//being in the right directory is also assumed for reading 'mods.txt' and when generating the RSS (`indexRSS`)
//(oddly with `chdir` the path must end in a slash)
@chdir (FORUM_ROOT.PATH_DIR) or die ("Invalid path");


/* access control
   ====================================================================================================================== */
/* name / password authorisation:
   ---------------------------------------------------------------------------------------------------------------------- */
//for HTTP authentication (sign-in / private forums):
//- CGI workaround <orangejuiceliberationfront.com/http-auth-with-php-in-cgi-mode-e-g-on-dreamhost/>
if (@$_SERVER['HTTP_AUTHORIZATION']) list ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode (
	':', base64_decode (substr ($_SERVER['HTTP_AUTHORIZATION'], 6))
);
define ('HTTP_AUTH_UN', @$_SERVER['PHP_AUTH_USER']);	//username if using HTTP authentication
define ('HTTP_AUTH_PW', @$_SERVER['PHP_AUTH_PW']);	//password if using HTTP authentication

//all pages can accept a name / password when committing actions (new thread / post &c.)
//in the case of HTTP authentication (sign in / private forums), these are provided in the request header
define ('NAME', HTTP_AUTH_UN ? HTTP_AUTH_UN : safeGet (@$_POST['username'], SIZE_NAME));
define ('PASS', HTTP_AUTH_PW ? HTTP_AUTH_PW : safeGet (@$_POST['password'], SIZE_PASS, false));

if ((
	//if any HTTP authentication is given, we don’t need to validate form fields
	HTTP_AUTH_UN && HTTP_AUTH_PW
) || (
	//if an input form was submitted:
	NAME && PASS &&
	//the email check is a fake hidden field in the form to try and fool spam bots
	isset ($_POST['email']) && @$_POST['email'] == 'example@abc.com' &&
	//I wonder what this does...?
	((isset ($_POST['x']) && isset ($_POST['y'])) || (isset ($_POST['submit_x']) && isset ($_POST['submit_y'])))
)) {
	//users are stored as text files based on the hash of the given name
	$name = hash ('sha512', strtolower (NAME));
	$user = FORUM_ROOT."/users/$name.txt";
	
	//create the user, if new:
	//- if registrations are allowed (`FORUM_NEWBIES`)
	//- you can’t create new users with the HTTP_AUTH sign in
	if (FORUM_NEWBIES && !HTTP_AUTH_UN && !file_exists ($user)) file_put_contents ($user, hash ('sha512', $name.PASS));
	
	//does password match?
	define ('AUTH', @file_get_contents ($user) == hash ('sha512', $name.PASS));
	
	//if signed in with HTTP_AUTH, confirm that it’s okay to use
	//(e.g. the user could still have given the wrong password with HTTP_AUTH)
	define ('HTTP_AUTH', HTTP_AUTH_UN ? AUTH : false);
} else {
	define ('AUTH',      false);
	define ('HTTP_AUTH', false);
}

//if the sign-in link was clicked, invoke a HTTP_AUTH request in the browser
if (!HTTP_AUTH && isset ($_GET['signin'])) {
	header ('WWW-Authenticate: Basic');
	header ('HTTP/1.0 401 Unauthorized');
}

/* access rights
   ---------------------------------------------------------------------------------------------------------------------- */
//get the lock status of the current forum we’re in:
//"threads"	- only users in "mods.txt" / "members.txt" can start threads, but anybody can reply
//"posts"	- only users in "mods.txt" / "members.txt" can start threads or reply
//"private"	- only users in "mods.txt" / "members.txt" can enter and use the forum, it is hidden from everybody else
define ('FORUM_LOCK', trim (@file_get_contents ('locked.txt')));

//get the list of moderators:
$MODS = array (
	//'mods.txt' on root for mods on all sub-forums
	'GLOBAL'=> file_exists (FORUM_ROOT.'/mods.txt')
		? file (FORUM_ROOT.'/mods.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES)
		: array (),
	//if in a sub-forum, the local 'mods.txt'
	'LOCAL'	=> PATH && file_exists ('mods.txt')
		? file ('mods.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES)
		: array ()
);

//get the list (if any) of users allowed to access this current forum
$MEMBERS = file_exists ('members.txt') ? file ('members.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES) : array ();

//is the current user a moderator in this forum?
define ('IS_MOD',    isMod (NAME));
//is the current user a member of this forum?
define ('IS_MEMBER', isMember (NAME));

//if the forum is private, check the current user and issue an auth request if not signed in or allowed
if (FORUM_LOCK == 'private' && !(IS_MOD || IS_MEMBER)) {
	header ('WWW-Authenticate: Basic');
	header ('HTTP/1.0 401 Unauthorized');
	//todo: a proper error page, if I make a splash/login screen for a private root-forum
	die ("Authorisation required.");
}

//can the current user post new threads in the current forum?
//(posting replies is dependent on the the thread -- if locked -- so tested in 'thread.php')
define ('CAN_POST', FORUM_ENABLED && (
	//- if the user is a moderator or member of the current forum, they can post
	IS_MOD || IS_MEMBER ||
	//- if the forum is unlocked (mods will have to log in to see the form)
	!FORUM_LOCK
));

/* ---------------------------------------------------------------------------------------------------------------------- */

//stop browsers caching, so you don’t have to refresh every time to see changes
header ('Cache-Control: no-cache', true);
header ('Expires: 0', true);

/* ====================================================================================================================== */

//sanitise input:
function safeGet ($data, $len=0, $trim=true) {
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
	//will the URL be output into HTML? (rather than, say, the HTTP headers)
	//if so, encode for HTML too, e.g. "&" must be "&amp;" within URLs when in HTML
	return $is_HTML ? safeHTML ($text) : $text;
}

//replace markers (“&__TAG__;”) in the template with some other text
function template_tags ($template, $values) {
	foreach ($values as $key=>&$value) $template = str_replace ("&__${key}__;", $value , $template);
	return $template;
}

//produces a truncated list of page numbers around the current page
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
	while (preg_match ('/^(?-s:(\s*)([%$])(.*?))\n(.*?)\n(?-s:\s*)\2(["”»]?)$/msu', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$code[] = "<pre><span class=\"ct\">{$m[2][0]}{$m[3][0]}</span>\n"
			 //unindent code blocks that have been quoted
		         .preg_replace ("/^\s{1,".strlen ($m[1][0])."}/m", '', $m[4][0])
		         ."\n<span class=\"cb\">{$m[2][0]}</span></pre>"
		;
		//replace the code block with a placeholder
		$text = substr_replace ($text, "\n&__CODE__;".$m[5][0], $m[0][1], strlen ($m[0][0]));
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
		array ('/&ldquo;<\/span>\n/',	'/\n<span class="qr">/'),
		array ('&ldquo;</span>',	'<span class="qr">'),
	$text);
	
	/* finalise:
	   -------------------------------------------------------------------------------------------------------------- */
	//add paragraph tags between blank lines
	foreach (preg_split ('/\n{2,}/', trim ($text), -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
		//if not a blockquote, wrap in a paragraph
		if (!preg_match ('/^<\/?bl|^&_/', $chunk)) $chunk = "<p>\n".str_replace ("\n", "<br />\n", $chunk)."\n</p>";
		$text = @$result .= "\n$chunk";
	}
	
	//restore code blocks
	foreach ($code as &$html) $text = preg_replace ('/&__CODE__;/', $html, $text, 1);
	
	return $text;
}

/* ====================================================================================================================== */

//check to see if a name is a known moderator in 'mods.txt'
function isMod ($name) {
	global $MODS;
	return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}

function isMember ($name) {
	global $MEMBERS;
	return in_array (strtolower ($name), array_map ('strtolower', $MEMBERS));
}

/* ====================================================================================================================== */

//regenerate a folder's RSS file (all changes happening in a folder)
function indexRSS () {
	//get list of threads
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	//get the last post made in each thread as an RSS item
	foreach (array_slice ($threads, 0, FORUM_THREADS) as $thread) if ($xml = @simplexml_load_file ($thread)) {
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
<atom:link href="&__URL__;&__PATH__;index.xml" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>&__URL__;</link>

&__ITEMS__;

</channel>
</rss>
XML
	, array (
		'URL'	=> FORUM_URL,
		'PATH'	=> PATH_URL,
		'TITLE'	=> safeHTML (FORUM_NAME.(PATH ? ' / '.PATH : '')),
		//if all threads are deleted, there won’t be any <item>s
		'ITEMS'	=> @$rss ? $rss : ''
	)));
	
	/* sitemap
	   -------------------------------------------------------------------------------------------------------------- */
	//we’re going to use the RSS files as sitemaps
	chdir (FORUM_ROOT);
	
	//get list of sub-forums
	$folders = array ('');
	foreach (array_filter (
		//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
		preg_grep ('/^(\.|users$|themes$)/', scandir (FORUM_ROOT.'/'), PREG_GREP_INVERT), 'is_dir'
	) as $folder) $folders[] = $folder;
	
	//generate a sitemap index file, to point to each index RSS file in the forum:
	//<https://www.google.com/support/webmasters/bin/answer.py?answer=71453>
	foreach ($folders as $folder) if (
		//get the time of the latest item in the RSS feed
		//(the RSS feed may be missing as they are not generated in new folders until something is posted)
		@$xml = simplexml_load_file (FORUM_ROOT.($folder ? "/$folder" : '').'/index.xml')
	) @$sitemaps .= template_tags (<<<XML
<sitemap>
	<loc>&__URL__;&__FILE__;/index.xml</loc>
	<lastmod>&__DATE__;</lastmod>
</sitemap>

XML
	, array (
		'URL'	=> FORUM_URL,
		'FILE'	=> $folder ? safeURL ("/$folder", false) : '',
		'DATE'	=> gmdate ('r', strtotime ($xml->channel->item[0]->pubDate))
	));
	
	file_put_contents (
		FORUM_ROOT.'/sitemap.xml',
		"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
		"<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n".
		@$sitemaps.
		"</sitemapindex>"
	);
	
	//you saw nothing, right?
	clearstatcache ();
}

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

?>