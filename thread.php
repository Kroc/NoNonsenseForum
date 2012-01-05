<?php //display a particular thread’s contents
/* ====================================================================================================================== */
/* NoNonsense Forum v11 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './shared.php';

//get the post message, the other fields (name / pass) are retrieved automatically in 'shared.php'
define ('TEXT', safeGet (@$_POST['text'], SIZE_TEXT));

//which thread to show
$FILE	= (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');
//load the thread (have to read lock status from the file)
$xml	= @simplexml_load_file ("$FILE.rss") or die ('Malformed XML');
$thread	= $xml->channel->xpath ('item');

//determine the page number (for threads, the page number can be given as "last")
define ('PAGE',
	@$_GET['page'] == 'last'
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

/* thread locked / unlocked
   ====================================================================================================================== */
if (isset ($_GET['lock']) && IS_MOD && AUTH) {
	//get a write lock on the file so that between now and saving, no other posts could slip in
	$f   = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
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


/* new reply submitted
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
		//(see 'theme.config.php', if it exists, otherwise 'theme.config.deafult.php',
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


/* template thread
   ====================================================================================================================== */
//load the template into DOM where we can manipulate it:
//(see 'shared.php' or http://camendesign.com/dom_templating for more details)
$nnf = new DOMTemplate (FORUM_ROOT.'/themes/'.FORUM_THEME.'/thread.html');

/* HTML <head>
   ---------------------------------------------------------------------------------------------------------------------- */
$nnf->set (array (
	//HTML title (= forum / sub-forum name and page number)
	'xpath:/html/head/title'				=> $xml->channel->title.(PAGE>1 ? ' # '.PAGE : ''),
	//application title (= forum / sub-forum name):
	//used for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
	'xpath://meta[@name="application-name"]/@content'	=> PATH ? PATH : FORUM_NAME,
	//application URL (where the pinned site opens at)
	'xpath://meta[@name="msapplication-starturl"]/@content'	=> FORUM_URL.PATH_URL
));

//remove 'custom.css' stylesheet if 'custom.css' is missing
if (!file_exists (FORUM_ROOT.FORUM_PATH.'themes/'.FORUM_THEME.'/custom.css'))
	$nnf->remove ('xpath://link[contains(@href,"custom.css")]')
;

/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
$nnf->set (array (
	'forum-name'	=> FORUM_NAME,
	'img:logo@src'	=> FORUM_PATH.'themes/'.FORUM_THEME.'/icons/'.THEME_LOGO,
	'a:rss@href'	=> PATH_URL."$FILE.rss"
));

//are we in a sub-folder?
if (PATH) {
	//if so, add the sub-forum name to the breadcrumb navigation,
	$nnf->setValue ('subforum-name', PATH);
} else {
	//otherwise -- remove the breadcrumb navigation
	$nnf->remove ('subforum');
}

//search form:
$nnf->set (array (
	//if you're using a Google search, change it to HTTPS if enforced
	'xpath://form[@action="http://google.com/search"]/@action'	=> FORUM_HTTPS	? 'https://encrypted.google.com/search'
											: 'http://google.com/search',
	//set the forum URL for Google search-by-site
	'xpath://input[@name="as_sitesearch"]/@value'			=> $_SERVER['HTTP_HOST']
));

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
	'post-author'			=> $post->author
));
$nnf->setHTML ('post-text', $post->description);

//if the user who made the post is a mod, also mark the whole post as by a mod
//(you might want to style any posts made by a mod differently)
if (isMod ($post->author)) {
	$nnf->addClass ('post-author', 'mod');
	$nnf->addClass ('post',        'mod');
}

//append / delete links?
if (CAN_REPLY) {
	$nnf->set (array (
		'a:post-append@href'	=> FORUM_PATH.'action.php?append&amp;path='.safeURL (PATH)."&amp;file=$FILE&amp;id="
					   .substr (strstr ($post->link, '#'), 1).'#append',
		'a:post-delete@href'	=> FORUM_PATH.'action.php?delete&amp;path='.safeURL (PATH)."&amp;file=$FILE"
	));
} else {
	$nnf->remove ('post-append');
	$nnf->remove ('post-delete');
}

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
			'reply-author'			=> $reply->author
		));
		
		$item->setHTML ('reply-text', $reply->description);
		
		//is this reply from the person who started the thread?
		if ($reply->author == $author) $item->addClass ('.', 'op');
		//if the user who made the reply is a mod, also mark the whole post as by a mod
		//(you might want to style any posts made by a mod differently)
		if (isMod ($reply->author)) {
			$item->addClass ('reply-author', 'mod');
			$item->addClass ('xpath:.',      'mod');
		}
		
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
		)) {
			$item->set (array (
				'a:reply-append@href'	=> FORUM_PATH.'action.php?append&amp;path='.safeURL (PATH)
							   ."&amp;file=$FILE&amp;id=".substr (strstr ($reply->link, '#'), 1)
							   .'#append',
				'a:reply-delete@href'	=> FORUM_PATH.'action.php?delete&amp;path='.safeURL (PATH)
							   ."&amp;file=$FILE&amp;id=".substr (strstr ($reply->link, '#'), 1)
			));
			//append link not available when the reply has been deleted
			if ($reply->xpath ("category[text()='deleted']")) $item->remove ('reply-append');
			//delete link not available when the reply has been deleted, except to mods
			if ($reply->xpath ("category[text()='deleted']") && !IS_MOD) $item->remove ('reply-delete');
		} else {
			$item->remove ('reply-append');
			$item->remove ('reply-delete');
		}
		$item->next ();
	}
} else {
	$nnf->remove ('replies');
}

/* footer
   ---------------------------------------------------------------------------------------------------------------------- */
//are there any local mods?	create the list of local mods
if (!empty ($MODS['LOCAL'])):	$nnf->setHTML ('mods-local-list', theme_nameList ($MODS['LOCAL']));
                        else:	$nnf->remove ('mods-local');	//remove the local mods list section
endif;
//are there any site mods?	create the list of mods
if (!empty ($MODS['GLOBAL'])):	$nnf->setHTML ('mods-list', theme_nameList ($MODS['GLOBAL']));
                         else:	$nnf->remove ('mods');		//remove the mods list section
endif;
//are there any members?	create the list of members
if (!empty ($MEMBERS)):		$nnf->setHTML ('members-list', theme_nameList ($MEMBERS));
                  else:		$nnf->remove ('members');	//remove the members list section
endif;

//is a user signed in?
if (HTTP_AUTH) {
	//yes: remove the signed-out section
	$nnf->remove ('signed-out');
	//set the name of the signed-in user
	$nnf->setValue ('signed-in-name', HTTP_AUTH_NAME);
} else {
	//no: remove the signed-in section
	$nnf->remove ('signed-in');
}

die ($nnf->html ());
















//info for the site header
$HEADER = array (
	'TITLE'		=> safeHTML ($xml->channel->title),
	'RSS'		=> PATH_URL."$FILE.rss",
	'LOCKED'	=> (bool) $xml->channel->xpath ("category[text()='locked']"),
	'LOCK_URL'	=> PATH_URL."$FILE?lock"
);

/* original post
   ---------------------------------------------------------------------------------------------------------------------- */
//take the first post from the thread (removing it from the rest)
$thread = $xml->channel->xpath ('item');
$post   = array_pop ($thread);

//prepare the first post, which on this forum appears above all pages of replies
$POST = array (
	'TITLE'		=> safeHTML ($xml->channel->title),
	'AUTHOR'	=> safeHTML ($post->author),
	'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
	'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
	'DELETE_URL'	=> FORUM_PATH . 'action.php?delete&amp;path='.safeURL (PATH)."&amp;file=$FILE",
	'APPEND_URL'	=> FORUM_PATH . 'action.php?append&amp;path='.safeURL (PATH)."&amp;file=$FILE&amp;id="
			  .substr (strstr ($post->link, '#'), 1).'#append',
	'TEXT'		=> $post->description,
	'MOD'		=> isMod ($post->author),
	'ID'		=> substr (strstr ($post->link, '#'), 1)
);

//remember the original poster’s name, for marking replies by the OP
$author = (string) $post->author;

/* replies
   ---------------------------------------------------------------------------------------------------------------------- */
//determine the page number (for threads, the page number can be given as "last")
define ('PAGE',
	@$_GET['page'] == 'last'
	? ceil (count ($thread) / FORUM_POSTS)
	: (preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1)
);

if (count ($thread)) {
	//sort the other way around
	//<stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	foreach ($thread as &$node) $sort[] = strtotime ($node->pubDate);
	array_multisort ($sort, SORT_ASC, $thread);
	
	//number of pages (stickies are not included in the count as they appear on all pages)
	define ('PAGES', ceil (count ($thread) / FORUM_POSTS));
	//slice the full list into the current page
	$thread = array_slice ($thread, (PAGE-1) * FORUM_POSTS, FORUM_POSTS);
	
	//index number of the replies, accounting for which page we are on
	$no = (PAGE-1) * FORUM_POSTS;
	foreach ($thread as &$post) $POSTS[] = array (
		'AUTHOR'	=> safeHTML ($post->author),
		'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),		//HTML5 `<time>` datetime attribute
		'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),	//human readable time
		'TEXT'		=> $post->description,
		'DELETED'	=> (bool) $post->xpath ("category[text()='deleted']") ? 'deleted' : '',
		//if the current user in the curent forum can append/delete the current post:
		'CAN_ACTION'	=> CAN_REPLY && (
			//moderators can always see append/delete links on all posts
			IS_MOD ||
			//if you are not signed in, all append/delete links are shown (if forum/thread locking is off)
			//if you are signed in, then only links on posts with your name will show
			!HTTP_AUTH ||
			//if this post is the by the owner (they can append/delete to their own posts)
			(strtolower (NAME) == strtolower ($post->author) && (
				//if the forum is post-locked, they must be a member to append/delete their own posts
				(!FORUM_LOCK || FORUM_LOCK == 'threads') || IS_MEMBER
			))
		),
		'DELETE_URL'	=> FORUM_PATH . 'action.php?delete&amp;path='.safeURL (PATH)."&amp;file=$FILE&amp;id="
				  .substr (strstr ($post->link, '#'), 1),
		'APPEND_URL'	=> FORUM_PATH . 'action.php?append&amp;path='.safeURL (PATH)."&amp;file=$FILE&amp;id="
				  .substr (strstr ($post->link, '#'), 1).'#append',
		'OP'		=> $post->author == $author ? 'op' : '',		//if author is the original poster
		'MOD'		=> isMod ($post->author) ? 'mod' : '',			//if the author is a moderator
		'NO'		=> ++$no,						//number of the reply
		'ID'		=> substr (strstr ($post->link, '#'), 1)
	);
} else {
	define ('PAGES', 1);
}

/* reply form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_REPLY) $FORM = array (
	'NAME'	=> safeString (NAME),
	'PASS'	=> safeString (PASS),
	'TEXT'	=> safeString (TEXT),
	'ERROR'	=> empty ($_POST) ? ERROR_NONE
		 : (!NAME ? ERROR_NAME
		 : (!PASS ? ERROR_PASS
		 : (!TEXT ? ERROR_TEXT
		 : ERROR_AUTH)))
);

//all the data prepared, now output the HTML
include FORUM_ROOT.'/themes/'.FORUM_THEME.'/thread.inc.php';

?>
