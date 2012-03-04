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

$LANG['x-pig-latin']['strings'] = array (

/* xpath/shorthand:			replacement text:			description:
   ====================================================================================================================== */
/* site header
   ---------------------------------------------------------------------------------------------------------------------- */
//search box
 '#query@placeholder'			=> 'Ooglegay Earchsay…'
,'#go@alt'				=> 'Earchsay'

//breadcrumb / navigation links
,'#nnf_add'				=> 'Addway Read’thay'			//the "add thread" link in 'index.html'
,'#nnf_reply'				=> 'Eplyray'				//the "reply" link in 'thread.html'
,'#nnf_rss'				=> 'RaySaySay'				//the RSS link in the header
,'//*[@id="index"]/li[1]/a'		=> 'Indexway'				//breadcrumb root location

/* index page
   ---------------------------------------------------------------------------------------------------------------------- */
//list of sub-forums
,'//*[@id="nnf_folders"]/h1'		=> 'Ubsay-Orumsfay'			//section title
,'.nnf_lock-threads@alt, '.
 '.nnf_lock-threads@title'		=> 'Epliesray-onlyway:'			//alt+title of lock-icon, if thread-locked
,'.nnf_lock-posts@alt, '.
 '.nnf_lock-posts@title'		=> 'Eadray-onlyway:'			//alt+title of lock-icon, if post-locked

//access rights
,'#nnf_forum-lock-threads'		=> <<<HTML
	Onlyway <a href="#mods">oderatorsmay orway embersmay</a> ancay art’stay ewnay reads’thay erehay
	[<a href="?signin">Ignsay-inway</a>], utbay <em>anyway-odybay</em> anay eplyray otay existingway reads’thay.
HTML
,'#nnf_forum-lock-posts'		=> <<<HTML
	Onlyway <a href="#mods">oderatorsmay orway embersmay</a> ancay articipatepay erehay.
	<a href="?signin">Ignsay-inway</a> ifway ouyay areway away oderatormay orway embermay inway orderway otay ostpay.
HTML

//list of threads
,'//*[@id="nnf_threads"]/h1'		=> 'Reads’thay'
,'.nnf_thread-locked@alt, '.
 '.nnf_thread-locked@title'		=> 'Ockedlay:'

,'//*[@id="nnf_new-form"]/h1'		=> 'Addway Read’thay'			//add thread form title
,'//*[@id="nnf_new-form"]'.
 '//label[@for="submit"]/span'		=> 'Ubmitsay'				//form submit button

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

,'//*[@id="nnf_replies"]/h1'		=> 'Epliesray'				//title for replies list

,'//*[@id="nnf_reply-form"]/h1'		=> 'Eplyray'				//reply form title
,'//*[@id="nnf_reply-form"]'.
 '//label[@for="submit"]/span'		=> 'Eplyray'				//form submit button

/* append page
   ---------------------------------------------------------------------------------------------------------------------- */
,'//*[@id="append"]/h1'			=> 'Appendway'				//append form title
,'//*[@id="append"]'.
 '//label[@for="submit"]/span'		=> 'Appendway'				//form submit button

/* delete page
   ---------------------------------------------------------------------------------------------------------------------- */
,'//*[@id="delete"]/h1'			=> 'Eleteday'				//delete form title

,'//label[@for="remove"]/span'		=> 'Emoveray ompletelycay (oderatorsmay onlyway)'
,'//div[@id="nnf_remove"]/ul/li[1]'	=> 'Hetay ostpay illway ebay emovedray ompletelycay romfay hetay read’thay, atherray hantay lankedbay'
,'//div[@id="nnf_remove"]/ul/li[2]'	=> 'Onlyway ostspay onway hetay astlay agepay ofway hetay read’thay ancay ebay emovedray ompletelycay (osay asway otay otnay reakbay ermapay-inkslay)'

,'//*[@id="delete"]'.
 '//label[@for="submit"]/span'		=> 'Eleteday'				//form submit button

/* input forms
   ---------------------------------------------------------------------------------------------------------------------- */
,'//label[@for="nnf_title-field"]'	=> 'Itletay:'				//title field label
,'#nnf_title-field@placeholder'		=> 'Ypetay read’thay itletay erehay…'	//placeholder text for the title field

,'//label[@for="nnf_name-field-http"]'	=> 'Ouyay areway ignedsay inway asway:'	//label for name field if HTTP_AUTH
,'//label[@for="nnf_name-field"]'	=> 'Amenay:'				//name field label
,'#nnf_name-field@placeholder'		=> 'Ouryay amenay'			//placeholder text for the name field

,'//label[@for="nnf_pass-field"]'	=> 'Asspay-ordway:'			//label for password field
,'#nnf_pass-field@placeholder'		=> 'Away asspay-ordway otay eepkay ouryay amenay'

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
			Amenay / asspay-ordway ismay-atchmay! Ouyay ustmay enterway hetay amenay andway asspay-ordway ofway
			eitherway hetay originalway authorway, orway away esignatedday oderatormay.
HTML
,'#nnf_error-auth-delete'		=> <<<HTML
			Amenay / asspay-ordway ismay-atchmay! Ouyay ustmay enterway hetay amenay andway asspay-ordway ofway
			eitherway hetay originalway authorway, orway away esignatedday oderatormay.
HTML
,'#markup'				=> <<<HTML
			Ropay iptay: Useway <a href="/markup.txt">arkupmay</a> otay addway inkslay, otes’quay andway oremay.
HTML

,'//label[@for="nnf_text-field"]'	=> 'Essagemay:'
,'#nnf_text-field@placeholder'		=> 'Ypetay ouryay essagemay erehay…'

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
	© Rockay Amencay ofway <a href="http://camendesign.com">Amencay Esignday</a>
HTML
,'.nnf_signed-in'			=> <<<HTML
	Ignedsay inway asway<br /><b class="nnf_signed-in-name">Rockay</b>
HTML
,'//*[@class="nnf_signed-out"]/a'	=> 'Ignsay inway'

);

?>