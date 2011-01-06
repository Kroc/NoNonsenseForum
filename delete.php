<?php  //delete threads and posts
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2010
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* ====================================================================================================================== */

//thread to deal with
$FILE = (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');

//if deleting just one post, rather than the thread
$ID = preg_match ('/^[1-9][0-9]*$/', @$_GET['id']) ? (int) $_GET['id'] : 1;

//load the thread to get the post preview
$xml = simplexml_load_file ("$FILE.xml", 'allow_prepend') or die ('Invalid file');
$post = &$xml->channel->item[count ($xml->channel->item) - $ID];

//has the un/pw been submitted to authenticate the delete?
if ($SUBMIT = @$_POST['submit']) if (
	//validate form
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
	//(todo: deleting a post should not push the thread to the top of the index!)
	file_put_contents ("$FILE.xml", $xml->asXML (), LOCK_EX);
	clearstatcache ();
	
	//return to the deleted post
	header ('Location: '.FORUM_URL.PATH_URL."$FILE#$ID", true, 303);
	exit;
	
} else {
	//delete the thread for reals
	@unlink (FORUM_ROOT.PATH_DIR."$FILE.xml");
	
	//return to the index
	header ('Location: '.FORUM_URL.PATH_URL, true, 303);
	exit;
}

echo template_tags (TEMPLATE_HEADER, array (
	'HTMLTITLE'	=> TEMPLATE_HTMLTITLE_SLUG.template_tag (TEMPLATE_HTMLTITLE_NAME, 'NAME',
				@$_GET['id'] ? TEMPLATE_HTMLTITLE_DELETE_POST : TEMPLATE_HTMLTITLE_DELETE_THREAD
			),
	'RSS'		=> "$FILE.xml",
	'ROBOTS'	=> TEMPLATE_HEADER_ROBOTS,
	'NAV'		=> ''
));

echo template_tags (@$_GET['id'] ? TEMPLATE_DELETE_POST : TEMPLATE_DELETE_THREAD, array (
	'NAME'	=> safeString (NAME),
	'PASS'	=> safeString (PASS),
	'POST'	=> template_tags (TEMPLATE_POST, array (
		'ID'		=> $ID,
		'TYPE'		=> '',
		'DELETE'	=> '',
		'NAME'		=> safeHTML ($post->author),
		'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
		'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
		'TEXT'		=> $post->description
	)),
	'ERROR'	=> !$SUBMIT ? ERROR_DELETE_NONE
		   : (!NAME ? ERROR_NAME
		   : (!PASS ? ERROR_PASS
		   : ERROR_DELETE_AUTH))
));

echo TEMPLATE_FOOTER;

?>