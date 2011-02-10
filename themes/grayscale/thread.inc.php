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
<title><?php echo FORUM_NAME; ?><?=($HEADER['THREAD'] ? ' :: '.$HEADER['THREAD'] : '').($HEADER['PAGE']>1 ? ' # '.$HEADER['PAGE'] : '')?></title>
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
                <li><a href="<?=$HEADER['PATH_URL']?>"><?=$HEADER['PATH']?></a></li>
            <?php else: ?>
                <li><a href="/">Index</a></li>
            <?php endif; ?>
        </ol>
	</nav>
</header>
<!-- =================================================================================================================== -->
<section class="thread">
    <h1><?=$POST['TITLE']?></h1>

    <article id="1" class="op">
        <header>
            <a class="delete" rel="noindex nofollow" href="<?=$POST['DELETE_URL']?>">[delete]</a>
            <a href="#1">#1.</a>
            <b><?=$POST['AUTHOR']?></b> <time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
        </header>
        
        <?=$POST['TEXT']?>
    </article>

    <?php if (isset ($POSTS)): ?>
        <div id="replies" class="list">
            <h2>Replies</h2>
            <nav class="pages">
                Page <?=$PAGES?>
            </nav>

            <?php foreach ($POSTS as $POST): ?>
                <article id="<?=$POST['ID']?>" class="<?=($POST['DELETED'] ? 'deleted' : ($POST['OP'] ? 'op' : ''))?>">
                    <header>
                        <?php if (!$POST['DELETED']): ?>
                            <a class="delete" rel="noindex nofollow" href="<?=$POST['DELETE_URL']?>">[delete]</a>
                        <?php endif;?>
                        <a href="#<?=$POST['ID']?>">#<?=$POST['ID']?>.</a>
                        <b><?=$POST['AUTHOR']?></b> <time datetime="<?=$POST['DATETIME']?>" pubdate><?=$POST['TIME']?></time>
                    </header>
                    
                    <?=$POST['TEXT']?>
                </article>
            <?php endforeach; ?>

            <nav class="pages">
                Page <?=$PAGES?>
            </nav>
        </div>
    <?php endif; ?>
</section>
<!-- =================================================================================================================== -->
<form id="reply" class="postform"  method="post" action="#reply" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on">
    <fieldset>
        <legend>Reply</legend>
        <?php if (FORUM_ENABLED): ?>
            <label>Message:
                <textarea tabindex="3" name="text" cols="40" rows="23" maxlength="32768" required
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
            
            <p id="rules">
                There’s only 1 rule: don’t be an arse.
                Rule #2 is Kroc makes up the rules.
                
                <input tabindex="4" name="submit" type="submit" value="Reply" />
            </p>
        <?php else: ?>
            <p class="error">Sorry, posting is currently disabled.</p>
        <?php endif; ?>
    </fieldset>
</form>
<!-- =================================================================================================================== -->
<?php include 'footer.inc.php'; ?>