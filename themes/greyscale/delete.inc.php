<!DOCTYPE html>
<meta charset="utf-8" />
<!-- NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
     licensed under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
     you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com> -->
<title><?=safeHTML(FORUM_NAME)?> :: <?=$HEADER['THREAD']?> ! <?=(ID==1) ? "Delete Thread" : "Delete Post"?></title>
<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/ie.css" />
<![endif]-->
<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/theme.css" />
<meta name="viewport" content="width=device-width" />
<meta name="robots" content="noindex, nofollow" />
<!-- details on using mobile favicons with thanks to <mathiasbynens.be/notes/touch-icons> -->
<link rel="shortcut icon" type="image/x-icon" href="/themes/<?=FORUM_THEME?>/favicon.ico" />
<link rel="apple-touch-icon-precomposed" href="/themes/<?=FORUM_THEME?>/touch.png" />
<!-- Microsoft’s insane IE9 pinned site syntax: <msdn.microsoft.com/library/gg131029> -->
<meta name="application-name" content="<?=safeString (FORUM_NAME)?>" />
<meta name="msapplication-starturl" content="<?=FORUM_URL?>" />
<meta name="msapplication-window" content="width=1024;height=600" />
<meta name="msapplication-navbutton-color" content="#222" />
<link rel="shortcut icon" type="image/x-icon" href="/themes/<?=FORUM_THEME?>/favicon.ico" />

<body>
<!-- =================================================================================================================== -->
<!-- original 'Grayscale' theme by Jon Gjengset <thesquareplanet.com>,
     greyscale theme by Kroc Camen, please modify to suit your needs -->
<header id="mast">
	<h1><a href="/"><?=safeHTML(FORUM_NAME)?></a></h1>
	<form id="search" method="get" action="http://google.com/search"><!--
		--><input type="hidden" name="as_sitesearch" value="<?=safeString($_SERVER['HTTP_HOST'])?>" /><!--
		--><input id="query" type="search" name="as_q" placeholder="Google Search…" /><!--
		--><input id="go" type="image" src="/themes/<?=FORUM_THEME?>/icons/go.png" value="Search" width="16" height="16" /><!--
	--></form>
</header>
<!-- =================================================================================================================== -->
<section id="delete">
	<h1>Delete <?=(ID==1) ? "Thread &amp; Replies" : "Post"?>?</h1>
	<form id="delete" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
		<p>
			<label for="user">Name:</label>
			<input name="username" id="user" type="text" size="28" maxlength="<?=SIZE_NAME?>" required autocomplete="on"
			       placeholder="Your name" value="<?=$FORM['NAME']?>" />
		</p><p>
			<label for="pass">Password:</label>
			<input name="password" id="pass" type="password" size="28" maxlength="<?=SIZE_PASS?>" required autocomplete="on"
			       placeholder="A password to keep your name" value="<?=$FORM['PASS']?>" />
		</p><p id="pemail">
			<label class="email">Email:</label>
			<input name="email" type="text" value="example@abc.com" required autocomplete="off" />
			(Leave this as-is, it’s a trap!)
		</p>
<?php switch ($FORM['ERROR']):
	case ERROR_NONE:
		if (ID==1): ?>
		<p id="ok">
			To delete this thread, and all replies to it, you must be either the original author
			or a designated moderator.
		</p>
<?php		else: ?>
		<p id="ok">To delete this post you must be either the original author or a designated moderator.</p>
<?php		endif;
	break;
	case ERROR_NAME: ?>
		<p id="error">Enter a name. You’ll need to use this with the password each time.</p>
<?php break;
	case ERROR_PASS: ?>
		<p id="error">Enter a password. It’s so you can re-use your name each time.</p>
<?php break;
	case ERROR_AUTH: ?>
		<p id="error">
			Name / password mismatch! You must enter the name and password of either the original author,
			or a designated moderator.
		</p>
<?php endswitch; ?>
		
		<p id="psubmit"><label for="submit">Delete
			<input id="submit" name="submit" type="image" src="/themes/<?=FORUM_THEME?>/icons/submit.png"
			       width="40" height="40" value="&gt;" />
		</label></p>
	</form>
</section>
<!-- =================================================================================================================== -->
<section id="post">
	<h1>Post</h1>
	<article id="<?=ID?>">
		<header>
			<time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
			<a href="#<?=ID?>">#<?=ID?>.</a> <b><?=$POST['AUTHOR']?></b>
		</header>
		
		<?=$POST['TEXT']?>
	</article>
</section>
<!-- =================================================================================================================== -->
<footer><p>
	Powered by <a href="https://github.com/Kroc/NoNonsenseForum">NoNonsenseForum</a><br />
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
</p></footer>
<!-- page generated in: <?=round (microtime(true) - START, 3)?>s -->
</body>