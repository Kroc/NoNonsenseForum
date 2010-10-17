<?php

//PHP 5.3 issues a warning if the timezone is not set when using date-related commands
date_default_timezone_set ('Europe/London');

define ('APP_HOST',    'forum.camendesign.com');	//preferred domain
define ('APP_ROOT',    dirname (__FILE__).'/');		//full path for absolute references
define ('APP_ENABLED', true);				//if posting is allowed
define ('APP_THREADS', 50);				//number of threads per page on the index
define ('APP_POSTS',   25);				//number of posts per page on the index
define ('APP_SALT',    'C64:');				//a string to prepend to names/passwords when hashing

//<uk3.php.net/manual/en/function.is-dir.php#70005>
chdir (APP_ROOT);

//include the HTML skin
require_once "theme/template.php";

/* ====================================================================================================================== */

//stop browsers caching, so you don’t have to refresh every time to see changes
//(this needs to be better placed and tested)
header("Cache-Control: no-cache");
header("Expires: -1");


//replace a marker (“&__TAG__;”) in the template with some other text
function template_tag ($s_template, $s_tag, $s_content) {
	return str_replace ("&__${s_tag}__;", $s_content , $s_template);
}

//replace many markers in one go
function template_tags ($s_template, $a_values) {
	foreach ($a_values as $key=>&$value) $s_template = template_tag ($s_template, $key, $value);
	return $s_template;
}

function flattenTitle ($s_title) {
	return preg_replace ('/_{2,}/', '_', preg_replace (
		//replace non alphanumerics with underscores (don’t use more than 2 above)
		'/[^_a-z0-9-]/i', '_',
		//for neatness use "Microsofts" instead of "Microsoft_s" when removing the apostrophe
		str_replace (array ("'", "‘", "’", '"', '“','”'), '', strtolower ($s_title))
	));
}

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

function checkName ($name, $pass) {
	//users are stored as text files based on the hash of the given name
	$user = APP_ROOT."users/".md5 (APP_SALT.$name).".txt";
	//create the user, if new
	if (!file_exists ($user)) file_put_contents ($user, md5 (APP_SALT.$pass));
	//does password match?
	return (file_get_contents ($user) == md5 (APP_SALT.$pass));
}

function createRSSIndex () {
	//get list of threads
	$threads = array_fill_keys (preg_grep ('/\.xml$/', scandir ('.')) , 0);
	foreach ($threads as $file => &$date) $date = filectime ($file);
	arsort ($threads, SORT_NUMERIC);
	
	$threads = array_slice ($threads, 0, APP_THREADS);
	foreach ($threads as $file => $date) {
		$xml = simplexml_load_file ($file);
		$items = $xml->channel->xpath ('item');
		$item = end ($items);
		
		@$rss .= template_tags (TEMPLATE_RSS_ITEM, array (
			'APP_HOST'	=> APP_HOST,
			'TITLE'		=> htmlspecialchars ($xml->channel->title, ENT_NOQUOTES, 'UTF-8'),
			'URL'		=> pathinfo ($file, PATHINFO_FILENAME),
			'NAME'		=> htmlspecialchars ($item->author, ENT_NOQUOTES, 'UTF-8'),
			'DATE'		=> gmdate ('r', strtotime ($item->pubDate)),
			'TEXT'		=> htmlspecialchars ($item->description, ENT_NOQUOTES, 'UTF-8'),
		));
	}
	
	file_put_contents ("index.rss", template_tags (TEMPLATE_RSS_INDEX, array (
		'APP_HOST' => APP_HOST,
		'TITLE'    => $xml->channel->title,
		'ITEMS'    => $rss
	)), LOCK_EX);
}

function formatText ($text) {
	$text = htmlspecialchars ($text, ENT_NOQUOTES, 'UTF-8');
	
	$text = preg_replace ('/
		((?:http|ftp)s?:\/\/)?					# $1 = protocol
		(?:www\.)?						# ignore www
		(							# $2 = friendly URL (no protocol)
			([a-z0-9._%+-]+@[a-z0-9.-]+)?			# $3 = email address
			[a-z0-9.-]{2,}(?:\.[a-z]{2,4})+			# domain name (mandatory if protocol given)
		)(							# $4 = folders and filename, relative URL
			\/						# slash required after full domain
			[\/a-z0-9_!~*\'().;?:@&=+$,%-]*			# folders and filename
			(?:\x23[^\s"]+)?				# bookmark
		)?			
		/exi',
		'"<a href=\"".("$1"?"$1":"http://")."$2$4"."\">&lt;$2".("$4"?"/…":"")."&gt;</a>"',
	$text);
	
	foreach (preg_split ('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
		$chunk = "<p>\n".str_replace ("\n", "<br />", $chunk)."\n</p>";
		$text = @$result .= "\n$chunk";
	}
	
	return $text;
}

//<http://stackoverflow.com/questions/2092012/simplexml-how-to-prepend-a-child-in-a-node/2093059#2093059>
class my_node extends SimpleXMLElement {
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