<?php //reduce some duplication
/* ====================================================================================================================== */
/* NoNonsense Forum v11 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

/* configuration:
   ---------------------------------------------------------------------------------------------------------------------- */
//default UTF-8 throughout
mb_internal_encoding ('UTF-8');
mb_regex_encoding    ('UTF-8');

//try set the forum owner’s personal config ('config.php'), if it exists
@include './config.php';
//include the defaults: (for anything missing from the user’s config)
//see that file for descriptions of the different available options
@(include './config.default.php') or die ("config.default.php missing!");

//PHP 5.3 issues a warning if the timezone is not set when using date commands
//(`FORUM_TIMEZONE` is set in the config and defaults to 'UTC')
date_default_timezone_set (FORUM_TIMEZONE);

/* constants: some stuff we don’t expect to change
   ---------------------------------------------------------------------------------------------------------------------- */
define ('FORUM_ROOT',		dirname (__FILE__));		//full server-path for absolute references
define ('FORUM_PATH', 		str_replace (			//relative from webroot--if running in a folder:
	array ('\\', '//'), '/',				//- replace Windows forward-slash with backslash
	dirname ($_SERVER['SCRIPT_NAME']).'/'			//- always starts with a slash and ends in one
));
define ('FORUM_URL',		'http'.				//base URL to produce hyperlinks throughout:
	(FORUM_HTTPS || @$_SERVER['HTTPS'] == 'on' ? 's' : '').	//- if HTTPS is enforced, links in RSS will use it
	'://'.$_SERVER['HTTP_HOST']
);

//these are just some enums for templates to react to
define ('ERROR_NONE',		0);
define ('ERROR_NAME',		1);				//name entered is invalid / blank
define ('ERROR_PASS',		2);				//password is invalid / blank
define ('ERROR_TITLE',		3);				//the title is invalid / blank
define ('ERROR_TEXT',		4);				//post text is invalid / blank
define ('ERROR_AUTH',		5);				//name / password did not match

//load the user’s theme configuration, if it exists
@include FORUM_ROOT.'/themes/'.FORUM_THEME.'/theme.config.php';
//include the theme defaults
//(can’t use `or die` on this otherwise it casts the string concatination to a bool!)
@(include FORUM_ROOT.'/themes/'.FORUM_THEME.'/theme.config.default.php') or die ("theme.config.default.php missing!");


/* common input
   ====================================================================================================================== */
//all our pages use 'path' (often optional) to specify the sub-forum being viewed, so this is done here
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
//for HTTP authentication (sign-in):
//- CGI workaround <orangejuiceliberationfront.com/http-auth-with-php-in-cgi-mode-e-g-on-dreamhost/>
if (@$_SERVER['HTTP_AUTHORIZATION']) list ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode (
	':', base64_decode (substr ($_SERVER['HTTP_AUTHORIZATION'], 6))
);
define ('HTTP_AUTH_UN', @$_SERVER['PHP_AUTH_USER']);	//username if using HTTP authentication
define ('HTTP_AUTH_PW', @$_SERVER['PHP_AUTH_PW']);	//password if using HTTP authentication

//all pages can accept a name / password when committing actions (new thread / reply &c.)
//in the case of HTTP authentication (sign in), these are provided in the request header instead
define ('NAME', HTTP_AUTH_UN ? HTTP_AUTH_UN : safeGet (@$_POST['username'], SIZE_NAME));
define ('PASS', HTTP_AUTH_PW ? HTTP_AUTH_PW : safeGet (@$_POST['password'], SIZE_PASS, false));

if ((	//if HTTP authentication is used, we don’t need to validate the form fields
	HTTP_AUTH_UN && HTTP_AUTH_PW
) || (	//if an input form was submitted:
	//are the name and password non-blank?
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
	//- if registrations are allowed (`FORUM_NEWBIES` is true)
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

/* access rights
   ---------------------------------------------------------------------------------------------------------------------- */
//get the lock status of the current forum we’re in:
//"threads"	- only users in "mods.txt" / "members.txt" can start threads, but anybody can reply
//"posts"	- only users in "mods.txt" / "members.txt" can start threads or reply
define ('FORUM_LOCK', trim (@file_get_contents ('locked.txt')));

//get the list of moderators:
//(`file` returns NULL if the file doesn’t exist; casting that to an array creates an array with a blank element, and
//`array_filter` removes blank elements, including blank lines in the text file; we could use the `FILE_SKIP_EMPTY_LINES`
//flag, but `array_filter` kills two birds with one stone since we don’t have to check if the file exists beforehand.)
$MODS = array (
	//'mods.txt' on root for mods on all sub-forums
	'GLOBAL'=>        array_filter ((array) @file (FORUM_ROOT.'/mods.txt', FILE_IGNORE_NEW_LINES)),
	//if in a sub-forum, the local 'mods.txt'
	'LOCAL'	=> PATH ? array_filter ((array) @file ('mods.txt', FILE_IGNORE_NEW_LINES)) : array ()
);

//get the list (if any) of users allowed to access this current forum
$MEMBERS = array_filter ((array) @file ('members.txt', FILE_IGNORE_NEW_LINES));

//is the current user a moderator in this forum?
define ('IS_MOD',    isMod (NAME));
//is the current user a member of this forum?
define ('IS_MEMBER', isMember (NAME));

//can the current user post new threads in the current forum?
//(posting replies is dependent on the the thread -- if locked -- so tested in 'thread.php')
define ('CAN_POST', FORUM_ENABLED && (
	//- if the user is a moderator or member of the current forum, they can post
	IS_MOD || IS_MEMBER ||
	//- if the forum is unlocked (mods will have to log in to see the form)
	!FORUM_LOCK
));

/* send HTTP headers
   ---------------------------------------------------------------------------------------------------------------------- */
//if enabled, enforce HTTPS
if (FORUM_HTTPS) if (@$_SERVER['HTTPS'] == 'on') {
	//if forced-HTTPS is on and a HTTPS connection is being used, send the 30-day HSTS header
	//see <en.wikipedia.org/wiki/Strict_Transport_Security> for more details
	header ('Strict-Transport-Security: max-age=2592000');
} else {
	//if forced-HTTPS is on and a HTTPS connection is not being used, redirect to the HTTPS version of the current page
	//(we don’t die here so that should the redirect be ignored, the HTTP version of the page will still be given)
	header ('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
}

//if the sign-in link was clicked, (and they're not already signed-in), invoke a HTTP_AUTH request in the browser:
//the browser will pop up a login box itself (no HTML involved) and continue to send the name & password with each request
//(these are trapped higher up as HTTP_AUTH_UN and HTTP_AUTH_PW and are authenticated the same as the regular post form)
if (!HTTP_AUTH && isset ($_GET['signin'])) {
	header ('WWW-Authenticate: Basic');
	header ('HTTP/1.0 401 Unauthorized');
	//we don't die here so that if they cancel the login prompt, they won't get a blank page
}

//stop browsers caching, so you don’t have to refresh every time to see changes
header ('Cache-Control: no-cache', true);
header ('Expires: 0', true);


//everything prepared; below are just shared functions
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
		…			…
		%			$
	*/
	$code = array ();
	while (preg_match ('/^(?-s:(\h*)([%$])(.*?))\n(.*?)\n\h*\2(["”»]?)$/msu', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$code[] = "<pre><span class=\"ct\">{$m[2][0]}{$m[3][0]}</span>\n"
			 //unindent code blocks that have been quoted
		         .(strlen ($m[1][0]) ? preg_replace ("/^\s{1,".strlen ($m[1][0])."}/m", '', $m[4][0]) : $m[4][0])
		         ."\n<span class=\"cb\">{$m[2][0]}</span></pre>"
		;
		//replace the code block with a placeholder:
		//(we will have to remove the code chunks from the source text to avoid the other markup processing from
		//munging it and then restore the chunks back later)
		$text = substr_replace ($text, "\n&__CODE__;".$m[5][0], $m[0][1], strlen ($m[0][0]));
	}
	
	/* inline code / teletype text:
	   -------------------------------------------------------------------------------------------------------------- */
	// example: `code` or ``code``
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
		array ("\n\n<h2>$1</h2>\n",			"\n\n<p class=\"hr\"/>$1</p>\n"),
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
	foreach ($code as $html) $text = preg_replace ('/&__CODE__;/', $html, $text, 1);
	
	return $text;
}

/* ====================================================================================================================== */

//check to see if a name is a known moderator
function isMod ($name) {
	global $MODS; return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}

function isMember ($name) {
	global $MEMBERS; return in_array (strtolower ($name), array_map ('strtolower', $MEMBERS));
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
		preg_grep ('/^(\.|users$|themes$)/', scandir (FORUM_ROOT.'/'), PREG_GREP_INVERT), 'is_dir'
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

class NNFDocument extends DOMDocument {
	public function __construct () {
		//can define an xpath here and register other NNF classes so that we can do
		//`DOMElement->xpath (...)` rather than `xpath->query ('...', DOMElement)`
	}
	
	public function saveXML ($DOMNode=NULL, $options=0) {
		return preg_replace (array (
			'/^<\?xml.*?>\n/',				//1: remove XML prolog
			'/<(.*?[^ ])\/>/s',				//2: add space to self-closing
			'/<(div|[ou]l|textarea)(.*?) ?\/>/'		//3: fix broken self-closed tags
		), array (
			'',
			'<$1 />',
			'<$1$2></$1>'
		), parent::saveXML ($DOMNode, $options));
	}
	
	public function __destruct () {
		//can do the auto-removal of NNF template attributes here
	}
}

class NNFXPath extends DOMXPath {
	public function set ($items, $DOMNode=NULL) {
		foreach ($items as $query => $value) $this->setValue ($query, $value, $DOMNode);
	}
	
	public function setValue ($query, $value, $DOMNode=NULL) {
		foreach ($this->query ($query, $DOMNode) as $node) if (is_string ($value)) {
			$node->nodeValue = $value;
		} else {
			$node->nodeValue = '';
			$node->appendChild ($value);
		}
	}
	
	public function replaceNode ($query, $value, $DOMNode=NULL) {
		foreach ($this->query ($query, $DOMNode) as $node) $node->parentNode->replaceChild (
			//if just raw text is provided, use that, if it’s a node / documentFragment then use that
			is_string ($value) ? new DOMText ($value) : $value,
		$node);
	}
	
	public function removeNode ($query, $DOMNode=NULL) {
		foreach ($this->query ($query, $DOMNode) as $node) $node->parentNode->removeChild ($node);
	}
	
	public function removeAttr ($query, $DOMNode=NULL) {
		foreach ($this->query ($query, $DOMNode) as $node) $node->parentNode->removeAttributeNode ($node); 
	}
	
	public function addClass ($query, $new_class, $DOMNode=NULL) {
		foreach ($this->query ($query, $DOMNode) as $node) {
			//first determine if there is a 'class' attribute already?
			if ($node->hasAttributes () && $class = $node->getAttribute ('class')) {
				//if the new class is not already in the list, add it in
				if (!in_array ($new_class, explode (' ', $class)))
					$node->setAttribute ('class', "$class $new_class")
				;
			} else {
				//no class attribute to begin with, add it
				$node->setAttribute ('class', $new_class);
			}
		};
	}
}

?>