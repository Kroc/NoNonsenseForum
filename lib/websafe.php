<?php

/* making strings web safe: a library for PHP 5.2+
   v1 copyright © Kroc Camen <kroc@camendesign.com> 2012, licenced under Creative Commons Attribution 3.0 licence
   you may do whatever you want with this code as long as you give credit
   special thanks to Zegnat for help and support with UTF-8
*//* 
   who / what is the web safe library for?
   ====================================================================================================================== */
/* this set of functions applies to all developers of all skill levels, but especially those new to PHP

   in an ideal world there would be a programming language that had separate String types for HTML strings and SQL strings
   and plain-text strings and therefore any time the programmer joined or manipulated strings, the proper escaping would
   happen behind the scenes and no matter how uneducated on safety the programmer was, the output would, without fail, be
   safe -- but, since we live in 2013 and nobody has yet thought that it would be a good idea to make a programming
   language that actually understood that there was this thing out there called the World Wide Web and that it's actually
   quite popular and that, if you don't escape things properly, bad things happen -- we must instead fret over every input
   and output just like the days before buffer overflow protections in C/C++
   
   the web safe library therefore provides *help* (but only where the developer is wise enough to use it) in making sure
   your inputs are safe to begin with and that when you output to HTML, some nasty won't manage to flow through your code,
   tucked away in a string, and land on the page intact & dangerous
*/


/* pre-emptive measures
   ====================================================================================================================== */
/* UTF-7 XSS protection
   ---------------------------------------------------------------------------------------------------------------------- */
//failure to explicitly define a character set, either by HTTP header or meta tag, can result in IE defaulting to UTF-7
//encoding, which can be exploited: <openmya.hacker.jp/hasegawa/public/20071107/s6/h6.html?file=datae.txt>
header ('Content-Type: text/html; charset=UTF-8');

/* Preprocess the super globals
   ---------------------------------------------------------------------------------------------------------------------- */
//TODO: preprocess $GLOBALS with safeUTF8 so that all potential input is santisied from the outset


/* begin web safe functions
   ====================================================================================================================== */
/* safeUTF8 : ensure any text given comes out as web-safe UTF-8
   ---------------------------------------------------------------------------------------------------------------------- */
function safeUTF8 ($text) {
	//what's given could be any imaginable encoding, normalise it into UTF-8 though it may not yet be web-safe
	//adapted from <php.net/mb_check_encoding#89286>, with thanks to Zegnat. this works by importing the current byte
	//stream into UTF-32 which has enough scope to contain any other encoding, then downsizing in to UTF-8
	$text = mb_convert_encoding (mb_convert_encoding ($text, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32');
	
	//remove Unicode bytes unsafe for XML: <www.w3.org/TR/REC-xml/#charsets>
	$text = preg_replace (
		//remove everything except:
		//#9: TAB / #A: LF / #D: CR / #20-#D7FF ASCII and main Unicode characters
		//#E000-#FFFD: upper Unicode space, excluding the Byte-Order-Marks
		//#10000-#10FFFF: extended Unicode space (mostly empty, but harmless)
		'/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '',
	$text);
	
	//remove "compatibility characters" and "permenantly undefined Unicode characters",
	//see note proceeding: <www.w3.org/TR/REC-xml/#charsets>
	$text = preg_replace (
		'/[\x{007f}-\x{0084}\x{0086}-\x{009f}\x{FDD0}-\x{FDEF}'.
		  '\x{1FFFE}\x{1FFFF}\x{2FFFE}\x{2FFFF}\x{3FFFE}\x{3FFFF}\x{4FFFE}\x{4FFFF}'.
		  '\x{5FFFE}\x{5FFFF}\x{6FFFE}\x{6FFFF}\x{7FFFE}\x{7FFFF}\x{8FFFE}\x{8FFFF}'.
		  '\x{9FFFE}\x{9FFFF}\x{AFFFE}\x{AFFFF}\x{BFFFE}\x{BFFFF}\x{CFFFE}\x{CFFFF}'.
		  '\x{DFFFE}\x{DFFFF}\x{EFFFE}\x{EFFFF}\x{FFFFE}\x{FFFFF}\x{10FFFE}\x{10FFFF}]+/u', '',
	$text);
	
	//TODO: strip invalid byte-sequences
	//see: http://stackoverflow.com/questions/8215050/replacing-invalid-utf-8-characters-by-question-marks-mbstring-substitute-charac/13695364#13695364
	
	//Some interesting references:
	//http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
	
}

/* safeGet : sanatise form input
   ---------------------------------------------------------------------------------------------------------------------- */
//TODO: this function can pretty much be removed in favour of pre-processing the $_REQUEST with `stripslashes`
function safeGet ($data, $len=0, $trim=true) {
	//remove PHP’s auto-escaping of text (depreciated, but still on by default in PHP5.3)
	if (get_magic_quotes_gpc ()) $data = stripslashes ($data);
	//remove useless whitespace. can be skipped (i.e for passwords).
	if ($trim) $data = safeTrim ($data);
	//clip the length in case of a fake crafted request
	return $len ? mb_substr ($data, 0, $len) : $data;
}

/* safeTrim : trim *all* kinds of whitespace, not just the ASCII space / tab / CRLF!
   ---------------------------------------------------------------------------------------------------------------------- */
function safeTrim ($text) {
	//PHP `trim` doesn't cover a wide variety of Unicode; the private-use area is left, should the Apple logo be used
	//<nadeausoftware.com/articles/2007/9/php_tip_how_strip_punctuation_characters_web_page#Unicodecharactercategories>
	return preg_replace ('/^[\pZ\p{Cc}\p{Cf}\p{Cn}\p{Cs}]+|[\pZ\p{Cc}\p{Cf}\p{Cn}\p{Cs}]+$/u', '', $text);
}

/* safeHTML : encode a string for insertion into an HTML element
   ---------------------------------------------------------------------------------------------------------------------- */
function safeHTML ($text) {
	//note that `ENT_XHTML` & `ENT_SUBSTITUTE` are PHP5.4+ only
	return htmlspecialchars ($text, ENT_NOQUOTES | @ENT_XHTML | @ENT_SUBSTITUTE, 'UTF-8');
}

/* safeURL : encode a string to be used in a URL, keeping path separators
   ---------------------------------------------------------------------------------------------------------------------- */
//WARNING: this does not sanitise against HTML, it’s assumed text is passed through `safeHTML` before output
function safeURL ($text) {
	return str_replace ('%2F', '/', rawurlencode ($text));
}

/* safeTransliterate : generate a safe (a-z0-9_) string, for use as filenames or URLs, from an arbitrary string
   ---------------------------------------------------------------------------------------------------------------------- */
function safeTransliterate ($text) {
	//if available, this function uses PHP5.4's transliterater, which is capable of converting arabic, hebrew, greek,
	//chinese, japanese and more into ASCII! however, we use our manual (and crude) fallback *first* instead because
	//we will take the liberty of transliterating some things into more readable ASCII-friendly forms,
	//e.g. "100℃" > "100degc" instead of "100oc"
	
	/* manual transliteration list:
	   -------------------------------------------------------------------------------------------------------------- */
	/* this list is supposed to be practical, not comprehensive, representing:
	   1. the most common accents and special letters that get typed, and
	   2. the most practical transliterations for readability;
	   
	   given that I know nothing of other languages, I will need your assistance to improve this list,
	   mail <kroc@camendesign.com> with help and suggestions.
	   
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
		'no'	=> '/[№]/u',	'-tm'	=> '/[™]/u'
	);
	//do the manual transliteration first
	$text = preg_replace (array_values ($translit), array_keys ($translit), $text);
	
	//flatten the text down to just a-z0-9 underscore and dash for spaces
	//(<www.mattcutts.com/blog/dashes-vs-underscores/>)
	$text = preg_replace (
		//replace non a-z		//deduplicate	//trim from start & end
		array ('/[^_a-z0-9-]/i',	'/-{2,}/',	'/^-|-$/'),
		array ('-',			'-',		''       ),
		
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

?>