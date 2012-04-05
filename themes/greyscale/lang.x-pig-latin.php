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

$LANG['x-pig-latin']['name']    = 'Igpay Atinlay';
$LANG['x-pig-latin']['strings'] = array (

/* xpath/shorthand:			replacement text:			description:
   ====================================================================================================================== */
/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
//search box
 '#query@placeholder'			=> 'Ooglegay Earchsay…'			//default text shown in search box
,'#go@alt'				=> 'Earchsay'				//search submit button

//language selector
,'#nnf_lang/img@alt, '.
 '#nnf_lang/img@title'			=> 'Anguagelay:'			//language chooser icon
,'#nnf_lang/input@alt'			=> 'Etsay anguagelay'			//language chooser submit button

//breadcrumb
,'#index/li[1]/a'			=> 'Indexway'				//breadcrumb root location

/* index page
   ---------------------------------------------------------------------------------------------------------------------- */
//list of sub-forums
,'#nnf_folders/h1'			=> 'Ubsay-Orumsfay'			//section title
,'.nnf_lock-threads@alt, '.
 '.nnf_lock-threads@title'		=> 'Epliesray-onlyway:'			//alt+title of lock-icon, if thread-locked
,'.nnf_lock-posts@alt, '.
 '.nnf_lock-posts@title'		=> 'Eadray-onlyway:'			//alt+title of lock-icon, if post-locked

//navigation links
,'#nnf_add'				=> 'Addway Read’thay'			//the "add thread" link in 'index.html'
,'#nnf_reply'				=> 'Eplyray'				//the "reply" link in 'thread.html'
,'#nnf_rss'				=> 'RaSaySay'				//the RSS link in the header

//access rights
,'#nnf_forum-lock-threads'		=> <<<HTML
	Onlyway <a href="#mods">oderatorsmay orway embersmay</a> ancay art’stay ewnay reads’thay erehay
	[<a href="?signin">Ignsay-inway</a>], utbay <em>anyway-odybay</em> ancay eplyray otay existingway reads’thay.
HTML
,'#nnf_forum-lock-posts'		=> <<<HTML
	Onlyway <a href="#mods">oderatorsmay orway embersmay</a> ancay articipatepay erehay.
	<a href="?signin">Ignsay-inway</a> ifway ouyay areway away oderatormay orway embermay inway orderway otay ostpay.
HTML

//list of threads
,'#nnf_threads/h1'			=> 'Reads’thay'
,'.nnf_thread-locked@alt, '.
 '.nnf_thread-locked@title'		=> 'Ockedlay:'

,'#nnf_new-form/h1'			=> 'Addway Read’thay'			//add thread form title
,'#nnf_new-form/form/p/'.
 'label@for="submit"/span'		=> 'Ubmitsay'				//form submit button

/* thread page
   ---------------------------------------------------------------------------------------------------------------------- */
,'#nnf_post-append, #nnf_post-append, '.
 '.nnf_reply-append, .nnf_reply-append'	=> 'appendway'				//"append" link in posts
,'#nnf_post-delete, #nnf_post-delete, '.
 '.nnf_reply-delete, .nnf_reply-delete'	=> 'eleteday'				//"delete" link in posts

,'.nnf_forum-locked'			=> <<<HTML
	Onlyway <a href="#mods">oderatorsmay orway embersmay</a> ancay eplyray otay histay read’thay.
	<a href="?signin">Ignsay-inway</a> ifway ouway areway away oderatormay orway embermay inway orderway otay ostpay.
HTML

,'#nnf_replies/h1'			=> 'Epliesray'				//title for replies list

,'#nnf_reply-form/h1'			=> 'Eplyray'				//reply form title
,'#nnf_reply-form/form/p/'.
 'label@for="submit"/span'		=> 'Eplyray'				//form submit button

/* append page
   ---------------------------------------------------------------------------------------------------------------------- */
,'#append/h1'				=> 'Appendway'				//append form title
,'#append/form/p/'.
 'label@for="submit"/span'		=> 'Appendway'				//form submit button

/* delete page
   ---------------------------------------------------------------------------------------------------------------------- */
,'#delete/h1'				=> 'Eleteday'				//delete form title

,'label@for="remove"/span'		=> 'Emoveray ompletelycay (oderatorsmay onlyway)'
,'#nnf_remove/ul/li[1]'			=> 'Hetay ostpay illway ebay emovedray ompletelycay romfay hetay read’thay, atherray hantay lankedbay'
,'#nnf_remove/ul/li[2]'			=> 'Onlyway ostspay onway hetay astlay agepay ofway hetay read’thay ancay ebay emovedray ompletelycay (osay asway otay otnay reakbay ermapay-inkslay)'

,'#delete/form/p/'.
 'label@for="submit"/span'		=> 'Eleteday'				//form submit button

/* input forms
   ---------------------------------------------------------------------------------------------------------------------- */
,'label@for="nnf_title-field"'		=> 'Itletay:'				//title field label
,'#nnf_title-field@placeholder'		=> 'Ypetay read’thay itletay erehay…'	//placeholder text for the title field

,'label@for="nnf_name-field-http"'	=> 'Ignedsay inway asway:'		//label for name field if HTTP_AUTH
,'label@for="nnf_name-field"'		=> 'Amenay:'				//name field label
,'#nnf_name-field@placeholder'		=> 'Ouryay amenay'			//placeholder text for the name field

,'label@for="nnf_pass-field"'		=> 'Asspay-ordway:'			//label for password field
,'#nnf_pass-field@placeholder'		=> 'Asspay-ordway orfay amenay'

//this is shown by default as long as new users aren't disabled and the user isn't signed in
,'#nnf_error-none'			=> <<<HTML
			Heretay isway onay eednay otay “egisterray”, ustjay enterway hetay amesay amenay + asspay-ordway
			ofway ouryay oice’chay everyway imetay.
HTML
//this is shown by default if the user is signed in
,'#nnf_error-none-http'			=> <<<HTML
			(It’quay ouryay owser’bray orway ear’clay hetay owser’bray achecay otay ignsay outway.)
HTML
,'#nnf_error-none-append'		=> <<<HTML
			Onlyway hetay originalway authorway orway away oderatormay ancay appendway otay histay ostpay.
HTML
,'#nnf_error-none-thread'		=> <<<HTML
			Otay eleteday histay read’thay, andway allway epliesray otay itway, ouyay ustmay ebay eitherway
			hetay originalway authorway orway away esignatedday oderatormay.
HTML
,'#nnf_error-none-reply'		=> <<<HTML
			Otay eleteday histay ostpay ouyay ustmay ebay eitherway hetay originalway authorway orway away
			esignatedday oderatormay.<br />Hetay ontentcay ofway hetay ostpay illway ebay emovedray utbay hetay
			amenay andway ateday illway emainray.
HTML
//if the `FORUM_NEWBIES` option is false, only existing users can post
,'#nnf_error-newbies'			=> <<<HTML
			Onlyway egisteredray usersway ancay ostpay.<br />
			Onay ewnay egistrationsray areway allowedway.
HTML
,'#nnf_error-title'			=> <<<HTML
			Ouyay eednay otay enterway hetay itletay ofway ouryay ewnay iscussionday read’thay.
HTML
,'#nnf_error-name'			=> <<<HTML
			Enterway away amenay. Ou’llyay eednay otay useway histay ithway hetay asspay-ordway eachway imetay.
HTML
,'#nnf_error-name-append'		=> <<<HTML
			Enterway hetay amenay ofway eitherway hetay originalway authorway orway away oderatormay otay ebay
			ableway otay appendway otay histay eplyray.
HTML
,'#nnf_error-name-delete'		=> <<<HTML
			Enterway hetay amenay ofway eitherway hetay originalway authorway orway away oderatormay otay ebay
			ableway otay eleteday.
HTML
,'#nnf_error-pass'			=> <<<HTML
			Enterway away asspay-ordway. It’sway osay ouyay ancay eray-useway ouryay amenay eachway imetay.
HTML
,'#nnf_error-pass-append'		=> <<<HTML
			Enterway hetay asspay-ordway orfay eitherway hetay originalway authorway orway away oderatormay
			otay ebay ableway otay appendway otay histay eplyray
HTML
,'#nnf_error-pass-delete'		=> <<<HTML
			Enterway hetay asspay-ordway orfay eitherway hetay originalway authorway orway away oderatormay
			otay ebay ableway otay eleteday.
HTML
,'#nnf_error-text'			=> <<<HTML
			Ellway, ite’wray away essagemay!
HTML
,'#nnf_error-auth'			=> <<<HTML
			Hattay amenay isway akentay. Ovide’pray hetay asspay-ordway orfay itway, orway oose’chay anotherway
			amenay. (asspay-ordway ypotay?)
HTML
,'#nnf_error-auth-append'		=> <<<HTML
			Amenay / asspay-ordway ismay-atchmay! Enterway hetay amenay andway asspay-ordway ofway hetay
			originalway authorway orway away oderatormay.
HTML
,'#nnf_error-auth-delete'		=> <<<HTML
			Amenay / asspay-ordway ismay-atchmay! Enterway hetay amenay andway asspay-ordway ofway hetay
			originalway authorway orway away oderatormay.
HTML
,'#protip'				=> <<<HTML
			O’pray iptay: Useway <a href="/markup.txt">arkmay-upway</a> otay addway inkslay, otes’quay andway
			oremay.
HTML

,'label@for="nnf_text-field"'		=> 'Essagemay:'
,'#nnf_text-field@placeholder'		=> 'Ypetay ouryay essagemay erehay…'

/* markup page
   ---------------------------------------------------------------------------------------------------------------------- */
//the markup documentation:
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

/* site footer
   ---------------------------------------------------------------------------------------------------------------------- */
,'#nnf_lock'				=> 'Ocklay'				//Lock link in 'thread.html'
,'#nnf_unlock'				=> 'Unway-ocklay'			//Unlock link in 'thread.html'

,'#nnf_mods-local'			=> <<<HTML
	Oderatorsmay orfay histay ubsay-orumfay:
	<span id="nnf_mods-local-list"><b class="mod">Aliceway</b></span>
HTML
,'#nnf_mods'				=> <<<HTML
	Ouryay riendlyfay eighbournay-oodhay oderatorsmay:
	<span id="nnf_mods-list"><b class="mod">Obbay</b></span>
HTML
,'#nnf_members'				=> <<<HTML
	Embersmay ofway histay orumfay:
	<span id="nnf_members-list"><b>Arlie’chay</b></span>
HTML

,'/html/body/footer/p[1]'		=> <<<HTML
	Oweredpay ybay <a href="http://camendesign.com/nononsense_forum">OnayOnsensenay Orumfay</a><br />
	© RocKay AmenCay ofway <a href="http://camendesign.com">Amencay Esignday</a>
HTML
,'.nnf_signed-in'			=> <<<HTML
	Ignedsay inway asway<br /><b class="nnf_signed-in-name">Rockay</b>
HTML
,'@class="nnf_signed-out"/a'		=> 'Ignsay inway'

);

?>