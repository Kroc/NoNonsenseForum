<?php //display a particular thread’s contents
/* ====================================================================================================================== */
/* NoNonsense Forum v7 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once './shared.php';

//which thread to show
$FILE = (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');

//get the post message, the other fields (name / pass) are retrieved automatically in 'shared.php'
define ('TEXT', safeGet (@$_POST['text'], SIZE_TEXT));

/* ====================================================================================================================== */

//was the submit button clicked? (and is the info valid?)
if (FORUM_ENABLED && NAME && PASS && AUTH && TEXT && @$_POST['email'] == 'example@abc.com') {
	//get a write lock on the file so that between now and saving, no other posts could slip in
	$f = fopen ("$FILE.rss", 'c'); flock ($f, LOCK_EX);
	
	//read the file (not dependent on the lock)
	$xml = simplexml_load_file ("$FILE.rss", 'allow_prepend') or die ('Malformed XML');
	
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
		$item = $xml->channel->prependChild ('item');
		$item->addChild ('title',	safeHTML (
			//add the "RE:" prefix, and reply number to the title
			template_tags (TEMPLATE_RE, array ('NO' => count ($xml->channel->item)-1)).$xml->channel->title
		));
		$item->addChild ('link',	$url);
		$item->addChild ('author',	safeHTML (NAME));
		$item->addChild ('pubDate',	gmdate ('r'));
		$item->addChild ('description',	safeHTML (formatText (TEXT)));
		
		//save
		ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
	} else {
		//if a double-post, link back to the previous item
		$url = $xml->channel->item[0]->link;
	}
	
	//close the lock / file
	flock ($f, LOCK_UN); fclose ($f);
	
	//regenerate the folder's RSS file
	indexRSS ();
	
	//refresh page to see the new post added
	header ("Location: $url", true, 303);
	exit;
}

/* ====================================================================================================================== */

//load the thread
$xml = simplexml_load_file ("$FILE.rss") or die ('Malformed XML');

//info for the site header
$HEADER = array (
	'TITLE'		=> safeHTML ($xml->channel->title),
	'RSS'		=> FORUM_PATH . "$FILE.rss",
	'LOCKED'	=> (bool) $xml->channel->xpath ("category[text()='locked']"),
	'LOCK_URL'	=> FORUM_PATH . 'action.php?lock&amp;path='.safeURL (PATH)."&amp;file=$FILE"
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
//determine the page number (for threads the page number can be given as "last")
define ('PAGE',
	@$_GET['page'] == 'last'
	? ceil (count ($thread) / FORUM_POSTS)
	: (preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : 1)
);

if (count ($thread)) {
	//sort the other way around
	//<stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	foreach ($thread as &$node) $sort_proxy[] = strtotime ($node->pubDate);
	array_multisort ($sort_proxy, SORT_ASC, $thread);
	
	//paging
	$PAGES  = pageList (PAGE, ceil (count ($thread) / FORUM_POSTS));
	$thread = array_slice ($thread, (PAGE-1) * FORUM_POSTS, FORUM_POSTS);
	
	//index number of the replies, accounting for which page we are on
	$no = (PAGE-1) * FORUM_POSTS;
	foreach ($thread as &$post) $POSTS[] = array (
		'AUTHOR'	=> safeHTML ($post->author),
		'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),		//HTML5 `<time>` datetime attribute
		'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),	//human readable time
		'TEXT'		=> $post->description,
		'DELETED'	=> (bool) $post->xpath ("category[text()='deleted']") ? 'deleted' : '',
		'DELETE_URL'	=> FORUM_PATH . 'action.php?delete&amp;path='.safeURL (PATH)."&amp;file=$FILE&amp;id="
				  .substr (strstr ($post->link, '#'), 1),
		'APPEND_URL'	=> FORUM_PATH . 'action.php?append&amp;path='.safeURL (PATH)."&amp;file=$FILE&amp;id="
				  .substr (strstr ($post->link, '#'), 1).'#append',
		'OP'		=> $post->author == $author ? 'op' : '',		//if author is the original poster
		'MOD'		=> isMod ($post->author) ? 'mod' : '',			//if the author is a moderator
		'NO'		=> ++$no,						//number of the reply
		'ID'		=> substr (strstr ($post->link, '#'), 1)
	);
}

/* reply form
   ---------------------------------------------------------------------------------------------------------------------- */
if (FORUM_ENABLED) $FORM = array (
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
