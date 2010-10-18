<?php //defines the HTML portions of the theme

/* the opening HTML and website header
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_HEADER", <<<HTML
<!doctype html>
<html><head>
	<meta charset="utf-8" />
	<title>&__TITLE__;</title>
	<link rel="stylesheet" href="/theme/c64.css" />
</head><body>

<header>
	<hgroup>
		<h1>**** Camen Design Forums v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2010 Kroc Camen</h2>
	</hgroup>
	<p>READY.</p>
	
	<nav>
&__MENU__;
&__PATH__;
	</nav>
</header>

HTML
);

//the menu items for the index page
define ("TEMPLATE_INDEX_MENU", <<<HTML
		<a href="#new">Add Thread</a>
		<a href="index.rss">RSS</a>
HTML
);

//the menu items for a thread page
define ("TEMPLATE_THREAD_MENU", <<<HTML
		<a href="#reply">Reply</a>
		<a href="&__RSS__;">RSS</a>
HTML
);

//the path navigation (on index pages), when on the home page
define ("TEMPLATE_INDEX_PATH", <<<HTML
		<ol>
			<li>• Forum Index:</li>
		</ol>
HTML
);
//the path navigation (on index pages), when in a folder
define ("TEMPLATE_INDEX_PATH_FOLDER", <<<HTML
		<ol>
			<li>
				<a href="/">Forum Index</a>
				<ol><li>&__PATH__;:</li></ol>
			</li>
		</ol>
HTML
);

//the path navigation (on thread pages)
define ("TEMPLATE_THREAD_PATH", <<<HTML
		<ol>
			<li><a href="/">Forum Index</a></li>
		</ol>
HTML
);
//the path navigation (on thread pages), when in a folder
define ("TEMPLATE_THREAD_PATH_FOLDER", <<<HTML
		<ol>
			<li>
				<a href="/">Forum Index</a>
				<ol><li><a href="/&__URL__;/">&__PATH__;</a></li></ol>
			</li>
		</ol>
HTML
);

/* the folders list on index pages
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_INDEX_FOLDERS", <<<HTML
<h2 id="folders">Folders</h2>
<dl>
&__FOLDERS__;</dl>

HTML
);
//a folder
define ("TEMPLATE_INDEX_FOLDER", <<<HTML
	<dt><a href="/&__URL__;/">&__FOLDER__;</a></dt>

HTML
);

/* the threads list on index pages (including page list)
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_INDEX_THREADS", <<<HTML
<h2 id="list">Threads</h2>
<dl>
&__THREADS__;</dl>
<nav class="pages">
	Page &__PAGES__;
</nav>

HTML
);
define ("TEMPLATE_INDEX_THREAD", <<<HTML
	<dt><a href="&__URL__;?page=&__PAGE__;"&__STICKY__;>&__TITLE__;</a> (&__COUNT__;)</dt>
	<dd>
		<span class="ltgrey">+</span><time datetime="&__DATE__;">&__TIME__;</time>
		<span class="ltgrey">&#x0ee1f;</span>&__NAME__;
	</dd>

HTML
);

/* the page list
   ---------------------------------------------------------------------------------------------------------------------- */
//I should probably do this using LIs so generated content can be used to do commas and the designer has more freedom
define ("TEMPLATE_PAGES_PAGE",      "<a href=\"?page=&__PAGE__;#list\">&__PAGE__;</a>");
define ("TEMPLATE_PAGES_CURRENT",   "<span class=\"ltgreen\">&__PAGE__;</span>");
define ("TEMPLATE_PAGES_GAP",       "…");
define ("TEMPLATE_PAGES_SEPARATOR", ",");

/* the new thread input form
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_INDEX_FORM", <<<HTML
<form id="new" method="post" action="#new" enctype="application/x-www-form-urlencoded;charset=utf-8"><fieldset>
	<legend>Add Thread</legend>
	
	<p><!--
		--><label for="name">Name:</label><!--
		--><input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		          value="&__NAME__;" /><!--
	--></p><p><!--
		--><label for="password">Password:</label><!--
		--><input id="password" name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		          value="&__PASS__;"/><!--
	--></p><p><!--
		--><label for="email">Email:</label><!--
		--><input id="email" name="email" type="text" value="example@abc.com" required autocomplete="off" /><!--
		-->(Leave this as-is, it’s a trap!)<!--
	--></p><p>
		&__ERROR__;
	   </p><p><!--
		--><label for="title">Title:</label><!--
		--><input id="title" name="title" type="text" size="28" maxlength="80" required autocomplete="off"
		    value="&__TITLE__;" /><!--
	--></p><p><!--
		--><label for="text">Message:</label><br /><!--
		--><textarea id="text" name="text" cols="40" rows="23" maxlength="32768" required autocomplete="off"
		   >&__TEXT__;</textarea><!--
	--></p><p id="rules">
		<input id="submit" name="submit" type="submit" value="Submit" />
		
		There’s only 1 rule: don’t be an arse. Rule #2 is Kroc makes up the rules.
	</p>
</fieldset></form>
HTML
);
define ("TEMPLATE_INDEX_FORM_DISABLED", <<<HTML
<h1>Add Thread</h1>
<p class="red">
	Sorry, posting is currently disabled.
</p>
HTML
);

/* the first post in a thread
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_THREAD_FIRST", <<<HTML
<h1>&__TITLE__;</h1>

<article id="1">
	<header>
		<time datetime="&__DATETIME__;" pubdate>&__PUBDATE__;</time> <a href="#1">#1.</a> <b>&__AUTHOR__;</b>
	</header>
	
	&__DESCRIPTION__;
</article>

HTML
);

/* the list of posts in a thread (including page list)
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_THREAD_POSTS", <<<HTML
<h2 id="list">Replies</h2>
<nav class="pages">
	Page &__PAGES__;
</nav>

&__POSTS__;

<nav class="pages">
	Page &__PAGES__;
</nav>
HTML
);

/* a post
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_THREAD_POST", <<<HTML
<article id="&__ID__;">
	<header>
		<time datetime="&__DATETIME__;" pubdate>&__PUBDATE__;</time>
		<a href="#&__ID__;">#&__ID__;.</a>
		<b>&__AUTHOR__;</b>
	</header>
	
	&__DESCRIPTION__;
</article>

HTML
);

/* the reply input form
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_THREAD_FORM", <<<HTML
<form id="reply" method="post" action="#reply" enctype="application/x-www-form-urlencoded;charset=utf-8"><fieldset>
	<legend>Reply</legend>
	
	<p><!--
		--><label for="name">Name:</label><!--
		--><input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		          value="&__NAME__;" /><!--
	--></p><p><!--
		--><label for="password">Password:</label><!--
		--><input id="password" name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		          value="&__PASS__;"/><!--
	--></p><p><!--
		--><label for="email">Email:</label><!--
		--><input id="email" name="email" type="text" value="example@abc.com" required automcomplete="on" /><!--
		-->Leave this as-is, it’s a trap!<!--
	--></p><p>
		&__ERROR__;
	   </p><p><!--
		--><label for="text">Message:</label><br /><!--
		--><textarea id="text" name="text" cols="40" rows="23" maxlength="32768" required autocomplete="off"
		   >&__TEXT__;</textarea><!--
	--></p><p id="rules">
		<input id="submit" name="submit" type="submit" value="Reply" />
		
		There’s only 1 rule: don’t be an arse. Rule #2 is Kroc makes up the rules.
	</p>
</fieldset></form>
HTML
);
define ("TEMPLATE_THREAD_FORM_DISABLED", <<<HTML
<h1>Reply</h1>
<p class="red">
	Sorry, posting is currently disabled.
</p>
HTML
);

/* the site footer and closing HTML
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_FOOTER", <<<HTML

<footer><p>
	&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;<!--
     -->&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;<!--
     -->&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;<!--
     -->&#x0ee40;&#x0ee40;&#x0ee40;&#x0ee40;
</p><p>
	<a href="mailto:kroccamen@gmail.com">kroccamen@gmail.com</a> • <a href="http://camendesign.com">camendesign.com</a>
</p></footer>

</body></html>
HTML
);

/* RSS feeds
   ====================================================================================================================== */
/* new thread RSS feed (replies get inserted to this)
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_RSS", <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
	<atom:link href="http://${_SERVER['HTTP_HOST']}/&__URL__;.xml" rel="self" type="application/rss+xml" />
	<title>&__TITLE__;</title>
	<link>http://${_SERVER['HTTP_HOST']}/&__URL__;</link>
	<item>
		<title>&__TITLE__;</title>
		<link>http://${_SERVER['HTTP_HOST']}/&__URL__;#1</link>
		<author>&__NAME__;</author>
		<pubDate>&__DATE__;</pubDate>
		<description>&__TEXT__;</description>
	</item>
</channel>
</rss>
XML
);

/* RSS feed for index pages
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_RSS_INDEX", <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="http://${_SERVER['HTTP_HOST']}/index.rss" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>http://${_SERVER['HTTP_HOST']}/</link>
&__ITEMS__;
</channel>
</rss>
XML
);

/* an individual post
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_RSS_ITEM", <<<XML
<item>
	<title>&__TITLE__;</title>
	<link>http://${_SERVER['HTTP_HOST']}/&__URL__;</link>
	<author>&__NAME__;</author>
	<pubDate>&__DATE__;</pubDate>
	<description>&__TEXT__;</description>
</item>
XML
);

?>