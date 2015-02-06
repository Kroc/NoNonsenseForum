<?php //theme-specific template strings / functions
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

/* translatable strings for the default language
   ====================================================================================================================== */
/* the default language is hard-coded into the templates, we override it with search/replace (see 'lang.example.php')
   the following are a set of dynamic strings which can't be stored in the HTML, these are also overrided by language
   translations (see 'lang.example.php')
*/

//name of the language, as the user will use to select it;
//therefore should be the name of the language, written in that language - i.e. "Espanol" (Spanish)
$LANG['']['name']               = 'English';

//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
$LANG['']['date_format']        = 'd M ’y · H:i';

//the following template replacements are done using `sprintf`,
//see <php.net/manual/en/function.sprintf.php> for details

//the HTML title for index and thread pages
//"%1$s" - the title
//"%2$s" - if on page 2 or greater, `THEME_TITLE_PAGENO` will be inserted here, otherwise it will be removed
$LANG['']['title']              = '%1$s%2$s';

//the page number, added to the titles of index pages and threads
//"%1$u" - the page number
$LANG['']['title_pagenum']      = ' + %1$u';

//the title for append pages
//"%1$s" - the post title (will come from `THEME_RE` for replies)
$LANG['']['title_append']       = 'Append to %1$s';

//the title for delete pages
//"%1$s" - the post title (will come from `THEME_RE` for replies)
$LANG['']['title_delete']       = 'Delete %1$s?';

//reply number shown in threads as a permalink
//"%1$u" - the number of the reply
$LANG['']['replynum']           = '#%1$u.';

//title format for each reply
//"%1$u" - number of the reply
//"%2$s" - the thread title
$LANG['']['re']                 = 'RE[%1$u]: %2$s';

//text used when appending to a post:
//(markup can be used as this is run through `formatText`)
//"%1$s" - username of who posted
//"%2$s" - human-readable time, as per `DATE_FORMAT`
$LANG['']['appended']           = ':: @%1$s added on %2$s';

//HTML that replaces a post when it's deleted (this is not rectroactive)
$LANG['']['delete_user']        = '<p>This post was deleted by its owner</p>';
$LANG['']['delete_mod']         = '<p>This post was deleted by a moderator</p>';

//HTML used to replace an invalid post:
$LANG['']['corrupted']          = '<p>This post is corrupted and cannot be displayed</p>';


/* theme-specific templating
   ====================================================================================================================== */
/* these functions are used to do additional templating usually unique to this particular theme */

//this function is called just before a templated page is outputted so that you have an opportunity to do any extra
//templating of your own. the `$template` object passed in is a DOMTemplate class, see '/lib/domtemplate/' for code
//or <camendesign.com/dom_templating> for documentation on how to template with it
function theme_custom ($template) {
        $template->set (array (
                //metadata for IE9+ pinned-sites: <msdn.microsoft.com/library/gg131029>
                //application title (= forum / sub-forum name):
                '//meta[@name="application-name"]/@content'          => SUBFORUM ? SUBFORUM : FORUM_NAME,
                //application URL (where the pinned site opens at)
                '//meta[@name="msapplication-starturl"]/@content'    => FORUM_URL.url (PATH_URL),
                //pinned site / metro colour to use
                '//meta[@name="msapplication-navbutton-color"]/@content, //meta[@name="msapplication-TileColor"]/@content'
                                                                     => METRO_COLOUR, 
                //set the site logo
                'img#nnf_logo@src'                                   => FORUM_PATH.'themes/'.FORUM_THEME.'/img/'.THEME_LOGO,
                
                //set the forum URL for Google search-by-site
                '//input[@name="as_sitesearch"]/@value'              => $_SERVER['HTTP_HOST'],
                //if you're using a Google search, change it to HTTPS if enforced
                '//form[@action="http://google.com/search"]/@action' => FORUM_HTTPS ? 'https://encrypted.google.com/search'
                                                                                    : 'http://google.com/search'
        ));
}

//produce an HTML list of names (used for the mods/members list)
function theme_nameList ($names) {
        foreach ($names as &$name) $name = '<b'.(isMod ($name) ? ' class="nnf_mod"' : '').'>'.safeHTML ($name).'</b>';
        return implode (', ', $names);
}

//produces a truncated list of page numbers around the current page:
//(you might want to do something different, like a combo box with a button)
function theme_pageList ($template, $file, $page, $pages) {
        //always include the first page
        $list[] = 1;
        //more than one page?
        if ($pages > 1) {
                //if previous page is not the same as 2, include ellipses
                //(there’s a gap between 1, and current-page minus 1, e.g. "1, …, 54, 55, 56, …, 100")
                if ($page-1 > 2) $list[] = '';
                //the page before the current page
                if ($page-1 > 1) $list[] = $page-1;
                //the current page
                if ($page != 1) $list[] = $page;
                //the page after the current page (if not at end)
                if ($page+1 < $pages) $list[] = $page+1;
                //if there’s a gap between page+1 and the last page
                if ($page+1 < $pages-1) $list[] = '';
                //last page
                if ($page != $pages) $list[] = $pages;
        }
        
        //manipulate the page list in the template
        $node = $template->repeat ('.nnf_pages/li');
        //add a previous page link
        if ($pages > 1 && $page > 1) $node->set (array (
                'a@href'        => url (PATH_URL, $file, $page-1),
                'a'             => '«'
        ))->next ();
        //generate the list of pages,
        foreach ($list as &$item) {
                //create the link
                $node->setValue ('a@href', url (PATH_URL, $file, $item));
                switch (true) {
                        //determine if this is the current page, a regular page number, or the “…” gap
                        case $item == $page:    $node->setValue ('a/em', $item)->next ();       break;
                        case $item:             $node->setValue ('a', $item)->next ();          break;
                        default:                $node->setValue ('.', '…')->next ();
                }
        }
        //add a next page link
        if ($page < $pages) $node->set (array (
                'a@href'        => url (PATH_URL, $file, $page+1),
                'a'             => '»'
        ))->next ();
}

?>