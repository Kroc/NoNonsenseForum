<? //generate an RSS feed for index pages

include "shared.php";

/* ====================================================================================================================== */

//which folder to show, not present for forum index
if ($path = preg_match ('/([^.\/]+)\//', @$_GET['path'], $_) ? $_[1] : '') chdir (FORUM_ROOT.$path);

//get list of threads
$threads = array_fill_keys (preg_grep ('/\.xml$/', scandir ('.')) , 0);
foreach ($threads as $file => &$date) $date = filectime ($file);
arsort ($threads, SORT_NUMERIC);

$threads = array_slice ($threads, 0, FORUM_THREADS);
foreach ($threads as $file => $date) {
	$xml = simplexml_load_file ($file);
	$items = $xml->channel->xpath ('item');
	$item = end ($items);
	
	@$rss .= template_tags (TEMPLATE_RSS_ITEM, array (
		'TITLE'	=> safeHTML ($xml->channel->title),
		'URL'	=> ($path ? rawurlencode ($path).'/' : '').pathinfo ($file, PATHINFO_FILENAME),
		'NAME'	=> safeHTML ($item->author),
		'DATE'	=> gmdate ('r', strtotime ($item->pubDate)),
		'TEXT'	=> safeHTML ($item->description),
	));
}

header ("Content-Type: application/rss+xml;charset=UTF-8");
die (template_tags (TEMPLATE_RSS_INDEX, array (
	'PATH'	=> $path ? rawurlencode ($path).'/' : '',
	'TITLE'	=> $path ? safeHTML ($path) : "Forum Index",
	'ITEMS'	=> $rss
)));

?>