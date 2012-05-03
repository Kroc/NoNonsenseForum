<?php //translation file
/* ====================================================================================================================== */
/* NoNonsense Forum v17 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*//*

how to create a theme translation:
----------------------------------
1.	determine the standard language code to use, e.g. "en" (or "en-GB", "en-US" …), "de", "es" etc.
	Read this document on how to choose a language code:
	http://www.w3.org/International/questions/qa-choosing-language-tags
	
2.	make a copy of 'lang.example.php' and rename as 'lang.en.php' where "en" is the language code for your translation
	(do not rename, modify or delete 'lang.example.php')

3.	if you have not already, make a copy of 'theme.config.default.php' and rename it to 'theme.config.php'.
	within that file add your language code, separated from any other languages by a space, to the `THEME_LANGS`
	option; this will tell NNF that your language is available to use and will add it to the theme's language selector

4.	translate the text within your lang file. please note that all text is HTML.
	you can preview as you go by selecting your new language when running NNF

*/

//name of the language, as the user will use to select it;
//therefore should be the name of the language, written in that language - i.e. "Espanol" (Spanish)
$LANG['en']['name']    = 'English';

$LANG['en']['strings'] = array (

/* xpath/shorthand:			replacement text:			description:
   ====================================================================================================================== */
/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
//search box
 '#query@placeholder'			=> 'Google Search…'			//default text shown in search box
,'#go@alt'				=> 'Search'				//search submit button

//language selector
,'#nnf_lang/img@alt, '.
 '#nnf_lang/img@title'			=> 'Language:'				//language chooser icon
,'#nnf_lang/input@alt'			=> 'Set language'			//language chooser submit button

//breadcrumb
,'#index"/li[1]/a'			=> 'Index'				//breadcrumb root location

/* index page
   ---------------------------------------------------------------------------------------------------------------------- */
//list of sub-forums
,'#nnf_folders/h1'			=> 'Sub-Forums'				//section title
,'.nnf_lock-threads@alt, '.
 '.nnf_lock-threads@title'		=> 'Replies-only:'			//alt+title of lock-icon, if thread-locked
,'.nnf_lock-posts@alt, '.
 '.nnf_lock-posts@title'		=> 'Read-only:'				//alt+title of lock-icon, if post-locked

//navigation links
,'#nnf_add'				=> 'Add Thread'				//the "add thread" link in 'index.html'
,'#nnf_reply'				=> 'Reply'				//the "reply" link in 'thread.html'
,'#nnf_rss'				=> 'RSS'				//the RSS link in the header

//access rights
,'#nnf_forum-lock-threads'		=> <<<HTML
	Only <a href="#mods">moderators or members</a> can start new threads here [<a href="?signin">sign-in</a>],
	but <em>anybody</em> can reply to existing threads.
HTML
,'#nnf_forum-lock-posts'		=> <<<HTML
	Only <a href="#mods">moderators or members</a> can participate here.
	<a href="?signin">Sign-in</a> if you are a moderator or member in order to post.
HTML

//list of threads
,'#nnf_threads/h1'			=> 'Threads'
,'.nnf_thread-locked@alt, '.
 '.nnf_thread-locked@title'		=> 'Locked:'

,'#nnf_new-form/h1'			=> 'Add Thread'				//add thread form title
,'#nnf_new-form/form/p/'.
 'label@for="submit"/span'		=> 'Submit'				//form submit button

/* thread page
   ---------------------------------------------------------------------------------------------------------------------- */
,'#nnf_post-append, #nnf_post-append, '.
 '.nnf_reply-append, .nnf_reply-append'	=> 'append'				//"append" link in posts
,'#nnf_post-delete, #nnf_post-delete, '.
 '.nnf_reply-delete, .nnf_reply-delete'	=> 'delete'				//"delete" link in posts

,'.nnf_forum-locked'			=> <<<HTML
	Only <a href="#mods">moderators or members</a> can reply to this thread.
	<a href="?signin">Sign-in</a> if you are a moderator or member in order to post.
HTML

,'#nnf_replies/h1'			=> 'Replies'				//title for replies list

,'#nnf_reply-form/h1'			=> 'Reply'				//reply form title
,'#nnf_reply-form/form/p/'.
 'label@for="submit"/span'		=> 'Reply'				//form submit button

/* append page
   ---------------------------------------------------------------------------------------------------------------------- */
,'#append/h1'				=> 'Append'				//append form title
,'#append/form/p/'.
 'label@for="submit"/span'		=> 'Append'				//form submit button

/* delete page
   ---------------------------------------------------------------------------------------------------------------------- */
,'#delete/h1'				=> 'Delete'				//delete form title

,'label@for="remove"/span'		=> 'Remove completely (moderators only)'
,'#nnf_remove"/ul/li[1]'		=> 'The post will be removed completely from the thread, rather than blanked'
,'#nnf_remove"/ul/li[2]'		=> 'Only posts on the last page of the thread can be removed completely (so as to not break permalinks)'

,'#delete/form/p/'.
 'label@for="submit"/span'		=> 'Delete'				//form submit button

/* input forms
   ---------------------------------------------------------------------------------------------------------------------- */
,'label@for="nnf_title-field"'		=> 'Title:'				//title field label
,'#nnf_title-field@placeholder'		=> 'Type thread title here…'		//placeholder text for the title field

,'label@for="nnf_name-field-http"'	=> 'You are signed in as:'		//label for name field if HTTP_AUTH
,'label@for="nnf_name-field"'		=> 'Name:'				//name field label
,'#nnf_name-field@placeholder'		=> 'Your name'				//placeholder text for the name field

,'label@for="nnf_pass-field"'		=> 'Password:'				//label for password field
,'#nnf_pass-field@placeholder'		=> 'A password to keep your name'	//placeholder text for the password field

,'#nnf_error-none'			=> <<<HTML
			<!-- this is shown by default as long as new users aren't disabled and the user isn't signed in -->
			There is no need to “register”, just enter the same name + password of your choice every time.
HTML
,'#nnf_error-none-http'			=> <<<HTML
			<!-- this is shown by default if the user is signed in -->
			(Quit your browser or clear the browser cache to sign out.)
HTML
,'#nnf_error-none-append'		=> <<<HTML
			<!-- this is shown by default as long as new users aren't disabled and the user isn't signed in -->
			Only the original author or a moderator can append to this post.
HTML
,'#nnf_error-none-thread'		=> <<<HTML
			To delete this thread, and all replies to it, you must be either the original author
			or a designated moderator.
HTML
,'#nnf_error-none-reply'		=> <<<HTML
			To delete this post you must be either the original author or a designated moderator.<br />
			The content of the post will be removed but the name and date will remain.
HTML
,'#nnf_error-newbies'			=> <<<HTML
			<!-- if the `FORUM_NEWBIES` option is false, only existing users can post -->
			Only registered users can post.<br />No new registrations are allowed.
HTML
,'#nnf_error-title'			=> <<<HTML
			You need to enter the title of your new discussion thread.
HTML
,'#nnf_error-name'			=> <<<HTML
			Enter a name. You’ll need to use this with the password each time.
HTML
,'#nnf_error-name-append'		=> <<<HTML
			Enter the name of either the original author or a moderator to be able to append to this reply.
HTML
,'#nnf_error-name-delete'		=> <<<HTML
			Enter the name of either the original author or a moderator to be able to delete.
HTML
,'#nnf_error-pass'			=> <<<HTML
			Enter a password. It’s so you can re-use your name each time.
HTML
,'#nnf_error-pass-append'		=> <<<HTML
			Enter the password for either the original author or a moderator to be able to append to this reply
HTML
,'#nnf_error-pass-delete'		=> <<<HTML
			Enter the password for either the original author or a moderator to be able to delete.
HTML
,'#nnf_error-text'			=> <<<HTML
			Well, write a message!
HTML
,'#nnf_error-auth'			=> <<<HTML
			That name is taken. Provide the password for it, or choose another name. (password typo?)
HTML
,'#nnf_error-auth-append'		=> <<<HTML
			Name / password mismatch! You must enter the name and password of either the original author,
			or a designated moderator.
HTML
,'#nnf_error-auth-delete'		=> <<<HTML
			Name / password mismatch! You must enter the name and password of either the original author,
			or a designated moderator.
HTML
,'#protip'				=> <<<HTML
			Pro tip: Use <a href="/markup.php">markup</a> to add links, quotes and more.
HTML

,'label@for="nnf_text-field"'		=> 'Message:'
,'#nnf_text-field@placeholder'		=> 'Type your message here…'

/* markup page
   ---------------------------------------------------------------------------------------------------------------------- */
//the markup documentation:
,'/html[@class="markup"]/head/title'	=> 'Markup'
,'#markup'				=> <<<HTML

<h1>Markup</h1>
<article>
<header>
<ol>
	<li><a href="#links">» Links</a></li>
	<li><a href="#names">» Names</a></li>
	<li><a href="#bolditalic">» Bold &amp; italic</a></li>
	<li><a href="#titles">» Titles</a></li>
	<li><a href="#dividers">» Dividers</a></li>
	<li><a href="#quotes">» Quotes</a></li>
	<li><a href="#pre">» Monospace Text</a></li>
</ol>
</header>
	
<h2 id="links">Links:</h2>
<p>
	Clickable links will be created automatically on any web addresses in your text that begin with
	"<samp>http://</samp>", "<samp>https://</samp>", "<samp>ftp://</samp>" or "<samp>irc://</samp>".
	Also, e-mail addresses will be turned into clickable links for you.
</p>
<pre>
YES:	http://www.google.com
NO:	www.google.com
YES:	email@email.com
</pre>

<h2 id="names">Names:</h2>
<p>
	You can refer to another person by prefixing their name with an	at-symbol. E.g. "<samp>@bob</samp>"
	The name will link to the last reply made by that person in the current discussion thread.
</p>

<h2 id="bolditalic">Bold &amp; Italic</h2>
<pre>
*Write bold text like this*, and _italic text like this_.
</pre>


<h2 id="titles">Titles:</h2>
<p>
	For a title, start a line with two colons. Example:
</p>
<pre>
:: Shopping list
</pre>

<h2 id="dividers">Dividers:</h2>
<p>
	To draw a line across your text, use three or more dashes:<br />
	(this should be on its own line, with a blank line before and after)
</p>
<pre>
---
</pre>

<h2 id="quotes">Quotes:</h2>
<p>
	To quote somebody else's text, place it on its own line with quote marks at the beginning and end.
	This applies even if the quoted text is more than one paragraph, or contains quotes itself.
</p><p>
	There must be a blank line between any quote and other text:<br />
	(a single line-break will not work)
</p>
<pre>
YES:	This is my text
	
	"This is your text"
	
	This is my text
</pre>
<p>
	There must be no text before or after the quote marks:
</p>
<pre>
NO:	"This is your text".
</pre>
<p>
	However, spaces before or after are allowed. When you copy and paste someone else's quote,
	extra spaces might be included, these will be ignored.
</p>
<pre>
YES:	This is my text

	     "this is your text
	
	"
	
	This is my text
</pre>
<p>
	A quote may span more than one line or paragraph:
</p>
<pre>
YES:	This is my text
	
	"The quick brown fox
	jumped over the lazy dog
	
	Jackdaws love my big
	sphinx of quartz"
	
	This is my text
</pre>
<p>
	Quotes can contain quotes:
</p>
<pre>
YES:	"This is the first quote
	
	"This is the second quote"
	
	This is the first quote"
</pre>
<p>	
	You may use three different kinds of quote marks:<br />
	(but you can’t mismatch the ends)
</p>
<pre>
YES:	"Plain speech marks"

	“Curly quotes”
	
	«Guillemots»
</pre>
<p>
	Different kinds of quotes can be nested:
</p>
<pre>
YES:	"This is the first quote

	“This is the second quote
	
	«This is a third quote»”"
</pre>

<h2 id="pre">Monospace Text:</h2>
<p>
	For small snippets of code or technical writing you want to appear as-is admidst other writing you can use
	backticks (1 or more allowed) to enclose the monospace text; the text within will not be processed for bold /
	italic and other markup.
</p>
<pre>
Use `<samp>*bold*</samp>` for bold and ``<samp>_italic_</samp>`` for italics.
</pre>
<p>
	When posting, all unnecessary white-space is automatically removed. If you have some text that relies upon a
	monospace font (such as ASCII art), you can use a "code block".
</p><p>
	The code block begins with a percent sign, then your text (starting the next line),
	and then the ending percent sign on the next line.
</p>
<pre>
%
      __...--~~~~~-._   _.-~~~~~--...__
    //               `V'               \\ 
   //                 |                 \\ 
  //__...--~~~~~~-._  |  _.-~~~~~~--...__\\ 
 //__.....----~~~~._\ | /_.~~~~----.....__\\
====================\\|//====================
                dwb `---`
%
</pre>
<p>
	Note that there should be a blank line between the code block and any other text before or after:
</p><p>
	You can include a title after the first percent sign, for example when inserting source code snippets you can state
	the programming language used:
</p>
<pre>
% CSS
div	{color: red;}
%
</pre>
<p>
	Should you need to quote something that has percent symbols as the first character on a line, such as the LaTeX
	programming language, you can simply use the dollar sign "<samp>$</samp>" as a delimiter instead.
</p>

</article>
HTML

/* privacy policy page
   ---------------------------------------------------------------------------------------------------------------------- */
,'/html[@class="privacy"]/head/title'	=> 'Privacy Policy'
,'#privacy'				=> <<<HTML

<h1>Privacy Policy</h1>
<article>
<header>
<ol>
	<li><a href="#collected">» Information Collected</a></li>
	<li><a href="#stored">» Information Stored</a></li>
	<li><a href="#other">» Other Information</a></li>
</ol>
</header>

<p>
	This discussion forum is powered by <a href="http://camendesign.com/nononsense_forum">NoNonsense Forum</a>
	(here after “NNF”). As a forum, NNF is unique in what and the way it stores information,
	notably in what it <em>doesn’t</em> store, compared to other forums.
</p>

<h2 id="collected">Information Collected:</h2>
<p>
	The only information you are asked to provide is a name, password and the text that you wish to publish.
</p>

<h2 id="stored">Information Stored:</h2>
<ul>
	<li><p>
		The name and message you provide is stored as part of the discussion feed and made public.<br />
		The name can be any text and you do not have to use your real name or any identifying moniker
	</p></li>
	<li><p>
		The password you provide is one-way encrypted once received and stored only in this encrypted form —
		this means that NNF (and the site owner) does not know your password and cannot recover it
	</p></li>
	<li><p>
		If this forum provides selectable translations then changing the display language will set a
		<em>cookie</em> specifying your chosen language.
	</p><p>
		A <em>Cookie</em> is a small piece of text stored on your computer used to remember some kind of
		interaction with a website, in this case which language you have selected. NNF only uses cookies to
		remember the language selection and does not track you in any way. It is completely safe to delete the
		cookie
	</p></li>
</ul>

<h2 id="other">Other Information:</h2>
<ul>
	<li><p>
		Please note that NoNonsense Forum is free, open-source software, adaptable by anybody with sufficient
		knowledge; this forum may be collecting additional information beyond this privacy policy.
		The person who owns and operates this website may have additional privacy policies in place.
		Please contact the site owner with any questions
	</p></li>
	<li><p>
		Almost all websites collect visitor information, which typically includes details such as your I.P. address,
		time of access and web browser (also possibly operating system) identifying mark.
		These details are typically used for measuring traffic and finding faults. None of this information is
		collected by any part of NoNonsense Forum itself; again, contact the website owner with any questions
	</p></li>
	<li><p>
		The search feature is controlled by a third party (usually <a href="http://google.com">Google</a>),
		and will certainly collect information. Please check their website for their privacy policy
	</p></li>
</ul>

</article>
HTML

/* site footer
   ---------------------------------------------------------------------------------------------------------------------- */
,'#nnf_lock'				=> 'Lock'				//Lock link in 'thread.html'
,'#nnf_unlock'				=> 'Unlock'				//Unlock link in 'thread.html'

,'#nnf_mods-local'			=> <<<HTML
	Moderators for this sub-forum:
	<span id="nnf_mods-local-list"><b class="mod">Alice</b></span>
HTML
,'#nnf_mods'				=> <<<HTML
	Your friendly neighbourhood moderators:
	<span id="nnf_mods-list"><b class="mod">Bob</b></span>
HTML
,'#nnf_members'				=> <<<HTML
	Members of this forum:
	<span id="nnf_members-list"><b>Charlie</b></span>
HTML

,'/html/body/footer/p[1]'		=> <<<HTML
	Powered by <a href="http://camendesign.com/nononsense_forum">NoNonsense Forum</a><br />
	<a href="/privacy.php">privacy policy</a>
HTML
,'.nnf_signed-in'			=> <<<HTML
	Signed in as<br /><b class="nnf_signed-in-name">Kroc</b>
HTML
,'.nnf_signed-out/a'			=> 'Sign in'

);

?>