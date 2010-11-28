<?php //display a particular thread’s contents
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2010
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

require_once 'shared.php';

/* ====================================================================================================================== */

//thread to show. todo: error page / 404
$FILE = (preg_match ('/^[^.\/]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');

$xml = simplexml_load_file ("$FILE.xml", 'allow_prepend');

$NAME = mb_substr (@$_POST['username'], 0, 18,    'UTF-8');
$PASS = mb_substr (@$_POST['password'], 0, 20,    'UTF-8');
$TEXT = mb_substr (@$_POST['text'],     0, 32768, 'UTF-8');

if ($SUBMIT = @$_POST['submit']) if (
	FORUM_ENABLED && @$_POST['email'] == 'example@abc.com' && $NAME && $PASS && $TEXT
	&& checkName ($NAME, $PASS)
) {
	//where to?
	$page = ceil (count ($xml->channel->item) / FORUM_POSTS) ;
	$url  = "$PATH_URL$FILE?page=$page#".(count ($xml->channel->item) +1);
	
	//ignore a double-post (could be an accident with the back button)
	if (!(
		$NAME == $xml->channel->item[0]->author &&
		formatText ($TEXT) == $xml->channel->item[0]->description
	)) {
		//add the comment to the thread
		$item = $xml->channel->prependChild ('item');
		$item->addChild ('title',	safeHTML (TEMPLATE_RE.$xml->channel->title));
		$item->addChild ('link',	FORUM_URL.$url);
		$item->addChild ('author',	safeHTML ($NAME));
		$item->addChild ('pubDate',	gmdate ('r'));
		$item->addChild ('description',	safeHTML (formatText ($TEXT)));

		//save
		file_put_contents ("$FILE.xml", $xml->asXML (), LOCK_EX);
		clearstatcache ();
	}
	
	header ('Location: '.FORUM_URL.$url, true, 303);
	exit;
}

/* ====================================================================================================================== */

echo template_tags (TEMPLATE_HEADER, array (
	'HTMLTITLE'	=> TEMPLATE_HTMLTITLE_SLUG
			   .template_tag (TEMPLATE_HTMLTITLE_NAME, 'NAME', safeHTML ($xml->channel->title))
			   .($PAGE > 1 ? template_tag (TEMPLATE_HTMLTITLE_PAGE, 'PAGE', $PAGE) : ''),
	'RSS'		=> "$FILE.xml",
	'ROBOTS'	=> '',
	'NAV'		=> template_tags (TEMPLATE_HEADER_NAV, array (
		'MENU'	=> template_tag (TEMPLATE_THREAD_MENU, 'RSS', "$FILE.xml"),
		'PATH'	=> $PATH ? template_tags (TEMPLATE_THREAD_PATH_FOLDER, array (
				'URL'	=> $PATH_URL,
				'PATH'	=> safeHTML ($PATH)
			)) : TEMPLATE_THREAD_PATH
	))
));

/* ---------------------------------------------------------------------------------------------------------------------- */

$thread = $xml->channel->xpath ('item');

$post = array_pop ($thread);
echo template_tags (TEMPLATE_THREAD_FIRST, array (
	'TITLE'		=> safeHTML ($xml->channel->title),
	'NAME'		=> safeHTML ($post->author),
	'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
	'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
	'DELETE'	=> template_tag (
				TEMPLATE_DELETE, 'URL',
				'/delete.php?path='.rawurlencode ($PATH)."&amp;file=$FILE"
			),
	'TEXT'		=> $post->description
));

//remember the original poster’s name, for marking replies by the OP
$author = (string) $post->author;

//any replies?
if (count ($thread)) {
	//sort the other way around
	//<stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	foreach ($thread as &$node) $sort_proxy[] = strtotime ($node->pubDate);
	array_multisort ($sort_proxy, SORT_ASC, $thread);
	
	//paging
	$pages = ceil (count ($thread) / FORUM_POSTS);
	$thread = array_slice ($thread, ($PAGE-1) * FORUM_POSTS, FORUM_POSTS);
	
	$c = 2 + (($PAGE-1) * FORUM_POSTS);
	foreach ($thread as &$post) @$html .= template_tags (TEMPLATE_POST, array (
		'DELETE'	=> $post->xpath ("category[text()='deleted']") ? '' : template_tag (
					TEMPLATE_DELETE, 'URL',
					'/delete.php?path='.rawurlencode ($PATH)."&amp;file=$FILE&amp;id=$c"
				),
		'ID'		=> $c++,
		'TYPE'		=> $post->xpath ("category[text()='deleted']") ? TEMPLATE_POST_DELETED
				   : ($post->author == $author ? TEMPLATE_POST_OP : ''),
		'NAME'		=> safeHTML ($post->author),
		'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
		'TIME'		=> date (DATE_FORMAT, strtotime ($post->pubDate)),
		'TEXT'		=> $post->description
	));
	
	echo template_tags (TEMPLATE_THREAD_POSTS, array (
		'POSTS' => $html,
		'PAGES' => pageLinks ($PAGE, $pages)
	));
}

/* ---------------------------------------------------------------------------------------------------------------------- */

//the reply form
echo FORUM_ENABLED ? template_tags (TEMPLATE_THREAD_FORM, array (
	'NAME'	=> safeString ($NAME),
	'PASS'	=> safeString ($PASS),
	'TEXT'	=> safeString ($TEXT),
	'ERROR'	=> !$SUBMIT ? ERROR_NONE
		   : (!$NAME ? ERROR_NAME
		   : (!$PASS ? ERROR_PASS
		   : (!$TEXT ? ERROR_TEXT
		   : ERROR_AUTH)))
)) : TEMPLATE_THREAD_FORM_DISABLED;

//bon voyage, HTML!
echo TEMPLATE_FOOTER;

?>