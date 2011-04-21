<?php  //commit actions on posts, like delete, edit &c.
/* ====================================================================================================================== */
/* NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* delete a thread / post?
   ====================================================================================================================== */
if (isset ($_GET['delete'])) {
	//thread to deal with
	$FILE = (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');
	
	//if deleting just one post, rather than the thread
	define ('ID', preg_match ('/^[A-Z0-9]+$/i', @$_GET['id']) ? $_GET['id'] : false);
	
	//load the thread to get the post preview
	$xml  = simplexml_load_file ("$FILE.rss", 'allow_prepend') or die ('Invalid file');
	//access the particular post. if no ID is provided (deleting the whole thread) use the last item in the RSS file
	//(the first post), otherwise find the ID of the specific post
	$post = !ID ? $xml->channel->item[count ($xml->channel->item) - 1] : @reset ($xml->channel->xpath (
		//this is an xpath 1.0 equivalent of 'ends-with', basically find the permalink with the same ID on the end
		'//item[substring(link, string-length(link) - '.(strlen (ID)-1).') = "'.ID.'"]')
	);
	
	//has the un/pw been submitted to authenticate the delete?
	if (
		NAME && PASS && AUTH
		//only a moderator, or the post originator can delete a post/thread
		&& (isMod (NAME) || NAME == (string) $post->author)
		
	//deleting a post?
	) if (ID) {
		//remove the post text
		$post->description = (NAME == (string) $post->author) ? TEMPLATE_DELETE_USER : TEMPLATE_DELETE_MOD;
		//add a "deleted" category so we know to no longer allow it to be edited or deleted again
		if (!$post->xpath ("category[text()='deleted']")) $post->category[] = 'deleted';
		
		//commit the data
		file_put_contents ("$FILE.rss", $xml->asXML (), LOCK_EX);
		
		//try set the modified date of the file back to the time of the last comment
		//(so that deleting does not push the thread back to the top of the list)
		//note: this may fail if the file is not owned by the Apache process
		@touch ("$FILE.rss", strtotime ($xml->channel->item[0]->pubDate));
		
		//regenerate the folder's RSS file
		indexRSS ();
		
		//return to the deleted post
		header ('Location: '.FORUM_URL.PATH_URL."$FILE#".ID, true, 303);
		exit;
	
	} else {
		//delete the thread for reals
		@unlink (FORUM_ROOT.PATH_DIR."$FILE.rss");
		
		//regenerate the folder's RSS file
		indexRSS ();
		
		//return to the index
		header ('Location: '.FORUM_URL.PATH_URL, true, 303);
		exit;
	}
	
	/* -------------------------------------------------------------------------------------------------------------- */
	
	//prepare template
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
}

?>