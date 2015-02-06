<?php //display the index of threads in a folder
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './start.php';

//submitted info for making a new thread
//(name / password already handled in 'start.php')
define ('TITLE', mb_substr (@$_POST['title'], 0, SIZE_TITLE));
define ('TEXT',  mb_substr (@$_POST['text'],  0, SIZE_TEXT ));

//can the current user post new threads in the current forum?
//(posting replies is dependent on the the thread -- if locked -- so tested in 'thread.php')
define ('CAN_POST', FORUM_ENABLED && (
        //- if the user is a moderator or member of the current forum, they can post
        IS_MOD || IS_MEMBER ||
        //- if the forum is unlocked (mods will have to log in to see the form)
        !FORUM_LOCK
));

/* ======================================================================================================================
   new thread submitted
   ====================================================================================================================== */
//has the user submitted a new thread?
//(`AUTH` will be true if username and password submitted and correct, `TITLE` and `TEXT` are checked to not be blank)
if (CAN_POST && AUTH && TITLE && TEXT) {
        //the file on disk is a simplified version of the title; see 'lib/functions.php' for `safeTransliterate`
        $translit = safeTransliterate (TITLE);
        
        //if a thread already exsits with that name, append a number until an available filename is found.
        //we also check for directories with the same name so as to avoid problematic Apache behaviour
        $c = 0; do $file = $translit.($c++ ? '_'.($c-1) : '');
        while (file_exists ("$file") || file_exists ("$file.rss"));
        
        //write out the new thread as an RSS file:
        $post_id = base_convert (microtime (), 10, 36);
        $rss = new DOMTemplate (file_get_contents (FORUM_LIB.'rss-template.xml'));
        $rss->set (array (
                '/rss/channel/title'            => TITLE,
                '/rss/channel/link'             => FORUM_URL.url (PATH_URL, $file),
                //the thread's first post
                '/rss/channel/item/title'       => TITLE,
                '/rss/channel/item/link'        => FORUM_URL.url (PATH_URL, $file).'#'.$post_id,
                '/rss/channel/item/author'      => NAME,
                '/rss/channel/item/pubDate'     => gmdate ('r'),
                '/rss/channel/item/description' => formatText (TEXT,  //process markup into HTML...
                                                        //provide a permalink so that title lines link to themselves
                                                        FORUM_URL.url (PATH_URL, $file, 1),
                                                        //also provide the post ID for title-linking and ID-uniqueness
                                                        $post_id
                                                )
        //remove the locked / deleted categories
        ))->remove ('//category');
        
        file_put_contents ("$file.rss", $rss) or require FORUM_LIB.'error_permissions.php';
        
        //regenerate the folder's RSS file
        indexRSS ();
        
        //redirect to newley created thread
        header ('Location: '.FORUM_URL.url (PATH_URL, $file), true, 303);
        exit;
}


/* ======================================================================================================================
   template the page
   ====================================================================================================================== */
//first load the list of threads in the forum so that we can determine the number of pages and validate the page number,
//the thread list won't be used until further down after templating begins
if ($threads = preg_grep ('/\.rss$/', scandir ('.'))) {
        //sort the list of threads in the forum
        array_multisort (array_map (
                //if the forum is set to "news" lock, we will order the threads by their original date
                (FORUM_LOCK == 'news') ? 'filectime' : 'filemtime',
        $threads), SORT_DESC, $threads);
        
        //get sticky list (see 'lib/functions.php')
        //(the use of `array_intersect` will only return filenames in `sticky.txt` that were also in the directory)
        $stickies = array_intersect (getStickies (), $threads);
        //order the stickies
        array_multisort (array_map (
                //if the forum is set to "news" lock, we will order the threads by their original date
                (FORUM_LOCK == 'news') ? 'filectime' : 'filemtime',
        $stickies), SORT_DESC, $stickies);
        //remove the stickies from the thread list
        $threads = array_diff ($threads, $stickies);
        
        //handle a rounding problem with working out the number of pages (PHP 5.3 has a fix for this)
        $PAGES = count ($threads) % FORUM_THREADS == 1  ? floor (count ($threads) / FORUM_THREADS)
                                                        : ceil  (count ($threads) / FORUM_THREADS);
        //validate the given page number; an invalid page number returns the first instead
        $PAGE  = !PAGE || PAGE > $PAGES ? 1 : PAGE;
} else {
        $PAGES = 1; $PAGE = 1;
}

/* load the template into DOM where we can manipulate it:
   ---------------------------------------------------------------------------------------------------------------------- */
//(see 'lib/domtemplate.php' or <camendesign.com/dom_templating> for more details. `prepareTemplate` can be found in
// 'lib/functions.php' and handles some shared templating done across all pages)
$template = prepareTemplate (
        THEME_ROOT.'index.html',
        //the canonical URL of this page
        url (PATH_URL, '', $PAGE),
        //the HTML title is both templated and translatable. `THEME_TITLE` is defined in 'start.php' and is a shorthand to
        //either the default language string in 'theme.php' or the translated string in 'lang.*.php'
        sprintf (THEME_TITLE,
                //if in a sub-forum use the folder name, else the site's name
                PATH ? SUBFORUM : FORUM_NAME,
                //if on page 2 or greater, include the page number in the title
                $PAGE>1 ? sprintf (THEME_TITLE_PAGENO, $PAGE) : ''
        )
)->setValue (
        //the RSS feed for this forum / sub-forum
        'a#nnf_rss@href', FORUM_PATH.PATH_URL.'index.xml'
)->remove (array (
        //if threads can't be added (forum is disabled / locked, user is not moderator / member),
        //remove the "add thread" link and anything else (like the input form) related to posting
        '#nnf_add, #nnf_new-form'       => !CAN_POST,
        //if the forum is not thread-locked (only mods can post, anybody can reply) then remove the warning message
        '#nnf_forum-lock-threads'       => !FORUM_LOCK || FORUM_LOCK == 'posts' || IS_MOD,
        //if the forum is not post-locked (only mods can post / reply) then remove the warning message
        '#nnf_forum-lock-posts'         => FORUM_LOCK != 'posts'   || IS_MOD || IS_MEMBER
));

//an 'about.html' file can be provided to add a description or other custom HTML to the forum / sub-forum,
//for translations, 'about_en.html' can be used where 'en' is the language code for the translation
//(see 'lang.example.php' in the themes folder for more details on translation)
if ($about = @array_shift (array_filter (array (
        @file_get_contents ('about_'.LANG.'.html'), @file_get_contents ('about.html')
)))) {
        //load the 'about.html' file and insert it into the page
        $template->setValue ('#nnf_about', $about, true);
} else {
        //no file? remove the element reserved for it
        $template->remove ('#nnf_about');
}

/* sub-forums
   ---------------------------------------------------------------------------------------------------------------------- */
if ($folders = array_filter (
        //get a list of folders:
        //include only directories, but ignore directories starting with ‘.’ and the users / themes folders
        //TODO: need to do this check in a way that allows user expansion
        preg_grep ('/^(\.|users$|themes$|lib$|cgi-bin$)/', scandir ('.'), PREG_GREP_INVERT), 'is_dir'
)) {
        //get the dummy list-item to repeat (removes it and takes a copy)
        $item = $template->repeat ('.nnf_folder');
        
        foreach ($folders as $FOLDER) {
                //the sorting (below) requires we be in the directory at hand to use `filemtime`
                chdir ($FOLDER);
                
                //check if / how the forum is locked
                $lock = trim (@file_get_contents ('locked.txt'));
                
                //get a list of files in the folder to determine which one is newest
                $files = preg_grep ('/\.rss$/', scandir ('.'));
                //order by last modified date / created date ("news" forum)
                array_multisort (array_map (
                        ($lock == 'news') ? 'filectime' : 'filemtime',
                $files), SORT_DESC, $files);
                
                //read the newest thread (folder could be empty though)
                $last = ($xml = @simplexml_load_file ($files[0])) ? $xml->channel->item[0] : '';
                
                //start applying the data to the template
                $item->set (array (
                        'a.nnf_folder-name'             => $FOLDER,
                        'a.nnf_folder-name@href'        => url (PATH_URL.safeURL ($FOLDER).'/')
                
                //remove the lock icons if not required
                ))->remove (array (
                        '.nnf_lock-threads'             => $lock != 'threads',
                        '.nnf_lock-posts'               => $lock != 'posts'
                ));
                //is there a last post in this sub-forum?
                if ((bool) $last) {
                        $item->set (array (
                                //last post author name
                                '.nnf_post-author'              => $last->author,
                                //last post time (human readable)
                                'time.nnf_post-time'            => date (DATE_FORMAT, strtotime ($last->pubDate)),
                                //last post time (machine readable)
                                'time.nnf_post-time@datetime'   => date ('c', strtotime ($last->pubDate)),
                                //link to the last post
                                'a.nnf_post-link@href'          => substr ($last->link, strpos ($last->link, '/', 9))
                        ))->remove (array (
                                //is the last author a mod?
                                '.nnf_post-author@class'        => isMod ($last->author) ? false : 'nnf_mod'
                        ));
                } else {
                        //no last post, remove the template for it
                        $item->remove ('.nnf_subforum-post');
                }
                
                //attach the templated sub-forum item to the list
                $item->next ();
                
                chdir ('..');
        }
        
} else {
        //no sub-forums, remove the template stuff
        $template->remove ('#nnf_folders');
}

/* threads
   ---------------------------------------------------------------------------------------------------------------------- */
if ($threads || @$stickies) {
        //do the page links (stickies are not included in the count as they appear on all pages)
        theme_pageList ($template, '', $PAGE, $PAGES);
        //slice the full list into the current page
        $threads = array_merge ($stickies, array_slice ($threads, ($PAGE-1) * FORUM_THREADS, FORUM_THREADS));
        
        //get the dummy list-item to repeat (removes it and takes a copy)
        $item = $template->repeat ('.nnf_thread');
        
        //generate the list of threads with data, for the template
        foreach ($threads as $file) if (
                //read the file, and refer to the last post made
                $xml = @simplexml_load_file ($file)
        ) if (  //get the last post in the thread
                $last = &$xml->channel->item[0]
                //apply the data to the template
        ) $item->set (array (
                //thread title and URL
                'a.nnf_thread-name'             => $xml->channel->title,
                'a.nnf_thread-name@href'        => url (PATH_URL, pathinfo ($file, PATHINFO_FILENAME)),
                //number of replies
                '.nnf_thread-replies'           => count ($xml->channel->item) - 1,
                
                //last post info:
                //link to the last post
                'a.nnf_thread-post@href'        => substr ($last->link, strpos ($last->link, '/', 9)),
                //last post time (human readable)
                'time.nnf_thread-time'          => date (DATE_FORMAT, strtotime ($last->pubDate)),
                //last post time (machine readable)
                'time.nnf_thread-time@datetime' => date ('c', strtotime ($last->pubDate)),
                //last post author
                '.nnf_thread-author'            => $last->author
        ))->remove (array (
                //if the thread isn’t locked, remove the lock icon
                '.nnf_thread-locked'            => !$xml->channel->xpath ('category[.="locked"]'),
                //if the thread isn't sticky, remove the 'sticky' class
                './@class'                      => !in_array ($file, $stickies) ? 'nnf_sticky' : false,
                //if the thread isn't sticky, remove the sticky icon
                '.nnf_thread-sticky'            => !in_array ($file, $stickies)
                                                //the lock-icon takes precedence over the sticky icon
                                                || $xml->channel->xpath ('category[.="locked"]'),
                //is the last post author a mod?
                '.nnf_thread-author@class'      => !isMod ($last->author) ? 'nnf_mod' : false
        
        //attach the templated sub-forum item to the list
        ))->next ();
        
} else {
        //no threads, remove the template stuff
        $template->remove ('#nnf_threads');
}

/* new thread form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_POST) $template->set (array (
        //set the field values from what was typed in before    //set the maximum field sizes
        'input#nnf_title-field@value'           => TITLE,       'input#nnf_title-field@maxlength'       => SIZE_TITLE,
        'input#nnf_name-field-http@value'       => NAME,
        'input#nnf_name-field@value'            => NAME,        'input#nnf_name-field@maxlength'        => SIZE_NAME,
        'input#nnf_pass-field@value'            => PASS,        'input#nnf_pass-field@maxlength'        => SIZE_PASS,
        'textarea#nnf_text-field'               => TEXT,        'textarea#nnf_text-field@maxlength'     => SIZE_TEXT
        
//is the user already signed-in?
))->remove (AUTH_HTTP
        //don’t need the usual name / password fields and the deafult message for anonymous users
        ? '#nnf_name, #nnf_pass, #nnf_email, #nnf_error-none'
        //user is not signed in, remove the "you are signed in as:" field and the message for signed in users
        : '#nnf_name-http, #nnf_error-none-http'
        
//are new registrations allowed?
)->remove (FORUM_NEWBIES
        ? '#nnf_error-newbies'  //yes: remove the warning message
        : '#nnf_error-none'     //no:  remove the default message
        
//handle error messages
)->remove (array (
        //if there's an error of any sort, remove the default messages
        '#nnf_error-none, #nnf_error-none-http, #nnf_error-newbies' => FORM_SUBMIT,
        //if the username & password are correct, remove the error message
        '#nnf_error-auth' => !FORM_SUBMIT || !TITLE || !TEXT || !NAME || !PASS || AUTH,
        //if the password is valid, remove the error message
        '#nnf_error-pass' => !FORM_SUBMIT || !TITLE || !TEXT || !NAME || PASS,
        //if the name is valid, remove the error message
        '#nnf_error-name' => !FORM_SUBMIT || !TITLE || !TEXT || NAME,
        //if the message text is valid, remove the error message
        '#nnf_error-text' => !FORM_SUBMIT || !TITLE || TEXT,
        //if the title is valid, remove the error message
        '#nnf_error-title'=> !FORM_SUBMIT || TITLE
));

//call the theme-specific templating function, in 'theme.php', before outputting
theme_custom ($template);
exit ($template);

?>