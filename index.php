<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsense Forum v7 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once './shared.php';

//get page number
define ('PAGE', preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1);

//submitted info for making a new thread
//(name / password already handled in 'shared.php')
define ('TITLE', safeGet (@$_POST['title'], SIZE_TITLE));
define ('TEXT',  safeGet (@$_POST['text'],  SIZE_TEXT ));

/* ====================================================================================================================== */

//has the user submitted a new thread? (and is the info valid?)
if (FORUM_ENABLED && NAME && PASS && AUTH && TITLE && TEXT && @$_POST['email'] == 'example@abc.com') {
	//the file on disk is a simplified version of the title:
	$translit = preg_replace (
		//replace non alphanumerics with underscores and don’t use more than 2 in a row
		array ('/[^_a-z0-9-]/i', '/_{2,}/'), '_',
		//remove the additional characters added by transliteration, e.g. "ñ" = "~n",
		//has the added benefit of converting “microsoft's” to “microsofts” instead of “microsoft_s”
		str_replace (array ("'", "`", "^", "~", "'", '"'), '', strtolower (
			//unaccent: <php.net/manual/en/function.iconv.php>
			iconv ('UTF-8', 'US-ASCII//IGNORE//TRANSLIT', TITLE)
		))
	);
	
	//old iconv versions and certain inputs may cause a nullstring. don't allow a blank filename
	if (!$translit) $translit = '_';
	
	//if a thread already exsits with that name, append a number until an available filename is found
	$c = 0;
	do $file = $translit.($c++ ? '_'.($c-1) : '');
	while (file_exists ("$file.rss"));
	
	//write out the new thread as an RSS file
	file_put_contents ("$file.rss", template_tags (<<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="http://${_SERVER['HTTP_HOST']}&__URL__;.rss" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>http://${_SERVER['HTTP_HOST']}&__URL__;</link>

<item>
	<title>&__TITLE__;</title>
	<link>http://${_SERVER['HTTP_HOST']}&__URL__;#&__ID__;</link>
	<author>&__NAME__;</author>
	<pubDate>&__DATE__;</pubDate>
	<description>&__TEXT__;</description>
</item>

</channel>
</rss>
XML
	, array (
		'TITLE'	=> safeHTML (TITLE),
		'URL'	=> PATH_URL.$file,
		'NAME'	=> safeHTML (NAME),
		'DATE'	=> gmdate ('r'),
		'TEXT'	=> safeHTML (formatText (TEXT)),		//process markup
		'ID'	=> base_convert (microtime (), 10, 36)		//generate a unique ID for the post A-Z/0-9
	)));
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	//redirect to newley created thread
	header ('Location: '.FORUM_URL.PATH_URL.$file, true, 303);
	exit;
}

/* ====================================================================================================================== */
/* sub-forums
   ---------------------------------------------------------------------------------------------------------------------- */
//don’t all sub-sub-forums
if (!PATH) foreach (array_filter (
	//get a list of folders:
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
) as $FOLDER) {
	//the sorting (below) requires we be in the directory at hand to use `filemtime`
	chdir ($FOLDER);
	
	//get a list of files in the folder to determine which one is newest
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	//order by last modified date
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	//read the newest thread (folder could be empty though)
	$last = ($xml = @simplexml_load_file ($threads[0])) ? $xml->channel->item[0] : '';
	
	$FOLDERS[] = array (
		'URL'		=> safeURL (FORUM_PATH."$FOLDER/"),
		'NAME'		=> safeHTML ($FOLDER),
		//can’t include these details if the folder was empty (no threads)
		'DATETIME'	=> !$last ? '' : date ('c', strtotime ($last->pubDate)),
		'TIME'		=> !$last ? '' : date (DATE_FORMAT, strtotime ($last->pubDate)),
		'AUTHOR'	=> !$last ? '' : safeHTML ($last->author),
		'MOD'		=> !$last ? '' : isMod ($last->author),
		'POSTLINK'	=> !$last ? '' : substr ($last->link, strpos ($last->link, '/', 9))
	);
	
	chdir ('..');
}

/* threads
   ---------------------------------------------------------------------------------------------------------------------- */
//get list of threads (if any--could be an empty folder)
if ($threads = preg_grep ('/\.rss$/', scandir ('.'))) {
	//order by last modified date
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	//does this folder have a sticky list?
	$stickies = array ();
	if (file_exists ('sticky.txt')) {
		//get sticky list, trimming any files that no longer exist
		$stickies = array_intersect (file ('sticky.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES), $threads);
		
		//order the stickies by reverse date order
		array_multisort (array_map ('filemtime', $stickies), SORT_DESC, $stickies);
		
		//remove the stickies from the thread list
		$threads = array_diff ($threads, $stickies);
	}
	
	//paging (stickies are not included in the count as they appear on all pages)
	$PAGES   = pageList (PAGE, ceil (count ($threads) / FORUM_THREADS));
	//slice the full list into the current page
	$threads = array_merge ($stickies, array_slice ($threads, (PAGE-1) * FORUM_THREADS, FORUM_THREADS));
	
	//generate the list of threads with data, for the template
	foreach ($threads as $file) if (
		//read the file, and refer to the last post made (the first item in RSS feed as newest is first)
		$xml  = @simplexml_load_file ($file)
	) {
		$last = &$xml->channel->item[0];
		$THREADS[] = array (
			'STICKY'	=> in_array ($file, $stickies),
			'LOCKED'	=> (bool) $xml->channel->xpath ("category[text()='locked']"),
			//link to the thread--go to the last page of replies
			'URL'		=> pathinfo ($file, PATHINFO_FILENAME).'?page=last',
			'TITLE'		=> safeHTML ($xml->channel->title),
			'COUNT'		=> count ($xml->channel->item) - 1,
			//info of last post made to thread
			'DATETIME'	=> date ('c', strtotime ($last->pubDate)),		//HTML5 datetime attr
			'TIME'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),	//human readable
			'AUTHOR'	=> safeHTML ($last->author),
			'MOD'		=> isMod ($last->author),
			'POSTLINK'	=> substr ($last->link, strpos ($last->link, '/', 9))	//link to the last post
		);
	}
}

/* new thread form
   ---------------------------------------------------------------------------------------------------------------------- */
//(exclude if posting has been disabled)
if (FORUM_ENABLED) $FORM = array (
	'NAME'	=> safeString (NAME),
	'PASS'	=> safeString (PASS),
	'TITLE'	=> safeString (TITLE),
	'TEXT'	=> safeHTML (TEXT),
	'ERROR'	=> empty ($_POST) ? ERROR_NONE	//no problem? show default help text
		 : (!NAME  ? ERROR_NAME		//the name is missing
		 : (!PASS  ? ERROR_PASS		//the password is missing
		 : (!TITLE ? ERROR_TITLE	//the title is missing
		 : (!TEXT  ? ERROR_TEXT		//the message text is missing
		 : ERROR_AUTH))))		//the name / password pair didn’t match
);

//all the data prepared, now output the HTML
include FORUM_ROOT.'/themes/'.FORUM_THEME.'/index.inc.php';

?>
