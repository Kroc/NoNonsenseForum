<?php

include "shared.php";

/* ====================================================================================================================== */

//thread to show. todo: error page / 404
$file = (preg_match ('/(?:([^.]+)\/)?([^.\/]+)$/', @$_GET['file'], $_) ? $_[2] : false) or die ("Malformed request");
if ($path = @$_[1]) chdir ($path);

$xml = simplexml_load_file ("$file.xml", 'my_node');

$page = preg_match ('/^[0-9]+$/', @$_GET['page']) ? (int) $_GET['page'] : 1;

$NAME	= mb_substr (stripslashes (@$_POST['username']), 0, 18,    'UTF-8');
$PASS	= mb_substr (stripslashes (@$_POST['password']), 0, 20,    'UTF-8');
$TEXT	= mb_substr (stripslashes (@$_POST['text']),     0, 32768, 'UTF-8');

if ($SUBMIT = @$_POST['submit']) if (
	APP_ENABLED && @$_POST['email'] == "example@abc.com" && $NAME && $PASS && $TEXT
	&& checkName ($NAME, $PASS)
) {
	//where to?
	$page = ceil ((count ($xml->channel->xpath ('item'))) / APP_POSTS);
	$url = ($path ? rawurlencode ($path)."/" : "")."$file?page=$page#".
		(count ($xml->channel->xpath ('item')) +1)
	;
	
	//add the comment to the thread
	$item = $xml->channel->prependChild ("item");
	$item->addChild ("title",	htmlspecialchars ("RE: ".$xml->channel->title, ENT_NOQUOTES, 'UTF-8'));
	$item->addChild ("link",	"http://".$_SERVER['HTTP_HOST']."/$url");
	$item->addChild ("author",	htmlspecialchars ($NAME, ENT_NOQUOTES, 'UTF-8'));
	$item->addChild ("pubDate",	gmdate ('r'));
	$item->addChild ("description",	htmlspecialchars (formatText ($TEXT), ENT_NOQUOTES, 'UTF-8'));
	
	//save
	file_put_contents ("$file.xml", $xml->asXML (), LOCK_EX);
	
	header ("Location: http://".$_SERVER['HTTP_HOST']."/$url", true, 303);
}

/* ====================================================================================================================== */

echo template_tags (TEMPLATE_HEADER, array (
	'URL'		=> "$file.xml",
	'TITLE'		=> htmlspecialchars ($xml->channel->title, ENT_NOQUOTES, 'UTF-8').
			   ($page > 1 ? " · Page $page" : ""),
	'RSS_URL'	=> "$file.xml",
	'RSS_TITLE'	=> "Replies",
	'MENU'		=> template_tag (TEMPLATE_THREAD_MENU, 'RSS', "$file.xml"),
	'PATH'		=> $path ? template_tags (TEMPLATE_THREAD_PATH_FOLDER, array (
				'URL' => rawurlencode ($path), 'PATH' => htmlspecialchars ($path, ENT_NOQUOTES, 'UTF-8')
			)) : TEMPLATE_THREAD_PATH
));

/* ====================================================================================================================== */

$thread = $xml->channel->xpath ('item');

$post = array_pop ($thread);
echo template_tags (TEMPLATE_THREAD_FIRST, array (
	'TITLE'		=> $xml->channel->title,
	'AUTHOR'	=> $post->author,
	'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
	'PUBDATE'	=> strtoupper (date ('d-M\'y H:i', strtotime ($post->pubDate))),
	'DESCRIPTION'	=> $post->description
));

//any replies?
if (count ($thread)) {
	//sort the other way around
	//<http://stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	$sort_proxy = array ();
	foreach ($thread as $node) $sort_proxy[] = strtotime ($node->pubDate);
	array_multisort ($sort_proxy, SORT_ASC, $thread);
	
	//paging
	$pages = ceil (count ($thread) / APP_POSTS);
	$thread = array_slice ($thread, ($page-1) * APP_POSTS, APP_POSTS);
	
	$c=2 + (($page-1) * APP_POSTS);
	foreach ($thread as &$post) {
		@$html .= template_tags (TEMPLATE_THREAD_POST, array (
			'ID'		=> $c,
			'AUTHOR'	=> $post->author,
			'DATETIME'	=> gmdate ('r', strtotime ($post->pubDate)),
			'PUBDATE'	=> strtoupper (date ('d-M\'y H:i', strtotime ($post->pubDate))),
			'DESCRIPTION'	=> $post->description
		));
		$c++;
	}
	
	echo template_tags (TEMPLATE_THREAD_POSTS, array (
		'POSTS' => $html,
		'PAGES' => pageLinks ($page, $pages)
	));
}

//the reply form
echo APP_ENABLED ? template_tags (TEMPLATE_THREAD_FORM, array (
	'NAME'	=> htmlspecialchars ($NAME,  ENT_COMPAT, 'UTF-8'),
	'PASS'	=> htmlspecialchars ($PASS,  ENT_COMPAT, 'UTF-8'),
	'TEXT'	=> htmlspecialchars ($TEXT,  ENT_COMPAT, 'UTF-8'),
	'ERROR'	=> !$SUBMIT
		   ? "There is no need to \"register\", just enter the name + password you want."
		   : "<span class=\"red\">".
		     (!$NAME  ? "Enter a name. You’ll need to use this with the password each time."
		   : (!$PASS  ? "Enter a password. It’s so you can re-use your name each time."
		   : (!$TEXT  ? "Well, write a message!"
		   : "That name is taken. Provide the password for it, or choose another name. (password typo?)"
		   )))."</span>"
)) : TEMPLATE_THREAD_FORM_DISABLED;

//bon voyage, HTML!
echo TEMPLATE_FOOTER;

?>