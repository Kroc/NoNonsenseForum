<?php //defines the HTML portions of the theme

/* the opening HTML and website header
   ---------------------------------------------------------------------------------------------------------------------- */
/* tags:
	&__TITLE__;		HTML `<title>`
	&__RSS_URL__;		URL to RSS feed for the current page
	&__RSS_TITLE__;		the label for the RSS feed, like “New threads”
	&__NAV__;		a placeholder for a menu used on index / thread pages, but not delete / edit
				see `TEMPLATE_HEADER_NAV` below
*/
define ("TEMPLATE_HEADER", <<<HTML
<!doctype html>
<html><head>
	<meta charset="utf-8" />
	<title>&__TITLE__;</title>
	<link rel="stylesheet" href="/theme/c64.css" />
	<link rel="alternate" type="application/rss+xml" href="&__RSS_URL__;" title="&__RSS_TITLE__;" />
	<meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no" />
</head><body>

<header>
	<hgroup>
		<h1>**** Camen Design Forums v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2010 Kroc Camen</h2>
	</hgroup>
	<p>READY.</p>
	&__NAV__;
</header>

HTML
);

//the nav menu for RSS, new / reply links
//some pages (like edit / delete) won’t include this
define ("TEMPLATE_HEADER_NAV", <<<HTML

	<nav>
&__MENU__;
&__PATH__;
	</nav>
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
	
	<label>Name:
		<input name="username" type="text" size="28" maxlength="18" required autocomplete="on"
	          value="&__NAME__;" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
	          value="&__PASS__;" />
	</label>
	<label class="email">Email:
		<input name="email" type="text" value="example@abc.com" required autocomplete="off" />
		(Leave this as-is, it’s a trap!)
	</label>
	
	<p>
		&__ERROR__;
	</p>
	
	<label>Title:
		<input name="title" type="text" size="28" maxlength="80" required autocomplete="off"
		    value="&__TITLE__;" />
	</label>
	<label>Message:
		<textarea name="text" cols="40" rows="23" maxlength="32768" required autocomplete="off"
		>&__TEXT__;</textarea>
	</label>
	
	<p id="rules">
		<input name="submit" type="submit" value="Submit" />
		
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

/* form error messages
   ---------------------------------------------------------------------------------------------------------------------- */
define ("ERROR_NONE",  "There is no need to \"register\", just enter the name + password you want.");
define ("ERROR_NAME",  "<span class=\"red\">Enter a name. You’ll need to use this with the password each time.</span>");
define ("ERROR_PASS",  "<span class=\"red\">Enter a password. It’s so you can re-use your name each time.</span>");
define ("ERROR_TITLE", "<span class=\"red\">You need to enter the title of your new discussion thread</span>");
define ("ERROR_TEXT",  "<span class=\"red\">Well, write a message!</span>");
define ("ERROR_AUTH",  "<span class=\"red\">That name is taken. Provide the password for it, or choose another name. (password typo?)</span>");

/* the first post in a thread
   ---------------------------------------------------------------------------------------------------------------------- */
/* tags:
	&__TITLE__;		Title of the thread
	&__DELETE__;		URL to delete the thread
	&__DATETIME__;		timestamp in "Sun, 17 Oct 2010 19:41:09 +0000" format for HTML5 `<time>` datetime attribute
	&__PUBDATE__;		Human readable timestamp
	&__AUTHOR__;		Name of thread originator
	&__DESCRIPTION__;	The post message text, HTML formatted and encoded
*/
define ("TEMPLATE_THREAD_FIRST", <<<HTML
<h1>&__TITLE__;</h1>

<article id="1">
	<header>
		<a class="delete" href="&__DELETE__;">Delete</a>
		<time datetime="&__DATETIME__;" pubdate>&__PUBDATE__;</time>
		<a href="#1">#1.</a> <b>&__AUTHOR__;</b>
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
	
	<label>Name:
		<input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		 value="&__NAME__;" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		 value="&__PASS__;" />
	</label>
	<label class="email">Email: (Leave this as-is, it’s a trap!)
		<input name="email" type="text" value="example@abc.com" required automcomplete="on" />
	</label>
	
	<p>
		&__ERROR__;
	</p>
	
	<label>Message:
		<textarea name="text" cols="40" rows="25" maxlength="32768" required autocomplete="off"
		>&__TEXT__;</textarea>
	</label>
	
	<p id="rules">
		<input name="submit" type="submit" value="Reply" />
		
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
/* tags: none */
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


/* the deletion page
   ====================================================================================================================== */
/* delete thread
   ---------------------------------------------------------------------------------------------------------------------- */
define ("TEMPLATE_DELETE_THREAD", <<<HTML
<form id="delete" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8"><fieldset>
	<legend>Delete Thread &amp; Replies</legend>
	
	<label>Name:
		<input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		 value="&__NAME__;" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		 value="&__PASS__;" />
	</label>
	
	<p>
		&__ERROR__;
	</p><p>
		<input id="submit" name="submit" type="submit" value="Delete" />
	</p>
</fieldset></form>
HTML
);
define ("ERROR_DELETE_NONE", "To delete this thread, and all replies to it, you must be either the original poster, or a designated moderator.");
define ("ERROR_DELETE_AUTH", "<span class=\"red\">Name / password mismatch! You must enter the name and password of either the post originator, or a designated moderator.</span>");


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