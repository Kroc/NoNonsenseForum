<?php //defines the website theme, keeping HTML in one place
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

/* how the theme works:
   ====================================================================================================================== */
/* to keep the PHP and HTML sparate we put HTML chunks into constants and use search and replace (via `template_tag` and
   `template_tags` in shared.php) to swap out “tags” in the form of “&__TAG__;” with the data from the PHP, or in other
   instances with another template. this keeps the PHP logic separate from the HTML it is outputting

   tags may be used once, more than once, or not at all in your templates */


/* common strings used throughout or for non-HTML purposes
   ---------------------------------------------------------------------------------------------------------------------- */
//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
define ('DATE_FORMAT', "d-M'y H:i");

//the HTML `<title>` string
define ('TEMPLATE_HTMLTITLE_SLUG',		'Camen Design Forum');		//always first
define ('TEMPLATE_HTMLTITLE_NAME',		' * &__NAME__;');		//added next, name of folder or thread
define ('TEMPLATE_HTMLTITLE_PAGE',		' * Page &__PAGE__;');		//added next, current page number
define ('TEMPLATE_HTMLTITLE_DELETE_THREAD',	' * Delete Thread?');		//on delete.php
define ('TEMPLATE_HTMLTITLE_DELETE_POST',	' * Delete Post?');		//on delete.php

//prepended to the thread title for each reply (like in e-mail)
define ('TEMPLATE_RE',				'RE: ');


/* the opening HTML and website header
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	nothing, inserted directly into the page by index.php, thread.php & delete.php
   tags:
	&__HTMLTITLE__;	HTML `<title>`, see TEMPLATE_HTMLTITLE_* for construction
	&__RSS__;	URL to RSS feed for the current page
	&__ROBOTS__;	on delete pages, TEMPLATE_HEADER_ROBOTS is inserted here (to tell crawlers to ignore delete pages)
	&__NAV__;	a placeholder for a menu used on index / thread pages, but not delete / edit pages
			(see `TEMPLATE_HEADER_NAV` below)
*/
define ('TEMPLATE_HEADER', <<<HTML
<!DOCTYPE html>
<meta charset="utf-8" />
<title>&__HTMLTITLE__;</title>
<link rel="stylesheet" href="/themes/C=64/theme.css" />
<link rel="alternate" type="application/rss+xml" href="&__RSS__;" />
<meta name="viewport" content="width=device-width, maximum-scale=1.0, user-scalable=no" />&__ROBOTS__;
<body>

<header>
	<hgroup>
		<h1>**** Camen Design Forums v2 ****</h1>
		<h2>Copyright (CC-BY) 1984-2011 Kroc Camen</h2>
	</hgroup>
	<p>READY.</p>&__NAV__;
</header>

HTML
);
/* attached to:
	&__ROBOTS__;	TEMPLATE_HEADER
   tags:
	none
*/
define ('TEMPLATE_HEADER_ROBOTS', <<<HTML

	<meta name="robots" content="noindex, nofollow" />
HTML
);

/* the thread page
   ====================================================================================================================== */
/* a post (appended in sequence)
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	&__POSTS__;	TEMPLATE_THREAD_POSTS
   tags:
	&__ID__;	HTML ID of the post, pointed to by the RSS
	&__TYPE__;	either nothing, TEMPLTE_POST_OP or TEMPLATE_POST_DELETED as appropriate
	&__OP__;	if the post is by the thread’s original poster, TEMPLATE_POST_OP gets inserted here
	&__DELETED__;	if the post is marked as deleted, TEMPLATE_POST_DELETED gets inserted here
	&__DELETE__;	if delete is allowed, TEMPLATE_DELETE is inserted here
	&__DATETIME__;	timestamp in "Sun, 17 Oct 2010 19:41:09 +0000" format for HTML5 `<time>` datetime attribute
	&__TIME__;	Human readable timestamp
	&__NAME__;	the poster’s name
	&__TEXT__;	the post message
*/
define ('TEMPLATE_POST', <<<HTML
<article id="&__ID__;" class="&__TYPE__;">
	<header>&__DELETE__;
		<time datetime="&__DATETIME__;" pubdate>&__TIME__;</time>
		<a href="#&__ID__;">#&__ID__;.</a> <b>&__NAME__;</b>
	</header>
	
	&__TEXT__;
</article>

HTML
);


/* the deletion page
   ====================================================================================================================== */
/* delete thread
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	nothing, inserted directly into the page by delete.php
   tags:
	&__NAME__;	the value of the input field named 'username', echoed back to maintain form state
	&__PASS__;	the password entered
	&__ERROR__;	a message / error depending on form state, see ERROR_* templates
*/
define ('TEMPLATE_DELETE_THREAD', <<<HTML
<form id="delete" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on"><fieldset>
	<legend>Delete Thread &amp; Replies</legend>
	
	<label>Name:
		<input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		 value="&__NAME__;" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		 value="&__PASS__;" />
	</label>
	
	&__ERROR__;
	
	<p>
		<input id="submit" name="submit" type="submit" value="Delete" />
	</p>
</fieldset></form>

<h1>Post</h1>
&__POST__;
HTML
);
/* delete post
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	nothing, inserted directly into the page by delete.php
   tags:
	&__NAME__;	the value of the input field named 'username', echoed back to maintain form state
	&__PASS__;	the password entered
	&__ERROR__;	a message / error depending on form state, see ERROR_* templates
*/
define ('TEMPLATE_DELETE_POST', <<<HTML

<form id="delete" method="post" action="#delete" enctype="application/x-www-form-urlencoded;charset=utf-8" autocomplete="on"><fieldset>
	<legend>Delete Post</legend>
	
	<label>Name:
		<input id="name" name="username" type="text" size="28" maxlength="18" required autocomplete="on"
		 value="&__NAME__;" />
	</label>
	<label>Password:
		<input name="password" type="password" size="28" maxlength="20" required autocomplete="on"
		 value="&__PASS__;" />
	</label>
	
	&__ERROR__;
	
	<p>
		<input id="submit" name="submit" type="submit" value="Delete" />
	</p>
</fieldset></form>

<h1>Post</h1>
&__POST__;
HTML
);

//a different default text for the delete form, and an error message if the user is not authorised to delete a thread/post
/* attached to:
	&__ERROR__;	TEMPLATE_DELETE_THREAD
   tags:
	none
*/
define ('ERROR_DELETE_THREAD', '<p>To delete this thread, and all replies to it, you must be either the original author or a designated moderator.</p>');
define ('ERROR_DELETE_POST',   '<p>To delete this post you must be either the original author or a designated moderator.</p>');
define ('ERROR_DELETE_AUTH',   '<p class="error">Name / password mismatch! You must enter the name and password of either the original author, or a designated moderator.</p>');

//the text left behind when a post is deleted
define ('TEMPLATE_DELETE_USER', '<p>This post was deleted by its owner</p>');
define ('TEMPLATE_DELETE_MOD',  '<p>This post was deleted by a moderator</p>');


/* RSS feeds
   ====================================================================================================================== */
/* new thread RSS feed (replies get inserted to this)
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	nothing, inserted directly into the page by index.php
   tags:
	&__URL__;	filename of the thread, sans “.xml” extension
	&__TITLE__;	title of the thread
	&__NAME__;	name of poster
	&__DATE__;	RSS formatted timestamp
	&__TEXT__;	the message, HTML formatted and XML encoded
*/
define ('TEMPLATE_RSS', <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="http://${_SERVER['HTTP_HOST']}&__URL__;.xml" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>http://${_SERVER['HTTP_HOST']}&__URL__;</link>

&__ITEMS__;

</channel>
</rss>
XML
);

/* RSS feed for index pages
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	nothing, inserted directly into the page by shared.php
   tags:
	&__PATH__;	if in a sub-folder, the URL encoded folder name (including ending slash but no prefix slash)
	&__TITLE__;	title of the thread
	&__ITEMS__;	a generated list of RSS items, see `TEMPLATE_RSS_ITEM` below
*/
define ('TEMPLATE_RSS_INDEX', <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="http://${_SERVER['HTTP_HOST']}&__PATH__;index.rss" rel="self" type="application/rss+xml" />
<title>&__TITLE__;</title>
<link>http://${_SERVER['HTTP_HOST']}/</link>

&__ITEMS__;

</channel>
</rss>
XML
);

/* an individual post (appended in sequence)
   ---------------------------------------------------------------------------------------------------------------------- */
/* attached to:
	&__ITEMS__;	TEMPLATE_RSS_INDEX
   tags:
	&__TITLE__;	title of the post (just “Re: Title…”)
	&__URL__;	path & filename of the thread, sans “.xml” extension
	&__NAME__;	name of poster
	&__DATE__;	RSS formatted timestamp
	&__TEXT__;	the message, HTML formatted and XML encoded
*/
define ('TEMPLATE_RSS_ITEM', <<<XML
<item>
	<title>&__TITLE__;</title>
	<link>http://${_SERVER['HTTP_HOST']}&__URL__;</link>
	<author>&__NAME__;</author>
	<pubDate>&__DATE__;</pubDate>
	<description>&__TEXT__;</description>
</item>
XML
);

?>