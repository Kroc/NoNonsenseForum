<!DOCTYPE html>
<meta charset="utf-8" />
<title>Camen Design Forum * <?=$HEADER['THREAD']?> * <?=(ID==1) ? "Delete Thread" : "Delete Post"?>?</title>
<link rel="stylesheet" href="/themes/C=64/theme.css" />
<meta name="viewport" content="width=device-width" />
<meta name="robots" content="noindex, nofollow" />
<!-- =================================================================================================================== -->
<header>
	<hgroup>
		<h1>**** Camen Design Forums v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2011 Kroc Camen</h2>
	</hgroup>
	<p>READY.</p>
</header>
<!-- =================================================================================================================== -->
<form id="delete" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<fieldset><legend><?=(ID==1) ? "Delete Thread &amp; Replies" : "Delete Post"?></legend>
	
	<label>Name:
		<input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		 value="<?=$FORM['NAME']?>" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		 value="<?=$FORM['PASS']?>" />
	</label>
<?php switch ($FORM['ERROR']):
	case ERROR_NONE: if (ID==1):
?>	<p>
		To delete this thread, and all replies to it, you must be either the original author
		or a designated moderator.
	</p>
<?	else:
?>	<p>
		To delete this post you must be either the original author or a designated moderator.
	</p>
<?php	endif; break;
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
	case ERROR_AUTH:
?>	<p class="error">
		Name / password mismatch! You must enter the name and password of either the original author,
		or a designated moderator.
	</p>
<?php endswitch; ?>
	<p>
		<input id="submit" name="submit" type="submit" value="Delete" />
	</p>
</fieldset></form>
<!-- =================================================================================================================== -->
<h1>Post</h1>
<article id="<?=ID?>">
	<header>
		<time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
		<a href="#<?=ID?>">#<?=ID?>.</a> <b><?=$POST['AUTHOR']?></b>
	</header>
	
	<?=$POST['TEXT']?>
</article>

<!-- =================================================================================================================== -->
<footer><p>
	<a href="mailto:kroccamen@gmail.com">kroccamen@gmail.com</a> • <a href="http://camendesign.com">camendesign.com</a>
</p><p>
	NoNonsenseForum: <a href="https://github.com/Kroc/NoNonsenseForum">Get the source on GitHub</a>
</p></footer>