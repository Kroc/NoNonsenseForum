<!DOCTYPE html>
<meta charset="utf-8" />
<!-- NoNonsense Forum v7 © Copyright (CC-BY) Kroc Camen 2011
     licensed under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
     you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com> -->
<title>Append to <?php echo $HEADER['TITLE']?></title>
<!-- get rid of IE site compatibility button -->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<link rel="stylesheet" href="<?php echo FORUM_PATH?>themes/<?php echo FORUM_THEME?>/theme.css" />
<meta name="viewport" content="width=device-width, maximum-scale=1, user-scalable=no" />
<meta name="robots" content="noindex, nofollow" />
<!-- details on using mobile favicons with thanks to <mathiasbynens.be/notes/touch-icons> -->
<link rel="shortcut icon" type="image/x-icon" href="<?php echo FORUM_PATH?>themes/<?php echo FORUM_THEME?>/favicon.ico" sizes="16x16 24x24 32x32" />
<link rel="apple-touch-icon-precomposed" href="<?php echo FORUM_PATH?>themes/<?php echo FORUM_THEME?>/touch.png" />
<!-- Microsoft’s insane IE9 pinned site syntax: <msdn.microsoft.com/library/gg131029> -->
<meta name="application-name" content="<?php echo PATH ? safeString(PATH) : safeString (FORUM_NAME)?>" />
<meta name="msapplication-starturl" content="<?php echo FORUM_URL.PATH_URL?>" />
<meta name="msapplication-window" content="width=1024;height=600" />
<meta name="msapplication-navbutton-color" content="#222" />

<body>
<!-- =================================================================================================================== -->
<!-- original 'Grayscale' theme by Jon Gjengset <thesquareplanet.com>,
     greyscale theme by Kroc Camen, please modify to suit your needs -->
<header id="mast">
	<h1><a href="<?php echo FORUM_PATH?>"><?php echo safeHTML(FORUM_NAME)?></a></h1>
	<form id="search" method="get" action="http://google.com/search"><!--
		--><input type="hidden" name="as_sitesearch" value="<?php echo safeString($_SERVER['HTTP_HOST'])?>" /><!--
		--><input id="query" type="search" name="as_q" placeholder="Google Search…" /><!--
		--><input id="go" type="image" src="<?php echo FORUM_PATH?>themes/<?php echo FORUM_THEME?>/icons/go.png" value="Search" width="20" height="20" /><!--
	--></form>
	
	<nav><p>
		<a id="index" href="<?php echo FORUM_PATH?>">Index</a><?php if (PATH): ?> » <a href="<?php echo PATH_URL?>"><?php echo PATH?></a><?php endif; ?>
	</p></nav>
</header>
<!-- =================================================================================================================== -->
<section id="post">
	<h1 id="<?php echo $POST['ID']?>"><?php echo $POST['TITLE']?></h1>
	
	<article>
		<header>
			<time datetime="<?php echo $POST['DATETIME']?>" pubdate><?php echo $POST['TIME']?></time>
			<b<?php echo $POST['MOD']?' class="mod"':''?>><?php echo $POST['AUTHOR']?></b>
		</header>
		
		<?php echo $POST['TEXT']?>
	</article>
</section>
<!-- =================================================================================================================== -->
<section id="append">
	<h1>Append</h1>
	<form method="post" action="#reply" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
<?php if (FORUM_ENABLED): ?>
		<div id="rightcol">
		
		<p id="puser">
			<label for="user">Name:</label>
			<input name="username" id="user" type="text" size="28" tabindex="2"
			       maxlength="<?php echo SIZE_NAME?>" required autocomplete="on"
			       placeholder="Your name" value="<?php echo $FORM['NAME']?>" />
		</p><p id="ppass">
			<label for="pass">Password:</label>
			<input name="password" id="pass" type="password" size="28" tabindex="3"
			       maxlength="<?php echo SIZE_PASS?>" required autocomplete="on"
			       placeholder="A password to keep your name" value="<?php echo $FORM['PASS']?>" />
		</p><p id="pemail">
			<label class="email">Email:</label>
			<input name="email" type="text" value="example@abc.com" tabindex="0"
			       required autocomplete="off" />
			(Leave this as-is, it’s a trap!)
		</p>
<?php switch ($FORM['ERROR']):
	case ERROR_NONE: ?>
		<p id="ok">Only the original author or a moderator can append to this post.</p>
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
		<p id="error">Name / password mismatch! You must enter the name and password of either the original author,
		or a designated moderator.</p>
<?php endswitch; ?>
		<p id="markup">
			Pro tip: Use <a href="<?php echo FORUM_PATH?>markup.txt">markup</a> to add links, quotes and more.
		</p>
		
		</div><div id="leftcol">
		
		<p id="ptext">
			<label for="text">Message:</label>
			<div id="wtext">
				<textarea name="text" id="text" cols="40" rows="14" tabindex="1"
				          maxlength="<?php echo SIZE_TEXT?>" required placeholder="Type your message here…"
				><?php echo $FORM['TEXT']?></textarea>
			</div>
		</p>
		
		</div>
		
		<p id="psubmit"><label for="submit">Submit
			<input id="submit" name="submit" type="image" src="<?php echo FORUM_PATH?>themes/<?php echo FORUM_THEME?>/icons/submit.png"
			       width="40" height="40" tabindex="4" value="&gt;" />
		</label></p>
<?php else: ?>
		<p id="error">Sorry, posting is currently disabled.</p>
<?php endif; ?>
	</form>
</section>
<!-- =================================================================================================================== -->
<div id="mods">
<?php if (!empty ($MODS['LOCAL'])): ?>
<p>
	Moderators for this sub-forum:
	<b class="mod"><?php echo implode ('</b>, <b class="mod">', array_map ('safeHTML', $MODS['LOCAL']))?></b>
</p>
<?php endif; ?>
<?php if (!empty ($MODS['GLOBAL'])): ?>
<p>
	Your friendly neighbourhood moderators:
	<b class="mod"><?php echo implode ('</b>, <b class="mod">', array_map ('safeHTML', $MODS['GLOBAL']))?></b>
</p>
<?php endif; ?>
</div>
<footer><p>
	Powered by <a href="http://camendesign.com/nononsense_forum">NoNonsense Forum</a><br />
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
</p></footer>
<div id="grid"></div>
<script>
//in iOS tapping a label doesn't click the related input element, we'll add this back in using JavaScript
if (document.getElementsByTagName !== undefined) {
	var labels = document.getElementsByTagName ("label");
	//for reasons completely unknown, one only has to reset the onclick event, not actually make it do anything!!
	for (i=0; i<labels.length; i++) if (labels[i].getAttribute ("for")) labels[i].onclick = function (){}
}
</script>
<!-- page generated in: <?php echo round (microtime (true) - START, 3)?>s -->
</body>
