<?php  //delete threads and posts

include "shared.php";

/* ====================================================================================================================== */

//thread to deal with, including path if in a folder
//todo: deleting indivdual posts (will need better post-IDs first)
$file = (preg_match ('/(?:([^.]+)\/)?([^.\/]+)$/', @$_GET['file'], $_) ? $_[2] : false) or die ("Malformed request");
if ($path = @$_[1]) chdir ($path);

$xml = simplexml_load_file ("$file.xml", 'allow_prepend');

$NAME = mb_substr (stripslashes (@$_POST['username']), 0, 18, 'UTF-8');
$PASS = mb_substr (stripslashes (@$_POST['password']), 0, 20, 'UTF-8');

//has the un/pw been submitted to authenticate the delete?
if ($SUBMIT = @$_POST['submit']) if (
	$NAME && $PASS && checkName ($NAME, $PASS)
	&& (isMod ($NAME) || $NAME == $xml->channel->item[0]->author)
) {
	//delete the thread for reals
	@unlink (APP_ROOT."$file.xml");
	
	//return to the index
	header ("Location: http://".$_SERVER['HTTP_HOST'].($path ? "/$path/" : "/"), true, 303);
}

echo template_tags (TEMPLATE_HEADER, array (
	'URL'		=> "$file.xml",
	'TITLE'		=> "Delete '".htmlspecialchars ($xml->channel->title, ENT_NOQUOTES, 'UTF-8')."'?",
	'RSS_URL'	=> "$file.xml",
	'RSS_TITLE'	=> "Replies",
	'NAV'		=> ""
));

echo template_tags (TEMPLATE_DELETE_THREAD, array (
	'NAME'		=> $NAME,
	'PASS'		=> $PASS,
	'ERROR'		=> !$SUBMIT ? ERROR_DELETE_NONE
			   : (!$NAME  ? ERROR_NAME
			   : (!$PASS  ? ERROR_PASS
			   : ERROR_DELETE_AUTH))
));

echo TEMPLATE_FOOTER;

?>