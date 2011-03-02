<!DOCTYPE html>
<meta charset="utf-8" />
<!-- NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
     licensed under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
     you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com> -->
<title><?=FORUM_NAME?> :: <?=$HEADER['THREAD']?> ! <?=(ID==1) ? "Delete Thread" : "Delete Post"?></title>
<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/ie.css" />
<![endif]-->
<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/theme.css" />
<meta name="viewport" content="width=device-width" />
<meta name="robots" content="noindex, nofollow" />

<body>
<!-- =================================================================================================================== -->
<!-- original 'Grayscale' theme by Jon Gjengset <thesquareplanet.com>,
     greyscale theme by Kroc Camen, please modify to suit your needs -->
<header id="mast">
	<h1><a href="/"><?=FORUM_NAME?></a></h1>
	<form id="search" method="get" action="http://google.com/search"><!--
		--><input type="hidden" name="as_sitesearch" value="<?=safeString($_SERVER['HTTP_HOST'])?>" /><!--
		--><input id="query" type="search" name="as_q" placeholder="Google Search…" /><!--
		--><input id="go" type="image" src="/themes/<?=FORUM_THEME?>/icons/go.png" width="16" height="16" /><!--
	--></form>
</header>
<!-- =================================================================================================================== -->
<form id="delete" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<fieldset><legend>Delete <?=(ID==1) ? "Thread &amp; Replies" : "Post"?>?</legend>
	<p>
		<label for="user">Name:</label>
		<input name="username" id="user" type="text" size="28" maxlength="18" required autocomplete="on"
		       placeholder="Your name" value="<?=$FORM['NAME']?>" />
	</p><p>
		<label for="pass">Password:</label>
		<input name="password" id="pass" type="password" size="28" maxlength="20" required autocomplete="on"
		       placeholder="A password to keep your name" value="<?=$FORM['PASS']?>" />
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
		<input id="submit" type="image" src="/themes/<?=FORUM_THEME?>/icons/submit.png" />
	</label></p>
</fieldset></form>
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
	Powered by <a href="https://github.com/Kroc/NoNonsense Forum">NoNonsense Forum</a><br />
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
</p></footer>

</body>