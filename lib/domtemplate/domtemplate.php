<?php

//DOM Templating classes v18 © copyright (cc-by) Kroc Camen 2012-2015
//you may do whatever you want with this code as long as you give credit
//documentation at <camendesign.com/dom_templating>

/*	Basic API:
	
	new DOMTemplate (source, [namespaces])
	
	(string)				to output the HTML / XML, cast the DOMTemplate object to a string,
						i.e. `echo $template;`
	query (query)				make an XPath query
	set (queries, [asHTML])			change HTML by specifying an array of ('XPath' => 'value')
	setValue (query, value, [asHTML])	change a single HTML value with an XPath query
	addClass (query, new_class)		add a class to an HTML element
	remove (query)				remove one or more HTML elements, attributes or classes
	repeat (query)				return one (or more) elements as sub-templates
		
		next ()				append the sub-template to the list and reset its content
*/

/* class DOMTemplate : the overall template controller
   ====================================================================================================================== */
class DOMTemplate extends DOMTemplateNode {
	private $DOMDocument;			//internal reference to the PHP DOMDocument for the template's XML
	
	//what type of data are we processing?
	protected $type = self::HTML;
	const HTML = 0;
	const XML  = 1;
	
	/* new DOMTemplate : instantiation
	   -------------------------------------------------------------------------------------------------------------- */
	public function __construct (
		$source,			//a string of the HTML or XML to form the template
		$namespaces=array ()		//an array of XML namespaces if your document uses them,
						//in the format of `'namespace' => 'namespace URI'`
	) {
		//detect the content type; HTML or XML. HTML will need filtering during input and output
		//does this source have an XML prolog?
		$this->type = substr_compare ($source, '<?xml', 0, 4, true) === 0 ? self::XML : self::HTML;
		
		//load the template file to work with,
		//it _must_ have only one root (wrapping) element; e.g. `<html>`
		$this->DOMDocument = new DOMDocument ();
		if (!$this->DOMDocument->loadXML (
			//if the source is HTML add an XML prolog to avoid mangling unicode characters,
			//see <php.net/manual/en/domdocument.loadxml.php#94291>. also convert it to XML for PHP DOM use
			$this->type == self::HTML
			? "<?xml version=\"1.0\" encoding=\"utf-8\"?>".self::toXML ($source)
			: $source,
			@LIBXML_COMPACT || @LIBXML_NONET
		)) trigger_error (
			"Source is invalid XML", E_USER_ERROR
		);
		//set the root node for all XPath searching
		//(handled all internally by `DOMTemplateNode`)
		parent::__construct ($this->DOMDocument->documentElement, $namespaces);
	}
	
	/* output the source code (cast the object to a string, i.e. `echo $template;`)
	   -------------------------------------------------------------------------------------------------------------- */
	public function __toString () {
		//if the input was HTML, remove the XML prolog on output
		return $this->type == self::HTML
		?	//we defer to DOMTemplateNode which returns the HTML for any node,
			//the top-level template only needs to consider the prolog
			preg_replace ('/^<\?xml[^<]*>\n/', '', parent::__toString ())
		:	parent::__toString ();
	}
}

/* class DOMTemplateNode
   ====================================================================================================================== */
//these functions are shared between the base `DOMTemplate` and the repeater `DOMTemplateRepeater`.
//see <php.net/manual/en/language.oop5.abstract.php#95404> for a good description of 'abstract',
//refer to the constructor function for reasons why!
abstract class DOMTemplateNode {
	protected $DOMNode;		//reference to the actual PHP DOMNode being operated upon
	private   $DOMXPath;		//an internal XPath object so you don't have to manage one externally
	
	protected $namespaces;		//optional XML namespaces
	
	/* html_entity_decode : convert HTML entities back to UTF-8
	   -------------------------------------------------------------------------------------------------------------- */
	public static function html_entity_decode ($html) {
		//because everything is XML, HTML named entities like "&copy;" will cause blank output.
		//we need to convert these named entities back to real UTF-8 characters (which XML doesn’t mind)
		return str_replace (array_keys (self::$entities), array_values (self::$entities), $html);
	}
	//a table of HTML entites to reverse, '&', '<' and '>' are exlcuded so we don’t turn user text into working HTML!
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
	
	/* toXML : convert string input to safe XML for inporting into DOM
	   -------------------------------------------------------------------------------------------------------------- */
	public static function toXML ($text) {
		//[1] because everything is XML, HTML named entities like "&copy;" will cause blank output.
		//we need to convert these named entities back to real UTF-8 characters (which XML doesn’t mind)
		$text = self::html_entity_decode ($text);
		
		//[2] properly self-close some elments
		$text = preg_replace (
			'/<(base|basefont|br|col|img|input|link|meta|param|source|track)([^>]*)(?<!\/)>(?!<\/$1>)/is',
			'<$1$2 />', $text
		);
		
		//[3] convert HTML-style attributes (`<a attr>`) to XML style attributes (`<a attr="attr">`)
		while (preg_match (
			'/(?>(<(?!!)[a-z-]+(?:\s|[a-z-]+="[^"]*")+))([a-z-]+)(?=[>\s])/is', $text, $m, PREG_OFFSET_CAPTURE
		)) $text = substr_replace (
			$text, $m[1][0].$m[2][0].'="'.$m[2][0].'"', $m[0][1], strlen ($m[0][0])
		);
		
		//[4] properly escape JavaScript with CDATA
		$text = preg_replace ('/(<script[^>]*>)(.*?)(<\/script>)/is', "$1<![CDATA[$2]]>$3", $text);
		
		return $text;
	}
	
	/* shorthand2xpath : convert our shorthand XPath syntax to full XPath
	   -------------------------------------------------------------------------------------------------------------- */
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
	public static function shorthand2xpath (
		$query,			//a string to convert
		$use_relative=true	//by default, the converted XPath uses a relative prefix "//" to work around a bug
					//in XPath matching. see <php.net/manual/en/domxpath.query.php#99760> for details
	) {
		//return from cache where possible
		//(this doubles the speed of repeat loops)
		static $cache = array ();
		if (isset ($cache[$query])) return $cache[$query];
		
		//match the allowed format of shorthand
		return $cache[$query] = preg_match (
			'/^(?!\/)([a-z0-9:-]+(\[\d+\])?)?(?:([\.#])([a-z0-9:_-]+))?(@[a-z-]+(="[^"]+")?)?(?:\/(.*))?$/i',
		$query, $m)
		?	($use_relative ? './/' : '').		//apply the relative prefix
			(@$m[1] ? @$m[1].@$m[2] : '*').		//the element name, if specified, otherwise "*"
			(@$m[4] ? ($m[3] == '#'			//is this an ID?
				? "[@id=\"${m[4]}\"]"		//- yes, match it
				//- no, a class. note that class attributes can contain multiple classes, separated by
				//  spaces, so we have to test for the whole-word, and not a partial-match
				: "[contains(concat(' ', @class, ' '),\" ${m[4]} \")]"
			) : '').
			(@$m[5] ? (@$m[6]			//optional attribute of the parent element
				? "[${m[5]}]"			//- an attribute test
				: "/${m[5]}"			//- or select the attribute
			) : '').
			(@$m[7] ? '/'.self::shorthand2xpath ($m[7], false) : '')
		: $query;
	}
	
	/* new DOMTemplateNode : instantiation
	   -------------------------------------------------------------------------------------------------------------- */
	//you cannot instantiate this class yourself, _always_ work through DOMTemplate! why? because you cannot mix nodes
	//from different documents! DOMTemplateNodes _must_ come from DOMDocument kept privately inside DOMTemplate
	public function __construct ($DOMNode, $namespaces=array ()) {
		//use a DOMNode as a base point for all the XPath queries and whatnot
		//(in DOMTemplate this will be the whole template, in DOMTemplateRepeater, it will be the chosen element)
		$this->DOMNode  = $DOMNode;
		$this->DOMXPath = new DOMXPath ($DOMNode->ownerDocument);
		//the painful bit: if you have an XMLNS in your template then XPath won’t work unless you:
		// a. register a default namespace, and
		// b. prefix element names in your XPath queries with this namespace
		if (!empty ($namespaces)) foreach ($namespaces as $NS=>$URI) $this->DOMXPath->registerNamespace ($NS, $URI);
		$this->namespaces = $namespaces;
	}
	
	/* query : find node(s)
	   -------------------------------------------------------------------------------------------------------------- */
	//note that this function returns a PHP DOMNodeList, not a DOMTemplateNode! you cannot use `query` and then use
	//other DOMTemplateNode methods off of the result. the reason for this is because you cannot yet extend
	//DOMNodeList and therefore can't create APIs that affect all the nodes returned by an XPath query
	public function query (
		$query			//an XPath/shorthand (see `shorthand2xpath`) string to search for nodes
	) {
		//convert each query to real XPath: (multiple targets are available by comma separating queries)
		$xpath = implode ('|', array_map (array ('self', 'shorthand2xpath'), explode (', ', $query)));
		
		//run the real XPath query and return the DOMNodeList result
		If ($result = @$this->DOMXPath->query ($xpath, $this->DOMNode)) {
			return $result;
		} else {
			throw new Exception ("Invalid XPath Expression: $xpath");
		}
	}
	
	/* set : change multiple nodes in a simple fashion
	   -------------------------------------------------------------------------------------------------------------- */
	public function set (
		$queries,		//an array of `'xpath' => 'text'` to find and set
		$asHTML=false		//text is by-default encoded for safety against HTML injection,
					//if this parameter is true then the text is added as real HTML
	) {
		foreach ($queries as $query => $value) $this->setValue ($query, $value, $asHTML); return $this;
	}
	
	/* setValue : set the text on the results of a single xpath query
	   -------------------------------------------------------------------------------------------------------------- */
	public function setValue (
		$query,			//an XPath/shorthand (see `shorthand2xpath`) string to search for nodes
		$value,			//what text to replace the node's contents with
		$asHTML=false		//as with `set`, if the text should be safety encoded or inserted as HTML
	) {
		foreach ($this->query ($query) as $node) switch (true) {
			
			//if the selected node is a "class" attribute, add the className to it
			case $node->nodeType == XML_ATTRIBUTE_NODE && $node->nodeName == 'class':
				$this->setClassNode ($node, $value); break;
				
			//if the selected node is any other element attribute, set its value
			case $node->nodeType == XML_ATTRIBUTE_NODE:
				$node->nodeValue = htmlspecialchars ($value, ENT_QUOTES); break;
				
			//if the text is to be inserted as HTML that will be included into the output
			case $asHTML:
				//remove exisiting element's content
				$node->nodeValue = '';
				//if supplied text is blank end here; you can't append a blank!
				if (!$value) break;
				
				//attch the HTML to the node
				$frag = $node->ownerDocument->createDocumentFragment ();
				if (	!@$frag->appendXML (
						//if the source document is HTML, filter it
						$this->type == DOMTemplate::HTML ? self::toXML ($value) : $value
					) ||
					!@$node->appendChild ($frag)
				) throw new Exception ("Invalid HTML");
				break;
				
			//otherwise, encode the text to display as-is
			default:
				$node->nodeValue = htmlspecialchars ($value, ENT_NOQUOTES);
		}
		return $this;
	}
	
	/* addClass : add a className to an element, appending it to existing classes if they exist
	   -------------------------------------------------------------------------------------------------------------- */
	public function addClass ($query, $new_class) {
		//first determine if there is a 'class' attribute already?
		foreach ($this->query ($query) as $node) if (
			$node->hasAttributes () && $class = $node->getAttribute ('class')
		) {	//if the new class is not already in the list, add it in
			$this->setClassNode ($node->attributes->getNamedItem ('class'), $new_class);
		} else {
			//no class attribute to begin with, add it
			$node->setAttribute ('class', $new_class);
		} return $this;
	}
	
	//add a className to an existing class attribute (this is shared between `setValue` & `addClass`
	private function setClassNode ($DOMNode, $class) {
		//check if the class node already has the className (don't add twice)
		if (!in_array ($class, explode (' ', $DOMNode->nodeValue)))
			@$DOMNode->nodeValue = $DOMNode->nodeValue." $class"
		;
	}
	
	/* remove : remove all the elements / attributes that match an xpath query
	   -------------------------------------------------------------------------------------------------------------- */
	public function remove (
		$query			//XPath query to select node(s) to remove:
				
		//can be either a single string, or an array in the format of `'xpath' => true|false`.
		//if the value is true then the xpath will be run and the found elements deleted,
		//if the value is false then the xpath is skipped. why on earth would you want to provide an xpath,
		//but not run it? because you can compact your code by providing the same array every time,
		//but precompute the logic.
				
		//additionally, an array item that targets the class node of an HTML element (e.g. 'a@class') can,
		//instead of using true / false for the value (as whether to remove the class attribute or not),
		//provide a class name to remove from the class attribute, whilst retaining the other class names
		//and the node; e.g. `$DOMTemplate->remove ('a@class' => 'undesired');`
	) {
		//if a string is provided, cast it into an array for assumption below
		if (is_string ($query)) $query = array ($query => true);
		//loop the array, test the logic, and select the node(s)...
		foreach ($query as $xpath => $logic) if ($logic) foreach ($this->query ($xpath) as $node) if (
			//is this an HTML element attribute?
			$node->nodeType == XML_ATTRIBUTE_NODE
		) {	//is this an HTML class attribute, and has a className been given to selectively remove?
			if ($node->nodeName == 'class' && is_string ($logic)) {
				//reconstruct the class attribute value, sans the chosen className
				$node->nodeValue = implode (' ',
					array_diff (explode (' ', $node->nodeValue), array ($logic))
				);
				//if there are classNames remaining, skip removing the whole class attribute
				if ($node->nodeValue) continue;
			}
			//remove the whole attribute:
			$node->parentNode->removeAttributeNode ($node);
		} else {
			//remove an element node, rather than an attribute node
			$node->parentNode->removeChild ($node);
		} return $this;
	}
	
	/* output the source code (cast the object to a string, i.e. `echo $template;`)
	   -------------------------------------------------------------------------------------------------------------- */
	public function __toString () {
		//get the document's code, we'll process it differently depending on desired output format
		$source = $this->DOMNode->ownerDocument->saveXML (
			//if you’re calling this function from the template-root we don’t specify a node,
			//otherwise the DOCTYPE / XML prolog won’t be included
			get_class ($this) == 'DOMTemplate' ? NULL : $this->DOMNode
		);
		
		//XML is already used for the internal representation; if outputting XML no filtering is needed
		//(`$this->XML` and `$this::XML` don't work consistently between PHP versions, so I'm cheeping out)
		if ($this->type == 1) return $source;
		
		//fix and clean DOM's XML into HTML:
		//----------------------------------
		//add space to self-closing tags, only beneficial for prettiness and very old browsers
		$source = preg_replace ('/<([^<]*[^ ])\/>/', '<$1 />', $source);
		//fix broken self-closed tags; e.g. `<script ...></script>` will automatically be converted to
		//`<script ... />` on loading the document, this breaks in browsers so we have to fix it on output
		$source = preg_replace ('/<(div|iframe|[ou]l|script|textarea|title)([^>]*) ?\/>/i', '<$1$2></$1>', $source);
		//convert XML style attributes (`<a attr="attr">`) to HTML style attributes (`<a attr>`),
		//this needs to be repeated until none are left as we must anchor each to the opening bracket of
		//the element, otherwise content text might be hit too
		while (preg_match ('/(<(?!!)[^>]+\s)([a-z-]+)=([\'"]?)\2\3/im', $source, $m, PREG_OFFSET_CAPTURE))
			$source = substr_replace ($source, $m[1][0].$m[2][0], $m[0][1], strlen ($m[0][0]))
		;
		//strip out CDATA sections
		$source = preg_replace ('/<!\[CDATA\[(.*?)\]\]>/s', '$1', $source);
		
		return $source;
	}
	
	/* repeat : iterate a node
	   -------------------------------------------------------------------------------------------------------------- */
	//this will return a DOMTemplateRepeaterArray class that allows you to modify the contents the same as with the
	//base template but also append the changed sub-template to the end of the list and reset its content to go again.
	//this makes creating a list stunningly simple! e.g.
	/*
		$item = $DOMTemplate->repeat ('.list-item');
		foreach ($data as $value) $item->setValue ('.', $value)->next ();
	*/
	public function repeat ($query) {
		//NOTE: the provided XPath query could return more than one element! DOMTemplateRepeaterArray therefore
		//	acts as a simple wrapper to propogate changes to all the matched nodes (DOMTemplateRepeater)
		return new DOMTemplateRepeaterArray ($this->query ($query), $this->namespaces);
	}
}

/* class DOMTemplateRepeaterArray : allow repetition over multiple nodes simultaneously
   ====================================================================================================================== */
//this is just a wrapper to handle that `repeat` might be executed on more than one element simultaneously;
//for example, if you are producing a list that occurs more than once on a page (e.g. page number links in a forum)
class DOMTemplateRepeaterArray {
	private $nodes;
	
	public function __construct ($DOMNodeList, $namespaces=array ()) {
		//convert the XPath query result into extended `DOMTemplateNode`s (`DOMTemplateRepeater`) so that you can
		//modify the HTML with the same usual DOMTemplate API
		foreach ($DOMNodeList as $DOMNode) $this->nodes[] = new DOMTemplateRepeater ($DOMNode, $namespaces);
	}
	
	public function next () {
		//cannot use `foreach` here because you shouldn't modify the nodes whilst iterating them
		for ($i=0; $i<count ($this->nodes); $i++) $this->nodes[$i]->next (); return $this;
	}
	
	//refer to `DOMTemplateNode->set`
	public function set ($queries, $asHTML=false) {
		foreach ($this->nodes as $node) $node->set ($queries, $asHTML); return $this;
	}
	
	//refer to `DOMTemplateNode->setValue`
	public function setValue ($query, $value, $asHTML=false) {
		foreach ($this->nodes as $node) $node->setValue ($query, $value, $asHTML); return $this;
	}
	
	//refer to `DOMTemplateNode->addClass`
	public function addClass ($query, $new_class) {
		foreach ($this->nodes as $node) $node->addClass ($query, $new_class); return $this;
	}
	
	//refer to `DOMTemplateNode->remove`
	public function remove ($query) {
		foreach ($this->nodes as $node) $node->remove ($query); return $this;
	}
}

/* class DOMTemplateRepeater : the business-end of `DOMTemplateNode->repeat`!
   ====================================================================================================================== */
class DOMTemplateRepeater extends DOMTemplateNode {
	private $refNode;		//the templated node will be added after this node
	private $template;		//a copy of the original node to work from each time
	
	protected $type;
	
	public function __construct ($DOMNode, $namespaces=array ()) {
		//we insert the templated item after the reference node,
		//which will always be the last item that was templated
		$this->refNode  = $DOMNode;
		//take a copy of the original node that we will use as a starting point each time we iterate
		$this->template = $DOMNode->cloneNode (true);
		//initialise the template with the current, original node
		parent::__construct ($DOMNode, $namespaces);
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
