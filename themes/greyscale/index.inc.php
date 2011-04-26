<?php
if (isset ($PAGES)) {
	foreach ($PAGES as &$PAGE) if ($PAGE == PAGE) {
		$PAGE = "<li><em>$PAGE</em></li>";
	} elseif ($PAGE) {
		$PAGE = "<li><a href=\"?page=$PAGE#threads\">$PAGE</a></li>";
	} else {
		$PAGE = '<li>…</li>';
	}
	$PAGES = (implode ('', $PAGES));
}
?><!DOCTYPE html>
<meta charset="utf-8" />
<!-- NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
     licensed under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
     you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com> -->
<title><?=safeHTML(FORUM_NAME)?><?=($HEADER['PATH'] ? ' :: '.$HEADER['PATH'] : '').($HEADER['PAGE']>1 ? ' # '.$HEADER['PAGE'] : '')?></title>
<!-- get rid of IE site compatibility button -->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/ie.css" />
<![endif]-->
<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/theme.css" />
<link rel="alternate" type="application/rss+xml" href="index.xml" />
<meta name="viewport" content="width=device-width, maximum-scale=1, user-scalable=no" />
<!-- details on using mobile favicons with thanks to <mathiasbynens.be/notes/touch-icons> -->
<link rel="shortcut icon" type="image/x-icon" href="/themes/<?=FORUM_THEME?>/favicon.ico" />
<link rel="apple-touch-icon-precomposed" href="/themes/<?=FORUM_THEME?>/touch.png" />
<!-- Microsoft’s insane IE9 pinned site syntax: <msdn.microsoft.com/library/gg131029> -->
<meta name="application-name" content="<?=safeString (FORUM_NAME)?>" />
<meta name="msapplication-starturl" content="<?=FORUM_URL?>" />
<meta name="msapplication-window" content="width=1024;height=600" />
<meta name="msapplication-navbutton-color" content="#222" />

<body>
<!-- =================================================================================================================== -->
<!-- original 'Grayscale' theme by Jon Gjengset <thesquareplanet.com>,
     greyscale theme by Kroc Camen, please modify to suit your needs -->
<header id="mast">
	<h1><a href="/"><?=safeHTML(FORUM_NAME)?></a></h1>
	
	<form id="search" method="get" action="http://google.com/search"><!--
		--><input type="hidden" name="as_sitesearch" value="<?=safeString($_SERVER['HTTP_HOST'])?>" /><!--
		--><input id="query" type="search" name="as_q" placeholder="Google Search…" /><!--
		--><input id="go" type="image" src="/themes/<?=FORUM_THEME?>/icons/go.png" value="Search" width="20" height="20" /><!--
	--></form>
	
	<nav><p>
		<a id="add" href="#new">Add Thread</a>
		<a id="rss" href="index.xml">RSS</a>
	</p><p>
		<a id="index" href="/">Index</a><?php if ($HEADER['PATH']): ?> » <?=$HEADER['PATH']?><?php endif; ?>
	</p></nav>
</header>
<!-- =================================================================================================================== -->
<?php if (isset ($FOLDERS)): ?>
<section id="folders">
	<h1>Sub-Forums</h1>
	<ol class="ui">
<?php foreach ($FOLDERS as $FOLDER): ?>
		<li>
			<?php if ($FOLDER['AUTHOR']): ?><time datetime="<?=$FOLDER['DATETIME']?>"><?=$FOLDER['TIME']?></time>
			<b><?=$FOLDER['AUTHOR']?></b> <?php endif;
?><a href="<?=$FOLDER['URL']?>"><?=$FOLDER['NAME']?></a>
		</li>
<?php endforeach; ?>
	</ol>
</section>
<?php endif; ?>

<?php if (isset ($THREADS) || isset ($STICKIES)): ?>
<section id="threads">
	<h1>Threads</h1>
	<nav><ol class="pages"><?=$PAGES?></ol></nav>
	<ol class="ui">
<?php if (isset ($STICKIES)): ?>
<?php foreach ($STICKIES as $THREAD): ?>
		<li class="sticky">
			<time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
			<i><?=$THREAD['COUNT']?></i> <b><?=$THREAD['AUTHOR']?></b>
			<img src="/themes/<?=FORUM_THEME?>/icons/sticky.png" width="16" height="16" alt="Announcement:" />
			<a href="<?=$THREAD['URL']?>" class="sticky"><?=$THREAD['TITLE']?></a>
		</li>
<?php endforeach; ?>
<?php endif; ?>

<?php foreach ($THREADS as $THREAD): ?>
		<li>
			<time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
			<i><?=$THREAD['COUNT']?></i> <b><?=$THREAD['AUTHOR']?></b>
			<a href="<?=$THREAD['URL']?>"><?=$THREAD['TITLE']?></a>
		</li>
<?php endforeach; ?>
	</ol>
	<nav><ol class="pages"><?=$PAGES?></ol></nav>
</section>
<?php endif; ?>
<!-- =================================================================================================================== -->
<section id="new">
	<h1>Add Thread</h1>
	<form method="post" action="#new" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<?php if (FORUM_ENABLED): ?>
		<p id="ptitle">
			<label for="title">Title:</label>
			<input name="title" id="title" type="text" size="28" maxlength="<?=SIZE_TITLE?>" required autocomplete="off"
			       placeholder="Type thread title here…" value="<?=$FORM['TITLE']?>" />
		</p>
		
		<div id="rightcol">
		
		<p id="puser">
			<label for="user">Name:</label>
			<input name="username" id="user" type="text" size="28" maxlength="<?=SIZE_NAME?>" required autocomplete="on"
			       placeholder="Your name" value="<?=$FORM['NAME']?>" />
		</p><p id="ppass">
			<label for="pass">Password:</label>
			<input name="password" id="pass" type="password" size="28" maxlength="<?=SIZE_PASS?>" required autocomplete="on"
			       placeholder="A password to keep your name" value="<?=$FORM['PASS']?>" />
		</p><p id="pemail">
			<label class="email">Email:</label>
			<input name="email" type="text" value="example@abc.com" required autocomplete="off" />
			(Leave this as-is, it’s a trap!)
		</p>
<?php switch ($FORM['ERROR']):
	case ERROR_NONE: ?>
		<p id="ok">There is no need to “register”, just enter the same name + password of your choice every time.</p>
<?php break;
	case ERROR_NAME: ?>
		<p id="error">Enter a name. You’ll need to use this with the password each time.</p>
<?php break;
	case ERROR_PASS: ?>
		<p id="error">Enter a password. It’s so you can re-use your name each time.</p>
<?php break;
	case ERROR_TITLE: ?>
		<p id="error">You need to enter the title of your new discussion thread</p>
<?php break;
	case ERROR_TEXT: ?>
		<p id="error">Well, write a message!</p>
<?php break;
	case ERROR_AUTH: ?>
		<p id="error">That name is taken. Provide the password for it, or choose another name. (password typo?)</p>
<?php endswitch; ?>
		
		</div><div id="leftcol">
		
		<p id="ptext">
			<label for="text">Message:</label>
			<div id="wtext">
				<textarea name="text" id="text" cols="40" rows="14" maxlength="<?=SIZE_TEXT?>" required
					  placeholder="Type your message here…"><?=$FORM['TEXT']?></textarea>
			</div>
		</p>
		
		</div>
		
		<p id="psubmit"><label for="submit">Submit
			<input id="submit" name="submit" type="image" src="/themes/<?=FORUM_THEME?>/icons/submit.png"
			       width="40" height="40" value="&gt;" />
		</label></p>
<?php else: ?>
		<p id="error">Sorry, posting is currently disabled.</p>
<?php endif; ?>
	</form>
</section>
<!-- =================================================================================================================== -->
<footer><p>
	Powered by <a href="https://github.com/Kroc/NoNonsenseForum">NoNonsense Forum</a><br />
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
</p></footer>
<div id="grid"></div>
<!-- page generated in: <?=round (microtime(true) - START, 3)?>s -->
</body>