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
) {
	$user = APP_ROOT."users/".md5 ("C64:$NAME").".txt";
	//create the user, if new
	if (!file_exists ($user)) file_put_contents ($user, md5 ("C64:$PASS"));
	//does password match?
	if (file_get_contents ($user) == md5 ("C64:$PASS")) {
		//where to?
		$page = ceil ((count ($xml->channel->xpath ('item'))) / APP_POSTS);
		$url = ($path ? rawurlencode ($path)."/" : "")."$file?page=$page#".
			(count ($xml->channel->xpath ('item')) +1)
		;
		
		//add the comment to the thread
		$item = $xml->channel->prependChild ("item");
		$item->addChild ("title", htmlspecialchars ("RE: ".$xml->channel->title, ENT_NOQUOTES, 'UTF-8'));
		$item->addChild ("link", "http://".APP_HOST."/$url");
		$item->addChild ("author", htmlspecialchars ($NAME, ENT_NOQUOTES, 'UTF-8'));
		$item->addChild ("pubDate", gmdate ('r'));
		$item->addChild ("description", htmlspecialchars (formatText ($TEXT), ENT_NOQUOTES, 'UTF-8'));
		
		//save
		file_put_contents ("$file.xml", $xml->asXML (), LOCK_EX);
		
		//todo: rebuild folder RSS feed
		
		header ("Location: http://".$_SERVER['HTTP_HOST']."/$url", 303);
	}
}

echo template_tags (<<<HTML
<meta charset="utf-8" />
<title>camen design forums</title>
<link rel="stylesheet" href="/theme/c64.css" />
<header>
	<hgroup>
		<h1>**** camen design forums v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2010 Kroc Camen</h2>
	</hgroup>
	<p>
		READY.
	</p>
	<nav>
		<a href="#reply">Reply</a>
		<a href="/&__URL__;">RSS</a>

&__PATH__;
	</nav>
</header>

<h1>&__TITLE__;</h1>
HTML
, array (
	'URL'		=> "$file.xml",
	'TITLE'		=> $xml->channel->title,
	'HOST'		=> APP_HOST,
	'PATH'		=> $path ? <<<HTML
		<ol>
			<li>
				<a href="/">Forum Index</a>
				<ol><li><a href="/$path/">$path</a></li></ol>
			</li>
		</ol>
HTML
			: <<<HTML
		<ol>
			<li><a href="/">Forum Index</a></li>
		</ol>
HTML
));

$thread = $xml->channel->xpath ('item');

$post = array_pop ($thread);
echo template_tags (<<<HTML
<article id="1">
<header><a href="#1">#1.</a> <dl>
	<dt>At</dt>	<dd><time pubdate>&__PUBDATE__;</time></dd>
	<dt>by</dt>	<dd>&__AUTHOR__;</dd>
</dl></header>
<p>
&__DESCRIPTION__;
</p>
</article>

HTML
, array (
	'AUTHOR'	=> $post->author,
	'PUBDATE'	=> strtoupper (date ('d-M\'y h:i', strtotime ($post->pubDate))),
	'DESCRIPTION'	=> $post->description
));

//any replies?
if (count ($thread)) {
	//sort the other way around
	//<http://stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
	$sort_proxy = array();
	foreach ($thread as $node) $sort_proxy[] = (string) $node->pubDate;
	array_multisort ($sort_proxy, SORT_ASC, $thread);
	
	//paging
	$pages = ceil (count ($thread) / APP_POSTS);
	$thread = array_slice ($thread, ($page-1) * APP_POSTS, APP_POSTS);
	
	echo template_tag (<<<HTML
<h2 id="list">Replies</h2>
<nav class="pages">
	Page &__PAGES__;
</nav>
HTML
	, 'PAGES', pageLinks ($page, $pages));

	$c=2 + (($page-1) * APP_POSTS);
	foreach ($thread as &$post) {
		echo template_tags (<<<HTML
<article id="&__ID__;">
<header><a href="#&__ID__;">#&__ID__;.</a> <dl>
	<dt>At</dt>	<dd><time pubdate>&__PUBDATE__;</time></dd>
	<dt>by</dt>	<dd>&__AUTHOR__;</dd>
</dl></header>
<p>
&__DESCRIPTION__;
</p>
</article>

HTML
		, array (
			'ID'		=> $c,
			'AUTHOR'	=> $post->author,
			'PUBDATE'	=> strtoupper (date ('d-M\'y h:i', strtotime ($post->pubDate))),
			'DESCRIPTION'	=> $post->description
		));
		$c++;
	}
	
	echo template_tag (<<<HTML
<nav class="pages">
	Page &__PAGES__;
</nav>
HTML
	, 'PAGES', pageLinks ($page, $pages));
}
?>
<form id="reply" method="post" action="#reply" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on"><fieldset>
	<legend>Reply</legend>
<?php
if (!APP_ENABLED) {
	echo <<<HTML
	<p class="red">Sorry, posting is currently disabled.</p>
HTML;
} else {
	echo template_tags (<<<HTML
	<p><!--
		--><label for="name">Name:</label><!--
		--><input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		          value="&__NAME__;" /><!--
	--></p><p><!--
		--><label for="password">Password:</label><!--
		--><input id="password" name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		          value="&__PASS__;"/><!--
	--></p><p><!--
		--><label for="email">Email:</label><!--
		--><input id="email" name="email" type="text" value="example@abc.com" required automcomplete="on" /><!--
		-->Leave this as-is, it’s a trap!<!--
	--></p><p>
		&__ERROR__;
	   </p><p><!--
		--><label for="text">Message:</label><br /><!--
		--><textarea id="text" name="text" cols="40" rows="23" maxlength="32768" required autocomplete="off">&__TEXT__;</textarea><!--
	--></p><p id="rules">
		<input id="submit" name="submit" type="submit" value="Reply" />

		There’s only 1 rule: don’t be an arse. Rule #2 is Kroc makes up the rules.
	</p>

HTML
	, array (
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
	));
}

?></fieldset></form>

<footer><p>
	&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;
</p><p>
	<a href="mailto:kroccamen@gmail.com">kroccamen@gmail.com</a> • <a href="http://camendesign.com">camendesign.com</a>
</p></footer>