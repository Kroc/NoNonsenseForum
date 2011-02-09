<?php //display a particular thread’s contents
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* ====================================================================================================================== */

//thread to show. todo: error page / 404
$FILE = (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');
$xml = simplexml_load_file ("$FILE.xml", 'allow_prepend') or die ('Malformed XML');

//get the post message, the other fields (name / pass) are retrieved automatically in 'shared.php'
define ('TEXT', mb_substr (@$_POST['text'], 0, 32768, 'UTF-8'));

//was the submit button clicked? (and is the info valid?)
if (FORUM_ENABLED && @$_POST['submit'] && NAME && PASS && AUTH && TEXT) {
	//where to?
	$page = ceil (count ($xml->channel->item) / FORUM_POSTS) ;
	$url  = PATH_URL."$FILE?page=$page#".(count ($xml->channel->item)+1);
	
	//ignore a double-post (could be an accident with the back button)
	if (!(
		NAME == $xml->channel->item[0]->author &&
		formatText (TEXT) == $xml->channel->item[0]->description
	)) {
		//add the comment to the thread
		$item = $xml->channel->prependChild ('item');
		$item->addChild ('title',	safeHTML (TEMPLATE_RE.$xml->channel->title));
		$item->addChild ('link',	FORUM_URL.$url);
		$item->addChild ('author',	safeHTML (NAME));
		$item->addChild ('pubDate',	gmdate ('r'));
		$item->addChild ('description',	safeHTML (formatText (TEXT)));
		
		//save
		file_put_contents ("$FILE.xml", $xml->asXML (), LOCK_EX);
		clearstatcache ();
	}
	
	header ('Location: '.FORUM_URL.$url, true, 303);
	exit;
}

/* ====================================================================================================================== */

$HEADER = array (
	'THREAD'	=> safeHTML ($xml->channel->title),
	'PAGE'		=> PAGE,
	'RSS'		=> "$FILE.xml",
	'PATH'		=> safeHTML (PATH),
	'PATH_URL'	=> PATH_URL
);

/* ---------------------------------------------------------------------------------------------------------------------- */

$thread = $xml->channel->xpath ('item');
$post = array_pop ($thread);

$POST = array (
	'TITLE'		=> safeHTML ($xml->channel->title),
	'AUTHOR'	=> safeHTML ($post->author),
	'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
	'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
	'DELETE_URL'	=> '/delete.php?path='.rawurlencode (PATH)."&amp;file=$FILE",
	'TEXT'		=> $post->description
);

//remember the original poster’s name, for marking replies by the OP
$author = (string) $post->author;

//any replies?
if (count ($thread)) {
	//sort the other way around
	//<stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	foreach ($thread as &$node) $sort_proxy[] = strtotime ($node->pubDate);
	array_multisort ($sort_proxy, SORT_ASC, $thread);
	
	//paging
	$PAGES  = pageLinks (PAGE, ceil (count ($thread) / FORUM_POSTS));
	$thread = array_slice ($thread, (PAGE-1) * FORUM_POSTS, FORUM_POSTS);
	
	$id = 2 + ((PAGE-1) * FORUM_POSTS);
	foreach ($thread as &$post) $POSTS[] = array (
		'ID'		=> $id++,
		'AUTHOR'	=> safeHTML ($post->author),
		'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
		'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
		'TEXT'		=> $post->description,
		'DELETED'	=> (bool) $post->xpath ("category[text()='deleted']"),
		'DELETE_URL'	=> '/delete.php?path='.rawurlencode (PATH)."&amp;file=$FILE&amp;id=$id",
		'OP'		=> $post->author == $author
	);
}

/* ---------------------------------------------------------------------------------------------------------------------- */

//the reply form
if (FORUM_ENABLED) $FORM = array (
	'NAME'	=> safeString (NAME),
	'PASS'	=> safeString (PASS),
	'TEXT'	=> safeString (TEXT),
	'ERROR'	=> !@$_POST['submit'] ? ERROR_NONE
		   : (!NAME ? ERROR_NAME
		   : (!PASS ? ERROR_PASS
		   : (!TEXT ? ERROR_TEXT
		   : ERROR_AUTH)))
);

//all the data prepared, now output the HTML
include 'themes/'.FORUM_THEME.'/thread.inc.php';

?>