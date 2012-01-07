<?php //DOM-based templating engine: <camendesign.com/dom_templating>
/* ====================================================================================================================== */
/* NoNonsense Forum v11 © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//DOM Templating classes v3 © copyright (cc-by) Kroc Camen of http://camendesign.com 2012
//documentation at http://camendesign.com/dom_templating
//you may do whatever you want with this code as long as you give credit

class DOMTemplate extends DOMTemplateNode {
	private $DOMDocument;
	
	public function __construct ($filepath) {
		$this->DOMDocument = new DOMDocument ();
		//load the template file to work with
		$this->DOMDocument->loadXML (
			static::html_entity_decode (file_get_contents ($filepath)), LIBXML_COMPACT | LIBXML_NONET
		) or trigger_error (
			"Template '$filepath' is invalid XML", E_USER_ERROR
		);
		//set the parent node for all xpath searching
		//(handled all internally by `DOMTemplateNode`)
		parent::__construct ($this->DOMDocument->documentElement);
		
		//fix all absolute URLs (i.e. if NNF is running in a folder):
		//(this also fixes the forum-title home link "/" when NNF runs in a folder)
		foreach ($this->xpath ('xpath://*/@href|//*/@src') as $node) if ($node->nodeValue[0] == '/')
			//prepend the base path of the forum ('/' if on root, '/folder/' if running in a sub-folder)
			$node->nodeValue = FORUM_PATH.ltrim ($node->nodeValue, '/')
		;
	}
	
	//output the complete HTML
	public function html () {
		//remove remaining `data-template` attributes
		$this->remove ('xpath://@data-template');
		//fix and clean DOM's XML output:
		return preg_replace (array (
			'/^<\?xml.*?>\n/',				//1: remove XML prolog
			'/<(.*?[^ ])\/>/s',				//2: add space to self-closing
			'/<(div|[ou]l|textarea)(.*?) ?\/>/'		//3: fix broken self-closed tags
		), array (
			'',
			'<$1 />',
			'<$1$2></$1>'
		), $this->DOMDocument->saveXML ());
	}
}

//these functions are shared between the base `DOMTemplate` and the repeater `DOMTemplateRepeater`,
//the DOM/XPATH voodoo is encapsulated here
class DOMTemplateNode {
	protected $DOMNode;
	private   $DOMXPath;
	
	//because everything is XML, HTML named entities like "&copy;" will cause blank output.
	//we need to convert these named entities back to real UTF-8 characters (which XML doesn’t mind)
	//'&', '<' and '>' are exlcuded so that we don’t turn user text into working HTML!
	public static $entities = array (
		//I hope your text editor is decent...
		'&nbsp;'	=> ' ', '&iexcl;'	=> '¡', '&cent;'	=> '¢', '&pound;'	=> '£',
		'&curren;'	=> '¤', '&yen;'		=> '¥', '&brvbar;'	=> '¦', '&sect;'	=> '§',
		'&uml;'		=> '¨', '&copy;'	=> '©', '&ordf;'	=> 'ª', '&laquo;'	=> '«',
		'&not;'		=> '¬', '&shy;'		=> '­', '&reg;'		=> '®', '&macr;'	=> '¯',
		'&deg;'		=> '°', '&plusmn;'	=> '±', '&sup2;'	=> '²', '&sup3;'	=> '³',
		'&acute;'	=> '´', '&micro;'	=> 'µ', '&para;'	=> '¶', '&middot;'	=> '·',
		'&cedil;'	=> '¸', '&sup1;'	=> '¹', '&ordm;'	=> 'º', '&raquo;'	=> '»',
		'&frac14;'	=> '¼', '&frac12;'	=> '½', '&frac34;'	=> '¾', '&iquest;'	=> '¿',
		'&Agrave;'	=> 'À', '&Aacute;'	=> 'Á', '&Acirc;'	=> 'Â', '&Atilde;'	=> 'Ã',
		'&Auml;'	=> 'Ä', '&Aring;'	=> 'Å', '&AElig;'	=> 'Æ', '&Ccedil;'	=> 'Ç',
		'&Egrave;'	=> 'È', '&Eacute;'	=> 'É', '&Ecirc;'	=> 'Ê', '&Euml;'	=> 'Ë',
		'&Igrave;'	=> 'Ì', '&Iacute;'	=> 'Í', '&Icirc;'	=> 'Î', '&Iuml;'	=> 'Ï',
		'&ETH;'		=> 'Ð', '&Ntilde;'	=> 'Ñ', '&Ograve;'	=> 'Ò', '&Oacute;'	=> 'Ó',
		'&Ocirc;'	=> 'Ô', '&Otilde;'	=> 'Õ', '&Ouml;'	=> 'Ö', '&times;'	=> '×',
		'&Oslash;'	=> 'Ø', '&Ugrave;'	=> 'Ù', '&Uacute;'	=> 'Ú', '&Ucirc;'	=> 'Û',
		'&Uuml;'	=> 'Ü', '&Yacute;'	=> 'Ý', '&THORN;'	=> 'Þ', '&szlig;'	=> 'ß',
		'&agrave;'	=> 'à', '&aacute;'	=> 'á', '&acirc;'	=> 'â', '&atilde;'	=> 'ã',
		'&auml;'	=> 'ä', '&aring;'	=> 'å', '&aelig;'	=> 'æ', '&ccedil;'	=> 'ç',
		'&egrave;'	=> 'è', '&eacute;'	=> 'é', '&ecirc;'	=> 'ê', '&euml;'	=> 'ë',
		'&igrave;'	=> 'ì', '&iacute;'	=> 'í', '&icirc;'	=> 'î', '&iuml;'	=> 'ï',
		'&eth;'		=> 'ð', '&ntilde;'	=> 'ñ', '&ograve;'	=> 'ò', '&oacute;'	=> 'ó',
		'&ocirc;'	=> 'ô', '&otilde;'	=> 'õ', '&ouml;'	=> 'ö', '&divide;'	=> '÷',
		'&oslash;'	=> 'ø', '&ugrave;'	=> 'ù', '&uacute;'	=> 'ú', '&ucirc;'	=> 'û',
		'&uuml;'	=> 'ü', '&yacute;'	=> 'ý', '&thorn;'	=> 'þ', '&yuml;'	=> 'ÿ',
		'&OElig;'	=> 'Œ', '&oelig;'	=> 'œ', '&Scaron;'	=> 'Š', '&scaron;'	=> 'š',
		'&Yuml;'	=> 'Ÿ', '&fnof;'	=> 'ƒ', '&circ;'	=> 'ˆ', '&tilde;'	=> '˜',
		'&Alpha;'	=> 'Α', '&Beta;'	=> 'Β', '&Gamma;'	=> 'Γ', '&Delta;'	=> 'Δ',
		'&Epsilon;'	=> 'Ε', '&Zeta;'	=> 'Ζ', '&Eta;'		=> 'Η', '&Theta;'	=> 'Θ',
		'&Iota;'	=> 'Ι', '&Kappa;'	=> 'Κ', '&Lambda;'	=> 'Λ', '&Mu;'		=> 'Μ',
		'&Nu;'		=> 'Ν', '&Xi;'		=> 'Ξ', '&Omicron;'	=> 'Ο', '&Pi;'		=> 'Π',
		'&Rho;'		=> 'Ρ', '&Sigma;'	=> 'Σ', '&Tau;'		=> 'Τ', '&Upsilon;'	=> 'Υ',
		'&Phi;'		=> 'Φ', '&Chi;'		=> 'Χ', '&Psi;'		=> 'Ψ', '&Omega;'	=> 'Ω',
		'&alpha;'	=> 'α', '&beta;'	=> 'β', '&gamma;'	=> 'γ', '&delta;'	=> 'δ',
		'&epsilon;'	=> 'ε', '&zeta;'	=> 'ζ', '&eta;'		=> 'η', '&theta;'	=> 'θ',
		'&iota;'	=> 'ι', '&kappa;'	=> 'κ', '&lambda;'	=> 'λ', '&mu;'		=> 'μ',
		'&nu;'		=> 'ν', '&xi;'		=> 'ξ', '&omicron;'	=> 'ο', '&pi;'		=> 'π',
		'&rho;'		=> 'ρ', '&sigmaf;'	=> 'ς', '&sigma;'	=> 'σ', '&tau;'		=> 'τ',
		'&upsilon;'	=> 'υ', '&phi;'		=> 'φ', '&chi;'		=> 'χ', '&psi;'		=> 'ψ',
		'&omega;'	=> 'ω', '&thetasym;'	=> 'ϑ', '&upsih;'	=> 'ϒ', '&piv;'		=> 'ϖ',
		'&ensp;'	=> ' ', '&emsp;'	=> ' ', '&thinsp;'	=> ' ', '&zwnj;'	=> '‌',
		'&zwj;'		=> '‍', '&lrm;'		=> '‎', '&rlm;'		=> '‏', '&ndash;'	=> '–',
		'&mdash;'	=> '—', '&lsquo;'	=> '‘', '&rsquo;'	=> '’', '&sbquo;'	=> '‚',
		'&ldquo;'	=> '“', '&rdquo;'	=> '”', '&bdquo;'	=> '„', '&dagger;'	=> '†',
		'&Dagger;'	=> '‡', '&bull;'	=> '•', '&hellip;'	=> '…', '&permil;'	=> '‰',
		'&prime;'	=> '′', '&Prime;'	=> '″', '&lsaquo;'	=> '‹', '&rsaquo;'	=> '›',
		'&oline;'	=> '‾', '&frasl;'	=> '⁄', '&euro;'	=> '€', '&image;'	=> 'ℑ',
		'&weierp;'	=> '℘', '&real;'	=> 'ℜ', '&trade;'	=> '™', '&alefsym;'	=> 'ℵ',
		'&larr;'	=> '←', '&uarr;'	=> '↑', '&rarr;'	=> '→', '&darr;'	=> '↓',
		'&harr;'	=> '↔', '&crarr;'	=> '↵', '&lArr;'	=> '⇐', '&uArr;'	=> '⇑',
		'&rArr;'	=> '⇒', '&dArr;'	=> '⇓', '&hArr;'	=> '⇔', '&forall;'	=> '∀',
		'&part;'	=> '∂', '&exist;'	=> '∃', '&empty;'	=> '∅', '&nabla;'	=> '∇',
		'&isin;'	=> '∈', '&notin;'	=> '∉', '&ni;'		=> '∋', '&prod;'	=> '∏',
		'&sum;'		=> '∑', '&minus;'	=> '−', '&lowast;'	=> '∗', '&radic;'	=> '√',
		'&prop;'	=> '∝', '&infin;'	=> '∞', '&ang;'		=> '∠', '&and;'		=> '∧',
		'&or;'		=> '∨', '&cap;'		=> '∩', '&cup;'		=> '∪', '&int;'		=> '∫',
		'&there4;'	=> '∴', '&sim;'		=> '∼', '&cong;'	=> '≅',	'&asymp;'	=> '≈',
		'&ne;'		=> '≠', '&equiv;'	=> '≡', '&le;'		=> '≤', '&ge;'		=> '≥',
		'&sub;'		=> '⊂', '&sup;'		=> '⊃', '&nsub;'	=> '⊄', '&sube;'	=> '⊆',
		'&supe;'	=> '⊇', '&oplus;'	=> '⊕', '&otimes;'	=> '⊗', '&perp;'	=> '⊥',
		'&sdot;'	=> '⋅', '&lceil;'	=> '⌈', '&rceil;'	=> '⌉', '&lfloor;'	=> '⌊',
		'&rfloor;'	=> '⌋', '&lang;'	=> '〈', '&rang;'	=> '〉', '&loz;'		=> '◊',
		'&spades;'	=> '♠', '&clubs;'	=> '♣', '&hearts;'	=> '♥', '&diams;'	=> '♦'
		/* BTW, if you have PHP 5.3.4+ you can produce this whole array with just two lines of code:
		
			$entities = array_flip (get_html_translation_table (HTML_ENTITIES, ENT_NOQUOTES, 'UTF-8'));
			unset ($entities['&'], $entities['<'], $entities['>']);
		
		   also, this list is *far* from comprehensive. see this page for the full list
		   http://www.whatwg.org/specs/web-apps/current-work/multipage/named-character-references.html */
	);
	public static function html_entity_decode ($html) {
		return str_replace (array_keys (static::$entities), array_values (static::$entities), $html);
	}
	
	public function __construct ($DOMNode) {
		//use a DOMNode as a base point for all the XPath queries and whatnot
		//(in DOMTemplate this will be the whole template, in DOMTemplateRepeater, it will be the chosen element)
		$this->DOMNode = $DOMNode;
		$this->DOMXPath = new DOMXPath ($DOMNode->ownerDocument);
	}
	
	//a simple wrapper to reduce some redundancy
	protected function xpath ($query) {
		/* templating works by putting `data-template="xyz"` attributes on the HTML elements in your templates,
		   and then using xpath to refer to these elements and change their values and contents, this means that
		   the vast majority of xpath queries are in the format of `.//*[@data-template="xyz"]`. because of this,
		   a shorthand format is provided by default:
		   
		   	element:template-name@attribute
		   
		   for eaxmple "a:xyz@href" would be translated to `.//a[@data-template="xyz"]/@href`. the "element:" and
		   "@attribute" parts are optional. since this type of syntax is default, prefix the query with "xpath:"
		   to use a full, real XPath query */
		if (substr ($query, 0, 6) == "xpath:") {
			//return the query without the "xpath:" prefix
			$query = substr ($query, 6);
		} else {
			//match the query against the shorthand syntax
			if (preg_match ('/^(?:([a-z0-9-]+):)?([a-z0-9_-]+)(@[a-z-]+)?$/i', $query, $m)) $query =
				".//".				//use relative: <php.net/manual/en/domxpath.query.php#99760>
				(@$m[1] ? $m[1] : "*").		//the element name, if specified, otherwise "*"
				'[@data-template="'.$m[2].'"]'.	//the data-template attribute to find
				(@$m[3] ? '/'.$m[3] : '')	//optional attribute of the parent element
			;
		}
		return $this->DOMXPath->query ($query, $this->DOMNode);
	}
	
	//specify an element to repeat (like a list-item):
	//this will return an DOMTemplateRepeater class that allows you to modify the contents the same as with the base
	//template but also append the results to the parent and return to the original element's content to go again
	public function repeat ($query) {
		//take just the first element found in a query and return a repeating template of the element
		return new DOMTemplateRepeater ($this->xpath ($query)->item (0));
	}
	
	//this sets multiple values using multiple xpath queries
	public function set ($queries) {
		foreach ($queries as $query => $value) $this->setValue ($query, $value); return $this;
	}
	
	//set the text content on the results of a single xpath query
	public function setValue ($query, $value) {
		foreach ($this->xpath ($query) as $node)
			$node->nodeValue = $node->nodeType == XML_ATTRIBUTE_NODE ? safeString ($value) : safeHTML ($value)
		; return $this;
	}
	
	//set HTML content for a single xpath query
	public function setHTML ($query, $html) {
		foreach ($this->xpath ($query) as $node) {
			$frag = $node->ownerDocument->createDocumentFragment ();
			$frag->appendXML (static::html_entity_decode ($html));
			$node->nodeValue = '';
			$node->appendChild ($frag);
		} return $this;
	}
	
	public function addClass ($query, $new_class) {
		//first determine if there is a 'class' attribute already?
		foreach ($this->xpath ($query) as $node) if (
			$node->hasAttributes () && $class = $node->getAttribute ('class')
		) {
			//if the new class is not already in the list, add it in
			if (!in_array ($new_class, explode (' ', $class)))
				$node->setAttribute ('class', "$class $new_class")
			;
		} else {
			//no class attribute to begin with, add it
			$node->setAttribute ('class', $new_class);
		} return $this;
	}
	
	//remove all the elements / attributes that match an xpath query
	public function remove ($query) {
		foreach ($this->xpath ($query) as $node) if ($node->nodeType == XML_ATTRIBUTE_NODE) {
			$node->parentNode->removeAttributeNode ($node);
		} else {
			$node->parentNode->removeChild ($node);
		} return $this;
	}
}

//using `DOMTemplate->repeat ('xpath');` returns one of these classes that acts as a sub-template that you can modify and
//then call the `next` method to append it to the parent and return to the template's original HTML code. this makes
//creating a list stunning simple! e.g.
/*
	$item = $DOMTemplate->repeat ('list-item');
	foreach ($data as $value) {
		$item->setValue ('item-name', $value);
		$item->next ();
	}
*/
class DOMTemplateRepeater extends DOMTemplateNode {
	private $parent;
	private $template;
	
	public function __construct ($DOMNode) {
		//add a reference to the parent node, where we will be appending the children
		$this->parent = $DOMNode->parentNode;
		//take the original node to use as the template for reuse
		//and remove the source node from the original document
		$this->template = $DOMNode->cloneNode (true);
		$DOMNode->parentNode->removeChild ($DOMNode);
		
		//intitialise the repeater with a copy of the template
		parent::__construct ($this->template->cloneNode (true));
	}
	
	public function next () {
		//attach the node to the parent
		$this->parent->appendChild ($this->DOMNode);
		//reset the template
		$this->DOMNode = $this->template->cloneNode (true);
	}
}

?>