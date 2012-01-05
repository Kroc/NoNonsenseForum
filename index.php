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
//(see 'shared.php' or http://camendesign.com/dom_templating for more details)
$nnf = new DOMTemplate (FORUM_ROOT.'/themes/'.FORUM_THEME.'/index.html');

/* HTML <head>
   ---------------------------------------------------------------------------------------------------------------------- */
$nnf->set (array (
	//HTML title (= forum / sub-forum name and page number)
	'xpath:/html/head/title'				=> (PATH ? PATH : FORUM_NAME).(PAGE>1 ? ' # '.PAGE : ''),
	//application title (= forum / sub-forum name):
	//used for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
	'xpath://meta[@name="application-name"]/@content'	=> PATH ? PATH : FORUM_NAME,
	//application URL (where the pinned site opens at)
	'xpath://meta[@name="msapplication-starturl"]/@content'	=> FORUM_URL.PATH_URL
));

//remove 'custom.css' stylesheet if 'custom.css' is missing
if (!file_exists (FORUM_ROOT.FORUM_PATH.'themes/'.FORUM_THEME.'/custom.css'))
	$nnf->remove ('xpath://link[contains(@href,"custom.css")]')
;

/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
$nnf->set (array (
	'forum-name'	=> FORUM_NAME,
	'img:logo@src'	=> FORUM_PATH.'themes/'.FORUM_THEME.'/icons/'.THEME_LOGO
));

//are we in a sub-folder?
if (PATH) {
	//if so, add the sub-forum name to the breadcrumb navigation,
	$nnf->setValue ('subforum-name', PATH);
} else {
	//otherwise -- remove the breadcrumb navigation
	$nnf->remove ('subforum');
}

//search form:
$nnf->set (array (
	//if you're using a Google search, change it to HTTPS if enforced
	'xpath://form[@action="http://google.com/search"]/@action'	=> FORUM_HTTPS	? 'https://encrypted.google.com/search'
											: 'http://google.com/search',
	//set the forum URL for Google search-by-site
	'xpath://input[@name="as_sitesearch"]/@value'			=> $_SERVER['HTTP_HOST']
));

//if threads can't be added (forum is disabled / locked, user is not moderator / member),
//remove the "add thread" link and anything else (like the input form) related to posting
if (!CAN_POST) $nnf->remove ('can-post');

//an 'about.html' file can be provided to add a description or other custom HTML to the forum / sub-forum
if (file_exists ('about.html')) {
	//load the 'about.html' file and insert it into the page
	$nnf->setHTML ('about', file_get_contents ('about.html'));
} else {
	//no file? remove the element reserved for it
	$nnf->remove ('about');
}

//if the forum is not thread-locked (only mods can start new threads, but anybody can reply) then remove the warning message
if (FORUM_LOCK != 'threads') $nnf->remove ('forum-lock-threads');
//if the forum is not post-locked (only mods can post / reply) then remove the warning message
if (FORUM_LOCK != 'posts')   $nnf->remove ('forum-lock-posts');

/* sub-forums
   ---------------------------------------------------------------------------------------------------------------------- */
//don’t allow sub-sub-forums (yet)
if (!PATH && $folders = array_filter (
	//get a list of folders:
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
)) {
	$item = $nnf->repeat ('folder');
	
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
		
		$item->set (array (
			'a:folder-name'		=> $FOLDER,				//name of sub-forum
			'a:folder-name@href'	=> safeURL (FORUM_PATH."$FOLDER/")	//URL to it
		));
		
		//remove the lock icons if not required
		if ($lock != 'threads') $item->remove ('lock-threads');
		if ($lock != 'posts')   $item->remove ('lock-posts');
		//is there a last post in this sub-forum?
		if ((bool) $last) {
			$item->set (array (
				//last post author name
				'post-author'			=> $last->author,
				//last post time (human readable)
				'time:post-time'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
				//last post time (machine readable)
				'time:post-time@datetime'	=> date ('c', strtotime ($last->pubDate)),
				//link to the last post
				'a:post-link@href'		=> substr ($last->link, strpos ($last->link, '/', 9)),
			));
			//is the last author a mod?
			if (isMod ($last->author)) $item->addClass ('post-author', 'mod');
		} else {
			//no last post, remove the template for it
			$item->remove ('subforum-post');
		}
		
		//attach the templated sub-forum item to the list
		$item->next ();
		
		chdir ('..');
	}
	
} else {
	//no sub-forums, remove the template stuff
	$nnf->remove ('folders');
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
	
	//do the page links (stickies are not included in the count as they appear on all pages)
	//(`theme_pageList` is defined in 'theme.config.php' if it exists, otherwise 'theme.config.default.php')
	$nnf->setHTML ('pages', theme_pageList (
		//page number,	number of pages
		PAGE, 		ceil (count ($threads) / FORUM_THREADS)
	));
	//slice the full list into the current page
	$threads = array_merge ($stickies, array_slice ($threads, (PAGE-1) * FORUM_THREADS, FORUM_THREADS));
	
	$item = $nnf->repeat ('thread');
	
	//generate the list of threads with data, for the template
	foreach ($threads as $file) if (
		//read the file, and refer to the last post made
		$xml  = @simplexml_load_file ($file)
	) {
		$last = &$xml->channel->item[0];
		
		//is the thread sticky?
		if (in_array ($file, $stickies)) $item->addClass ('xpath:.', 'sticky'); 
		//if the thread isn’t locked, remove the lock icon
		if (!$xml->channel->xpath ("category[text()='locked']")) $item->remove ('thread-locked');
		
		$item->set (array (
			//thread title and URL
			'a:thread-name'			=> $xml->channel->title,
			'a:thread-name@href'		=> pathinfo ($file, PATHINFO_FILENAME).'?page=last',
			//number of replies
			'thread-replies'		=> count ($xml->channel->item) - 1,
			
			//last post info:
			//link to the last post
			'a:thread-post@href'		=> substr ($last->link, strpos ($last->link, '/', 9)),
			//last post time (human readable)
			'time:thread-time'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
			//last post time (machine readable)
			'time:thread-time@datetime'	=> date ('c', strtotime ($last->pubDate)),
			//last post author
			'thread-author'			=> $last->author
		));
		
		//is the last post author a mod?
		if (isMod ($last->author)) $item->addClass ('thread-author', 'mod');
		
		//attach the templated sub-forum item to the list
		$item->next ();
	}
	
} else {
	//no threads, remove the template stuff
	$nnf->remove ('threads');
}

/* new thread form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_POST) {
	//set the field values from what was typed in before
	$nnf->set (array (
		'input:title-field@value'	=> TITLE,
		'input:name-field-http@value'	=> NAME,
		'input:name-field@value'	=> NAME,
		'input:pass-field@value'	=> PASS,
		'textarea:text-field'		=> TEXT
	));
	
	//is the user already signed-in?
	if (HTTP_AUTH) {
		//don’t need the usual name / password fields
		$nnf->remove ('name');
		$nnf->remove ('pass');
		$nnf->remove ('email');
		//remove the deafult message for anonymous users
		$nnf->remove ('error-none');
	} else {
		//user is not signed in, remove the "you are signed in as:" field
		$nnf->remove ('http-auth');
		//remove the default message for signed in users
		$nnf->remove ('error-none-http');
	}
	
	//are new registrations allowed?
	$nnf->remove (FORUM_NEWBIES
		? 'error-newbies'	//yes: remove the warning message
		: 'error-none'		//no:  remove the default message
	);
	
	//if there's an error of any sort, remove the default messages
	if (!empty ($_POST)) {
		$nnf->remove ('error-none');
		$nnf->remove ('error-none-http');
		$nnf->remove ('error-newbies');
	}
	
	//if the username & password are correct, remove the error message
	if (empty ($_POST) || !TITLE || !TEXT || !NAME || !PASS || AUTH) $nnf->remove ('error-auth');
	//if the password is valid, remove the erorr message
	if (empty ($_POST) || !TITLE || !TEXT || !NAME || PASS) $nnf->remove ('error-pass');
	//if the name is valid, remove the erorr message
	if (empty ($_POST) || !TITLE || !TEXT || NAME) $nnf->remove ('error-name');
	//if the message text is valid, remove the error message
	if (empty ($_POST) || !TITLE || TEXT) $nnf->remove ('error-text');
	//if the title is valid, remove the erorr message
	if (empty ($_POST) || TITLE) $nnf->remove ('error-title');
}

/* footer
   ---------------------------------------------------------------------------------------------------------------------- */
//are there any local mods?	create the list of local mods
if (!empty ($MODS['LOCAL'])):	$nnf->setHTML ('mods-local-list', theme_nameList ($MODS['LOCAL']));
                        else:	$nnf->remove ('mods-local');	//remove the local mods list section
endif;
//are there any site mods?	create the list of mods
if (!empty ($MODS['GLOBAL'])):	$nnf->setHTML ('mods-list', theme_nameList ($MODS['GLOBAL']));
                         else:	$nnf->remove ('mods');		//remove the mods list section
endif;
//are there any members?	create the list of members
if (!empty ($MEMBERS)):		$nnf->setHTML ('members-list', theme_nameList ($MEMBERS));
                  else:		$nnf->remove ('members');	//remove the members list section
endif;

//is a user signed in?
if (HTTP_AUTH) {
	//yes: remove the signed-out section
	$nnf->remove ('signed-out');
	//set the name of the signed-in user
	$nnf->setValue ('signed-in-name', HTTP_AUTH_NAME);
} else {
	//no: remove the signed-in section
	$nnf->remove ('signed-in');
}

die ($nnf->html ());

?>