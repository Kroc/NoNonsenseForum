<?php //display a particular thread’s contents

require_once "shared.php";

/* ====================================================================================================================== */

//thread to show. todo: error page / 404
$file = (preg_match ('/(?:([^.\/&]+)\/)?([^.\/]+)$/', @$_GET['file'], $_) ? $_[2] : false) or die ("Malformed request");
if ($path = @$_[1]) chdir ($path);

$xml = simplexml_load_file ("$file.xml", 'allow_prepend');

$page = preg_match ('/^[0-9]+$/', @$_GET['page']) ? (int) $_GET['page'] : 1;

$NAME	= mb_substr (stripslashes (@$_POST['username']), 0, 18,    'UTF-8');
$PASS	= mb_substr (stripslashes (@$_POST['password']), 0, 20,    'UTF-8');
$TEXT	= mb_substr (stripslashes (@$_POST['text']),     0, 32768, 'UTF-8');

if ($SUBMIT = @$_POST['submit']) if (
	FORUM_ENABLED && @$_POST['email'] == "example@abc.com" && $NAME && $PASS && $TEXT
	&& checkName ($NAME, $PASS)
) {
	//where to?
	$page = ceil (count ($xml->channel->item) / FORUM_POSTS) ;
	$url  = ($path ? rawurlencode ($path).'/' : '')."$file?page=$page#".(count ($xml->channel->item) +1);
	
	//add the comment to the thread
	$item = $xml->channel->prependChild ("item");
	$item->addChild ("title",	safeHTML ("RE: ".$xml->channel->title));
	$item->addChild ("link",	FORUM_URL.$url);
	$item->addChild ("author",	safeHTML ($NAME));
	$item->addChild ("pubDate",	gmdate ('r'));
	$item->addChild ("description",	safeHTML (formatText ($TEXT)));
	
	//save
	file_put_contents ("$file.xml", $xml->asXML (), LOCK_EX);
	
	header ('Location: '.FORUM_URL.$url, true, 303);
}

/* ====================================================================================================================== */

echo template_tags (TEMPLATE_HEADER, array (
	'URL'		=> "$file.xml",
	'TITLE'		=> safeHTML ($xml->channel->title).
			   ($page > 1 ? " · Page $page" : ""),
	'RSS_URL'	=> "$file.xml",
	'RSS_TITLE'	=> "Replies",
	'ROBOTS'	=> '',
	'NAV'		=> template_tags (TEMPLATE_HEADER_NAV, array (
		'MENU'	=> template_tag (TEMPLATE_THREAD_MENU, 'RSS', "$file.xml"),
		'PATH'	=> $path ? template_tags (TEMPLATE_THREAD_PATH_FOLDER, array (
				'URL'	=> '/'.rawurlencode ($path).'/',
				'PATH'	=> safeHTML ($path)
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
				"/delete.php?file=".($path ? rawurlencode ($path)."/" : "")."$file"
			),
	'TEXT'		=> $post->description
));

//remember the original poster’s name, for marking replies by the OP
$author = (string) $post->author;

//any replies?
if (count ($thread)) {
	//sort the other way around
	//<http://stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	foreach ($thread as &$node) $sort_proxy[] = strtotime ($node->pubDate);
	array_multisort ($sort_proxy, SORT_ASC, $thread);
	
	//paging
	$pages = ceil (count ($thread) / FORUM_POSTS);
	$thread = array_slice ($thread, ($page-1) * FORUM_POSTS, FORUM_POSTS);
	
	$c=2 + (($page-1) * FORUM_POSTS);
	foreach ($thread as &$post) @$html .= template_tags (TEMPLATE_POST, array (
		'DELETE'	=> $post->xpath ("category[text()='deleted']") ? '' : template_tag (
					TEMPLATE_DELETE, 'URL',
					'/delete.php?file='.($path ? rawurlencode ($path).'/' : '')."$file&amp;id=$c"
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
		'PAGES' => pageLinks ($page, $pages)
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