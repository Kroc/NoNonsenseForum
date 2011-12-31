<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsense Forum v11 © Copyright (CC-BY) Kroc Camen 2011
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

//load the template into DOM where we can manipulate it:
$html = new NNFTemplate (FORUM_ROOT.'/themes/'.FORUM_THEME.'/index.html');

//fix all absolute URLs (i.e. if NNF is running in a folder):
foreach ($html->xpath->query ('//*/@href|//*/@src') as $node) if ($node->nodeValue[0] == '/')
	//prepend the base path of the forum ('/' if on root, '/folder/' if running in a sub-folder)
	$node->nodeValue = FORUM_PATH.ltrim ($node->nodeValue, '/')
;

/* HTML <head>
   ---------------------------------------------------------------------------------------------------------------------- */
$html->set (array (
	//HTML title (= forum / sub-forum name and page number)
	'/html/head/title'					=> (PATH ? PATH : safeHTML (FORUM_NAME)).
								   (PAGE>1 ? ' # '.PAGE : ''),
	//application title (= forum / sub-forum name):
	//used for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
	'//meta[@name="application-name"]/@content'		=> PATH ? safeString (PATH) : safeString (FORUM_NAME),
	//application URL (where the pinned site opens at)
	'//meta[@name="msapplication-starturl"]/@content'	=> FORUM_URL.PATH_URL
));

//remove 'custom.css' stylesheet if 'custom.css' is missing
if (!file_exists (FORUM_ROOT.FORUM_PATH.'themes/'.FORUM_THEME.'/custom.css'))
	$html->remove ('//link[contains(@href,"custom.css")]')
;

/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
$html->set (array (
	//forum name
	'//*[@nnf:template="forum-name"]'			=> safeHTML (FORUM_NAME),
	//where the forum logo and index links to, usually just "/", but will be different if the forum is in a sub-folder
	'//a[@nnf:template="root"]/@href'			=> FORUM_PATH,
	//the forum logo
	'//img[@nnf:template="logo"]/@src'			=> FORUM_PATH.'themes/'.FORUM_THEME.'/icons/'.THEME_LOGO
));

//are we in a sub-folder?
if (PATH) {
	//if so, add the sub-forum name to the breadcrumb navigation,
	$html->setValue ('//*[@nnf:template="subforum-name"]', PATH);
} else {
	//otherwise -- remove the breadcrumb navigation
	$html->remove ('//*[@nnf:template="subforum"]');
}

//search form:
$html->set (array (
	//if you're using a Google search, change it to HTTPS if enforced
	'//form/@action["http://google.com/search"]'		=> FORUM_HTTPS	? 'https://encrypted.google.com/search'
										: 'http://google.com/search',
	//set the forum URL for Google search-by-site
	'//input[@name="as_sitesearch"]/@value'			=> safeString ($_SERVER['HTTP_HOST'])
));

//if threads can't be added (forum is disabled / locked, user is not moderator / member),
//remove the "add thread" link and anything else (like the input forum) related to posting
if (!CAN_POST) $html->remove ('//*[@nnf:template="can_post"]');

//an 'about.html' file can be provided to add a description or other custom HTML to the forum / sub-forum
if (file_exists ('about.html')) {
	//load the 'about.html' file and insert it into the page
	$about = $html->createDocumentFragment ();
	$about->appendXML (file_get_contents ('about.html'));
	$node = $html->xpath->query ('//*[@nnf:template="about"]')->item (0);
	$node->nodeValue = '';
	$node->appendChild ($about);
} else {
	//no file? remove the element reserved for it
	$html->remove ('//*[@nnf:template="about"]');
}

/* sub-forums
   ---------------------------------------------------------------------------------------------------------------------- */
//don’t allow sub-sub-forums (yet)
if (!PATH && $folders = array_filter (
	//get a list of folders:
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
)) {
	$dummy = $html->xpath->query ('//*[@nnf:template="folder"]')->item (0);
	
	foreach ($folders as $FOLDER) {
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
		
		//copy the dummy template provided
		$item = $dummy->cloneNode (true);
		
		$item->set (array (
			'//a[@nnf:template="folder-name"]'	 => safeHTML ($FOLDER),			//name of sub-forum
			'//a[@nnf:template="folder-name"]/@href' => safeURL (FORUM_PATH."$FOLDER/")	//URL to it
		));
		
		//remove the lock icons if not required
		if ($lock != 'threads') $item->remove ('//*[@nnf:template="lock-threads"]');
		if ($lock != 'posts')   $item->remove ('//*[@nnf:template="lock-posts"]');
		//is there a last post in this sub-forum?
		if ((bool) $last) {
			//is the author a mod?
			if (isMod ($last->author)) $item->addClass ('//*[@nnf:template="post-author"]', 'mod');
			
			$item->set (array (
				//last post author name
				'//*[@nnf:template="post-author"]' => safeHTML ($last->author),
				//last post time (human readable)
				'//*[@nnf:template="post-time"]' => date (DATE_FORMAT, strtotime ($last->pubDate)),
				//last post time (machine readable)
				'//*[@nnf:template="post-time"]/@datetime' => date ('c', strtotime ($last->pubDate)),
				//link to the last post
				'//*[@nnf:template="post-link"]/@href' => substr ($last->link, strpos ($last->link, '/', 9)),
			));
		} else {
			//no last post, remove the template for it
			$item->remove ('//*[@nnf:template="subforum-post"]');
		}
		
		//attach the templated sub-forum item to the list
		$dummy->parentNode->appendChild ($item);
		
		chdir ('..');
	}
	//remove the dummy template
	$dummy->removeNode ();
	
} else {
	//no sub-forums, remove the template stuff
	$html->remove ('//*[@nnf:template="folders"]');
}

//remove `nnf:template` attributes
$html->remove ('//@nnf:template');

die ($html->getHTML ());












/* ====================================================================================================================== */

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
		'HAS_POST'	=> (bool) $last				//if there is any last-post info for this sub-forum
		
	//don’t include last-post info if no threads in sub-forum
	) + ((bool) $last ? array (
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
	define ('PAGES', ceil (count ($threads) / FORUM_THREADS));
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
	define ('PAGES', 1);
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
