<?php

//DOM Templating classes v9 © copyright (cc-by) Kroc Camen 2012
//you may do whatever you want with this code as long as you give credit
//documentation at <camendesign.com/dom_templating>

class DOMTemplate extends DOMTemplateNode {
	private $DOMDocument;
	private $keep_prolog = false;
	
	public function __construct ($filepath, $NS='', $NS_URI='') {
		//get just the template text to begin with
		$xml = file_get_contents ($filepath);
		//does this file have an XML prolog? if so, we’ll keep it as-is in the output
		if (substr_compare ($xml, '<?xml', 0, 4, true) === 0) $this->keep_prolog = true;
		
		//load the template file to work with. this must be valid XML (but not XHTML)
		$this->DOMDocument = new DOMDocument ();
		$this->DOMDocument->loadXML (
			//if the document doesn't already have an XML prolog, add one to avoid mangling unicode characters
			//see <php.net/manual/en/domdocument.loadxml.php#94291>
			(!$this->keep_prolog ? "<?xml version=\"1.0\" encoding=\"utf-8\"?>" : '').
			//replace HTML entities (e.g. "&copy;") with real unicode characters to prevent invalid XML
			self::html_entity_decode ($xml), @LIBXML_COMPACT | @LIBXML_NONET
		) or trigger_error (
			"Template '$filepath' is invalid XML", E_USER_ERROR
		);
		//set the root node for all xpath searching
		//(handled all internally by `DOMTemplateNode`)
		parent::__construct ($this->DOMDocument->documentElement, $NS, $NS_URI);
	}
	
	//output the complete HTML
	public function html () {
		//fix and clean DOM's XML output:
		return preg_replace (
			//add space to self-closing	//fix broken self-closed tags
			array ('/<(.*?[^ ])\/>/s',	'/<(div|[ou]l|textarea|script)(.*?) ?\/>/'),
			array ('<$1 />',		'<$1$2></$1>'),
			//should we remove the XML prolog?
			!$this->keep_prolog
				? preg_replace ('/^<\?xml.*?>\n/', '', $this->DOMDocument->saveXML ())
				: $this->DOMDocument->saveXML ()
		);
	}
}

//these functions are shared between the base `DOMTemplate` and the repeater `DOMTemplateRepeater`,
//the DOM/XPATH voodoo is encapsulated here
class DOMTemplateNode {
	protected $DOMNode;
	private   $DOMXPath;
	
	protected $NS;		//namespace
	protected $NS_URI;	//namespace URI
	
	//because everything is XML, HTML named entities like "&copy;" will cause blank output.
	//we need to convert these named entities back to real UTF-8 characters (which XML doesn’t mind)
	//'&', '<' and '>' are exlcuded so that we don’t turn user text into working HTML!
	public static $entities = array (
		//BTW, if you have PHP 5.3.4+ you can produce this whole array with just two lines of code:
		//
		//	$entities = array_flip (get_html_translation_table (HTML_ENTITIES, ENT_NOQUOTES, 'UTF-8'));
		//	unset ($entities['&'], $entities['<'], $entities['>']);
		//
		//also, this list is *far* from comprehensive. see this page for the full list
		//http://www.whatwg.org/specs/web-apps/current-work/multipage/named-character-references.html
		'&nbsp;'        => ' ', '&iexcl;'       => '¡', '&cent;'        => '¢', '&pound;'       => '£',
		'&curren;'      => '¤', '&yen;'         => '¥', '&brvbar;'      => '¦', '&sect;'        => '§',
		'&uml;'         => '¨', '&copy;'        => '©', '&ordf;'        => 'ª', '&laquo;'       => '«',
		'&not;'         => '¬', '&shy;'         => '­', '&reg;'         => '®', '&macr;'        => '¯',
		'&deg;'         => '°', '&plusmn;'      => '±', '&sup2;'        => '²', '&sup3;'        => '³',
		'&acute;'       => '´', '&micro;'       => 'µ', '&para;'        => '¶', '&middot;'      => '·',
		'&cedil;'       => '¸', '&sup1;'        => '¹', '&ordm;'        => 'º', '&raquo;'       => '»',
		'&frac14;'      => '¼', '&frac12;'      => '½', '&frac34;'      => '¾', '&iquest;'      => '¿',
		'&Agrave;'      => 'À', '&Aacute;'      => 'Á', '&Acirc;'       => 'Â', '&Atilde;'      => 'Ã',
		'&Auml;'        => 'Ä', '&Aring;'       => 'Å', '&AElig;'       => 'Æ', '&Ccedil;'      => 'Ç',
		'&Egrave;'      => 'È', '&Eacute;'      => 'É', '&Ecirc;'       => 'Ê', '&Euml;'        => 'Ë',
		'&Igrave;'      => 'Ì', '&Iacute;'      => 'Í', '&Icirc;'       => 'Î', '&Iuml;'        => 'Ï',
		'&ETH;'         => 'Ð', '&Ntilde;'      => 'Ñ', '&Ograve;'      => 'Ò', '&Oacute;'      => 'Ó',
		'&Ocirc;'       => 'Ô', '&Otilde;'      => 'Õ', '&Ouml;'        => 'Ö', '&times;'       => '×',
		'&Oslash;'      => 'Ø', '&Ugrave;'      => 'Ù', '&Uacute;'      => 'Ú', '&Ucirc;'       => 'Û',
		'&Uuml;'        => 'Ü', '&Yacute;'      => 'Ý', '&THORN;'       => 'Þ', '&szlig;'       => 'ß',
		'&agrave;'      => 'à', '&aacute;'      => 'á', '&acirc;'       => 'â', '&atilde;'      => 'ã',
		'&auml;'        => 'ä', '&aring;'       => 'å', '&aelig;'       => 'æ', '&ccedil;'      => 'ç',
		'&egrave;'      => 'è', '&eacute;'      => 'é', '&ecirc;'       => 'ê', '&euml;'        => 'ë',
		'&igrave;'      => 'ì', '&iacute;'      => 'í', '&icirc;'       => 'î', '&iuml;'        => 'ï',
		'&eth;'         => 'ð', '&ntilde;'      => 'ñ', '&ograve;'      => 'ò', '&oacute;'      => 'ó',
		'&ocirc;'       => 'ô', '&otilde;'      => 'õ', '&ouml;'        => 'ö', '&divide;'      => '÷',
		'&oslash;'      => 'ø', '&ugrave;'      => 'ù', '&uacute;'      => 'ú', '&ucirc;'       => 'û',
		'&uuml;'        => 'ü', '&yacute;'      => 'ý', '&thorn;'       => 'þ', '&yuml;'        => 'ÿ',
		'&OElig;'       => 'Œ', '&oelig;'       => 'œ', '&Scaron;'      => 'Š', '&scaron;'      => 'š',
		'&Yuml;'        => 'Ÿ', '&fnof;'        => 'ƒ', '&circ;'        => 'ˆ', '&tilde;'       => '˜',
		'&Alpha;'       => 'Α', '&Beta;'        => 'Β', '&Gamma;'       => 'Γ', '&Delta;'       => 'Δ',
		'&Epsilon;'     => 'Ε', '&Zeta;'        => 'Ζ', '&Eta;'         => 'Η', '&Theta;'       => 'Θ',
		'&Iota;'        => 'Ι', '&Kappa;'       => 'Κ', '&Lambda;'      => 'Λ', '&Mu;'          => 'Μ',
		'&Nu;'          => 'Ν', '&Xi;'          => 'Ξ', '&Omicron;'     => 'Ο', '&Pi;'          => 'Π',
		'&Rho;'         => 'Ρ', '&Sigma;'       => 'Σ', '&Tau;'         => 'Τ', '&Upsilon;'     => 'Υ',
		'&Phi;'         => 'Φ', '&Chi;'         => 'Χ', '&Psi;'         => 'Ψ', '&Omega;'       => 'Ω',
		'&alpha;'       => 'α', '&beta;'        => 'β', '&gamma;'       => 'γ', '&delta;'       => 'δ',
		'&epsilon;'     => 'ε', '&zeta;'        => 'ζ', '&eta;'         => 'η', '&theta;'       => 'θ',
		'&iota;'        => 'ι', '&kappa;'       => 'κ', '&lambda;'      => 'λ', '&mu;'          => 'μ',
		'&nu;'          => 'ν', '&xi;'          => 'ξ', '&omicron;'     => 'ο', '&pi;'          => 'π',
		'&rho;'         => 'ρ', '&sigmaf;'      => 'ς', '&sigma;'       => 'σ', '&tau;'         => 'τ',
		'&upsilon;'     => 'υ', '&phi;'         => 'φ', '&chi;'         => 'χ', '&psi;'         => 'ψ',
		'&omega;'       => 'ω', '&thetasym;'    => 'ϑ', '&upsih;'       => 'ϒ', '&piv;'         => 'ϖ',
		'&ensp;'        => ' ', '&emsp;'        => ' ', '&thinsp;'      => ' ', '&zwnj;'        => '‌',
		'&zwj;'         => '‍', '&lrm;'         => '‎', '&rlm;'         => '‏', '&ndash;'       => '–',
		'&mdash;'       => '—', '&lsquo;'       => '‘', '&rsquo;'       => '’', '&sbquo;'       => '‚',
		'&ldquo;'       => '“', '&rdquo;'       => '”', '&bdquo;'       => '„', '&dagger;'      => '†',
		'&Dagger;'      => '‡', '&bull;'        => '•', '&hellip;'      => '…', '&permil;'      => '‰',
		'&prime;'       => '′', '&Prime;'       => '″', '&lsaquo;'      => '‹', '&rsaquo;'      => '›',
		'&oline;'       => '‾', '&frasl;'       => '⁄', '&euro;'        => '€', '&image;'       => 'ℑ',
		'&weierp;'      => '℘', '&real;'        => 'ℜ', '&trade;'       => '™', '&alefsym;'     => 'ℵ',
		'&larr;'        => '←', '&uarr;'        => '↑', '&rarr;'        => '→', '&darr;'        => '↓',
		'&harr;'        => '↔', '&crarr;'       => '↵', '&lArr;'        => '⇐', '&uArr;'        => '⇑',
		'&rArr;'        => '⇒', '&dArr;'        => '⇓', '&hArr;'        => '⇔', '&forall;'      => '∀',
		'&part;'        => '∂', '&exist;'       => '∃', '&empty;'       => '∅', '&nabla;'       => '∇',
		'&isin;'        => '∈', '&notin;'       => '∉', '&ni;'          => '∋', '&prod;'        => '∏',
		'&sum;'         => '∑', '&minus;'       => '−', '&lowast;'      => '∗', '&radic;'       => '√',
		'&prop;'        => '∝', '&infin;'       => '∞', '&ang;'         => '∠', '&and;'         => '∧',
		'&or;'          => '∨', '&cap;'         => '∩', '&cup;'         => '∪', '&int;'         => '∫',
		'&there4;'      => '∴', '&sim;'         => '∼', '&cong;'        => '≅', '&asymp;'       => '≈',
		'&ne;'          => '≠', '&equiv;'       => '≡', '&le;'          => '≤', '&ge;'          => '≥',
		'&sub;'         => '⊂', '&sup;'         => '⊃', '&nsub;'        => '⊄', '&sube;'        => '⊆',
		'&supe;'        => '⊇', '&oplus;'       => '⊕', '&otimes;'      => '⊗', '&perp;'        => '⊥',
		'&sdot;'        => '⋅', '&lceil;'       => '⌈', '&rceil;'       => '⌉', '&lfloor;'      => '⌊',
		'&rfloor;'      => '⌋', '&lang;'        => '〈', '&rang;'        => '〉', '&loz;'         => '◊',
		'&spades;'      => '♠', '&clubs;'       => '♣', '&hearts;'      => '♥', '&diams;'       => '♦'
	);
	public static function html_entity_decode ($html) {
		return str_replace (array_keys (self::$entities), array_values (self::$entities), $html);
	}
	
	//actions are performed on elements using xpath, but for brevity a shorthand is also recognised in the format of:
	//	#id		- find an element with a particular ID (instead of writing './/*[@id="…"]')
	//	.class		- find an element with a particular class
	//	element#id	- enforce a particular element type (ID or class supported)
	//	#id@attr	- select the named attribute of the found element
	//	element#id@attr	- a fuller example
	//note also:
	//*	you can test the value of attributes (e.g. '#id@attr="test"') this selects the element, not the attribute
	//*	sub-trees in shorthand can be expressed with '/', e.g. '#id/li/a@attr'
	//*	an index-number can be provided after the element name, e.g. 'li[1]'	
	public static function shorthand2xpath ($query, $apply_prefix=true) {
		return preg_match (
			'/^(?!\/)([a-z0-9:-]+(\[\d+\])?)?(?:([\.#])([a-z0-9:_-]+))?(@[a-z-]+(="[^"]+")?)?(?:\/(.*))?$/i',
		$query, $m)
		?	($apply_prefix ? './/' : '').			//see <php.net/manual/en/domxpath.query.php#99760>
			(@$m[1] ? @$m[1].@$m[2] : '*').			//- the element name, if specified, otherwise "*"
			(@$m[4] ? ($m[3] == '#'				//is this an ID?
				? "[@id=\"${m[4]}\"]"			//- yes
				: "[contains(@class,\"${m[4]}\")]"	//- no, a class	
			) : '').
			(@$m[5] ? (@$m[6]				//optional attribute of the parent element
				? "[${m[5]}]"				//- an attribute test
				: "/${m[5]}"				//- or select the attribute
			) : '').
			(@$m[7] ? '/'.self::shorthand2xpath ($m[7], false) : '')
		: $query;
	}
	
	public function __construct ($DOMNode, $NS='', $NS_URI='') {
		//use a DOMNode as a base point for all the XPath queries and whatnot
		//(in DOMTemplate this will be the whole template, in DOMTemplateRepeater, it will be the chosen element)
		$this->DOMNode = $DOMNode;
		$this->DOMXPath = new DOMXPath ($DOMNode->ownerDocument);
		//the painful bit. if you have an XMLNS in your template then XPath won’t work unless you:
		// a. register a default namespace, and
		// b. prefix all your XPath queries with this namespace
		$this->NS = $NS; $this->NS_URI = $NS_URI;
		if ($this->NS && $this->NS_URI) $this->DOMXPath->registerNamespace ($this->NS, $this->NS_URI);
	}
	
	public function query ($query) {
		//run the real XPath query and return the nodelist result
		return $this->DOMXPath->query (implode ('|',
			//convert each query to real XPath:
			//(multiple targets are available by comma separating queries)
			array_map (array ('self', 'shorthand2xpath'), explode (', ', $query))
		), $this->DOMNode);
	}
	
	//specify an element to repeat (like a list-item):
	//this will return an DOMTemplateRepeater class that allows you to modify the contents the same as with the base
	//template but also append the results to the parent and return to the original element's content to go again
	public function repeat ($query) {
		//take just the first element found in a query and return a repeating template of the element
		return new DOMTemplateRepeater ($this->query ($query)->item (0), $this->NS, $this->NS_URI);
	}
	
	//this sets multiple values using multiple xpath queries
	public function set ($queries, $asHTML=false) {
		foreach ($queries as $query => $value) $this->setValue ($query, $value, $asHTML); return $this;
	}
	
	//set the text content on the results of a single xpath query
	public function setValue ($query, $value, $asHTML=false) {
		foreach ($this->query ($query) as $node) switch (true) {
			//if the selected node is a "class" attribute, add the className to it
			case $node->nodeType == XML_ATTRIBUTE_NODE && $node->nodeName == 'class':
				$this->addClass ($query, $value); break;
				
			//if the selected node is any other element attribute, set its value
			case $node->nodeType == XML_ATTRIBUTE_NODE:
				$node->nodeValue = htmlspecialchars ($value, ENT_QUOTES); break;
				
			//if the text is to be inserted as HTML that will be inluded into the output
			case $asHTML:
				$frag = $node->ownerDocument->createDocumentFragment ();
				//if the HTML string is not valid XML, it won’t work!
				$frag->appendXML (self::html_entity_decode ($value));
				$node->nodeValue = '';
				$node->appendChild ($frag);
				break;
				
			//otherwise, encode the text to display as-is
			default:
				$node->nodeValue = htmlspecialchars ($value, ENT_NOQUOTES);
		}
		return $this;
	}
	
	public function addClass ($query, $new_class) {
		//first determine if there is a 'class' attribute already?
		foreach ($this->query ($query) as $node) if (
			$node->hasAttributes () && $class = $node->getAttribute ('class')
		) {	//if the new class is not already in the list, add it in
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
		//this function can accept either a single query, or an array in the format of `'xpath' => true|false`.
		//if the value is true then the xpath will be run and the found elements deleted, if the value is false
		//then the xpath is skipped. why on earth would you want to provide an xpath, but not run it? because
		//you can compact your code by using logic comparisons for the value
		if (is_string ($query)) $query = array ($query => true);
		foreach ($query as $xpath => $logic) if ($logic) foreach ($this->query ($xpath) as $node) if (
			$node->nodeType == XML_ATTRIBUTE_NODE
		) {	$node->parentNode->removeAttributeNode ($node);
		} else {
			$node->parentNode->removeChild ($node);
		} return $this;
	}
}

//using `DOMTemplate->repeat ('xpath');` returns one of these classes that acts as a sub-template that you can modify and
//then call the `next` method to append it to the parent and return to the template's original HTML code. this makes
//creating a list stunning simple! e.g.
/*
	$item = $DOMTemplate->repeat ('.list-item');
	foreach ($data as $value) {
		$item->setValue ('.item-name', $value);
		$item->next ();
	}
*/
class DOMTemplateRepeater extends DOMTemplateNode {
	private $refNode;
	private $template;
	
	public function __construct ($DOMNode, $NS='', $NS_URI='') {
		//we insert the templated item before or after the reference node,
		//which will always be set to the last item that was templated
		$this->refNode  = $DOMNode;
		//take a copy of the original node that we will use as a starting point each time we iterate
		$this->template = $DOMNode->cloneNode (true);
		//initialise the template with the current, original node
		parent::__construct ($DOMNode, $NS, $NS_URI);
	}
	
	public function next () {
		//when we insert the newly templated item, use it as the reference node for the next item and so on.
		$this->refNode = ($this->refNode->parentNode->lastChild === $this->DOMNode)
			? $this->refNode->parentNode->appendChild ($this->DOMNode)
			//if there's some kind of HTML after the reference node, we can use that to insert our item
			//inbetween. this means that the list you are templating doesn't have to be wrapped in an element!
			: $this->refNode->parentNode->insertBefore ($this->DOMNode, $this->refNode->nextSibling)
		;
		//reset the template
		$this->DOMNode = $this->template->cloneNode (true);
		return $this;
	}
}

?>