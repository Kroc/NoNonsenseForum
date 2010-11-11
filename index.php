<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2010
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* ====================================================================================================================== */

//submitted info for making a new thread
$NAME  = mb_substr (trim (stripslashes (@$_POST['username'])), 0, 18,    'UTF-8');
$PASS  = mb_substr (      stripslashes (@$_POST['password']),  0, 20,    'UTF-8');
$TITLE = mb_substr (trim (stripslashes (@$_POST['title']   )), 0, 80,    'UTF-8');
$TEXT  = mb_substr (trim (stripslashes (@$_POST['text']    )), 0, 32768, 'UTF-8');

//has the user the submitted a new thread?
if ($SUBMIT = @$_POST['submit']) if (
	//`FORUM_ENABLED` (in 'shared.php') can be toggled to disable posting
	//the email check is a fake hidden field in the form to try and fool spam bots
	FORUM_ENABLED && @$_POST['email'] == 'example@abc.com' && $NAME && $PASS && $TITLE && $TEXT
	&& checkName ($NAME, $PASS)
) {
	//the file on disk is a simplified version of the title
	$file = preg_replace (
		//replace non alphanumerics with underscores and don’t use more than 2 in a row
		array ('/[^_a-z0-9-]/i', '/_{2,}/'), '_',
		//for neatness use "Microsofts" instead of "Microsoft_s" when removing the apostrophe
		str_replace (array ("'", "‘", "’", '"', '“','”'), '', strtolower ($TITLE))
	);
	
	//write out the new thread as an RSS file
	if (!file_exists ("$file.xml")) file_put_contents ("$file.xml", template_tags (TEMPLATE_RSS, array (
		'ITEMS'	=> TEMPLATE_RSS_ITEM,
		'TITLE'	=> safeHTML ($TITLE),
		'URL'	=> "$PATH_URL$file#1",
		'NAME'	=> safeHTML ($NAME),
		'DATE'	=> gmdate ('r'),
		'TEXT'	=> safeHTML (formatText ($TEXT)),
	)));
	
	//redirect to newley created thread
	header ('Location: '.FORUM_URL.$PATH_URL.$file, true, 303);
}

/* ====================================================================================================================== */

//write the website header:
echo template_tags (TEMPLATE_HEADER, array (
	//HTML `<title>`
	'HTMLTITLE'	=> TEMPLATE_HTMLTITLE_SLUG
			  .($PATH ? template_tag (TEMPLATE_HTMLTITLE_NAME, 'NAME', safeHTML ($PATH)) : '')
		   	  .($PAGE > 1 ? template_tag (TEMPLATE_HTMLTITLE_PAGE, 'PAGE', $PAGE) : ''),
	'RSS'		=> 'index.rss',
	'ROBOTS'	=> '',
	'NAV'		=> template_tags (TEMPLATE_HEADER_NAV, array (
		'MENU'	=> TEMPLATE_INDEX_MENU,
		'PATH'	=> $PATH ? template_tag (TEMPLATE_INDEX_PATH_FOLDER, 'PATH', safeHTML ($PATH)) : TEMPLATE_INDEX_PATH
	))
));

/* ---------------------------------------------------------------------------------------------------------------------- */

//get a list of folders
if ($folders = array_filter (
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
)) {
	//string together the list
	foreach ($folders as $folder) @$html .= template_tags (TEMPLATE_INDEX_FOLDER, array (
		'URL'	=> '/'.rawurlencode ($folder).'/',
		'FOLDER'=> safeHTML ($folder)
	));
	
	//output
	echo template_tag (TEMPLATE_INDEX_FOLDERS, 'FOLDERS', $html); $html = "";
}

/* ---------------------------------------------------------------------------------------------------------------------- */

//get list of threads
$threads = preg_grep ('/\.xml$/', scandir ('.'));
array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);

if ($threads) {
	//does this folder have a sticky list?
	$stickies = array ();
	if (file_exists ('sticky.txt')) {
		//get sticky list, trimming any files that no longer exist
		$stickies = array_intersect (file ('sticky.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES), $threads);
		
		//order the stickies by reverse date order
		array_multisort (array_map ('filemtime', $stickies), SORT_DESC, $stickies);
		
		//remove the stickies from the thread list, then add them to the top of the list
		$threads = array_merge ($stickies, array_diff ($threads, $stickies));
	}
	
	//paging (stickies are not included in the count as they appear on all pages)
	$pages = ceil ((count ($threads) - count ($stickies)) / FORUM_THREADS);
	$threads = $stickies + array_slice (array_diff_key ($threads, $stickies), ($PAGE-1) * FORUM_THREADS, FORUM_THREADS);
	
	foreach ($threads as $file) {
		
		$xml = simplexml_load_file ($file);
		$last = &$xml->channel->item[0];
		
		@$html .= template_tags (TEMPLATE_INDEX_THREAD, array (
			'URL'		=> pathinfo ($file, PATHINFO_FILENAME).'?page='.(count ($xml->channel->item) > 1
						? ceil ((count ($xml->channel->item) -1) / FORUM_POSTS) : 1
					),
			'STICKY'	=> in_array ($file, $stickies) ? TEMPLATE_STICKY : '',
			'TITLE'		=> safeHTML ($xml->channel->title),
			'COUNT'		=> count ($xml->channel->item),
			'DATETIME'	=> date ('c', strtotime ($last->pubDate)),
			'TIME'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
			'AUTHOR'	=> safeHTML ($last->author)
		));
	}
	
	echo template_tags (TEMPLATE_INDEX_THREADS, array (
		'THREADS' => $html,
		'PAGES'   => pageLinks ($PAGE, $pages)
	)); $html = "";
}

/* ---------------------------------------------------------------------------------------------------------------------- */

//the new thread form
echo FORUM_ENABLED ? template_tags (TEMPLATE_INDEX_FORM, array (
	'NAME'	=> safeString ($NAME),
	'PASS'	=> safeString ($PASS),
	'TITLE'	=> safeString ($TITLE),
	'TEXT'	=> safeString ($TEXT),
	'ERROR'	=> !$SUBMIT ? ERROR_NONE	//no problem? show default help text
		   : (!$NAME  ? ERROR_NAME	//the name is missing
		   : (!$PASS  ? ERROR_PASS	//the password is missing
		   : (!$TITLE ? ERROR_TITLE	//the title is missing
		   : (!$TEXT  ? ERROR_TEXT	//the message text is missing
		   : ERROR_AUTH))))		//the name / password pair didn’t match
)) : TEMPLATE_INDEX_FORM_DISABLED;

//and we’re all done
echo TEMPLATE_FOOTER;

?>