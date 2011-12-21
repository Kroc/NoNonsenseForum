<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsense Forum v9 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './shared.php';

//get page number
define ('PAGE', preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1);

//submitted info for making a new thread
//(name / password already handled in 'shared.php')
define ('TITLE', safeGet (@$_POST['title'], SIZE_TITLE));
define ('TEXT',  safeGet (@$_POST['text'],  SIZE_TEXT ));

/* ====================================================================================================================== */

//has the user submitted a new thread?
//(`AUTH` will be true if username and password submitted and correct, `TITLE` and `TEXT` are checked to not be blank)
if (CAN_POST && AUTH && TITLE && TEXT) {
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
	
	//write out the new thread as an RSS file:
	$rss  = new SimpleXMLElement (
		'<?xml version="1.0" encoding="UTF-8"?>'.
		'<rss version="2.0" />'
	);
	$chan = $rss->addChild ('channel');
	//RSS feed title and URL to this forum / sub-forum
	$chan->addChild ('title',	safeHTML (TITLE));
	$chan->addChild ('link',	FORUM_URL.PATH_URL.$file);
	//the thread's first post
	$item = $chan->addChild ('item');
	$item->addChild ('title',	safeHTML (TITLE));
	$item->addChild ('link',	FORUM_URL.PATH_URL."$file#".base_convert (microtime (), 10, 36));
	$item->addChild ('author',	safeHTML (NAME));
	$item->addChild ('pubDate',	gmdate ('r'));
	$item->addChild ('description',	safeHTML (formatText (TEXT)));
	//save to disk
	$rss->asXML ("$file.rss");
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	//redirect to newley created thread
	header ('Location: '.FORUM_URL.PATH_URL.$file, true, 303);
	exit;
}

/* ====================================================================================================================== */
//the about text for a forum, if present
$ABOUT = @file_get_contents ('about.html');

/* sub-forums
   ---------------------------------------------------------------------------------------------------------------------- */
//don’t allow sub-sub-forums (yet)
if (!PATH) foreach (array_filter (
	//get a list of folders:
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
) as $FOLDER) {
	//the sorting (below) requires we be in the directory at hand to use `filemtime`
	chdir ($FOLDER);
	
	//check if / how the forum is locked
	$lock = trim (@file_get_contents ('locked.txt'));
	
	//get a list of files in the folder to determine which one is newest
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	//order by last modified date
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	//read the newest thread (folder could be empty though)
	$last = ($xml = @simplexml_load_file ($threads[0])) ? $xml->channel->item[0] : '';
	
	$FOLDERS[] = array (
		'URL'		=> safeURL (FORUM_PATH."$FOLDER/"),
		'NAME'		=> safeHTML ($FOLDER),
		'LOCK'		=> $lock,
		'HAS_POST'	=> $lock != 'private' && (bool) $last	//if there is any last-post info for this sub-forum
		
	//don’t include last-post info if no threads in sub-forum, or sub-forum is private
	) + ($lock != 'private' && (bool) $last ? array (
		'DATETIME'	=> date ('c', strtotime ($last->pubDate)),
		'TIME'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
		'AUTHOR'	=> safeHTML ($last->author),
		'MOD'		=> isMod ($last->author),
		'POSTLINK'	=> substr ($last->link, strpos ($last->link, '/', 9))
	) : array ());
	
	chdir ('..');
}

/* threads
   ---------------------------------------------------------------------------------------------------------------------- */
//get list of threads (if any--could be an empty folder)
if ($threads = preg_grep ('/\.rss$/', scandir ('.'))) {
	//order by last modified date
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	//get sticky list, trimming any files that no longer exist
	//(the use of `array_intersect` will only return filenames in `sticky.txt` that were also in the directory)
	if ($stickies = array_intersect (
		//`file` returns NULL on failure, so we can cast it to an array to get an array with one blank item,
		//then `array_filter` removes blank items. this way saves having to check if the file exists first
		array_filter ((array) @file ('sticky.txt', FILE_IGNORE_NEW_LINES)), $threads
	)) {
		//order the stickies by reverse date order
		array_multisort (array_map ('filemtime', $stickies), SORT_DESC, $stickies);
		//remove the stickies from the thread list
		$threads = array_diff ($threads, $stickies);
	}
	
	//number of pages (stickies are not included in the count as they appear on all pages)
	define (PAGES, ceil (count ($threads) / FORUM_THREADS));
	//slice the full list into the current page
	$threads = array_merge ($stickies, array_slice ($threads, (PAGE-1) * FORUM_THREADS, FORUM_THREADS));
	
	//generate the list of threads with data, for the template
	foreach ($threads as $file) if (
		//read the file, and refer to the last post made
		$xml  = @simplexml_load_file ($file)
	) {
		$last = &$xml->channel->item[0];
		$THREADS[] = array (
			'STICKY'	=> in_array ($file, $stickies),
			'LOCKED'	=> (bool) $xml->channel->xpath ("category[text()='locked']"),
			//link to the thread--go to the last page of replies
			'URL'		=> pathinfo ($file, PATHINFO_FILENAME).'?page=last',
			'TITLE'		=> safeHTML ($xml->channel->title),
			'COUNT'		=> count ($xml->channel->item) - 1,			//number of replies
			//info of last post made to thread
			'DATETIME'	=> date ('c', strtotime ($last->pubDate)),		//HTML5 datetime attr
			'TIME'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),	//human readable
			'AUTHOR'	=> safeHTML ($last->author),
			'MOD'		=> isMod ($last->author),
			'POSTLINK'	=> substr ($last->link, strpos ($last->link, '/', 9))	//link to the last post
		);
	}
} else {
	define (PAGES, 1);
}

/* new thread form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_POST) $FORM = array (
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
