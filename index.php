<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* ====================================================================================================================== */

//submitted info for making a new thread
//(name / password already handled in 'shared.php')
define ('TITLE', mb_substr (trim (@$_POST['title']), 0, 80,    'UTF-8'));
define ('TEXT',  mb_substr (trim (@$_POST['text'] ), 0, 32768, 'UTF-8'));

//has the user the submitted a new thread? (and is the info valid?)
if (FORUM_ENABLED && @$_POST['submit'] && NAME && PASS && AUTH && TITLE && TEXT) {
	//the file on disk is a simplified version of the title
	$c = 0; do $file = preg_replace (
		//replace non alphanumerics with underscores and don’t use more than 2 in a row
		array ('/[^_a-z0-9-]/i', '/_{2,}/'), '_',
			//remove the additional characters added by transliteration, e.g. "ñ" = "~n",
			//has the added benefit of converting “microsoft's” to “microsofts” instead of “microsoft_s”
			str_replace (array ("'", "`", "^", "~", "'", '"'), '', strtolower (
				//unaccent: <php.net/manual/en/function.iconv.php>
			iconv ('UTF-8', 'US-ASCII//IGNORE//TRANSLIT', TITLE)
		))
	//if a thread already exsits with that name, append a number until an available filename is found
	).(++$c ? "_$c" : '');
	while (file_exists ("$file.xml"));
	
	//write out the new thread as an RSS file
	file_put_contents ("$file.xml", template_tags (TEMPLATE_RSS, array (
		'ITEMS'	=> TEMPLATE_RSS_ITEM,
		'TITLE'	=> safeHTML (TITLE),
		'URL'	=> PATH_URL."$file#1",
		'NAME'	=> safeHTML (NAME),
		'DATE'	=> gmdate ('r'),
		'TEXT'	=> safeHTML (formatText (TEXT)),
	)));
	clearstatcache ();
	
	//redirect to newley created thread
	header ('Location: '.FORUM_URL.PATH_URL.$file, true, 303);
	exit;
}

/* ====================================================================================================================== */

//prepare the website header:
$HEADER = array (
	'PATH'	=> safeHTML (PATH),	//the current sub-folder, if any
	'PAGE'	=> PAGE			//the current page number
);

//get a list of folders
foreach (array_filter (
	//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
	preg_grep ('/^(\.|users$|themes$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
) as $FOLDER) $FOLDERS[] = array (
	'URL'	=> '/'.rawurlencode ($FOLDER).'/',
	'NAME'	=> safeHTML ($FOLDER)
);

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
		
		//remove the stickies from the thread list
		$threads = array_diff ($threads, $stickies);
	}
	
	//paging (stickies are not included in the count as they appear on all pages)
	$pages   = ceil ((count ($threads) - count ($stickies)) / FORUM_THREADS);
	$threads = array_merge ($stickies, array_slice ($threads, (PAGE-1) * FORUM_THREADS, FORUM_THREADS));
	
	foreach ($threads as $file) {
		
		$xml = simplexml_load_file ($file);
		$last = &$xml->channel->item[0];
		
		$thread = array (
			'URL'		=> pathinfo ($file, PATHINFO_FILENAME).'?page='.(count ($xml->channel->item) > 1
						? ceil ((count ($xml->channel->item) -1) / FORUM_POSTS) : 1
					),
			'TITLE'		=> safeHTML ($xml->channel->title),
			'COUNT'		=> count ($xml->channel->item),
			'DATETIME'	=> date ('c', strtotime ($last->pubDate)),
			'TIME'		=> date (DATE_FORMAT, strtotime ($last->pubDate)),
			'AUTHOR'	=> safeHTML ($last->author)
		);
		
		if (in_array ($file, $stickies)): $STICKIES[] = $thread; else: $THREADS[] = $thread; endif;
	}
	
	$PAGES = pageLinks (PAGE, $pages);
}

include 'themes/'.FORUM_THEME.'/index.php';

/* ---------------------------------------------------------------------------------------------------------------------- */

//the new thread form
echo FORUM_ENABLED ? template_tags (TEMPLATE_INDEX_FORM, array (
	'NAME'	=> safeString (NAME),
	'PASS'	=> safeString (PASS),
	'TITLE'	=> safeString (TITLE),
	'TEXT'	=> safeString (TEXT),
	'ERROR'	=> !@$_POST['submit'] ? ERROR_NONE	//no problem? show default help text
		   : (!NAME  ? ERROR_NAME		//the name is missing
		   : (!PASS  ? ERROR_PASS		//the password is missing
		   : (!TITLE ? ERROR_TITLE		//the title is missing
		   : (!TEXT  ? ERROR_TEXT		//the message text is missing
		   : ERROR_AUTH))))			//the name / password pair didn’t match
)) : TEMPLATE_INDEX_FORM_DISABLED;

//and we’re all done
echo TEMPLATE_FOOTER;

?>