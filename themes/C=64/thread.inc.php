<?php
if (isset ($PAGES)) {
	foreach ($PAGES as &$PAGE) if ($PAGE == PAGE) {
		$PAGE = "<em>$PAGE</em>";
	} elseif ($PAGE) {
		$PAGE = "<a href=\"?page=$PAGE#replies\">$PAGE</a>";
	} else {
		$PAGE = "…";
	}
	$PAGES = (implode (", ", $PAGES));
}
?><!DOCTYPE html>
<meta charset="utf-8" />
<title><?=FORUM_NAME?><?=($HEADER['THREAD'] ? ' * '.$HEADER['THREAD'] : '').($HEADER['PAGE']>1 ? ' * page '.$HEADER['PAGE'] : '')?></title>
<link rel="stylesheet" href="/themes/C=64/theme.css" />
<link rel="alternate" type="application/rss+xml" href="<?=$HEADER['RSS']?>" />
<meta name="viewport" content="width=device-width" />
<!-- =================================================================================================================== -->
<header>
	<hgroup>
		<h1>**** <?=FORUM_NAME?> v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2011 Kroc Camen</h2>
	</hgroup>
	<p>READY.</p>
	
	<nav>
		<a href="#reply">Reply</a>
		<a href="<?=$HEADER['RSS']?>">RSS</a>
<?php if ($HEADER['PATH']): ?>
		<ol>
			<li>
				<a href="/">Index</a>
				<ol><li><a href="<?=$HEADER['PATH_URL']?>"><?=$HEADER['PATH']?></a></li></ol>
			</li>
		</ol>
<?php else: ?>
		<ol>
			<li><a href="/">Index</a></li>
		</ol>
<?php endif; ?>
	</nav>
</header>
<!-- =================================================================================================================== -->
<h1><?=$POST['TITLE']?></h1>

<article id="1" class="op">
	<header>
		<a class="delete" rel="noindex nofollow" href="<?=$POST['DELETE_URL']?>">Delete</a>
		<time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
		<a href="#1">#1.</a> <b><?=$POST['AUTHOR']?></b>
	</header>
	
	<?=$POST['TEXT']?>

</article>

<?php if (isset ($POSTS)): ?>
<h2 id="replies">Replies</h2>
<nav class="pages">
	Page <?=$PAGES?>
</nav>

<?php foreach ($POSTS as $POST): ?>
<article id="<?=$POST['ID']?>" class="<?=($POST['DELETED'] ? 'deleted' : ($POST['OP'] ? 'op' : ''))?>">
	<header>
<?php if (!$POST['DELETED']): ?>
		<a class="delete" rel="noindex nofollow" href="<?=$POST['DELETE_URL']?>">Delete</a>
<?php endif;?>
		<time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
		<a href="#<?=$POST['ID']?>">#<?=$POST['ID']?>.</a> <b><?=$POST['AUTHOR']?></b>
	</header>
	
	<?=$POST['TEXT']?>

</article>

<?php endforeach; ?>

<nav class="pages">
	Page <?=$PAGES?>
</nav>
<?php endif; ?>

<!-- =================================================================================================================== -->
<?php if (FORUM_ENABLED): ?>
<form id="reply" method="post" action="#reply" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<fieldset><legend>Reply</legend>
	
	<label>Name:
		<input name="username" type="text" size="28" maxlength="18" required autocomplete="on"
	          value="<?=$FORM['NAME']?>" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
	          value="<?=$FORM['PASS']?>" />
	</label>
	<label class="email">Email:
		<input name="email" type="text" value="example@abc.com" required autocomplete="off" />
		(Leave this as-is, it’s a trap!)
	</label>
<?php switch ($FORM['ERROR']):
	case ERROR_NONE:
?>	<p>
		There is no need to "register", just enter the name + password you want.
	</p>
<?php	break;
	case ERROR_NAME:
?>	<p class="error">
		Enter a name. You’ll need to use this with the password each time.
	</p>
<?php	break;
	case ERROR_PASS:
?>	<p class="error">
		Enter a password. It’s so you can re-use your name each time.
</p>
<?php	break;
	case ERROR_TEXT:
?>	<p class="error">
		Well, write a message!
	</p>
<?php	break;
	case ERROR_AUTH:
?>	<p class="error">
		That name is taken. Provide the password for it, or choose another name. (password typo?)
	</p>
<?php endswitch; ?>
	<label>Message:
		<textarea name="text" cols="40" rows="23" maxlength="32768" required
		><?=$FORM['TEXT']?></textarea>
	</label>
	
	<p id="rules">
		<input name="submit" type="submit" value="Reply" />
		There’s only 1 rule: don’t be an arse. Rule #2 is Kroc makes up the rules.
	</p>
</fieldset></form>
<?php else: ?>
<h1>Reply</h1>
<p class="error">
	Sorry, posting is currently disabled.
</p>
<?php endif; ?>

<!-- =================================================================================================================== -->
<footer><p>
	<a href="mailto:kroccamen@gmail.com">kroccamen@gmail.com</a> • <a href="http://camendesign.com">camendesign.com</a>
</p><p>
	NoNonsense Forum: <a href="https://github.com/Kroc/NoNonsense Forum">Get the source on GitHub</a>
</p></footer>