<!DOCTYPE html>
<meta charset="utf-8" />
<title><?php echo FORUM_NAME; ?> :: <?=$HEADER['THREAD']?> ! <?=(ID==1) ? "Delete Thread" : "Delete Post"?></title>
<link rel="stylesheet" href="/themes/greyscale/icons/iconic.css" />
<link rel="stylesheet" href="/themes/greyscale/theme.css" />
<link rel="alternate" type="application/rss+xml" href="index.rss" />
<meta name="viewport" content="width=device-width" />
<meta name="robots" content="noindex, nofollow" />
<!-- =================================================================================================================== -->
<header>
	<hgroup>
		<h1><a href="/" class="iconic home"><?php echo FORUM_NAME; ?></a></h1>
	</hgroup>
</header>
<!-- =================================================================================================================== -->
<form id="delete" class="postform" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
    <fieldset>
        <legend><?=(ID==1) ? "Delete Thread &amp; Replies" : "Delete Post"?></legend>
	
        <?php switch ($FORM['ERROR']):
            case ERROR_NONE: ?>
                <?php if (ID==1): ?>
                    <p>To delete this thread, and all replies to it, you must be either the original author or a designated moderator.</p>
                <?php else: ?>
                    <p>To delete this post you must be either the original author or a designated moderator.</p>
                <?php endif; ?>
                <?php break;
            case ERROR_NAME: ?>
                <p class="error">Enter a name. You’ll need to use this with the password each time.</p>
                <?php break;
            case ERROR_PASS: ?>
                <p class="error">Enter a password. It’s so you can re-use your name each time.</p>
                <?php break;
            case ERROR_AUTH: ?>
                <p class="error">
                    Name / password mismatch! You must enter the name and password of either the original author,
                    or a designated moderator.
                </p>
        <?php endswitch; ?>
    
        <label>Name:
            <input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
             value="<?=$FORM['NAME']?>" />
        </label>
        <label>Password:
            <input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
             value="<?=$FORM['PASS']?>" />
        </label>
        
        <p>
            <input id="submit" name="submit" type="submit" value="Delete" />
        </p>
    </fieldset>
</form>
<!-- =================================================================================================================== -->
<div class="list">
    <h2>Post</h2>
    <article id="<?=ID?>">
        <header>
            <a href="#<?=ID?>">#<?=ID?>.</a>
            <b><?=$POST['AUTHOR']?></b> <time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
        </header>
        
        <?=$POST['TEXT']?>
    </article>
</div>
<!-- =================================================================================================================== -->
<?php include 'footer.inc.php'; ?>