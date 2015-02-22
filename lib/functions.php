<?php //shared functions
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//formulate a URL (used to automatically fallback to non-pretty URLs when htaccess is not available),
//the domain is not included because it is not used universally throughout (absolute-base / relative links)
function url (
        $path='',       //sub-forum path
        $file='',       //a thread file name (sans extension)
        $page=0,        //page number
        $action='',     //an action such as "append", "delete", "lock" or "unlock" 
        $action_id=''   //an optional post-id to go with the action above
) {
        //begin with the subfolder the forum is in, if any. all URLs must be absolute to be able to juggle the mix of
        //htaccess vs. no-htaccess + running in root vs. running in a sub-folder
        $filepath = FORUM_PATH."$path$file";
        if (substr ($filepath, strlen (FORUM_PATH.PATH_URL)) == FORUM_PATH.PATH_URL)
                $filepath = substr ($filepath, strlen (FORUM_PATH.PATH_URL)+1)
        ;
        
        return HTACCESS
        //if htaccess is on, then use pretty URLs:
        ?       $filepath.($page ? "+$page" : '').rtrim ('?'.implode ('&', array_filter (array (
                //single actions without any ID (only delete, un/lock use form buttons)
                !$action_id && ($action == 'delete') ? $action : '',
                //otherwise, actions with an ID?
                $action_id ? "$action=$action_id" : ''
        ))), '?')
        //if htaccess is off, fallback to real URLs:
        :       FORUM_PATH.
                //which page to point to; if a file is given, it's always a thread
                ($file ? 'thread.php' : 'index.php').rtrim ('?'.
                //concatenate a query string
                implode ('&', array_filter (array (
                        //actions without an ID (only delete, un/lock use form buttons)
                        !$action_id && ($action == 'delete') ? $action : '',
                        //append or delete post
                        $action_id ? "$action=$action_id" : '',
                        //sub-forum? for no-htaccess, all links must be made relative from the NNF folder root
                        $path   ? "path=$path" : '',
                        //if a file is specified (view thread, append, delete &c.)
                        $file   ? "file=$file" : '',
                        //page number
                        $page   ? "page=$page" : ''
                ))), '?')
        ;
}

//the shared template stuff for all pages
function prepareTemplate (
        $filepath,      //template file to load
        $canonical='',  //the canonical URL for the page, so that search engines can ignore querystring spam from links
        $title=NULL     //HTML title to use, if NULL, existing `<title>` is kept
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
        if (isset ($LANG[LANG]['strings'])) $template->set ($LANG[LANG]['strings'], true)->setValue ('/html/@lang', LANG);
        //template the language chooser
        if (THEME_LANGS) {
                $item = $template->repeat ('.nnf_lang');
                //build the list for each additional language
                foreach ($LANG as $code => $lang) $item->set (array (
                        './@value'      => $code,
                        '.'             => $lang['name']
                ))->remove (array (
                        './@selected'   => !($code == LANG)
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
        //set the canonical URL
        if ($canonical) $template->setValue ('/html/head/meta[@rel="canonical"]/@href', $canonical);
        
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
                'a.nnf_subforum-name'           => $items[$i],
                'a.nnf_subforum-name@href'      => url (
                        //reconstruct the URL from each sub-forum up to the current one
                        implode ('/', array_map ('safeURL', array_slice ($items, 0, $i+1))).'/'
                )
        ))->next ();
        //not in a sub-folder? remove the breadcrumb navigation
        if (!PATH) $template->remove ('.nnf_breadcrumb');
        
        /* site footer
           -------------------------------------------------------------------------------------------------------------- */
        //are there any local mods?     create the list of local mods
        if (!empty ($MODS['LOCAL'])):   $template->setValue ('#nnf_mods-local-list', theme_nameList ($MODS['LOCAL']), true);
                                else:   $template->remove   ('#nnf_mods-local');        //remove the local mods list section
        endif;
        //are there any site mods?      create the list of mods
        if (!empty ($MODS['GLOBAL'])):  $template->setValue ('#nnf_mods-list', theme_nameList ($MODS['GLOBAL']), true);
                                 else:  $template->remove   ('#nnf_mods');              //remove the mods list section
        endif;
        //are there any members?        create the list of members
        if (!empty ($MEMBERS)):         $template->setValue ('#nnf_members-list', theme_nameList ($MEMBERS), true);
                          else:         $template->remove   ('#nnf_members');           //remove the members list section
        endif;
        
        //set the name of the signed-in user
        $template->setValue ('.nnf_signed-in-name', NAME)->remove (
                //remove the relevant section for signed-in / out
                AUTH_HTTP ? '.nnf_signed-out' : '.nnf_signed-in'
        );
        
        return $template;
}

/* ====================================================================================================================== */

//the first mod on the list is the site administrator and has extra privileges such as stickying threads
function isAdmin ($name) {
        global $MODS;    return strtolower ($name) === strtolower ((string) @$MODS['GLOBAL'][0]);
}
//check to see if a name is a known moderator
function isMod ($name) {
        global $MODS;    return in_array (strtolower ($name), array_map ('strtolower', $MODS['GLOBAL'] + $MODS['LOCAL']));
}
//a member of a locked forum?
function isMember ($name) {
        global $MEMBERS; return in_array (strtolower ($name), array_map ('strtolower', $MEMBERS));
}

//get the list of sticky threads in the current forum / sub-forum
function getStickies () {
        //`file` returns NULL on failure, so we can cast it to an array to get an array with one blank item,
        //then `array_filter` removes blank items. this way saves having to check if the file exists first
        return array_filter ((array) @file ('sticky.txt', FILE_IGNORE_NEW_LINES));
}

/* ====================================================================================================================== */

//take the author's message, process markup, and encode it safely for the RSS feed
function formatText (
        $text,          //the text to process into HTML
        $permalink='',  //optional full URL to the thread this text will be a part of, used to make title links permanent
        $post_id='',    //optional HTML ID of the post that this text will form, used for title self-links
        $rss=NULL       //optional simpleXML object of the whole thread, to link to other user's posts
) {
        //unify carriage returns between Windows / UNIX, and sanitise HTML against injection
        $text = safeHTML (preg_replace ('/\r\n?/', "\n", $text));
        
        //these arrays will hold any portions of text that have to be temporarily removed to avoid interference with the
        //markup processing, i.e code spans / blocks
        $pre = array (); $code = array ();
        
        /* preformatted text (code blocks):
           -------------------------------------------------------------------------------------------------------------- */
        /* example:                     or: (latex in particular since it uses % as a comment marker)
        
                % title                 $ title
                ⋮                       ⋮
                %                       $
        */
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
                $text = substr_replace ($text, "\n&PRE_".(count ($pre)-1).";\n".$m[5][0], $m[0][1], strlen ($m[0][0]));
        }
        
        /* inline code / teletype text:
           -------------------------------------------------------------------------------------------------------------- */
        // example: `code` or ``code``
        while (preg_match ('/(?<=[\s\p{Z}\p{P}]|^)(`+)(.*?)(?<!`)\1(?!`)/m', $text, $m, PREG_OFFSET_CAPTURE)) {
                //format the code block
                $code[] = '<code>'.$m[1][0].$m[2][0].$m[1][0].'</code>';
                //same as with normal code blocks, replace them with a placeholder
                $text = substr_replace ($text, '&CODE_'.(count ($code)-1).';', $m[0][1], strlen ($m[0][0]));
        }
        
        /* hyperlinks:
           -------------------------------------------------------------------------------------------------------------- */
        //find full URLs and turn into HTML hyperlinks. we also detect e-mail addresses automatically
        while (preg_match (
                '/(?:
                        ((?:(?:http|ftp)s?|irc)?:\/\/)                  # $1 = protocol
                |       ([a-z0-9\._%+\-]+@)                             # $2 = email name
                )(                                                      # $3 = friendly URL (no protocol)
                        [-\.\p{L}\p{M}\p{N}]+                           # domain (letters, diacritics, numbers & dash only)
                        (?:\.[\p{L}\p{M}\p{N}]+)+                       # TLDs (also letters, diacritics & numbers only)
                )(?(2)|                                                 # email ends here
                        (\/)?                                           # $4 = slash is excluded from friendly URL
                        (?(4)(                                          # $5 = folders and filename, relative URL
                                (?>                                     # folders and filename
                                        "(?!\/?&gt;|\s|$)|              # ignore the end of an HTML hyperlink
                                        \)(?![:\.,"”»]?(?:\s|$))|       # ignore brackets on end with punctuation
                                        [:\.,”»](?!\s|$)|               # ignore various characters on the end
                                        [^\s:)\.,"”»]                   # the rest, including bookmark
                                )*
                        )?)
                )/xiu',
                //capture the starting point of the match, so that `$m[x][0]` is the text and $m[x][1] is the offset
                $text, $m, PREG_OFFSET_CAPTURE,
                //use an offset to search from so we don’t get stuck in an infinite loop
                //(this isn’t valid the first time around obviously so gives 0)
                @($m[0][1] + strlen ($replace))
                
        //replace the URL in the source text with a hyperlinked version:
        //(we record the HTML in `$replace` so that we can skip forward that much for the next search iteration)
        )) $text = substr_replace ($text, $replace =
                '<a href="'.($p=(@$m[2][0] ? 'mailto:'.$m[2][0]                     //is this an e-mail address?
                                           : ($m[1][0] ? $m[1][0] : 'http://')))    //has a protocol been given?
                        //rest of the URL [domain . slash . everything-else]
                        //(encode double-quotes without double-encoding existing ampersands; this is the PHP5.2.3 req.)
                        .htmlspecialchars ($m[3][0].@$m[4][0].@$m[5][0], ENT_COMPAT, 'UTF-8', false).'"'
                        //is the URL external? if so add the rel attributes
                        .($p.$m[3][0] !== FORUM_URL ? ' rel="nofollow external"' : '')
                .'>'    //the link-text
                        .$m[0][0]
                .'</a>',
                //where to substitute
                $m[0][1], strlen ($m[0][0])
        );
        
        /* inline formatting:
           -------------------------------------------------------------------------------------------------------------- */
        $text = preg_replace (
                //example: _italic_ & *bold*
                array ('/(?<=\s|^)_(?!_)(.*?)(?<!_)_(?=\s|$)/m',        '/(?<![*\w])\*(?!\*)(.*?)(?<!\*)\*(?![*\w])/'),
                array ('<em>_$1_</em>',                                 '<strong>*$1*</strong>'),
        $text);
        
        /* divider: "---"
           -------------------------------------------------------------------------------------------------------------- */
        $text = preg_replace (
                '/(?:\n|\A)\h*(---+)\h*(?:\n?$|\Z)/m',                  "\n\n<p class=\"hr\">$1</p>\n",
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
        ),      //extra quote marks are inserted in the spans for both themeing, and so that when you copy a quote, the
                //nesting is preserved for you. there must be a line break between spans and the text otherwise it prevents
                //the regex from finding quote marks at the ends of lines (these extra linebreaks are removed next)
                "\n\n<blockquote>\n\n".
                        "<span class=\"ql\">&ldquo;</span>\n$2\n<span class=\"qr\">&rdquo;</span>\n\n".
                "</blockquote>\n",
                $text, -1, $c
        ); while ($c);
        
        //remove the extra linebreaks addeded between our theme quotes
        //(required so that extra `<br />`s don’t get added!)
        $text = preg_replace (
                array ('/&ldquo;<\/span>\n(?!\n)/',     '/\n<span class="qr">/'),
                array ('&ldquo;</span>',                '<span class="qr">'),
        $text);
        
        /* name references:
           -------------------------------------------------------------------------------------------------------------- */
        //name references (e.g. "@bob") will link back to the last reply in the thread made by that person.
        //this requires that the whole RSS thread is passed to this function to refer to
        if (!is_null ($rss)) {
                //first, produce a list of all authors in the thread
                $names = array ();
                foreach ($rss->channel->xpath ('./item/author') as $name) $names[] = $name[0];
                $names = array_unique ($names);                 //remove duplicates
                $names = array_map ('strtolower', $names);      //set all to lowercase
                $names = array_map ('safeHTML',   $names);      //HTML encode names as they will be in the source text
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
                        {       //replace the reference with the link to the post
                                $text = substr_replace ($text,
                                        //TODO: `safeHTML` isn't quote safe
                                        '<a href="'.safeHTML ($item->link).'"'.(isMod ($name) ? ' class="nnf_mod"' : '').'>'
                                                .substr ($m[1][0], 0, strlen ($name)+1).
                                        '</a>',
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
        
        /* titles
           -------------------------------------------------------------------------------------------------------------- */
        //example: :: title
        $replace = ''; $titles=array (); while (preg_match (
                '/(?:\n|\A)(::.*)(?:\n?$|\Z)/mu',
                //capture the starting point of the match, so that `$m[x][0]` is the text and $m[x][1] is the offset
                $text, $m, PREG_OFFSET_CAPTURE,
                //use an offset to search from so we don’t get stuck in an infinite loop
                //(this isn’t valid the first time around obviously so gives 0)
                @($m[0][1] + strlen ($replace))
        )) {
                //generate a unique HTML ID for the title:
                //flatten the title text into a URL-safe string of [a-z0-9_]
                $translit = safeTransliterate (strip_tags ($m[1][0]));
                //if a title already exsits with that ID, append a number until an available ID is found.
                $c = 0; do $id = $translit.($c++ ? '_'.($c-1) : ''); while (in_array ($id, $titles));
                //add the current ID to the list of used IDs
                $titles[] = $id;
                //remove hyperlinks in the title (since the title will be a hyperlink too)
                //if a user-link is present, keep the mod class if present
                $m[1][0] = preg_replace ('/<a href="[^"]+"( class="nnf_mod")?>(.*?)<\/a>/', "<b$1>$2</b>", $m[1][0]);
                //create the replacement HTML, including an anchor link
                $text = substr_replace ($text, $replace =
                        //(note: code spans in titles don't transliterate since they've been replaced with placeholders)
                        "\n\n<h2 id=\"$post_id::$id\">".
                                //TODO: `safeHTML` isn't quote-safe
                                "<a href=\"".safeHTML ($permalink)."#$post_id::$id\">".$m[1][0]."</a>".
                        "</h2>\n",
                        //where to substitute
                        $m[0][1], strlen ($m[0][0])
                );
        }
        
        /* finalise:
           -------------------------------------------------------------------------------------------------------------- */
        //add paragraph tags between blank lines
        foreach (preg_split ('/\n{2,}/', safeTrim ($text), -1, PREG_SPLIT_NO_EMPTY) as $chunk) {
                //if not a blockquote, title, hr or pre-block, wrap in a paragraph
                if (!preg_match ('/^<\/?(?:bl|h2|p)|^&PRE_/', $chunk))
                        $chunk = "<p>\n".str_replace ("\n", "<br />\n", $chunk)."\n</p>"
                ;
                $text = @$result .= "\n$chunk";
        }
        
        //restore code spans/blocks
        foreach ($code as $i => $html) $text = str_replace ("&CODE_$i;", $html, $text);
        foreach ($pre  as $i => $html) $text = str_replace ("&PRE_$i;",  $html, $text);
        return $text;
}

//reverse the text formatting, turning HTML back into plain-text markup,
//this is used to append text to existing posts whilst ensuring unique heading IDs
function unformatText ($text) {
        return html_entity_decode (strip_tags ($text), ENT_COMPAT, 'UTF-8');
}

/* ====================================================================================================================== */

//regenerate a folder's RSS file (all changes happening in a folder)
function indexRSS () {
        /* create an RSS feed
           -------------------------------------------------------------------------------------------------------------- */
        $rss = new DOMTemplate (file_get_contents (FORUM_LIB.'rss-template.xml'));
        $rss->set (array (
                '/rss/channel/title'    => FORUM_NAME.(PATH ? str_replace ('/', ' / ', PATH) : ''),
                '/rss/channel/link'     => FORUM_URL.url (PATH_URL)
        //remove the locked / deleted categories
        ))->remove ('/rss/channel/category');
        
        //get list of threads, sort by date; most recently modified first
        $threads = preg_grep ('/\.rss$/', scandir ('.'));
        array_multisort (array_map ('filemtime', $threads), SORT_DESC, $threads);
        
        $items = $rss->repeat ('/rss/channel/item');
        //get the last post made in each thread as an RSS item
        foreach (array_slice ($threads, 0, FORUM_THREADS) as $thread)
                if ($xml  = @simplexml_load_file ($thread))     //if the RSS feed is valid
                if ($item = @$xml->channel->item[0])            //if the feed has any items
                $items->set (array (
                        './title'       => $item->title,
                        './link'        => $item->link,
                        './author'      => $item->author,
                        './pubDate'     => gmdate ('r', strtotime ($item->pubDate)),
                        './description' => $item->description
                ))->remove (array (
                        './category[.="deleted"]' => !$item->xpath ('category[.="deleted"]'),
                ))->next ()
        ;
        file_put_contents ('index.xml', $rss);
        
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
                        './x:loc'       => FORUM_URL.FORUM_PATH.($folder ? safeURL ("$folder/", false) : '').'index.xml',
                        './x:lastmod'   => gmdate ('r', strtotime ($rss->channel->item[0]->pubDate))
                ))->next ()
        ;
        file_put_contents (FORUM_ROOT.DIRECTORY_SEPARATOR.'sitemap.xml', $xml);
        
        //you saw nothing, right?
        clearstatcache ();
}

?>