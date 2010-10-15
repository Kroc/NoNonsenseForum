<?php

include "shared.php";

/* ====================================================================================================================== */

$page = preg_match ('/^[0-9]+$/', @$_GET['page']) ? (int) $_GET['page'] : 1;

$NAME	= mb_substr (stripslashes (@$_POST['username']), 0, 18,    'UTF-8');
$PASS	= mb_substr (stripslashes (@$_POST['password']), 0, 20,    'UTF-8');
$TITLE	= mb_substr (stripslashes (@$_POST['title']),    0, 80,    'UTF-8');
$TEXT	= mb_substr (stripslashes (@$_POST['text']),     0, 32768, 'UTF-8');

if ($SUBMIT = @$_POST['submit']) if (
	APP_ENABLED && @$_POST['email'] == "example@abc.com" && $NAME && $PASS && $TITLE && $TEXT
) {
	$user = "users/".md5 ("C64:$NAME").".txt";
	//create the user, if new
	if (!file_exists ($user)) file_put_contents ($user, md5 ("C64:$PASS"));
	//does password match?
	if (file_get_contents ($user) == md5 ("C64:$PASS")) {
		//generate the file name for the RSS thread
		$url = flattenTitle ($TITLE);
		//if this file already exists (double-submission from back button?), redirect to it
		if (file_exists ("$url.xml")) header ("Location: http://".$_SERVER['HTTP_HOST']."/$url", 303);
		
		file_put_contents ("$url.xml", template_tags (<<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="http://&__APP_HOST__;/&__URL__;.xml" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>http://&__APP_HOST__;/&__URL__;</link>
<item>
<title>&__TITLE__;</title>
<link>http://&__APP_HOST__;/&__URL__;#1</link>
<author>&__NAME__;</author>
<pubDate>&__DATE__;</pubDate>
<description>&__TEXT__;</description>
</item>
</channel>
</rss>
XML
		, array (
			'APP_HOST'	=> APP_HOST,
			'TITLE'		=> htmlspecialchars ($TITLE, ENT_NOQUOTES, 'UTF-8'),
			'URL'		=> $url,
			'NAME'		=> htmlspecialchars ($NAME, ENT_NOQUOTES, 'UTF-8'),
			'DATE'		=> gmdate ('r'),
			'TEXT'		=> htmlspecialchars (formatText ($TEXT), ENT_NOQUOTES, 'UTF-8'),
		)));
		
		//create rss thread for this folder
		createRSSIndex ();
		
		//redirect to newley created thread
		header ("Location: http://".$_SERVER['HTTP_HOST']."/$url", 303);
	}
}

?>
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
		<a href="#new">Add Thread</a>
		<a href="index.rss">RSS</a>
	</nav>
</header>

<h2 id="list">Threads</h2>
<?

//get list of threads
$threads = array_fill_keys (preg_grep ('/\.xml$/', scandir ('.')) , 0);
foreach ($threads as $file => &$date) $date = filemtime ($file);
arsort ($threads, SORT_NUMERIC);

//paging
$pages = ceil (count ($threads) / APP_THREADS);
$threads = array_slice ($threads, ($page-1) * APP_THREADS, APP_THREADS);

?>
<dl>
<?php
foreach ($threads as $file => $date) {
	
	$xml = simplexml_load_file ($file);
	$items = $xml->channel->xpath ('item');
	$last = reset ($items);
	
	echo template_tags (<<<HTML
	<dt><a href="/&__URL__;?page=&__PAGE__;">&__TITLE__;</a> (&__COUNT__;)</dt>
	<dd>
		<span class="ltgrey">+</span><time datetime="&__DATE__;">&__TIME__;</time> <span class="ltgrey">&#x0ee1f;</span>&__NAME__;
	</dd>

HTML
	, array (
		'URL'	=> flattenTitle ($xml->channel->title),
		'PAGE'	=> ceil ((count ($items) -1) / APP_POSTS),
		'TITLE' => $xml->channel->title,
		'COUNT' => count ($items),
		'TIME'  => strtoupper (date ('d-M\'y h:i', strtotime ($last->pubDate))),
		'DATE'	=> date ('c', strtotime ($last->pubDate)),
		'NAME'  => $last->author
	));
}

?>
</dl>
<?php

echo template_tag (<<<HTML
<nav class="pages">
Page &__PAGES__;
</nav>
HTML
, 'PAGES', pageLinks ($page, $pages));

?>

<form id="new" method="post" action="#new" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on"><fieldset>
	<legend>New Thread</legend>
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
		--><input id="email" name="email" type="text" value="example@abc.com" required autocomplete="off" /><!--
		-->(Leave this as-is, it’s a trap!)<!--
	--></p><p>
		&__ERROR__;
	   </p><p><!--
		--><label for="title">Title:</label><!--
		--><input id="title" name="title" type="text" size="28" maxlength="80" required autocomplete="off"
		    value="&__TITLE__;" /><!--
	--></p><p><!--
		--><label for="text">Message:</label><br /><!--
		--><textarea id="text" name="text" cols="40" rows="23" maxlength="32768" required autocomplete="off">&__TEXT__;</textarea><!--
	--></p><p id="rules">
		<input id="submit" name="submit" type="submit" value="Submit" />
		
		There’s only 1 rule: don’t be an arse. Rule #2 is Kroc makes up the rules.
	</p>

HTML
	, array (
		'NAME'	=> htmlspecialchars ($NAME,  ENT_COMPAT, 'UTF-8'),
		'PASS'	=> htmlspecialchars ($PASS,  ENT_COMPAT, 'UTF-8'),
		'TITLE'	=> htmlspecialchars ($TITLE, ENT_COMPAT, 'UTF-8'),
		'TEXT'	=> htmlspecialchars ($TEXT,  ENT_COMPAT, 'UTF-8'),
		'ERROR'	=> !$SUBMIT
			   ? "There is no need to \"register\", just enter the name + password you want."
			   : "<span class=\"red\">".
			     (!$NAME  ? "Enter a name. You’ll need to use this with the password each time."
			   : (!$PASS  ? "Enter a password. It’s so you can re-use your name each time."
			   : (!$TITLE ? "You need to enter the title of your new discussion thread"
			   : (!$TEXT  ? "Well, write a message!"
			   : "That name is taken. Provide the password for it, or choose another name. (password typo?)"
			   ))))."</span>"
	));
}

?></fieldset></form>

<footer>
	<a href="mailto:kroccamen@gmail.com">kroccamen@gmail.com</a> • <a href="http://camendesign.com">camendesign.com</a>
</footer>