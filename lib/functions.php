<?php //shared functions
/* ====================================================================================================================== */
/* NoNonsense Forum v22 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//formulate a URL (used to automatically fallback to non-pretty URLs when htaccess is not available),
//the domain is not included because it is not used universally throughout
function url ($action='index', $path='', $file='', $page=0, $id='', $signin=false) {
	$filepath = FORUM_PATH."$path$file";
	if (substr ($filepath, strlen (FORUM_PATH.PATH_URL)) == FORUM_PATH.PATH_URL)
		$filepath = substr ($filepath, strlen (FORUM_PATH.PATH_URL)+1)
	;
	
	//begin with the subfolder the forum is in, if any. all URLs must be absolute to be able to juggle the mix of
	//htaccess vs. no-htaccess + running in root vs. running in a sub-folder
	return HTACCESS
	//if htaccess is on, then use pretty URLs:
	?	$filepath.($page ? "+$page" : '').rtrim ('?'.implode ('&', array_filter (array (
		//single actions without any ID
		!$id && in_array ($action, array ('delete', 'lock', 'unlock'))  ? $action : '',
		//otherwise, actions with an ID?
		$id	? "$action=$id" : '',
		//signin link?
		$signin	? "signin" : ''
	))), '?')
	//if htaccess is off, fallback to real URLs:
	:	FORUM_PATH.
		//which page to point to; append / delete actions are a part of 'thread.php',
		//"delete" can be done without an ID (delete whole thread)
		($id || in_array ($action, array ('delete', 'lock', 'unlock')) ? 'thread.php?' : "$action.php?").
		//concatenate a query string
		implode ('&', array_filter (array (
			//actions without an ID
			!$id && in_array ($action, array ('delete', 'lock', 'unlock')) ? $action : '',
			//append or delete post
			$id	? "$action=$id" : '',
			//sub-forum? for no-htaccess, all links must be made relative from the NNF folder root
			'path='.$path,
			//if a file is specified (view thread, append, delete &c.)
			$file	? "file=$file" : '',
			//page number
			$page	? "page=$page" : '',
			//signin link?
			$signin	? "signin" : ''
		)))
	;
}

//the shared template stuff for all pages
function prepareTemplate (
	$filepath,	//template file to load
	$title=NULL,	//HTML title to use, if NULL, existing title is kept
	
	//these are used to create the signin link which points back to the same page but with the signin parameter added
	$action='index', $file='', $path='', $page=0, $id=''
) {
	global $LANG, $MODS, $MEMBERS;
	
	//load the template into DOM for manipulation. see 'domtemplate.php' for code and
	//<camendesign.com/dom_templating> for documentation of this object
	$template = new DOMTemplate (file_get_contents ($filepath));
	
	//fix all absolute URLs (i.e. if NNF is running in a folder):
	//(this also fixes the forum-title home link "/" when NNF runs in a folder)
	foreach ($template->query ('//*/@href, //*/@src, //*/@content') as $node) if ($node->nodeValue[0] == '/')
		//prepend the base path of the forum ('/' if on root, '/folder/' if running in a sub-folder)
		$node->nodeValue = FORUM_PATH.ltrim ($node->nodeValue, '/')
	;
	
	/* translate!
	   ---------------------------------------------------------------------------------------------------------------*/
	//before we start changing element content, we run through the language translation, if necessary;
	//if the current user-chosen language is in the list of available language translations for this theme,
	//execute the array of XPath string replacements in the translation. see the 'lang.*.php' files for details
	if (@$LANG[LANG]) $template->set ($LANG[LANG]['strings'], true)->setValue ('/html/@lang', LANG);
	//template the language chooser
	if (THEME_LANGS) {
		//the first item in the template should be your default language (mark it as selected if LANG is not blank)
		$item = $template->repeat ('.nnf_lang')->remove (array ('./@selected' => LANG))->next ();
		//build the list for each additional language
		foreach ($LANG as $code => $lang) $item->set (array (
			'./@value'	=> $code,
			'.'		=> $lang['name']
		))->remove (array (
			'./@selected'	=> !($code == LANG)
		))->next ();
	} else {
		$template->remove ('#nnf_lang');
	}
	
	/* HTML <head>
	   -------------------------------------------------------------------------------------------------------------- */
	//if no title is provided, the one already in the template remains (likely for translation purposes)
	if (!is_null ($title)) $template->setValue ('/html/head/title', $title);
	//remove 'custom.css' stylesheet if 'custom.css' is missing
	if (!file_exists (THEME_ROOT.'custom.css')) $template->remove ('//link[contains(@href,"custom.css")]');
	
	/* site header
	   -------------------------------------------------------------------------------------------------------------- */
	//site title
	$template->setValue ('.nnf_forum-name', FORUM_NAME);
	
	//are we in a sub-folder? if so, build the breadcrumb navigation
	if (PATH) for (
		//split the path by '/' to get each sub-forum
		$items = explode ('/', trim (PATH, '/')), $item = $template->repeat ('.nnf_breadcrumb'),
		$i = 0; $i < count ($items); $i++
	) $item->set (array (
		'a.nnf_subforum-name'		=> $items[$i],
		'a.nnf_subforum-name@href'	=> url ('index',
			//reconstruct the URL from each sub-forum up to the current one
			implode ('/', array_map ('safeURL', array_slice ($items, 0, $i+1))).'/'
		)
	))->next ();
	//not in a sub-folder? remove the breadcrumb navigation
	if (!PATH) $template->remove ('.nnf_breadcrumb');
	
	/* site footer
	   -------------------------------------------------------------------------------------------------------------- */
	//are there any local mods?	create the list of local mods
	if (!empty ($MODS['LOCAL'])):	$template->setValue ('#nnf_mods-local-list', theme_nameList ($MODS['LOCAL']), true);
				else:	$template->remove   ('#nnf_mods-local');	//remove the local mods list section
	endif;
	//are there any site mods?	create the list of mods
	if (!empty ($MODS['GLOBAL'])):	$template->setValue ('#nnf_mods-list', theme_nameList ($MODS['GLOBAL']), true);
				 else:	$template->remove   ('#nnf_mods');		//remove the mods list section
	endif;
	//are there any members?	create the list of members
	if (!empty ($MEMBERS)):		$template->setValue ('#nnf_members-list', theme_nameList ($MEMBERS), true);
			  else:		$template->remove   ('#nnf_members');		//remove the members list section
	endif;
	
	//set the name of the signed-in user
	$template->setValue ('.nnf_signed-in-name', NAME)->remove (
		//remove the relevant section for signed-in / out
		AUTH_HTTP ? '.nnf_signed-out' : '.nnf_signed-in'
	);
	
	//set the sign-in link
	$template->setValue ('.//a[@href="?signin"]/@href', url ($action, $path, $file, $page, $id, true));
	
	return $template;
}

/* ====================================================================================================================== */

//check to see if a name is a known moderator
function isMod ($name) {
	global $MODS;    return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}
//a member of a locked forum?
function isMember ($name) {
	global $MEMBERS; return in_array (strtolower ($name), array_map ('strtolower', $MEMBERS));
}

/* ====================================================================================================================== */

//sanitise input:
function safeGet ($data, $len=0, $trim=true) {
	//remove PHP’s auto-escaping of text (depreciated, but still on by default in PHP5.3)
	if (get_magic_quotes_gpc ()) $data = stripslashes ($data);
	//remove useless whitespace. can be skipped (i.e for passwords)
	if ($trim) $data = trim ($data);
	//clip the length in case of a fake crafted request
	return $len ? mb_substr ($data, 0, $len) : $data;
}

//sanitise output:
function safeHTML ($text) {
	//encode a string for insertion into an HTML element
	//(`ENT_XHTML` & `ENT_SUBSTITUTE` are PHP5.4+ only)
	return htmlspecialchars ($text, ENT_NOQUOTES | @ENT_XHTML | @ENT_SUBSTITUTE, 'UTF-8');
}
function safeURL ($text) {
	//encode a string to be used in a URL, keeping path separators
	//WARNING: this does not sanitise against HTML, it’s assumed text is passed through `safeHTML` before output
	return str_replace ('%2F', '/', rawurlencode ($text));
}

//safeTransliterate v2, copyright (cc-by 3.0) Kroc Camen <camendesign.com>
//generate a safe (a-z0-9_) string, for use as filenames or URLs, from an arbitrary string
function safeTransliterate ($text) {
	//if available, this function uses PHP5.4's transliterate, which is capable of converting arabic, hebrew, greek,
	//chinese, japanese and more into ASCII! however, we use our manual (and crude) fallback *first* instead because
	//we will take the liberty of transliterating some things into more readable ASCII-friendly forms,
	//e.g. "100℃" > "100degc" instead of "100oc"
	
	/* manual transliteration list:
	   -------------------------------------------------------------------------------------------------------------- */
	/* this list is supposed to be practical, not comprehensive, representing:
	   1. the most common accents and special letters that get typed, and
	   2. the most practical transliterations for readability;
	   
	   given that I know nothing of other languages, I will need your assistance to improve this list,
	   mail kroc@camendesign.com with help and suggestions.
	   
	   this data was produced with the help of:
	   http://www.unicode.org/charts/normalization/
	   http://www.yuiblog.com/sandbox/yui/3.3.0pr3/api/text-data-accentfold.js.html
	   http://www.utf8-chartable.de/
	*/
	static $translit = array (
		'a'	=> '/[ÀÁÂẦẤẪẨÃĀĂẰẮẴȦẲǠẢÅÅǺǍȀȂẠẬẶḀĄẚàáâầấẫẩãāăằắẵẳȧǡảåǻǎȁȃạậặḁą]/u',
		'b'	=> '/[ḂḄḆḃḅḇ]/u',			'c'	=> '/[ÇĆĈĊČḈçćĉċčḉ]/u',
		'd'	=> '/[ÐĎḊḌḎḐḒďḋḍḏḑḓð]/u',
		'e'	=> '/[ÈËĒĔĖĘĚȄȆȨḔḖḘḚḜẸẺẼẾỀỂỄỆèëēĕėęěȅȇȩḕḗḙḛḝẹẻẽếềểễệ]/u',
		'f'	=> '/[Ḟḟ]/u',				'g'	=> '/[ĜĞĠĢǦǴḠĝğġģǧǵḡ]/u',
		'h'	=> '/[ĤȞḢḤḦḨḪĥȟḣḥḧḩḫẖ]/u',		'i'	=> '/[ÌÏĨĪĬĮİǏȈȊḬḮỈỊiìïĩīĭįǐȉȋḭḯỉị]/u',
		'j'	=> '/[Ĵĵǰ]/u',				'k'	=> '/[ĶǨḰḲḴKķǩḱḳḵ]/u',
		'l'	=> '/[ĹĻĽĿḶḸḺḼĺļľŀḷḹḻḽ]/u',		'm'	=> '/[ḾṀṂḿṁṃ]/u',
		'n'	=> '/[ÑŃŅŇǸṄṆṈṊñńņňǹṅṇṉṋ]/u',
		'o'	=> '/[ÒŌŎŐƠǑǪǬȌȎȬȮȰṌṎṐṒỌỎỐỒỔỖỘỚỜỞỠỢØǾòōŏőơǒǫǭȍȏȭȯȱṍṏṑṓọỏốồổỗộớờởỡợøǿ]/u',
		'p'	=> '/[ṔṖṕṗ]/u',				'r'	=> '/[ŔŖŘȐȒṘṚṜṞŕŗřȑȓṙṛṝṟ]/u',
		's'	=> '/[ŚŜŞŠȘṠṢṤṦṨſśŝşšșṡṣṥṧṩ]/u',	'ss'	=> '/[ß]/u',
		't'	=> '/[ŢŤȚṪṬṮṰţťțṫṭṯṱẗ]/u',		'th'	=> '/[Þþ]/u',
		'u'	=> '/[ÙŨŪŬŮŰŲƯǓȔȖṲṴṶṸṺỤỦỨỪỬỮỰùũūŭůűųưǔȕȗṳṵṷṹṻụủứừửữựµ]/u',
		'v'	=> '/[ṼṾṽṿ]/u',				'w'	=> '/[ŴẀẂẄẆẈŵẁẃẅẇẉẘ]/u',
		'x'	=> '/[ẊẌẋẍ×]/u',			'y'	=> '/[ÝŶŸȲẎỲỴỶỸýÿŷȳẏẙỳỵỷỹ]/u',
		'z'	=> '/[ŹŻŽẐẒẔźżžẑẓẕ]/u',				
		//combined letters and ligatures:
		'ae'	=> '/[ÄǞÆǼǢäǟæǽǣ]/u',			'oe'	=> '/[ÖȪŒöȫœ]/u',
		'dz'	=> '/[ǄǅǱǲǆǳ]/u',
		'ff'	=> '/[ﬀ]/u',	'fi'	=> '/[ﬃﬁ]/u',	'ffl'	=> '/[ﬄﬂ]/u',
		'ij'	=> '/[Ĳĳ]/u',	'lj'	=> '/[Ǉǈǉ]/u',	'nj'	=> '/[Ǌǋǌ]/u',
		'st'	=> '/[ﬅﬆ]/u',	'ue'	=> '/[ÜǕǗǙǛüǖǘǚǜ]/u',
		//currencies:
		'eur'   => '/[€]/u',	'cents'	=> '/[¢]/u',	'lira'	=> '/[₤]/u',	'dollars' => '/[$]/u',
		'won'	=> '/[₩]/u',	'rs'	=> '/[₨]/u',	'yen'	=> '/[¥]/u',	'pounds'  => '/[£]/u',
		'pts'	=> '/[₧]/u',
		//misc:
		'degc'	=> '/[℃]/u',	'degf'  => '/[℉]/u',
		'no'	=> '/[№]/u',	'tm'	=> '/[™]/u'
	);
	//do the manual transliteration first
	$text = preg_replace (array_values ($translit), array_keys ($translit), $text);
	
	//flatten the text down to just a-z0-9 and dash, with underscores instead of spaces
	$text = preg_replace (
		//remove punctuation	//replace non a-z	//deduplicate	//trim underscores from start & end
		array ('/\p{P}/u',	'/[^_a-z0-9-]/i',	'/_{2,}/',	'/^_|_$/'),
		array ('',		'_',			'_',		''),
		
		//attempt transliteration with PHP5.4's transliteration engine (best):
		//(this method can handle near anything, including converting chinese and arabic letters to ASCII.
		// requires the 'intl' extension to be enabled)
		function_exists ('transliterator_transliterate') ? transliterator_transliterate (
			//split unicode accents and symbols, e.g. "Å" > "A°":
			'NFKD; '.
			//convert everything to the Latin charset e.g. "ま" > "ma":
			//(splitting the unicode before transliterating catches some complex cases,
			// such as: "㏳" >NFKD> "20日" >Latin> "20ri")
			'Latin; '.
			//because the Latin unicode table still contains a large number of non-pure-A-Z glyphs (e.g. "œ"),
			//convert what remains to an even stricter set of characters, the US-ASCII set:
			//(we must do this because "Latin/US-ASCII" alone is not able to transliterate non-Latin characters
			// such as "ま". this two-stage method also means we catch awkward characters such as:
			// "㏀" >Latin> "kΩ" >Latin/US-ASCII> "kO")
			'Latin/US-ASCII; '.
			//remove the now stand-alone diacritics from the string
			'[:Nonspacing Mark:] Remove; '.
			//change everything to lowercase; anything non A-Z 0-9 that remains will be removed by
			//the letter stripping above
			'Lower',
		$text)
		
		//attempt transliteration with iconv: <php.net/manual/en/function.iconv.php>
		: strtolower (function_exists ('iconv') ? str_replace (array ("'", '"', '`', '^', '~'), '', strtolower (
			//note: results of this are different depending on iconv version,
			//      sometimes the diacritics are written to the side e.g. "ñ" = "~n", which are removed
			iconv ('UTF-8', 'US-ASCII//IGNORE//TRANSLIT', $text)
		)) : $text)
	);
	
	//old iconv versions and certain inputs may cause a nullstring. don't allow a blank response
	return !$text ? '_' : $text;
}

//take the author's message, process markup, and encode it safely for the RSS feed
function formatText ($text, $rss=NULL) {
	//unify carriage returns between Windows / UNIX, and sanitise HTML against injection
	$text = safeHTML (preg_replace ('/\r\n?/', "\n", $text));
	
	/* preformatted text (code blocks):
	   -------------------------------------------------------------------------------------------------------------- */
	/* example:			or: (latex in partiular since it uses % as a comment marker)
	
		% title 		$ title
		⋮			⋮
		%			$
	*/
	$pre = array ();
	while (preg_match ('/^(?-s:(\h*)([%$])(.*?))\n(.*?)\n\h*\2(["”»]?)$/msu', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$pre[] = "<pre><span class=\"ct\">{$m[2][0]}{$m[3][0]}</span>\n"
			 //unindent code blocks that have been quoted
		         .(strlen ($m[1][0]) ? preg_replace ("/^\s{1,".strlen ($m[1][0])."}/m", '', $m[4][0]) : $m[4][0])
		         ."\n<span class=\"cb\">{$m[2][0]}</span></pre>"
		;
		//replace the code block with a placeholder:
		//(we will have to remove the code chunks from the source text to avoid the other markup processing from
		//munging it and then restore the chunks back later)
		$text = substr_replace ($text, "\n&__PRE__;".$m[5][0], $m[0][1], strlen ($m[0][0]));
	}
	
	/* inline code / teletype text:
	   -------------------------------------------------------------------------------------------------------------- */
	// example: `code` or ``code``
	$code = array ();
	while (preg_match ('/(?<=[\s\p{Z}\p{P}]|^)(`+)(.*?)(?<!`)\1(?!`)/m', $text, $m, PREG_OFFSET_CAPTURE)) {
		//format the code block
		$code[] = '<code>'.$m[1][0].$m[2][0].$m[1][0].'</code>';
		//same as with normal code blocks, replace them with a placeholder
		$text = substr_replace ($text, "&__CODE__;", $m[0][1], strlen ($m[0][0]));
	}
	
	/* hyperlinks:
	   -------------------------------------------------------------------------------------------------------------- */
	//find full URLs and turn into HTML hyperlinks. we also detect e-mail addresses automatically
	while (preg_match (
		'/(?:
			((?:(?:http|ftp)s?|irc)?:\/\/)			# $1 = protocol
		|	([a-z0-9\._%+\-]+@)				# $2 = email name
		)(							# $3 = friendly URL (no protocol)
			[-\.\p{L}\p{M}\p{N}]+				# domain (letters, diacritics, numbers & dash only)
			(?:\.[\p{L}\p{M}\p{N}]+)+			# TLDs (also letters, diacritics & numbers only)
		)(?(2)|							# email ends here
			(\/)?						# $4 = slash is excluded from friendly URL
			(?(4)(						# $5 = folders and filename, relative URL
				(?>					# folders and filename
					"(?!\/?&gt;|\s|$)|		# ignore the end of an HTML hyperlink
					\)(?![:\.,"”»]?(?:\s|$))|	# ignore brackets on end with punctuation
					[:\.,”»](?!\s|$)|		# ignore various characters on the end
					[^\s:)\.,"”»]			# the rest, including bookmark
				)*
			)?)
		)/xiu',
		//capture the starting point of the match, so that `$m[x][0]` is the text and $m[x][1] is the offset
		$text, $m, PREG_OFFSET_CAPTURE,
		//use an offset to search from so we don’t get stuck in an infinite loop
		//(this isn’t valid the first time around obviously so gives 0)
		@($m[0][1] + strlen ($replace))
		
	//replace the URL in the source text with a hyperlinked version:
	)) $text = substr_replace ($text, $replace =
		'<a href="'.(@$m[2][0]	? 'mailto:'.$m[2][0]			//is this an e-mail address?
					: ($m[1][0] ? $m[1][0] : 'http://'))	//has a protocol been given?
		//rest of the URL [domain . slash . everything-else]
		//(encode double-quotes without double-encoding existing ampersands; this is the PHP5.2.3 requirement)
		.htmlspecialchars ($m[3][0].@$m[4][0].@$m[5][0], ENT_COMPAT, 'UTF-8', false).'" rel="nofollow">'
		.$m[0][0].'</a>',
		//where to substitute
		$m[0][1], strlen ($m[0][0])
	);
	
	/* inline formatting:
	   -------------------------------------------------------------------------------------------------------------- */
	$text = preg_replace (
		//example: _italic_ & *bold*
		array ('/(?<=\s|^)_(?!_)(.*?)(?<!_)_(?=\s|$)/m',	'/(?<![*\w])\*(?!\*)(.*?)(?<!\*)\*(?![*\w])/'),
		array ('<em>_$1_</em>',					'<strong>*$1*</strong>'),
	$text);
	
	/* titles and dividers
	   -------------------------------------------------------------------------------------------------------------- */
	/* example: (titles)	/	(dividers)
		
		:: title		---
	*/
	$text = preg_replace(
		array ('/(?:\n|\A)(::.*)(?:\n?$|\Z)/mu',	'/(?:\n|\A)\h*(---+)\h*(?:\n?$|\Z)/m'),
		array ("\n\n<h2>$1</h2>\n",			"\n\n<p class=\"hr\">$1</p>\n"),
	$text);
	
	/* blockquotes:
	   -------------------------------------------------------------------------------------------------------------- */
	/* example:
	
		“this is the first quote level.
		
		“this is the second quote level.”
		
		back to the first quote level.”
	*/
	do $text = preg_replace (array (
		//you would think that you could combine these. you really would
		'/(?:\n|\A)\h*("(?!\s+)((?>(?1)|.)*?)\s*")\h*(?:\n?$|\Z)/msu',
		'/(?:\n|\A)\h*(“(?!\s+)((?>(?1)|.)*?)\s*”)\h*(?:\n?$|\Z)/msu',
		'/(?:\n|\A)\h*(«(?!\s+)((?>(?1)|.)*?)\s*»)\h*(?:\n?$|\Z)/msu'
	),	//extra quote marks are inserted in the spans for both themeing, and so that when you copy a quote, the
		//nesting is preserved for you. there must be a line break between spans and the text otherwise it prevents
		//the regex from finding quote marks at the ends of lines (these extra linebreaks are removed next)
		"\n\n<blockquote>\n\n<span class=\"ql\">&ldquo;</span>\n$2\n<span class=\"qr\">&rdquo;</span>\n\n</blockquote>\n",
		$text, -1, $c
	); while ($c);
	
	//remove the extra linebreaks addeded between our theme quotes
	//(required so that extra `<br />`s don’t get added!)
	$text = preg_replace (
		array ('/&ldquo;<\/span>\n(?!\n)/',	'/\n<span class="qr">/'),
		array ('&ldquo;</span>',		'<span class="qr">'),
	$text);
	
	/* name references:
	   -------------------------------------------------------------------------------------------------------------- */
	//name references (e.g. "@bob") will link back to the last reply in the thread made by that person.
	//this requires that the whole RSS thread is passed to this function to refer to
	if (!is_null ($rss)) {
		//first, produce a list of all authors in the thread
		$names = array ();
		foreach ($rss->channel->xpath ('./item/author') as $name) $names[] = $name[0];
		$names = array_map ('strtolower', $names);	//set all to lowercase
		$names = array_map ('safeHTML',   $names);	//HTML encode names as they will be in the source text
		$names = array_unique ($names);			//remove duplicates
		//sort the list of names Z-A so that longer names and names with spaces occur first,
		//this is so that we don’t choose "Bob" over "Bob Monkhouse" when matching names
		rsort ($names);
		
		//find all possible name references in the text:
		//(that is, any "@" followed by text up to the end of a line. note that this means that what might be
		//matched may include additional text that *isn't* part of the name, e.g. "@bob How are you?")
		$offset = 0; while (preg_match ('/(?:^|\s+)(@.+)/m', $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
			//check each of the known names in the thread and see if one fits the source text reference
			//e.g. does "@bob How are you?" begin with "bob"
			foreach ($names as $name) if (stripos ($m[1][0], $name) === 1)
				//locate the last post made by that author in the thread to link to
				foreach ($rss->channel->item as $item) if (safeHTML (strtolower ($item->author)) == $name)
			{	//replace the reference with the link to the post
				$text = substr_replace ($text,
					'<a href="'.$item->link.'">'.substr ($m[1][0], 0, strlen ($name)+1).'</a>',
					$m[1][1], strlen ($name)+1
				);
				//move on to the next reference, no need to check any further names for this one
				$offset = $m[1][1] + strlen ($name) + strlen ($item->link) + 15 + 1;
				break 2;
			}
			
			//failing any match, continue searching
			//(avoid getting stuck in an infinite loop)
			$offset = $m[1][1] + 1;
		};
	}
	
	/* finalise:
	   -------------------------------------------------------------------------------------------------------------- */
	//add paragraph tags between blank lines
	foreach (preg_split ('/\n{2,}/', trim ($text), -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
		//if not a blockquote, title, hr or pre-block, wrap in a paragraph
		if (!preg_match ('/^<\/?(?:bl|h2|p)|^&__PRE/', $chunk))
			$chunk = "<p>\n".str_replace ("\n", "<br />\n", $chunk)."\n</p>"
		;
		$text = @$result .= "\n$chunk";
	}
	
	//restore code blocks/spans
	foreach ($pre  as $html) $text = preg_replace ('/&__PRE__;/',  $html, $text, 1);
	foreach ($code as $html) $text = preg_replace ('/&__CODE__;/', $html, $text, 1);
	
	return $text;
}

/* ====================================================================================================================== */

//regenerate a folder's RSS file (all changes happening in a folder)
function indexRSS () {
	/* create an RSS feed
	   -------------------------------------------------------------------------------------------------------------- */
	$rss = new DOMTemplate (file_get_contents (FORUM_LIB.'rss-template.xml'));
	$rss->set (array (
		'/rss/channel/title'	=> FORUM_NAME.(PATH ? str_replace ('/', ' / ', PATH) : ''),
		'/rss/channel/link'	=> FORUM_URL.url ('index', PATH_URL)
	//remove the locked / deleted categories
	))->remove ('/rss/channel/category');
	
	//get list of threads, sort by date; most recently modified first
	$threads = preg_grep ('/\.rss$/', scandir ('.'));
	array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
	
	$items = $rss->repeat ('/rss/channel/item');
	//get the last post made in each thread as an RSS item
	foreach (array_slice ($threads, 0, FORUM_THREADS) as $thread)
		if ($xml  = @simplexml_load_file ($thread))	//if the RSS feed is valid
		if ($item = @$xml->channel->item[0])		//if the feed has any items
		$items->set (array (
			'./title'	=> $item->title,
			'./link'	=> $item->link,
			'./author'	=> $item->author,
			'./pubDate'	=> gmdate ('r', strtotime ($item->pubDate)),
			'./description'	=> $item->description
		))->remove (array (
			'./category[.="deleted"]' => !$item->xpath ('category[.="deleted"]'),
		))->next ()
	;
	file_put_contents ('index.xml', $rss->html ());
	
	/* sitemap
	   -------------------------------------------------------------------------------------------------------------- */
	//we’re going to use the RSS files as sitemaps
	chdir (FORUM_ROOT);
	
	//get list of sub-forums and include the root too
	$folders = array ('') + array_filter (
		//include only directories, but ignore directories starting with ‘.’ and the users / themes folders
		preg_grep ('/^(\.|users$|themes$|lib$)/', scandir (FORUM_ROOT.DIRECTORY_SEPARATOR), PREG_GREP_INVERT),
		'is_dir'
	);
	
	//start the XML file. this template has an XMLNS, so we have to prefix all our XPath queries :(
	$xml = new DOMTemplate (
		file_get_contents (FORUM_LIB.'sitemap-template.xml'),
		array ('x' => 'http://www.sitemaps.org/schemas/sitemap/0.9')
	);
	
	//generate a sitemap index file, to point to each index RSS file in the forum:
	//<https://www.google.com/support/webmasters/bin/answer.py?answer=71453>
	$sitemap = $xml->repeat ('//x:sitemap');
	foreach ($folders as $folder)
		//get the time of the latest item in the RSS feed
		//(the RSS feed may be missing as they are not generated in new folders until something is posted)
		if (@$rss = simplexml_load_file (
			FORUM_ROOT.($folder ? DIRECTORY_SEPARATOR.$folder : '').DIRECTORY_SEPARATOR.'index.xml'
		))
		//if you delete the last thread in a folder, there won’t be anything in the RSS index file!
		if (@$rss->channel->item[0]) $sitemap->set (array (
			'./x:loc'	=> FORUM_URL.($folder ? safeURL ("/$folder", false) : '').'/index.xml',
			'./x:lastmod'	=> gmdate ('r', strtotime ($rss->channel->item[0]->pubDate))
		))->next ()
	;
	file_put_contents (FORUM_ROOT.DIRECTORY_SEPARATOR.'sitemap.xml', $xml->html ());
	
	//you saw nothing, right?
	clearstatcache ();
}

?>