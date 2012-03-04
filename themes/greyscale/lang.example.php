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


*/

$LANG['en']['strings'] = array (

/* xpath/shorthand:			replacement text:			description:
   ====================================================================================================================== */
/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
//search box
 '#query@placeholder'			=> 'Google Search…'
,'#go@alt'				=> 'Search'

//breadcrumb / navigation links
,'#nnf_add'				=> 'Add Thread'				//the "add thread" link in 'index.html'
,'#nnf_reply'				=> 'Reply'				//the "reply" link in 'thread.html'
,'#nnf_rss'				=> 'RSS'				//the RSS link in the header
,'//*[@id="index"]/li[1]/a'		=> 'Index'				//breadcrumb root location

/* index page
   ---------------------------------------------------------------------------------------------------------------------- */
//list of sub-forums
,'//*[@id="nnf_folders"]/h1'		=> 'Sub-Forums'				//section title
,'.nnf_lock-threads@alt, '.
 '.nnf_lock-threads@title'		=> 'Replies-only:'			//alt+title of lock-icon, if thread-locked
,'.nnf_lock-posts@alt, '.
 '.nnf_lock-posts@title'		=> 'Read-only:'				//alt+title of lock-icon, if post-locked

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
,'//*[@id="nnf_threads"]/h1'		=> 'Threads'
,'.nnf_thread-locked@alt, '.
 '.nnf_thread-locked@title'		=> 'Locked:'

,'//*[@id="nnf_new-form"]/h1'		=> 'Add Thread'				//add thread form title
,'//*[@id="nnf_new-form"]'.
 '//label[@for="submit"]/span'		=> 'Submit'				//form submit button

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

,'//*[@id="nnf_replies"]/h1'		=> 'Replies'				//title for replies list

,'//*[@id="nnf_reply-form"]/h1'		=> 'Reply'				//reply form title
,'//*[@id="nnf_reply-form"]'.
 '//label[@for="submit"]/span'		=> 'Reply'				//form submit button

/* append page
   ---------------------------------------------------------------------------------------------------------------------- */
,'//*[@id="append"]/h1'			=> 'Append'				//append form title
,'//*[@id="append"]'.
 '//label[@for="submit"]/span'		=> 'Append'				//form submit button

/* delete page
   ---------------------------------------------------------------------------------------------------------------------- */
,'//*[@id="delete"]/h1'			=> 'Delete'				//delete form title

,'//label[@for="remove"]/span'		=> 'Remove completely (moderators only)'
,'//div[@id="nnf_remove"]/ul/li[1]'	=> 'The post will be removed completely from the thread, rather than blanked'
,'//div[@id="nnf_remove"]/ul/li[2]'	=> 'Only posts on the last page of the thread can be removed completely (so as to not break permalinks)'

,'//*[@id="delete"]'.
 '//label[@for="submit"]/span'		=> 'Delete'				//form submit button

/* input forms
   ---------------------------------------------------------------------------------------------------------------------- */
,'//label[@for="nnf_title-field"]'	=> 'Title:'				//title field label
,'#nnf_title-field@placeholder'		=> 'Type thread title here…'		//placeholder text for the title field

,'//label[@for="nnf_name-field-http"]'	=> 'You are signed in as:'		//label for name field if HTTP_AUTH
,'//label[@for="nnf_name-field"]'	=> 'Name:'				//name field label
,'#nnf_name-field@placeholder'		=> 'Your name'				//placeholder text for the name field

,'//label[@for="nnf_pass-field"]'	=> 'Password:'				//label for password field
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
,'#markup'				=> <<<HTML
			Pro tip: Use <a href="/markup.txt">markup</a> to add links, quotes and more.
HTML

,'//label[@for="nnf_text-field"]'	=> 'Message:'
,'#nnf_text-field@placeholder'		=> 'Type your message here…'

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
	© Kroc Camen of <a href="http://camendesign.com">Camen Design</a>
HTML
,'.nnf_signed-in'			=> <<<HTML
	Signed in as<br /><b class="nnf_signed-in-name">Kroc</b>
HTML
,'//*[@class="nnf_signed-out"]/a'	=> 'Sign in'

);

?>