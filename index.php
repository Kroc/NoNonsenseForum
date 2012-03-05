<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsense Forum v18 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './start.php';

//get page number
define ('PAGE', preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1);

//submitted info for making a new thread
//(name / password already handled in 'start.php')
define ('TITLE', safeGet (@$_POST['title'], SIZE_TITLE));
define ('TEXT',  safeGet (@$_POST['text'],  SIZE_TEXT ));


/* ======================================================================================================================
   new thread submitted
   ====================================================================================================================== */
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
	$rss = new DOMTemplate (FORUM_ROOT.'/lib/rss-template.xml');
	$rss->set (array (
		'/rss/channel/title'		=> TITLE,
		'/rss/channel/link'		=> FORUM_URL.PATH_URL.$file,
		//the thread's first post
		'/rss/channel/item/title'	=> TITLE,
		'/rss/channel/item/link'	=> FORUM_URL.PATH_URL."$file#".base_convert (microtime (), 10, 36),
		'/rss/channel/item/author'	=> NAME,
		'/rss/channel/item/pubDate'	=> gmdate ('r'),
		'/rss/channel/item/description'	=> formatText (TEXT)
	//remove the locked / deleted categories
	))->remove ('//category');
	
	file_put_contents ("$file.rss", $rss->html ()) or die (
		"Failed to save thread. Folder permissions may be incorrect."
	);
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	//redirect to newley created thread
	header ('Location: '.FORUM_URL.PATH_URL.$file, true, 303);
	exit;
}


/* ======================================================================================================================
   template the page
   ====================================================================================================================== */
//load the template into DOM where we can manipulate it:
//(see 'lib/domtemplate.php' or <camendesign.com/dom_templating> for more details. `prepareTemplate` can be found in
// 'lib/functions.php' and handles some shared templating done across all pages)
$template = prepareTemplate (
	FORUM_ROOT.'/themes/'.FORUM_THEME.'/index.html',
	//`THEME_TITLE` is defined in 'theme.config.php' if it exists, else 'theme.config.default.php'
	sprintf (THEME_TITLE,
		//if in a sub-forum use the folder name, else the site's name
		PATH ? SUBFORUM: FORUM_NAME,
		//if on page 2 or greater, include the page number in the title
		PAGE>1 ? sprintf (THEME_TITLE_PAGENO, PAGE) : ''
	)
)->remove (array (
	//if threads can't be added (forum is disabled / locked, user is not moderator / member),
	//remove the "add thread" link and anything else (like the input form) related to posting
	'#nnf_add, #nnf_new-form'	=> !CAN_POST,
	//if the forum is not thread-locked (only mods can post, anybody can reply) then remove the warning message
	'.nnf_forum-lock-threads'	=> FORUM_LOCK != 'threads' || IS_MOD,
	//if the forum is not post-locked (only mods can post / reply) then remove the warning message
	'.nnf_forum-lock-posts'		=> FORUM_LOCK != 'posts'   || IS_MOD || IS_MEMBER
));

//an 'about.html' file can be provided to add a description or other custom HTML to the forum / sub-forum
if (file_exists ('about.html')) {
	//load the 'about.html' file and insert it into the page
	$template->setHTML ('#nnf_about', file_get_contents ('about.html'));
} else {
	//no file? remove the element reserved for it
	$template->remove ('#nnf_about');
}

/* sub-forums
   ---------------------------------------------------------------------------------------------------------------------- */
if ($folders = array_filter (
	//get a list of folders:
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$|lib$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
)) {
	//get the dummy list-item to repeat (removes it and takes a copy)
	$item = $template->repeat ('.nnf_folder');
	
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
		
		//start applying the data to the template
		$item->set (array (
			'a.nnf_folder-name'		=> $FOLDER,				//name of sub-forum
			'a.nnf_folder-name@href'	=> PATH_URL.safeURL ($FOLDER).'/'	//URL to it
		
		//remove the lock icons if not required
		))->remove (array (
			'.nnf_lock-threads'		=> $lock != 'threads',
			'.nnf_lock-posts'		=> $lock != 'posts'
		));
		//is there a last post in this sub-forum?
		if ((bool) $last) {
			$item->set (array (
				//last post author name
				'.nnf_post-author'		=> $last->author,
				//last post time (human readable)
				'time.nnf_post-time'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
				//last post time (machine readable)
				'time.nnf_post-time@datetime'	=> date ('c', strtotime ($last->pubDate)),
				//link to the last post
				'a.nnf_post-link@href'		=> substr ($last->link, strpos ($last->link, '/', 9)),
			));
			//is the last author a mod?
			if (isMod ($last->author)) $item->addClass ('.nnf_post-author', 'mod');
		} else {
			//no last post, remove the template for it
			$item->remove ('.nnf_subforum-post');
		}
		
		//attach the templated sub-forum item to the list
		$item->next ();
		
		chdir ('..');
	}
	
} else {
	//no sub-forums, remove the template stuff
	$template->remove ('#nnf_folders');
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
	$template->setHTML ('.nnf_pages', theme_pageList (
		//base URL to work with
		PATH_URL,
		//page number,	number of pages
		PAGE, 		ceil (count ($threads) / FORUM_THREADS)
	));
	//slice the full list into the current page
	$threads = array_merge ($stickies, array_slice ($threads, (PAGE-1) * FORUM_THREADS, FORUM_THREADS));
	
	//get the dummy list-item to repeat (removes it and takes a copy)
	$item = $template->repeat ('.nnf_thread');
	
	//generate the list of threads with data, for the template
	foreach ($threads as $file) if (
		//read the file, and refer to the last post made
		$xml = @simplexml_load_file ($file)
	) {
		//is the thread sticky?
		if (in_array ($file, $stickies)) $item->addClass ('.', 'sticky'); 
		
		//get the last post in the thread
		$last = &$xml->channel->item[0];
		
		//apply the data to the template
		$item->set (array (
			//thread title and URL
			'a.nnf_thread-name'		=> $xml->channel->title,
			'a.nnf_thread-name@href'	=> pathinfo ($file, PATHINFO_FILENAME),
			//number of replies
			'.nnf_thread-replies'		=> count ($xml->channel->item) - 1,
			
			//last post info:
			//link to the last post
			'a.nnf_thread-post@href'	=> substr ($last->link, strpos ($last->link, '/', 9)),
			//last post time (human readable)
			'time.nnf_thread-time'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
			//last post time (machine readable)
			'time.nnf_thread-time@datetime'	=> date ('c', strtotime ($last->pubDate)),
			//last post author
			'.nnf_thread-author'		=> $last->author
		))->remove (array (
			//if the thread isn’t locked, remove the lock icon
			'.nnf_thread-locked'		=> !$xml->channel->xpath ("category[.='locked']"),
			//if the thread is not sticky, remove the sticky icon
			'.nnf_thread-sticky'		=> !in_array ($file, $stickies)
							//the lock-icon takes precedence over the sticky icon
							|| $xml->channel->xpath ("category[.='locked']")
		));
		
		//is the last post author a mod?
		if (isMod ($last->author)) $item->addClass ('.nnf_thread-author', 'mod');
		
		//attach the templated sub-forum item to the list
		$item->next ();
	}
	
} else {
	//no threads, remove the template stuff
	$template->remove ('#nnf_threads');
}

/* new thread form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_POST) $template->set (array (
	//set the field values from what was typed in before
	'input#nnf_title-field@value'		=> TITLE,
	'input#nnf_name-field-http@value'	=> NAME,
	'input#nnf_name-field@value'		=> NAME,
	'input#nnf_pass-field@value'		=> PASS,
	'textarea#nnf_text-field'		=> TEXT,
	//set the maximum field sizes
	'input#nnf_title-field@maxlength'	=> SIZE_TITLE,
	'input#nnf_name-field@maxlength'	=> SIZE_NAME,
	'input#nnf_pass-field@maxlength'	=> SIZE_PASS,
	'textarea#nnf_text-field@maxlength'	=> SIZE_TEXT
	
//is the user already signed-in?
))->remove (HTTP_AUTH
	//don’t need the usual name / password fields and the deafult message for anonymous users
	? '#nnf_name, #nnf_pass, #nnf_email, #nnf_error-none'
	//user is not signed in, remove the "you are signed in as:" field and the message for signed in users
	: '#nnf_name-http, #nnf_error-none-http'
	
//are new registrations allowed?
)->remove (FORUM_NEWBIES
	? '#nnf_error-newbies'	//yes: remove the warning message
	: '#nnf_error-none'	//no:  remove the default message
	
//handle error messages
)->remove (array (
	//if there's an error of any sort, remove the default messages
	'#nnf_error-none, #nnf_error-none-http, #nnf_error-newbies' => !empty ($_POST),
	//if the username & password are correct, remove the error message
	'#nnf_error-auth' => empty ($_POST) || !TITLE || !TEXT || !NAME || !PASS || AUTH,
	//if the password is valid, remove the erorr message
	'#nnf_error-pass' => empty ($_POST) || !TITLE || !TEXT || !NAME || PASS,
	//if the name is valid, remove the erorr message
	'#nnf_error-name' => empty ($_POST) || !TITLE || !TEXT || NAME,
	//if the message text is valid, remove the error message
	'#nnf_error-text' => empty ($_POST) || !TITLE || TEXT,
	//if the title is valid, remove the erorr message
	'#nnf_error-title'=> empty ($_POST) || TITLE
));

//call the user-defined function in 'theme.config.php' (if it exists), otherwise 'theme.config.default.php'.
//this function is provided to allow custom themes to do their own additional templating
theme_custom ($template);
die ($template->html ());

?>