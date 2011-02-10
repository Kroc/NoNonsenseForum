<?php
if (isset ($PAGES)) {
	foreach ($PAGES as &$PAGE) if ($PAGE == PAGE) {
		$PAGE = "<em>$PAGE</em>";
	} elseif ($PAGE) {
		$PAGE = "<a href=\"?page=$PAGE#list\">$PAGE</a>";
	} else {
		$PAGE = "…";
	}
	$PAGES = (implode (", ", $PAGES));
}
?><!DOCTYPE html>
<meta charset="utf-8" />
<title><?php echo FORUM_NAME; ?><?=($HEADER['PATH'] ? ' :: '.$HEADER['PATH'] : '').($HEADER['PAGE']>1 ? ' # '.$HEADER['PAGE'] : '')?></title>
<link rel="stylesheet" href="/themes/grayscale/icons/iconic.css" />
<link rel="stylesheet" href="/themes/grayscale/theme.css" />
<link rel="alternate" type="application/rss+xml" href="index.rss" />
<meta name="viewport" content="width=device-width" />
<!-- =================================================================================================================== -->
<header>
	<hgroup>
		<h1><a href="/" class="iconic home"><?php echo FORUM_NAME; ?></a></h1>
	</hgroup>
	
	<nav>
		<a href="#new" class="iconic new-window">Add Thread</a>
        <a href="index.rss" class="iconic rss">RSS</a>
        <ol>
            <li><span class="iconic map-pin"></span><span class="hide">You are here:</span></li>
            <?php if ($HEADER['PATH']): ?>
                <li><a href="/">Index</a></li>
                <li><?=$HEADER['PATH']?></li>
            <?php else: ?>
                <li>Index</li>
            <?php endif; ?>
        </ol>
	</nav>
</header>
<!-- =================================================================================================================== -->
<?php if (isset ($FOLDERS)): ?>
    <div class="list" id="folders">
        <h2 class="iconic book">Folders</h2>
        <dl>
            <?php foreach ($FOLDERS as $FOLDER): ?>
                <dt><a href="<?=$FOLDER['URL']?>"><?=$FOLDER['NAME']?></a></dt>
            <?php endforeach; ?>
        </dl>
    </div>
<?php endif; ?>

<?php if (isset ($THREADS) || isset ($STICKIES)): ?>
    <div id="threads" class="list">
        <h2 class="iconic chat">Threads</h2>
        <form method="get" action="http://google.com/search">
            Search
            <input type="hidden" name="as_sitesearch" value="<?=$_SERVER['HTTP_HOST']?>" />
            <input type="search" name="as_q" />
            <input type="submit" value="✓" />
        </form>

        <dl>
            <?php if (isset ($STICKIES)): ?>
                <?php foreach ($STICKIES as $THREAD): ?>
                    <dt><a href="<?=$THREAD['URL']?>" class="sticky"><?=$THREAD['TITLE']?></a> (<?=$THREAD['COUNT']?>)</dt>
                    <dd>
                        <time datetime="<?=$THREAD['DATETIME']?>"><?=$THREAD['TIME']?></time>
                        <b><?=$THREAD['AUTHOR']?></b>
                    </dd>
                <?php endforeach; ?>
            <?php endif; ?>
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
    </div>
<?php endif; ?>
<!-- =================================================================================================================== -->
<form id="new" class="postform" method="post" action="#new" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
    <fieldset>
        <legend>Add Thread</legend>
        <?php if (FORUM_ENABLED): ?>
            <label>Message:
                <textarea tabindex="4" name="text" cols="40" rows="23" maxlength="32768" required
                ><?=$FORM['TEXT']?></textarea>
            </label>
            
            <?php switch ($FORM['ERROR']):
                case ERROR_NONE: ?>
                    <p>There is no need to "register", just enter the name + password you want.</p>
                    <?php break;
                case ERROR_NAME: ?>
                    <p class="error">Enter a name. You’ll need to use this with the password each time.</p>
                    <?php break;
                case ERROR_PASS: ?>
                    <p class="error">Enter a password. It’s so you can re-use your name each time.</p>
                    <?php break;
                case ERROR_TITLE: ?>
                    <p class="error">You need to enter the title of your new discussion thread</p>
                    <?php break;
                case ERROR_TEXT: ?>
                    <p class="error">Well, write a message!</p>
                    <?php break;
                case ERROR_AUTH: ?>
                    <p class="error">That name is taken. Provide the password for it, or choose another name. (password typo?)</p>
            <?php endswitch; ?>
            
            <label>Name:
                <input tabindex="1" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
                      value="<?=$FORM['NAME']?>" />
            </label>
            <label>Password:
                <input tabindex="2" name="password" type="password" size="28" maxlength="20" required autocomplete="on"
                      value="<?=$FORM['PASS']?>" />
            </label>
            <label class="email">Email:
                <input name="email" type="text" value="example@abc.com" required autocomplete="off" />
                (Leave this as-is, it’s a trap!)
            </label>
        
            <label>Title:
                <input tabindex="3" name="title" type="text" size="28" maxlength="80" required autocomplete="off"
                       value="<?=$FORM['TITLE']?>" />
            </label>
            
            <p id="rules">
                There’s only 1 rule: don’t be an arse.
                Rule #2 is Kroc makes up the rules.
                <input tabindex="5" name="submit" type="submit" value="Submit" />
            </p>
        <?php else: ?>
            <p class="error">Sorry, posting is currently disabled.</p>
        <?php endif; ?>
    </fieldset>
</form>
<!-- =================================================================================================================== -->
<?php include 'footer.inc.php'; ?>