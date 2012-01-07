<?php //display a particular thread’s contents
/* ====================================================================================================================== */
/* NoNonsense Forum v11 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './start.php';

//get the post message, the other fields (name / pass) are retrieved automatically in 'start.php'
define ('TEXT', safeGet (@$_POST['text'], SIZE_TEXT));

//which thread to show
$FILE	= (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');
//load the thread (have to read lock status from the file)
$xml	= @simplexml_load_file ("$FILE.rss") or die ('Malformed XML');
$thread	= $xml->channel->xpath ('item');

//determine the page number (for threads, the page number can be given as "last")
define ('PAGE', @$_GET['page'] == 'last'
	? ceil (count ($thread) / FORUM_POSTS)
	: (preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1)
);

//access rights for the current user
define ('CAN_REPLY', FORUM_ENABLED && (
	//- if you are a moderator (doesn’t matter if the forum or thread is locked)
	IS_MOD ||
	//- if you are a member, the forum lock doesn’t matter, but you can’t reply to locked threads (only mods can)
	(!(bool) $xml->channel->xpath ("category[text()='locked']") && IS_MEMBER) ||
	//- if you are neither a mod nor a member, then as long as: 1. the thread is not locked, and
	//  2. the forum is such that anybody can reply (unlocked or thread-locked), then you can reply
	(!(bool) $xml->channel->xpath ("category[text()='locked']") && (!FORUM_LOCK || FORUM_LOCK == 'threads'))
));


/* ======================================================================================================================
   thread lock / unlock action
   ====================================================================================================================== */
if (isset ($_GET['lock']) && IS_MOD && AUTH) {
	//get a read/write lock on the file so that between now and saving, no other posts could slip in
	//normally we could use a write-only lock 'c', but on Windows you can't read the file when write-locked!
	$f   = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
	//we have to read the XML using the file handle that's locked because in Windows, functions like
	//`get_file_contents`, or even `simplexml_load_file`, won't work due to the lock
	$xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss")), 'DXML') or die ('Malformed XML');
	
	if ((bool) $xml->channel->xpath ("category[text()='locked']")) {
		//if there’s a "locked" category, remove it
		//note: for simplicity this removes *all* channel categories as NNF only uses one atm,
		//      in the future the specific "locked" category needs to be removed
		unset ($xml->channel->category);
		//when unlocking, go to the thread
		$url = FORUM_URL.PATH_URL."$FILE?page=last#reply";
	} else {
		//if no "locked" category, add it
		$xml->channel->category[] = 'locked';
		//if locking return to the index
		//(todo: could return to the particular page in the index the thread is on--complex!)
		$url = FORUM_URL.PATH_URL;
	}
	
	//commit the data
	rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
	//close the lock / file
	flock ($f, LOCK_UN); fclose ($f);
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	header ("Location: $url", true, 303);
	exit;
}


/* ======================================================================================================================
   append link clicked
   ====================================================================================================================== */
if ($ID = (preg_match ('/^[A-Z0-9]+$/i', @$_GET['append']) ? $_GET['append'] : false)) {
	//get a write lock on the file so that between now and saving, no other posts could slip in
	$f = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
	$xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss")), 'DXML') or die ('Malformed XML');
	
	//find the post using the ID (we need to know the numerical index for later)
	for ($i=0; $i<count ($xml->channel->item); $i++) if (strstr ($xml->channel->item[$i]->link, '#') == "#$ID") break;
	$post = $xml->channel->item[$i];
	
	/* has the un/pw been submitted to authenticate the append?
	   -------------------------------------------------------------------------------------------------------------- */
	if (AUTH && TEXT && CAN_REPLY && (
		//a moderator can always append
		IS_MOD ||
		//the owner of a post can append
		(strtolower (NAME) == strtolower ($post->author) && (
			//if the forum is post-locked, they must be a member to append to their own posts
			(!FORUM_LOCK || FORUM_LOCK == 'threads') || IS_MEMBER
		))
	)) {
		$now = time ();
		$post->description .= "\n".sprintf (THEME_APPEND,
			safeHTML (NAME),		//author
			gmdate ('r', $now),		//machine-readable time
			date (DATE_FORMAT, $now)	//human-readable time
		).formatText (TEXT);
		
		//need to know what page this post is on to redirect back to it
		$page = ceil ((count ($xml->channel->item)-1-$i) / FORUM_POSTS);
		
		//commit the data
		rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
		//close the lock / file
		flock ($f, LOCK_UN); fclose ($f);
		
		//try set the modified date of the file back to the time of the last comment
		//(appending to a post does not push the thread back to the top of the list)
		//note: this may fail if the file is not owned by the Apache process
		@touch ("$FILE.rss", strtotime ($xml->channel->item[0]->pubDate));
		
		//regenerate the folder's RSS file
		indexRSS ();
		
		//return to the appended post
		header ('Location: '.FORUM_URL.PATH_URL."$FILE?page=$page#$ID", true, 303);
		exit;
	}
	
	//close the lock / file
	flock ($f, LOCK_UN); fclose ($f);
	
	/* template the append page
	   -------------------------------------------------------------------------------------------------------------- */
	$nnf = prepareTemplate (
		FORUM_ROOT.'/themes/'.FORUM_THEME.'/append.html',
		'Append to '.$post->title
	);
	
	//the preview post
	$nnf->set (array (
		'post-title'			=> $xml->channel->title,
		'post-title@id'			=> substr (strstr ($post->link, '#'), 1),
		'time:post-time'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
		'time:post-time@datetime'	=> gmdate ('r', strtotime ($post->pubDate)),
		'post-author'			=> $post->author
	))->setHTML (
		'post-text', $post->description
	);
	
	//if the user who made the post is a mod, also mark the whole post as by a mod
	//(you might want to style any posts made by a mod differently)
	if (isMod ($post->author)) $nnf->addClass ('post, post-author', 'mod');
	
	$nnf->set (array (
		//set the field values from what was typed in before
		'input:name-field-http@value'	=> NAME,
		'input:name-field@value'	=> NAME,
		'input:pass-field@value'	=> PASS,
		'textarea:text-field'		=> TEXT

	//is the user already signed-in?
	))->remove (HTTP_AUTH
		//don’t need the usual name / password fields and the deafult message for anonymous users
		? 'name, pass, email, error-none'
		//user is not signed in, remove the "you are signed in as:" field and the message for signed in users
		: 'http-auth, error-none-http'
		
	//handle error messages
	)->remove (array (
		//if there's an error of any sort, remove the default messages
		'error-none, error-none-http' => !empty ($_POST),
		//if the username & password are correct, remove the error message
		'error-auth'	=> empty ($_POST) || !TEXT || !NAME || !PASS || AUTH,
		//if the password is valid, remove the erorr message
		'error-pass'	=> empty ($_POST) || !TEXT || !NAME || PASS,
		//if the name is valid, remove the erorr message
		'error-name'	=> empty ($_POST) || !TEXT || NAME,
		//if the message text is valid, remove the error message
		'error-text'	=> empty ($_POST) || TEXT
	));
	
	die ($nnf->html ());
}


/* ======================================================================================================================
   delete link clicked
   ====================================================================================================================== */
if (isset ($_GET['delete'])) {
	//the ID of the post to delete. will be omitted if deleting the whole thread
	$ID = (preg_match ('/^[A-Z0-9]+$/i', @$_GET['delete']) ? $_GET['delete'] : false);
	//get a write lock on the file so that between now and saving, no other posts could slip in
	$f = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
	
	//load the thread to get the post preview
	$xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss")), 'DXML') or die ('Malformed XML');
	
	//access the particular post. if no ID is provided (deleting the whole thread) use the last item in the RSS file
	//(the first post), otherwise find the ID of the specific post
	if (!$ID) {
		$post = $xml->channel->item[count ($xml->channel->item) - 1];
	} else {
		//find the post using the ID (we need to know the numerical index for later)
		for ($i=0; $i<count ($xml->channel->item); $i++) if (
			strstr ($xml->channel->item[$i]->link, '#') == "#$ID"
		) break;
		$post = $xml->channel->item[$i];
	}
	
	/* has the un/pw been submitted to authenticate the delete?
	   -------------------------------------------------------------------------------------------------------------- */
	if (AUTH && CAN_REPLY && (
		//a moderator can always delete
		IS_MOD ||
		//the owner of a post can delete
		(strtolower (NAME) == strtolower ($post->author) && (
			//if the forum is post-locked, they must be a member to delete their own posts
			(!FORUM_LOCK || FORUM_LOCK == 'threads') || IS_MEMBER
		))
	//deleting a post?
	)) if ($ID) {
		//full delete? (option ticked, is moderator, and post is on the last page)
		if (
			(IS_MOD && $i <= (count ($xml->channel->item)-2) % FORUM_POSTS) &&
			//if the post has already been blanked, delete it fully
			(isset ($_POST['remove']) || $post->xpath ("category[text()='deleted']"))
		) {
			//remove the post from the thread entirely
			unset ($xml->channel->item[$i]);
			
			//we’ll redirect to the last page (which may have changed when the post was deleted)
			$url = FORUM_URL.PATH_URL."$FILE?page=last#replies";
			
		} else {
			//remove the post text
			$post->description = (NAME == (string) $post->author) ? THEME_DEL_USER : THEME_DEL_MOD;
			//add a "deleted" category so we know to no longer allow it to be edited or deleted again
			if (!$post->xpath ("category[text()='deleted']")) $post->category[] = 'deleted';
			
			//need to know what page this post is on to redirect back to it
			$url = FORUM_URL.PATH_URL."$FILE?page=".ceil ((count ($xml->channel->item)-1-$i) / FORUM_POSTS)."#$ID";
		}
		
		//commit the data
		rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
		//close the lock / file
		flock ($f, LOCK_UN); fclose ($f);
		
		//try set the modified date of the file back to the time of the last comment
		//(so that deleting does not push the thread back to the top of the list)
		//note: this may fail if the file is not owned by the Apache process
		@touch ("$FILE.rss", strtotime ($xml->channel->item[0]->pubDate));
		
		//regenerate the folder's RSS file
		indexRSS ();
		
		//return to the deleted post / last page
		header ("Location: $url", true, 303);
		exit;
	
	} else {
		//close the lock / file
		flock ($f, LOCK_UN); fclose ($f);
		
		//delete the thread for reals
		@unlink (FORUM_ROOT.PATH_DIR."$FILE.rss");
		
		//regenerate the folder's RSS file
		indexRSS ();
		
		//return to the index
		header ('Location: '.FORUM_URL.PATH_URL, true, 303);
		exit;
	}
	
	//close the lock / file
	flock ($f, LOCK_UN); fclose ($f);
	
	/* template the delete page
	   -------------------------------------------------------------------------------------------------------------- */
	$nnf = prepareTemplate (
		FORUM_ROOT.'/themes/'.FORUM_THEME.'/delete.html',
		'Delete '.$post->title.'?'
	);
	
	//the preview post
	$nnf->set (array (
		'post-title'			=> $xml->channel->title,
		'post-title@id'			=> substr (strstr ($post->link, '#'), 1),
		'time:post-time'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
		'time:post-time@datetime'	=> gmdate ('r', strtotime ($post->pubDate)),
		'post-author'			=> $post->author
	))->setHTML (
		'post-text', $post->description
	);
	
	//if the user who made the post is a mod, also mark the whole post as by a mod
	//(you might want to style any posts made by a mod differently)
	if (isMod ($post->author)) $nnf->addClass ('post, post-author', 'mod');
	
	$nnf->set (array (
		//set the field values from what was typed in before
		'input:name-field@value'	=> NAME,
		'input:pass-field@value'	=> PASS,
		
	//are we deleting the whole thread, or just one reply?
	))->remove ($ID 
		? 'error-none-thread'
		: 'error-none-reply, remove'	//if deleting the whole thread, also remove the checkbox option
		
	//handle error messages
	)->remove (array (
		//if there's an error of any sort, remove the default messages
		'error-none-thread, error-none-reply' => !empty ($_POST),
		//if the username & password are correct, remove the error message
		'error-auth'	=> empty ($_POST) || !NAME || !PASS || AUTH,
		//if the password is valid, remove the erorr message
		'error-pass'	=> empty ($_POST) || !NAME || PASS,
		//if the name is valid, remove the erorr message
		'error-name'	=> empty ($_POST) || NAME
	));
	
	die ($nnf->html ());
}


/* ======================================================================================================================
   new reply submitted
   ====================================================================================================================== */
//was the submit button clicked? (and is the info valid?)
if (CAN_REPLY && AUTH && TEXT) {
	//get a read/write lock on the file so that between now and saving, no other posts could slip in
	//normally we could use a write-only lock 'c', but on Windows you can't read the file when write-locked!
	$f = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
	//we have to read the XML using the file handle that's locked because in Windows, functions like
	//`get_file_contents`, or even `simplexml_load_file`, won't work due to the lock
	$xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss")), 'DXML') or die ('Malformed XML');
	
	if (!(
		//ignore a double-post (could be an accident with the back button)
		NAME == $xml->channel->item[0]->author &&
		formatText (TEXT) == $xml->channel->item[0]->description &&
		//can’t post if the thread is locked
		!$xml->channel->xpath ("category[text()='locked']")
	)) {
		//where to?
		//(we won’t use `page=last` here as we are effecitvely handing the user a permalink here)
		$page = ceil (count ($xml->channel->item) / FORUM_POSTS);
		$url  = FORUM_URL.PATH_URL.$FILE.($page > 1 ? "?page=$page" : '').'#'.base_convert (microtime (), 10, 36);
		
		//add the comment to the thread
		$item = $xml->channel->item[0]->insertBefore ('item');
		//add the "RE:" prefix, and reply number to the title
		//(see 'theme.config.php' if it exists, otherwise 'theme.config.deafult.php',
		//in the theme's folder for the definition of `THEME_RE`)
		$item->addChild ('title',	safeHTML (sprintf (THEME_RE,
			count ($xml->channel->item)-1,	//number of the reply
			$xml->channel->title		//thread title
		)));
		$item->addChild ('link',	$url);
		$item->addChild ('author',	safeHTML (NAME));
		$item->addChild ('pubDate',	gmdate ('r'));
		$item->addChild ('description',	safeHTML (formatText (TEXT)));
		
		//write the file: first move the write-head to 0, remove the file's contents, and then write new ones
		rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
	} else {
		//if a double-post, link back to the previous post
		$url = $xml->channel->item[0]->link;
	}
	
	//close the lock / file
	flock ($f, LOCK_UN); fclose ($f);
	
	//regenerate the forum / sub-forums's RSS file
	indexRSS ();
	
	//refresh page to see the new post added
	header ("Location: $url", true, 303);
	exit;
}


/* ======================================================================================================================
   template thread
   ====================================================================================================================== */
//load the template into DOM where we can manipulate it:
//(see 'lib/domtemplate.php' or <camendesign.com/dom_templating> for more details)
$nnf = prepareTemplate (
	FORUM_ROOT.'/themes/'.FORUM_THEME.'/thread.html',
	$xml->channel->title.(PAGE>1 ? ' # '.PAGE : '')
);

//the thread itself is the RSS feed :)
$nnf->setValue ('a:rss@href', PATH_URL."$FILE.rss");

//if replies can't be added (forum or thread is locked, user is not moderator / member),
//remove the "add reply" link and anything else (like the input form) related to posting
if (!CAN_REPLY) $nnf->remove ('can-reply');

//if the forum is not post-locked (only mods can post / reply) then remove the warning message
if (FORUM_LOCK != 'posts') $nnf->remove ('forum-lock-posts');


/* post
   ---------------------------------------------------------------------------------------------------------------------- */
//take the first post from the thread (removing it from the rest)
$post = array_pop ($thread);

//prepare the first post, which on this forum appears above all pages of replies
$nnf->set (array (
	'post-title'			=> $xml->channel->title,
	'post-title@id'			=> substr (strstr ($post->link, '#'), 1),
	'time:post-time'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
	'time:post-time@datetime'	=> gmdate ('r', strtotime ($post->pubDate)),
	'post-author'			=> $post->author,
	'a:post-append@href'		=> '?append='.substr (strstr ($post->link, '#'), 1).'#append',
	'a:post-delete@href'		=> '?delete'
))->setHTML (
	'post-text', $post->description
);

//if the user who made the post is a mod, also mark the whole post as by a mod
//(you might want to style any posts made by a mod differently)
if (isMod ($post->author)) $nnf->addClass ('post, post-author', 'mod');

//append / delete links?
if (!CAN_REPLY) $nnf->remove ('post-append', 'post-delete');

//remember the original poster’s name, for marking replies by the OP
$author = (string) $post->author;


/* replies
   ---------------------------------------------------------------------------------------------------------------------- */
if (count ($thread)) {
	//sort the other way around
	//<stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	foreach ($thread as &$node) $sort[] = strtotime ($node->pubDate);
	array_multisort ($sort, SORT_ASC, $thread);
	
	//do the page links
	//(`theme_pageList` is defined in 'theme.config.php' if it exists, otherwise 'theme.config.default.php')
	$nnf->setHTML ('pages', theme_pageList (
		//page number,	number of pages
		PAGE, 		ceil (count ($thread) / FORUM_POSTS)
	));
	//slice the full list into the current page
	$thread = array_slice ($thread, (PAGE-1) * FORUM_POSTS, FORUM_POSTS);
	
	$item = $nnf->repeat ('reply');
	
	//index number of the replies, accounting for which page we are on
	$no = (PAGE-1) * FORUM_POSTS;
	foreach ($thread as &$reply) {
		//has the reply been deleted (blanked)?
		if ($reply->xpath ("category[text()='deleted']")) $item->addClass ('xpath:.', 'deleted');
		
		$item->set (array (
			'xpath:./@id'			=> substr (strstr ($reply->link, '#'), 1),
			'time:reply-time'		=> date (DATE_FORMAT, strtotime ($reply->pubDate)),
			'time:reply-time@datetime'	=> gmdate ('r', strtotime ($reply->pubDate)),
			'a:reply-number'		=> '#'.(++$no).'.', //todo: need to template this
			'a:reply-number@href'		=> '?page='.PAGE.strstr ($reply->link, '#'),
			'reply-author'			=> $reply->author,
			'a:reply-append@href'		=> '?append='.substr (strstr ($reply->link, '#'), 1).'#append',
			'a:reply-delete@href'		=> '?delete='.substr (strstr ($reply->link, '#'), 1)
		))->setHTML (
			'reply-text', $reply->description
		);
		
		//is this reply from the person who started the thread?
		if ($reply->author == $author) $item->addClass ('xpath:.', 'op');
		//if the user who made the reply is a mod, also mark the whole post as by a mod
		//(you might want to style any posts made by a mod differently)
		if (isMod ($reply->author)) $item->addClass ('xpath:., reply-author', 'mod');
		
		//if the current user in the curent forum can append/delete the current reply:
		if (CAN_REPLY && (
			//moderators can always see append/delete links on all replies
			IS_MOD ||
			//if you are not signed in, all append/delete links are shown (if forum/thread locking is off)
			//if you are signed in, then only links on replies with your name will show
			!HTTP_AUTH ||
			//if this reply is the by the owner (they can append/delete to their own replies)
			(strtolower (NAME) == strtolower ($reply->author) && (
				//if the forum is post-locked, they must be a member to append/delete their own replies
				(!FORUM_LOCK || FORUM_LOCK == 'threads') || IS_MEMBER
			))
		)) {	//append link not available when the reply has been deleted
			if ($reply->xpath ("category[text()='deleted']")) $item->remove ('reply-append');
			//delete link not available when the reply has been deleted, except to mods
			if ($reply->xpath ("category[text()='deleted']") && !IS_MOD) $item->remove ('reply-delete');
		} else {
			$item->remove ('reply-append, reply-delete');
		}
		$item->next ();
	}
} else {
	$nnf->remove ('replies');
}

/* reply form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_REPLY) $nnf->set (array (
	//set the field values from what was typed in before
	'input:name-field-http@value'	=> NAME,
	'input:name-field@value'	=> NAME,
	'input:pass-field@value'	=> PASS,
	'textarea:text-field'		=> TEXT
	
//is the user already signed-in?
))->remove (HTTP_AUTH
	//don’t need the usual name / password fields and the deafult message for anonymous users
	? 'name, pass, email, error-none'
	//user is not signed in, remove the "you are signed in as:" field and the message for signed in users
	: 'http-auth, error-none-http'
	
//are new registrations allowed?
)->remove (FORUM_NEWBIES
	? 'error-newbies'	//yes: remove the warning message
	: 'error-none'		//no:  remove the default message
	
//handle error messages
)->remove (array (
	//if there's an error of any sort, remove the default messages
	'error-none, error-none-http, error-newbies' => !empty ($_POST),
	//if the username & password are correct, remove the error message
	'error-auth'	=> empty ($_POST) || !TEXT || !NAME || !PASS || AUTH,
	//if the password is valid, remove the erorr message
	'error-pass'	=> empty ($_POST) || !TEXT || !NAME || PASS,
	//if the name is valid, remove the erorr message
	'error-name'	=> empty ($_POST) || !TEXT || NAME,
	//if the message text is valid, remove the error message
	'error-text'	=> empty ($_POST) || TEXT
));

die ($nnf->html ());

?>