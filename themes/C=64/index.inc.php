<?php
if (isset ($PAGES)) {
	foreach ($PAGES as &$PAGE) if ($PAGE == PAGE) {
		$PAGE = "<em>$PAGE</em>";
	} elseif ($PAGE) {
		$PAGE = "<a href=\"?page=$PAGE#list\">$PAGE</a>";
	} else {
		$PAGE = '…';
	}
	$PAGES = (implode (',', $PAGES));
}
?><!DOCTYPE html>
<meta charset="utf-8" />
<title><?=FORUM_NAME?><?=($HEADER['PATH'] ? ' * '.$HEADER['PATH'] : '').($HEADER['PAGE']>1 ? ' * page '.$HEADER['PAGE'] : '')?></title>
<link rel="stylesheet" href="/themes/C=64/theme.css" />
<link rel="alternate" type="application/rss+xml" href="index.rss" />
<meta name="viewport" content="width=device-width" />
<!-- =================================================================================================================== -->
<header>
	<hgroup>
		<h1>**** <?=FORUM_NAME?> v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2011 Kroc Camen</h2>
	</hgroup>
	<p>READY.</p>
	
	<nav>
		<a href="#new">Add Thread</a>
		<a href="index.rss">RSS</a>
<?php if ($HEADER['PATH']): ?>
		<ol>
			<li>
				<a href="/">Index</a>
				<ol><li><?=$HEADER['PATH']?>:</li></ol>
			</li>
		</ol>
<?php else: ?>
		<ol>
			<li>• Index:</li>
		</ol>
<?php endif; ?>
	</nav>
</header>
<!-- =================================================================================================================== -->
<?php if (isset ($FOLDERS)): ?>
<h2 id="folders">Folders</h2>
<dl><?php foreach ($FOLDERS as $FOLDER): ?>
	<dt><a href="<?=$FOLDER['URL']?>"><?=$FOLDER['NAME']?></a></dt>

<?php endforeach; ?></dl>
<?php endif; ?>

<?php if (isset ($THREADS) || isset ($STICKIES)): ?>
<h2 id="list">Threads</h2>
<form method="get" action="http://google.com/search">
	Search
	<input type="hidden" name="as_sitesearch" value="<?=$_SERVER['HTTP_HOST']?>" /><!--
	--><input type="search" name="as_q" /><!--
	--><input type="submit" value="✓" />
</form>

<dl>
<?php if (isset ($STICKIES)) foreach ($STICKIES as $THREAD): ?>
	<dt><a href="<?=$THREAD['URL']?>" class="sticky"><?=$THREAD['TITLE']?></a> (<?=$THREAD['COUNT']?>)</dt>
	<dd>
		<time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
		<b><?=$THREAD['AUTHOR']?></b>
	</dd>

<?php endforeach; ?>
<?php foreach ($THREADS as $THREAD): ?>
	<dt><a href="<?=$THREAD['URL']?>"><?=$THREAD['TITLE']?></a> (<?=$THREAD['COUNT']?>)</dt>
	<dd>
		<time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
		<b><?=$THREAD['AUTHOR']?></b>
	</dd>

<?php endforeach; ?>
</dl>
<?php if (isset ($PAGES)): ?>
<nav class="pages">
	Page <?=$PAGES?>
</nav>
<?php endif; ?>
<?php endif; ?>

<!-- =================================================================================================================== -->
<?php if (FORUM_ENABLED): ?>
<form id="new" method="post" action="#new" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<fieldset><legend>Add Thread</legend>
	
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
	case ERROR_TITLE:
?>	<p class="error">
		You need to enter the title of your new discussion thread
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
<?php	endswitch; ?>
	<label>Title:
		<input name="title" type="text" size="28" maxlength="80" required autocomplete="off"
		       value="<?=$FORM['TITLE']?>" />
	</label>
	<label>Message:
		<textarea name="text" cols="40" rows="23" maxlength="32768" required
		><?=$FORM['TEXT']?></textarea>
	</label>
	
	<p id="rules">
		<input name="submit" type="submit" value="Submit" />
		There’s only 1 rule: don’t be an arse. Rule #2 is Kroc makes up the rules.
	</p>
</fieldset></form>
<?php else: ?>
<h1>Add Thread</h1>
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