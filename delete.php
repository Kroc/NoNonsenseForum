<?php  //delete threads and posts

include "shared.php";

/* ====================================================================================================================== */

//thread to deal with, including path if in a folder
//todo: deleting indivdual posts (will need better post-IDs first)
$file = (preg_match ('/(?:([^.]+)\/)?([^.\/]+)$/', @$_GET['file'], $_) ? $_[2] : false) or die ("Malformed request");
if ($path = @$_[1]) chdir ($path);

//if deleting just one post, rather than the thread
$id = preg_match ('/^[0-9]+$/', @$_GET['id']) ? (int) $_GET['id'] : 1;

$xml = simplexml_load_file ("$file.xml", 'allow_prepend');
$post = &$xml->channel->item [count ($xml->channel->item) - $id];

$NAME = mb_substr (stripslashes (@$_POST['username']), 0, 18, 'UTF-8');
$PASS = mb_substr (stripslashes (@$_POST['password']), 0, 20, 'UTF-8');

//has the un/pw been submitted to authenticate the delete?
if ($SUBMIT = @$_POST['submit']) if (
	$NAME && $PASS && checkName ($NAME, $PASS)
	//only a moderator, or the post originator can delete a post/thread
	&& (isMod ($NAME) || $NAME == (string) $post->author)

//delete thread or post?
) if (@$_GET['id']) {
	//remove the post text
	$post->description = $NAME == (string) $post->author ? TEMPLATE_DELETE_USER : TEMPLATE_DELETE_MOD;
	//add a "deleted" category so we know to no longer allow it to be edited or deleted again
	if (!$post->xpath ("category[text()='deleted']")) $post->category[] = "deleted";
	
	//commit the data
	file_put_contents ("$file.xml", $xml->asXML (), LOCK_EX);
	
	//return to the deleted post
	header ("Location: http://".$_SERVER['HTTP_HOST'].($path ? "/$path/" : "/")."$file#$id", true, 303);
	
} else {
	//delete the thread for reals
	@unlink (APP_ROOT."$file.xml");
	
	//return to the index
	header ("Location: http://".$_SERVER['HTTP_HOST'].($path ? "/$path/" : "/"), true, 303);

}

echo template_tags (TEMPLATE_HEADER, array (
	'URL'		=> "$file.xml",
	'TITLE'		=> "Delete '".safetext ($xml->channel->title)."'?",
	'RSS_URL'	=> "$file.xml",
	'RSS_TITLE'	=> "Replies",
	'NAV'		=> ""
));

echo template_tags (@$_GET['id'] ? TEMPLATE_DELETE_POST : TEMPLATE_DELETE_THREAD, array (
	'NAME'	=> safevalue ($NAME),
	'PASS'	=> safevalue ($PASS),
	'POST'	=> template_tags (TEMPLATE_POST, array (
		'ID'		=> $id,
		'TYPE'		=> '',
		'DELETE'	=> '',
		'NAME'		=> safetext ($post->author),
		'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
		'TIME'		=> strtoupper (date (DATE_FORMAT, strtotime ($post->pubDate))),
		'TEXT'		=> $post->description
	)),
	'ERROR'	=> !$SUBMIT ? ERROR_DELETE_NONE
		   : (!$NAME  ? ERROR_NAME
		   : (!$PASS  ? ERROR_PASS
		   : ERROR_DELETE_AUTH))
));

echo TEMPLATE_FOOTER;

?>