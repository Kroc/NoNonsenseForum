<?php
if (isset ($PAGES)) {
	foreach ($PAGES as &$PAGE) if ($PAGE == PAGE) {
		$PAGE = "<em>$PAGE</em>";
	} elseif ($PAGE) {
		$PAGE = "<a href=\"?page=$PAGE#replies\">$PAGE</a>";
	} else {
		$PAGE = '…';
	}
	$PAGES = (implode (', ', $PAGES));
}
?>
<!DOCTYPE html>
<meta charset="utf-8" />
<!-- NoNonsense Forum © Copyright (CC-BY) Kroc Camen 2011
     licensed under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
     you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com> -->
<title><?=FORUM_NAME?><?=($HEADER['THREAD'] ? ' :: '.$HEADER['THREAD'] : '').($HEADER['PAGE']>1 ? ' # '.$HEADER['PAGE'] : '')?></title>
<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/ie.css" />
<![endif]-->
<link rel="stylesheet" href="/themes/<?=FORUM_THEME?>/theme.css" />
<link rel="alternate" type="application/rss+xml" href="<?=$HEADER['RSS']?>" />
<meta name="viewport" content="width=device-width" />

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
	
	<nav><p>
		<a id="add" href="#reply">Reply</a>
		<a id="rss" href="<?=$HEADER['RSS']?>">RSS</a>
	</p><p>
		<a id="index" href="/">Index</a><?php if ($HEADER['PATH']): ?> » <a href="<?=$HEADER['PATH_URL']?>"><?=$HEADER['PATH']?></a><?php endif; ?>
	</p></nav>
</header>
<!-- =================================================================================================================== -->
<section id="post">
	<h1><?=$POST['TITLE']?></h1>
	
	<article id="1" class="op">
		<header>
			<a class="delete" rel="noindex nofollow" href="<?=$POST['DELETE_URL']?>">delete</a>
			<time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
			<a href="#1">#1.</a> <b><?=$POST['AUTHOR']?></b>
		</header>
		
		<?=$POST['TEXT']?>
	</article>
</section>

<?php if (isset ($POSTS)): ?>
<section id="replies">
	<h1>Replies</h1>
	<nav class="pages">Page <?=$PAGES?></nav>
	
<?php foreach ($POSTS as $POST): ?>
	<article id="<?=$POST['ID']?>" class="<?=($POST['DELETED'] ? 'deleted' : ($POST['OP'] ? 'op' : ''))?>">
		<header>
			<?php if (!$POST['DELETED']): ?><a class="delete" rel="noindex nofollow" href="<?=$POST['DELETE_URL']?>">delete</a><?php endif;?>
			<time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
			<a href="#<?=$POST['ID']?>">#<?=$POST['ID']?>.</a> <b><?=$POST['AUTHOR']?></b>
		</header>
		
		<?=$POST['TEXT']?>
	</article>
<?php endforeach; ?>
	
	<nav class="pages">Page <?=$PAGES?></nav>
</section>
<?php endif; ?>
<!-- =================================================================================================================== -->
<form id="reply" method="post" action="#reply" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<fieldset><legend>Reply</legend>
<?php if (FORUM_ENABLED): ?>
	<p id="message">
		<label for="text">Message:</label>
		<textarea name="text" id="text" cols="40" rows="15" maxlength="32768" required
		          placeholder="Type your message here…"><?=$FORM['TEXT']?></textarea>
	</p><p>
		<label for="user">Name:</label>
		<input name="username" id="user" type="text" size="28" maxlength="18" required autocomplete="on"
		       placeholder="Your name" value="<?=$FORM['NAME']?>" />
	</p><p>
		<label for="pass">Password:</label>
		<input name="password" id="pass" type="password" size="28" maxlength="20" required autocomplete="on"
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
	case ERROR_TEXT: ?>
	<p id="error">Well, write a message!</p>
<?php break;
	case ERROR_AUTH: ?>
	<p id="error">That name is taken. Provide the password for it, or choose another name. (password typo?)</p>
<?php endswitch; ?>
	
	<p id="psubmit"><label for="submit">Submit
		<input id="submit" type="image" src="/themes/<?=FORUM_THEME?>/icons/submit.png" />
	</label></p>
<?php else: ?>
	<p id="error">Sorry, posting is currently disabled.</p>
<?php endif; ?>
</fieldset></form>
<!-- =================================================================================================================== -->
<footer><p>
	Powered by <a href="https://github.com/Kroc/NoNonsense Forum">NoNonsense Forum</a><br />
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
</p></footer>

</body>