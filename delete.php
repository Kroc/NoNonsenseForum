<?php  //delete threads and posts
/* ====================================================================================================================== */
/* NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* ====================================================================================================================== */

//thread to deal with
$FILE = (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');

//if deleting just one post, rather than the thread
define ('ID', preg_match ('/^[1-9][0-9]*$/', @$_GET['id']) ? (int) $_GET['id'] : 1);

//load the thread to get the post preview
$xml = simplexml_load_file ("$FILE.xml", 'allow_prepend') or die ('Invalid file');
$post = &$xml->channel->item[count ($xml->channel->item) - ID];

//has the un/pw been submitted to authenticate the delete?
if (
	NAME && PASS && AUTH
	//only a moderator, or the post originator can delete a post/thread
	&& (isMod (NAME) || NAME == (string) $post->author)

//deleting a post?
) if (@$_GET['id']) {
	//remove the post text
	$post->description = (NAME == (string) $post->author) ? TEMPLATE_DELETE_USER : TEMPLATE_DELETE_MOD;
	//add a "deleted" category so we know to no longer allow it to be edited or deleted again
	if (!$post->xpath ("category[text()='deleted']")) $post->category[] = 'deleted';
	
	//commit the data
	file_put_contents ("$FILE.xml", $xml->asXML (), LOCK_EX);
	
	//try set the modified date of the file back to the time of the last comment
	//(so that deleting does not push the thread back to the top of the list)
	//note: this may fail if the file is not owned by the Apache process
	@touch ("$FILE.xml", strtotime ($xml->channel->item[0]->pubDate));
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	//return to the deleted post
	header ('Location: '.FORUM_URL.PATH_URL."$FILE#".ID, true, 303);
	exit;
	
} else {
	//delete the thread for reals
	@unlink (FORUM_ROOT.PATH_DIR."$FILE.xml");
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	//return to the index
	header ('Location: '.FORUM_URL.PATH_URL, true, 303);
	exit;
}

/* ====================================================================================================================== */

$HEADER = array (
	'THREAD'	=> safeHTML ($xml->channel->title)
);

$FORM = array (
	'NAME'		=> safeString (NAME),
	'PASS'		=> safeString (PASS),
	'ERROR'		=> empty ($_POST) ? ERROR_NONE
			 : (!NAME ? ERROR_NAME
			 : (!PASS ? ERROR_PASS
			 : ERROR_AUTH))
);

$POST = array (
	'AUTHOR'	=> safeHTML ($post->author),
	'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
	'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
	'TEXT'		=> $post->description
);

//all the data prepared, now output the HTML
include 'themes/'.FORUM_THEME.'/delete.inc.php';

?>