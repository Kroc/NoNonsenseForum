<?php
//conactenate page links
if (isset ($PAGES)) {
	foreach ($PAGES as &$PAGE) if ($PAGE == PAGE) {
		$PAGE = "<em>$PAGE</em>";
	} elseif ($PAGE) {
		$PAGE = "<a href=\"?page=$PAGE#threads\">$PAGE</a>";
	} else {
		$PAGE = '…';
	}
	$PAGES = (implode (', ', $PAGES));
}
?><!DOCTYPE html>
<meta charset="utf-8" />
<!-- NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
     licensed under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
     you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com> -->
<title><?=FORUM_NAME?><?=($HEADER['PATH'] ? ' :: '.$HEADER['PATH'] : '').($HEADER['PAGE']>1 ? ' # '.$HEADER['PAGE'] : '')?></title>
<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/theme.css" />
<link rel="alternate" type="application/rss+xml" href="index.rss" />
<meta name="viewport" content="width=device-width" />
<!-- =================================================================================================================== -->
<!-- original 'Grayscale' theme by Jon Gjengset <thesquareplanet.com>,
     greyscale theme by Kroc Camen, please modify to suit your needs -->
<header>
	<h1><a href="/"><?=FORUM_NAME?></a></h1>
	
	<nav><p>
		<a id="add" href="#new">Add Thread</a>
		<a id="rss" href="index.rss">RSS</a>
	</p><p>
		<a href="/">Index</a><?php if ($HEADER['PATH']): ?> » <?=$HEADER['PATH']?><?php endif; ?>
	</p></nav>
	
	<form id="search" method="get" action="http://google.com/search"><!--
		--><input type="hidden" name="as_sitesearch" value="<?=$_SERVER['HTTP_HOST']?>" /><!--
		--><input id="query"  type="search" name="as_q" placeholder="Google Search…" /><!--
		--><input id="go" type="image" src="/themes/<?=FORUM_THEME?>/icons/go.png" width="16" height="16" /><!--
	--></form>
</header>
<!-- =================================================================================================================== -->
<?php if (isset ($FOLDERS)): ?>
<section id="folders">
	<h1>Sub-Forums</h1>
	<dl>
<?php foreach ($FOLDERS as $FOLDER): ?>
		<dt><a href="<?=$FOLDER['URL']?>"><?=$FOLDER['NAME']?></a></dt>
<?php endforeach; ?>
	</dl>
</section>
<?php endif; ?>

<?php if (isset ($THREADS) || isset ($STICKIES)): ?>
<section id="threads">
	<h1>Threads</h1>
<?php if (isset ($PAGES)): ?>
	<nav class="pages">Page <?=$PAGES?></nav>
<?php endif; ?>
	<dl>
<?php if (isset ($STICKIES)): ?>
<?php foreach ($STICKIES as $THREAD): ?>
		<dt class="sticky"><i><?=$THREAD['COUNT']?></i> <img src="/themes/<?=FORUM_THEME?>/icons/sticky.png" width="16" height="16" alt="Announcement:" /> <a href="<?=$THREAD['URL']?>" class="sticky"><?=$THREAD['TITLE']?></a></dt>
		<dd>
			<time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
			<b><?=$THREAD['AUTHOR']?></b>
		</dd>
<?php endforeach; ?>
<?php endif; ?>

<?php foreach ($THREADS as $THREAD): ?>
		<dt><i><?=$THREAD['COUNT']?></i> <a href="<?=$THREAD['URL']?>"><?=$THREAD['TITLE']?></a></dt>
		<dd>
			<time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
			<b><?=$THREAD['AUTHOR']?></b>
		</dd>
<?php endforeach; ?>
	</dl>
<?php if (isset ($PAGES)): ?>
	<nav class="pages">Page <?=$PAGES?></nav>
<?php endif; ?>
</section>
<?php endif; ?>
<!-- =================================================================================================================== -->
<form id="new" method="post" action="#new" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<fieldset><legend>Add Thread</legend>
<?php if (FORUM_ENABLED): ?>
	<p id="ptitle">
		<label for="title">Title:</label>
		<input tabindex="3" name="title" id="title" type="text" size="28" maxlength="80" required autocomplete="off"
		       placeholder="Type thread title here…" value="<?=$FORM['TITLE']?>" />
	</p><p id="message">
		<label for="text">Message:</label>
		<textarea tabindex="4" name="text" id="text" cols="40" rows="15" maxlength="32768" required
		          placeholder="Type your message here…"><?=$FORM['TEXT']?></textarea>
	</p><p>
		<label for="user">Name:</label>
		<input tabindex="1" name="username" id="user" type="text" size="28" maxlength="18" required autocomplete="on"
		       placeholder="Your name" value="<?=$FORM['NAME']?>" />
	</p><p>
		<label for="pass">Password:</label>
		<input tabindex="2" name="password" id="pass" type="password" size="28" maxlength="20" required autocomplete="on"
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
	
	<p id="psubmit">
		<label for="submit">Submit
			<input tabindex="5" id="submit" type="image" src="/themes/<?=FORUM_THEME?>/icons/submit.png" value="Submit" />
		</label>
	</p>
<?php else: ?>
	<p class="error">Sorry, posting is currently disabled.</p>
<?php endif; ?>
</fieldset></form>
<!-- =================================================================================================================== -->
<footer><p>
	Powered by <a href="https://github.com/Kroc/NoNonsense Forum">NoNonsense Forum</a><br />
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
</p></footer>