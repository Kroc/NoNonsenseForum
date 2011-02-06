<!DOCTYPE html>
<meta charset="utf-8" />
<title>Camen Design Forum<?=($HEADER['PATH'] ? ' * '.$HEADER['PATH'] : '').($HEADER['PAGE']>1 ? ' * page '.$HEADER['PAGE'] : '')?></title>
<link rel="stylesheet" href="/themes/C=64/theme.css" />
<link rel="alternate" type="application/rss+xml" href="index.rss" />
<meta name="viewport" content="width=device-width" />
<body>

<header>
	<hgroup>
		<h1>**** Camen Design Forums v2 ****</h1>
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
<?php foreach ($STICKIES as $THREAD): ?>
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
	Page <?php
foreach ($PAGES as &$PAGE):
	if ($PAGE == PAGE):
		$PAGE = "<em>$PAGE</em>";
	elseif ($PAGE):
		$PAGE = "<a href=\"?page=$PAGE#list\">$PAGE</a>";
	else:
		$PAGE = "…";
	endif;
endforeach;
echo (implode (", ", $PAGES));
?>
</nav>
<?php endif; ?>
<?php endif; ?>