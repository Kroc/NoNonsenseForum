<?php //reduce some duplication
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2010
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

error_reporting (-1);			//let me know when I’m being stupid
date_default_timezone_set ('UTC');	//PHP 5.3 issues a warning if the timezone is not set when using date commands

/* constants: some stuff we don’t expect to change
   ---------------------------------------------------------------------------------------------------------------------- */
define ('FORUM_ROOT',		dirname (__FILE__));			//full path for absolute references
define ('FORUM_URL',		'http://'.$_SERVER['HTTP_HOST']);	//todo: https support

/* options: stuff for you
   ---------------------------------------------------------------------------------------------------------------------- */
define ('FORUM_ENABLED',	true);					//if posting is allowed
define ('FORUM_THEME',		'C=64');				//theme name, in “/themes/*”
define ('FORUM_THREADS',	50);					//number of threads per page on the index
define ('FORUM_POSTS',		25);					//number of posts per page on threads
define ('FORUM_SALT',		'C64:');				//string to prepend to names/passwords when hashing

//include the HTML skin
require_once 'themes/'.FORUM_THEME.'/theme.php';

//all our pages use path (often optional) so this is done here
$PATH = preg_match ('/[^.\/&]+/', @$_GET['path']) ? $_GET['path'] : '';
//these two get used an awful lot
$PATH_URL = !$PATH ? '/' : '/'.rawurlencode ($PATH).'/';		//when outputting as part of a URL to HTML
$PATH_DIR = !$PATH ? '/' : "/$PATH/";					//when using serverside, like `chdir` / `unlink`

//we have to change directory for `is_dir` to work, see <uk3.php.net/manual/en/function.is-dir.php#70005>
//being in the right directory is also assumed for reading 'mods.txt' and in 'rss.php'
//(oddly with `chdir` the path must end in a slash)
chdir (FORUM_ROOT.$PATH_DIR);

//whilst page number is not used everywhere (like 'delete.php'), it does no harm to get it here because it can simply be
//ignored on 'delete.php' &c. whilst avoiding duplicated code on the scripts that do use it
$PAGE = preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1;

/* ---------------------------------------------------------------------------------------------------------------------- */

//stop browsers caching, so you don’t have to refresh every time to see changes
//(this needs to be better placed and tested)
header ('Cache-Control: no-cache');
header ('Expires: -1');


/* ====================================================================================================================== */

//replace a marker (“&__TAG__;”) in the template with some other text
function template_tag ($s_template, $s_tag, $s_content) {
	return str_replace ("&__${s_tag}__;", $s_content , $s_template);
}

//replace many markers in one go
function template_tags ($s_template, $a_values) {
	foreach ($a_values as $key=>&$value) $s_template = template_tag ($s_template, $key, $value);
	return $s_template;
}

//santise output:
function safeHTML ($text) {
	//encode a string for insertion into an HTML element
	return htmlspecialchars ($text, ENT_NOQUOTES, 'UTF-8');
}
function safeString ($text) {
	//encode a string for insertion between quotes in an HTML attribute (like `value` or `title`)
	return htmlspecialchars ($text, ENT_COMPAT, 'UTF-8');
}

/* ====================================================================================================================== */

//<http://stackoverflow.com/questions/2092012/simplexml-how-to-prepend-a-child-in-a-node/2093059#2093059>
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

function checkName ($name, $pass) {
	//users are stored as text files based on the hash of the given name
	$user = FORUM_ROOT.'/users/'.md5 (FORUM_SALT.strtolower ($name)).'.txt';
	//create the user, if new
	if (!file_exists ($user)) file_put_contents ($user, md5 (FORUM_SALT.$pass));
	//does password match?
	return (file_get_contents ($user) == md5 (FORUM_SALT.$pass));
}

//check to see if a name is a known moderator in mods.txt
function isMod ($name) {
	//'mods.txt' on webroot defines moderators for the whole forum
	return (file_exists (FORUM_ROOT.'/mods.txt') && in_array (
		strtolower ($name),  //(names are case insensitive)
		array_map ('strtolower', file (FORUM_ROOT.'/mods.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES))
		
	//a 'mods.txt' can also exist in sub-folders for per-folder moderators
	//(it is assumed that the current working directory has been changed to the sub-folder in question)
	)) || (file_exists ('mods.txt') && in_array (
		strtolower ($name),
		array_map ('strtolower', file ('mods.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES))
	));
}

/* ====================================================================================================================== */

function pageLinks ($page, $pages) {
	//always include the first page
	$html[] = template_tag ($page == 1 ? TEMPLATE_PAGES_CURRENT : TEMPLATE_PAGES_PAGE, 'PAGE', 1);
	//more than one page?
	if ($pages > 1) {
		//if previous page is not the same as 2, include ellipses
		//(there’s a gap between 1, and current-page minus 1, e.g. "1, …, 54, 55, 56, …, 100")
		if ($page-1 > 2) $html[] = TEMPLATE_PAGES_GAP;
		//the page before the current page
		if ($page-1 > 1) $html[] = template_tag (TEMPLATE_PAGES_PAGE, 'PAGE', $page-1);
		//the current page
		if ($page != 1) $html[] = template_tag (TEMPLATE_PAGES_CURRENT, 'PAGE', $page);
		//the page after the current page (if not at end)
		if ($page+1 < $pages) $html[] = template_tag (TEMPLATE_PAGES_PAGE, 'PAGE', $page+1);
		//if there’s a gap between page+1 and the last page
		if ($page+1 < $pages-1) $html[] = TEMPLATE_PAGES_GAP;
		//last page
		if ($page != $pages) $html[] = template_tag (TEMPLATE_PAGES_PAGE, 'PAGE', $pages);
	}
	
	return implode (TEMPLATE_PAGES_SEPARATOR, $html);
}

function formatText ($text) {
	//sanitise HTML against injection
	$text = safeHTML ($text);
	
	//find URLs
	$text = preg_replace (
		'/(?:
			((?:http|ftp)s?:\/\/)					# $1 = protocol
			(?:www\.)?						# ignore www in friendly URL
			(							# $2 = friendly URL (no protocol)
				[a-z0-9.-]{1,}(?:\.[a-z]{2,4})+			# domain name
			)(\/)?							# $3 = slash is excluded from friendly URL
			(?(3)(							# $4 = folders and filename, relative URL
				(?:						# folders and filename
					[:).](?!\s|$)|				# ignore a colon, bracket or dot on the end
					[\/a-z0-9_!~*\'(;?@&=+$,%-]
				)*
				(?:\x23[^\s"]+)?				# bookmark
			)?)
		|
			([a-z0-9._%+-]+@[a-z0-9.-]{1,}(?:\.[a-z]{2,4})+)	# $5 = e-mail
		)/exi',
		'"<a href=\"".("$5"?"mailto:$5":("$1"?"$1":"http://")."$2$3$4")."\">$2$5".("$4"?"/…":"")."</a>"',
	$text);
	
	foreach (preg_split ('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
		$chunk = "<p>\n".str_replace ("\n", '<br />', $chunk)."\n</p>";
		$text = @$result .= "\n$chunk";
	}
	
	return $text;
}

?>