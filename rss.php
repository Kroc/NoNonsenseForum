<? //generate an RSS feed for index pages
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

include 'shared.php';

/* ====================================================================================================================== */

//get list of threads
$threads = preg_grep ('/\.xml$/', scandir ('.'));
array_multisort (array_map ('filectime', $threads), SORT_DESC, $threads);	//look ma, no loop!

foreach (array_slice ($threads, 0, FORUM_THREADS) as $file) {
	$xml  = simplexml_load_file ($file);
	$item = $xml->channel->item[count ($xml->channel->item) - 1];
	
	@$rss .= template_tags (<<<XML
<item>
	<title>&__TITLE__;</title>
	<link>http://${_SERVER['HTTP_HOST']}&__URL__;</link>
	<author>&__NAME__;</author>
	<pubDate>&__DATE__;</pubDate>
	<description>&__TEXT__;</description>
</item>
XML
	, array (
		'TITLE'	=> safeHTML ($xml->channel->title),
		'URL'	=> PATH_URL.pathinfo ($file, PATHINFO_FILENAME),
		'NAME'	=> safeHTML ($item->author),
		'DATE'	=> gmdate ('r', strtotime ($item->pubDate)),
		'TEXT'	=> safeHTML ($item->description),
	));
}

header ('Content-Type: application/rss+xml;charset=UTF-8');
die (template_tags (<<<XML
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
, array (
	'PATH'	=> safeHTML (PATH_URL),
	'TITLE'	=> safeHTML (PATH),
	'ITEMS'	=> $rss
)));

?>