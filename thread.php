<?php //display a particular thread’s contents
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//bootstrap the forum; you should read that file first
require_once './start.php';

//get the post message, the other fields (name / pass) are retrieved automatically in 'start.php'
define ('TEXT', mb_substr (@$_POST['text'], 0, SIZE_TEXT));

//which thread to show
//TODO: an error here should generate a 404, but we can't create a 404 in PHP that will send the server's provided 404 page.
//      I may revist this if I create an NNF-provided 404 page
$FILE   = (preg_match ('/^[_a-z0-9-]+$/', @$_GET['file']) ? $_GET['file'] : '') or die ('Malformed request');

//load the thread (have to read lock status from the file)
//TODO: if file is missing, give 404, as above
$xml    = @simplexml_load_file ("$FILE.rss") or require FORUM_LIB.'error_xml.php';
$thread = $xml->channel->xpath ('item');

//handle a rounding problem with working out the number of pages (PHP 5.3 has a fix for this)
$PAGES = count ($thread) % FORUM_POSTS == 1 ? floor (count ($thread) / FORUM_POSTS) : ceil (count ($thread) / FORUM_POSTS);
//validate the page number, when no page number is specified default to the last page
$PAGE  = !PAGE || PAGE > $PAGES ? $PAGES : PAGE;

//access rights for the current user
define ('CAN_REPLY', FORUM_ENABLED && (
        //- if you are a moderator (doesn’t matter if the forum or thread is locked)
        IS_MOD ||
        //- if you are a member, the forum lock doesn’t matter, but you can’t reply to locked threads (only mods can)
        (!(bool) $xml->channel->xpath ('category[.="locked"]') && IS_MEMBER) ||
        //- if you are neither a mod nor a member, then as long as:
        //  1. the *thread* is not locked, and
        //  2. the *forum* is such that anybody can reply (unlocked or news/thread-locked), then you can reply
        (!(bool) $xml->channel->xpath ('category[.="locked"]') && (FORUM_LOCK != 'posts'))
));

/* ======================================================================================================================
   thread stick / unstick action
   ====================================================================================================================== */
if (    (isset ($_POST['stick']) || isset ($_POST['unstick'])) &&
        //the site admin, or the first mod of the sub-forum have stick / unstick rights
        (IS_ADMIN || strtolower (NAME) === strtolower ((string) @$MODS['LOCAL'][0]))
) {
        //add or remove the filename from "sticky.txt"
        if (in_array ("$FILE.rss", $stickies = getStickies ())) {
                $stickies = array_diff ($stickies, array ("$FILE.rss"));
        } else {
                $stickies[] = "$FILE.rss";
        };
        
        file_put_contents ('sticky.txt', implode ("\r\n", $stickies), LOCK_EX);
        
        //regenerate the folder's RSS file
        indexRSS ();
        
        //redirect to eat the form submission
        header ("Location: $url", true, 303);
        exit;
}

/* ======================================================================================================================
   thread lock / unlock action
   ====================================================================================================================== */
if ((isset ($_POST['lock']) || isset ($_POST['unlock'])) && IS_MOD) {
        //get a read/write lock on the file so that between now and saving, no other posts could slip in
        //normally we could use a write-only lock 'c', but on Windows you can't read the file when write-locked!
        $f   = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
        //we have to read the XML using the file handle that's locked because in Windows, functions like
        //`get_file_contents`, or even `simplexml_load_file`, won't work due to the lock
        $xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss"))) or require FORUM_LIB.'error_xml.php';
        
        //if there’s a "locked" category, remove it
        if ((bool) $xml->channel->xpath ('category[.="locked"]')) {
                //note: for simplicity this removes *all* channel categories as NNF only uses one at the moment,
                //      in the future the specific "locked" category needs to be removed
                unset ($xml->channel->category);
                //when unlocking, go to the thread
                $url = FORUM_URL.url (PATH_URL, $FILE).'#nnf_reply-form';
        } else {
                //if no "locked" category, add it
                $xml->channel->category[] = 'locked';
                //if locking, return to the index
                //(TODO: could return to the particular page in the index the thread is on--complex!)
                $url = FORUM_URL.url (PATH_URL);
        }
        
        //commit the data
        rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
        //close the lock / file
        flock ($f, LOCK_UN); fclose ($f);
        
        //try set the modified date of the file back to the time of the last reply
        //(un/locking a thread does not push the thread back to the top of the index)
        //note: this may fail if the file is not owned by the Apache process
        @touch ("$FILE.rss", strtotime ($xml->channel->item[0]->pubDate));
        
        //regenerate the folder's RSS file
        indexRSS ();
        
        //redirect to eat the form submission
        header ("Location: $url", true, 303);
        exit;
}


/* ======================================================================================================================
   append link clicked
   ====================================================================================================================== */
if ($ID = (preg_match ('/^[A-Z0-9]+$/i', @$_GET['append']) ? $_GET['append'] : false)) {
        //get a write lock on the file so that between now and saving, no other posts could slip in
        $f   = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
        $xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss"))) or require FORUM_LIB.'error_xml.php';
        
        //find the post using the ID (we need to know the numerical index for later)
        for ($i=0; $i<count ($xml->channel->item); $i++) if (strstr ($xml->channel->item[$i]->link, '#') == "#$ID") break;
        $post = $xml->channel->item[$i];
        
        /* has the un/pw been submitted to authenticate the append?
           -------------------------------------------------------------------------------------------------------------- */
        if (AUTH && TEXT && CAN_REPLY && (
                //a moderator can always append
                IS_MOD ||
                //the owner of a post can append
                (strtolower (NAME) == strtolower ($post->author) && (
                        //if the forum is post-locked, they must be a member to append to their own posts
                        (FORUM_LOCK != 'posts') || IS_MEMBER
                ))
        )) {
                //check for duplicate append:
                if (    //normalise the original post and the append, and check the end of the original for a match
                        substr (unformatText ($post->description), -strlen ($_ = unformatText (formatText (TEXT)))) !== $_
                ) {     
                        //append the given text to the reply
                        $post->description = formatText (
                                //NNF's markup is unique in that it is fully reversable just by stripping the HTML tags!
                                //to ensure that appended title links do not duplicate title links in the existing text, we
                                //convert the original HTML back to markup and add the appended text.
                                //(`THEME_APPENDED` is defined in 'start.php' and is a shorthand to the translated string
                                // used as a divider when appending text to a post)
                                unformatText ($post->description)."\n\n".sprintf (THEME_APPENDED,
                                        safeHTML (NAME), date (DATE_FORMAT, time ())
                                )."\n\n".TEXT,
                                //provide the permalink to the thread and the post ID for title's self-link ID uniqueness
                                FORUM_URL.url (PATH_URL, $FILE, $PAGE), $ID,
                                //provide access to the whole discussion thread to be able to link "@user" names
                                $xml
                        );
                        
                        //commit the data
                        rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
                        //close the lock / file
                        flock ($f, LOCK_UN); fclose ($f);
                        
                        //try set the modified date of the file back to the time of the last reply
                        //(appending to a post does not push the thread back to the top of the index)
                        //note: this may fail if the file is not owned by the Apache process
                        @touch ("$FILE.rss", strtotime ($xml->channel->item[0]->pubDate));
                        
                        //regenerate the folder's RSS file
                        indexRSS ();
                }
                
                //return to the appended post
                header ('Location: '.FORUM_URL.url (PATH_URL, $FILE, $PAGE)."#$ID", true, 303);
                exit;
        }
        
        //close the lock / file
        flock ($f, LOCK_UN); fclose ($f);
        
        /* template the append page
           -------------------------------------------------------------------------------------------------------------- */
        $template = prepareTemplate (
                //template, no canonical URL  //HTML title
                THEME_ROOT.'append.html', '', sprintf (THEME_TITLE_APPEND, $post->title)
        //the preview post:
        )->set (array (
                '#nnf_post-title'               => $xml->channel->title,
                '#nnf_post-title@id'            => substr (strstr ($post->link, '#'), 1),
                'time#nnf_post-time'            => date (DATE_FORMAT, strtotime ($post->pubDate)),
                'time#nnf_post-time@datetime'   => gmdate ('r', strtotime ($post->pubDate)),
                '#nnf_post-author'              => $post->author
        ))->setValue (
                '#nnf_post-text', $post->description, true
        )->remove (array (
                //if the user who made the post is a mod, also mark the whole post as by a mod
                //(you might want to style any posts made by a mod differently)
                '.nnf_post@class, #nnf_post-author@class' => !isMod ($post->author) ? 'nnf_mod' : false
        
        //the append form:
        ))->set (array (
                //set the field values from what was typed in before
                'input#nnf_name-field-http@value'       => NAME, //set the maximum field sizes
                'input#nnf_name-field@value'            => NAME, 'input#nnf_name-field@maxlength'       => SIZE_NAME,
                'input#nnf_pass-field@value'            => PASS, 'input#nnf_pass-field@maxlength'       => SIZE_PASS,
                'textarea#nnf_text-field'               => TEXT, 'textarea#nnf_text-field@maxlength'    => SIZE_TEXT
                
        //is the user already signed-in?
        ))->remove (AUTH_HTTP
                //don’t need the usual name / password fields and the deafult message for anonymous users
                ? '#nnf_name, #nnf_pass, #nnf_email, #nnf_error-none-append'
                //user is not signed in, remove the "you are signed in as:" field and the message for signed in users
                : '#nnf_name-http, #nnf_error-none-http'
                
        //handle error messages
        )->remove (array (
                //if there's an error of any sort, remove the default messages
                '#nnf_error-none-append, #nnf_error-none-http' => FORM_SUBMIT,
                //if the username & password are correct, remove the error message
                '#nnf_error-auth-append' => !FORM_SUBMIT || !TEXT || !NAME || !PASS || AUTH,
                //if the password is valid, remove the error message
                '#nnf_error-pass-append' => !FORM_SUBMIT || !TEXT || !NAME || PASS,
                //if the name is valid, remove the error message
                '#nnf_error-name-append' => !FORM_SUBMIT || !TEXT || NAME,
                //if the message text is valid, remove the error message
                '#nnf_error-text'        => !FORM_SUBMIT || TEXT
        ));
        
        //call the theme-specific templating function, in 'theme.php', before outputting
        theme_custom ($template);
        exit ($template);
}


/* ======================================================================================================================
   delete link clicked
   ====================================================================================================================== */
if (isset ($_GET['delete'])) {
        //the ID of the post to delete. will be omitted if deleting the whole thread
        $ID = (preg_match ('/^[A-Z0-9]+$/i', @$_GET['delete']) ? $_GET['delete'] : false);
        //get a write lock on the file so that between now and saving, no other posts could slip in
        $f = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
        
        //load the thread to get the post preview
        $xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss"))) or require FORUM_LIB.'error_xml.php';
        
        //access the particular post. if no ID is provided (deleting the whole thread) use the last item in the RSS file
        //(the first post), otherwise find the ID of the specific post
        if (!$ID) {
                $post = $xml->channel->item[count ($xml->channel->item) - 1];
        } else {
                //find the post using the ID (we need to know the numerical index for later)
                for ($i=0; $i<count ($xml->channel->item); $i++) if (
                        strstr ($xml->channel->item[$i]->link, '#') == "#$ID"
                ) break;
                $post = $xml->channel->item[$i];
        }
        
        /* has the un/pw been submitted to authenticate the delete?
           -------------------------------------------------------------------------------------------------------------- */
        if (AUTH && CAN_REPLY && (
                //a moderator can always delete
                IS_MOD ||
                //the owner of a post can delete
                (strtolower (NAME) == strtolower ($post->author) && (
                        //if the forum is post-locked, they must be a member to delete their own posts
                        (FORUM_LOCK != 'posts') || IS_MEMBER
                ))
        //deleting a post?
        )) if ($ID) {
                if ((   //full delete? (option ticked, is moderator, and post is on the last page)
                        (IS_MOD && $i <= (count ($xml->channel->item)-2) % FORUM_POSTS) &&
                        //if the post has already been blanked, delete it fully
                        (isset ($_POST['remove']) || $post->xpath ('category[.="deleted"]'))
                ) ||    //if the post is corrupt, remove it entirely instead of blanking it
                        @simplexml_load_string (
                                //most HTML entities are not allowed in XML, we need to convert these to test XML validity
                                '<body>'.DOMTemplate::html_entity_decode ($post->description).'</body>'
                        ) === false      
                ) {
                        //remove the post from the thread entirely
                        unset ($xml->channel->item[$i]);
                        
                        //we’ll redirect to the last page (which may have changed number when the post was deleted)
                        $url = FORUM_URL.url (PATH_URL, $FILE).'#nnf_replies';
                } else {
                        //remove the post text and replace with the deleted messgae
                        $post->description = (NAME == (string) $post->author) ? THEME_DEL_USER : THEME_DEL_MOD;
                        //add a "deleted" category so we know to no longer allow it to be edited or deleted again
                        if (!$post->xpath ('category[.="deleted"]')) $post->category[] = 'deleted';
                        
                        //need to know what page this post is on to redirect back to it
                        $url = FORUM_URL.url (PATH_URL, $FILE, $PAGE)."#$ID";
                }
                
                //commit the data
                rewind ($f); ftruncate ($f, 0); fwrite ($f, $xml->asXML ());
                //close the lock / file
                flock ($f, LOCK_UN); fclose ($f);
                
                //try set the modified date of the file back to the time of the last reply
                //(so that deleting does not push the thread back to the top of the index)
                //note: this may fail if the file is not owned by the Apache process
                @touch ("$FILE.rss", strtotime ($xml->channel->item[0]->pubDate));
                
                //regenerate the folder's RSS file
                indexRSS ();
                
                //return to the deleted post / last page
                header ("Location: $url", true, 303);
                exit;
        } else {
                //close the lock / file
                flock ($f, LOCK_UN); fclose ($f);
                
                //delete the thread for reals
                @unlink (FORUM_ROOT.PATH_DIR."$FILE.rss");
                
                //regenerate the folder's RSS file
                indexRSS ();
                
                //return to the index
                header ('Location: '.FORUM_URL.url (PATH_URL), true, 303);
                exit;
        }
        
        //close the lock / file
        flock ($f, LOCK_UN); fclose ($f);
        
        /* template the delete page
           -------------------------------------------------------------------------------------------------------------- */
        $template = prepareTemplate (
                //template, no canonical URL  //HTML title
                THEME_ROOT.'delete.html', '', sprintf (THEME_TITLE_DELETE, $post->title)
        //the preview post:
        )->set (array (
                '#nnf_post-title'               => $post->title,
                '#nnf_post-title@id'            => substr (strstr ($post->link, '#'), 1),
                'time#nnf_post-time'            => date (DATE_FORMAT, strtotime ($post->pubDate)),
                'time#nnf_post-time@datetime'   => gmdate ('r', strtotime ($post->pubDate)),
                '#nnf_post-author'              => $post->author
        ))->remove (array (
                //if the user who made the post is a mod, also mark the whole post as by a mod
                //(you might want to style any posts made by a mod differently)
                '.nnf_post@class, #nnf_post-author@class' => !isMod ($post->author) ? 'nnf_mod' : false
        
        //the authentication form:
        ))->set (array (
                //set the field values from input        //set the maximum field sizes
                'input#nnf_name-field@value'    => NAME, 'input#nnf_name-field@maxlength'       => SIZE_NAME,
                'input#nnf_pass-field@value'    => PASS, 'input#nnf_pass-field@maxlength'       => SIZE_PASS
                
        //are we deleting the whole thread, or just one reply?
        ))->remove ($ID
                ? '#nnf_error-none-thread'
                : '#nnf_error-none-reply, #nnf_remove'  //if deleting the whole thread, also remove the checkbox option
                
        //handle error messages
        )->remove (array (
                //if there's an error of any sort, remove the default messages
                '#nnf_error-none-thread, #nnf_error-none-reply' => FORM_SUBMIT,
                //if the username & password are correct, remove the error message
                '#nnf_error-auth-delete' => !FORM_SUBMIT || !NAME || !PASS || AUTH,
                //if the password is valid, remove the error message
                '#nnf_error-pass-delete' => !FORM_SUBMIT || !NAME || PASS,
                //if the name is valid, remove the error message
                '#nnf_error-name-delete' => !FORM_SUBMIT || NAME
        ));
        
        try {   //insert the post-text, dealing with an invalid HTML error
                $template->setValue ('#nnf_post-text', $post->description, true);
                $template->remove (array ('.nnf_post@class' => 'nnf_error'));
        } catch (Exception $e) {
                //if the HTML was invalid, replace with the corruption message
                $template->setValue ('#nnf_post-text', THEME_HTML_ERROR, true);
        }
        
        //call the theme-specific templating function, in 'theme.php', before outputting
        theme_custom ($template);
        exit ($template);
}


/* ======================================================================================================================
   new reply submitted
   ====================================================================================================================== */
//was the submit button clicked? (and is the info valid?)
if (CAN_REPLY && AUTH && TEXT) {
        //get a read/write lock on the file so that between now and saving, no other posts could slip in
        //normally we could use a write-only lock 'c', but on Windows you can't read the file when write-locked!
        $f = fopen ("$FILE.rss", 'r+'); flock ($f, LOCK_EX);
        //we have to read the XML using the file handle that's locked because in Windows, functions like
        //`get_file_contents`, or even `simplexml_load_file`, won't work due to the lock
        $xml = simplexml_load_string (fread ($f, filesize ("$FILE.rss"))) or require FORUM_LIB.'error_xml.php';
        
        //ignore a double-post (could be an accident with the back button)
        if (    //same author?
                NAME == $xml->channel->item[0]->author &&
                //check if the markup text is the same (strips out HTML due to possible unique HTML IDs)
                strip_tags ($xml->channel->item[0]->description) == strip_tags (formatText (TEXT))
        ) {
                //if you can't post / double-post, redirect back to the previous post
                $url = $xml->channel->item[0]->link;
        } else {
                //where will this post exist?
                $post_id = base_convert (microtime (), 10, 36);
                $page = (count ($thread)+1) % FORUM_POSTS == 1
                        ? floor ((count ($thread)+1) / FORUM_POSTS)
                        : ceil  ((count ($thread)+1) / FORUM_POSTS)
                ;
                $url = FORUM_URL.url (PATH_URL, $FILE, $page).'#'.$post_id;
                
                //re-template the whole thread:
                $rss = new DOMTemplate (file_get_contents (FORUM_LIB.'rss-template.xml'));
                $rss->set (array (
                        '/rss/channel/title'            => $xml->channel->title,
                        '/rss/channel/link'             => FORUM_URL.url (PATH_URL, $FILE)
                ))->remove (array (
                        //is the thread unlocked?
                        '/rss/channel/category'         => !$xml->channel->xpath ('category[.="locked"]')
                ));
                
                //template the new reply first
                $items = $rss->repeat ('/rss/channel/item');
                $items->set (array (
                        //add the "RE:" prefix, and reply number to the title
                        './title'               => sprintf (THEME_RE,
                                                        count ($xml->channel->item),    //number of the reply
                                                        $xml->channel->title            //thread title
                                                ),
                        './link'                => $url,
                        './author'              => NAME,
                        './pubDate'             => gmdate ('r'),
                        './description'         => formatText (TEXT,  //process markup into HTML
                                                        //provide a permalink and post ID for title self-links
                                                        FORUM_URL.url (PATH_URL, $FILE, $page), $post_id,
                                                        //provide reference to the thread to link "@user" names
                                                        $xml
                                                )
                ))->remove (
                        //the new reply isn’t deleted, so remove the category marker
                        './category'
                )->next ();
                
                //copy the remaining replies across
                foreach ($xml->channel->item as $item) $items->set (array (
                        './title'               => $item->title,
                        './link'                => $item->link,
                        './author'              => $item->author,
                        './pubDate'             => $item->pubDate,
                        './description'         => $item->description
                ))->remove (array (
                        //has the reply been deleted? (blanked)
                        './category'            => !$item->xpath ('./category')
                ))->next ();
                
                //write the file: first move the write-head to 0, remove the file's contents, and then write new one
                rewind ($f); ftruncate ($f, 0); fwrite ($f, $rss);
        }
        
        //close the lock / file
        flock ($f, LOCK_UN); fclose ($f);
        
        //regenerate the forum / sub-forums's RSS file
        indexRSS ();
        
        //refresh page to see the new post added
        header ("Location: $url", true, 303);
        exit;
}


/* ======================================================================================================================
   template thread
   ====================================================================================================================== */
//is this thread stickied?
define ('IS_STICKY', in_array ("$FILE.rss", $stickies = getStickies ()));

/* load the template into DOM where we can manipulate it:
   --------------------------------------------------------------------------------------------------------------------- */
//(see 'lib/domtemplate/domtemplate.php' or <camendesign.com/dom_templating> for more details)
$template = prepareTemplate (
        THEME_ROOT.'thread.html',
        //canonical URL of this thread
        url (PATH_URL, $FILE, $PAGE),
        //HTML title:
        sprintf (THEME_TITLE,
                //title of the thread, obviously
                $xml->channel->title,
                //if on page 2 or greater, include the page number in the title
                $PAGE>1 ? sprintf (THEME_TITLE_PAGENO, $PAGE) : ''
        )
)->set (array (
        //the thread itself is the RSS feed :)
        '//link[@rel="alternate"]/@href, '.
        'a#nnf_rss@href'                => FORUM_PATH.PATH_URL."$FILE.rss"
))->remove (array (
        //if replies can't be added (forum or thread is locked, user is not moderator / member),
        //remove the "add reply" link and anything else (like the input form) related to posting
        '#nnf_reply, #nnf_reply-form'   => !CAN_REPLY,
        //if the forum is not post-locked (only mods can post / reply) then remove the warning message
        '.nnf_forum-locked'             => FORUM_LOCK != 'posts',
        //is the user a mod and can un/lock or un/stick the thread?
        '#nnf_admin'                    => !IS_MOD,
        //is the thread already locked?
        '#nnf_lock'                     =>  $xml->channel->xpath ('category[.="locked"]'),
        '#nnf_unlock'                   => !$xml->channel->xpath ('category[.="locked"]'),
        //is the thread already stickied?
        '#nnf_stick'                    =>  IS_STICKY,
        '#nnf_unstick'                  => !IS_STICKY
));

/* post
   ---------------------------------------------------------------------------------------------------------------------- */
//take the first post from the thread (removing it from the rest)
$post = array_pop ($thread);
//remember the original poster’s name, for marking replies by the OP
$author = (string) $post->author;

//prepare the first post, which on this forum appears above all pages of replies
$template->set (array (
        '#nnf_post-title'               => $xml->channel->title,
        'time#nnf_post-time'            => date (DATE_FORMAT, strtotime ($post->pubDate)),
        'time#nnf_post-time@datetime'   => gmdate ('r', strtotime ($post->pubDate)),
        '#nnf_post-author'              => $post->author,
        'a#nnf_post-append@href'        => url (PATH_URL, $FILE, $PAGE, 'append',
                                                substr (strstr ($post->link, '#'), 1)).'#append',
        'a#nnf_post-delete@href'        => url (PATH_URL, $FILE, $PAGE, 'delete')
))->remove (array (
        //if the user who made the post is a mod, also mark the whole post as by a mod
        //(you might want to style any posts made by a mod differently)
        '.nnf_post@class, #nnf_post-author@class' => !isMod ($post->author) ? 'nnf_mod' : false,
        
        //append / delete links?
        '#nnf_post-append, #nnf_post-delete' => !CAN_REPLY
));

try {   //insert the post-text, dealing with an invalid HTML error
        $template->setValue ('#nnf_post-text', $post->description, true);
        $template->remove (array ('.nnf_post@class' => 'nnf_error'));
} catch (Exception $e) {
        //if the HTML was invalid, replace with the corruption message
        $template->setValue ('#nnf_post-text', THEME_HTML_ERROR, true);
        //remove the append button
        $template->remove ('#nnf_post-append');
}

/* replies
   ---------------------------------------------------------------------------------------------------------------------- */
if (!count ($thread)) {
        $template->remove ('#nnf_replies');
} else {
        //sort the other way around
        //<stackoverflow.com/questions/2119686/sorting-an-array-of-simplexml-objects/2120569#2120569>
        foreach ($thread as &$node) $sort[] = strtotime ($node->pubDate);
        array_multisort ($sort, SORT_ASC, $thread);
        
        //do the page links
        theme_pageList ($template, $FILE, $PAGE, $PAGES);
        //slice the full list into the current page
        $thread = array_slice ($thread, ($PAGE-1) * FORUM_POSTS, FORUM_POSTS);
        
        //get the dummy list-item to repeat (removes it and takes a copy)
        $item = $template->repeat ('.nnf_reply');
        
        //index number of the replies, accounting for which page we are on
        $no = ($PAGE-1) * FORUM_POSTS;
        //apply the data to the template (a reply)
        foreach ($thread as &$reply) {
                $item->set (array (
                        './@id'                         => substr (strstr ($reply->link, '#'), 1),
                        'time.nnf_reply-time'           => date (DATE_FORMAT, strtotime ($reply->pubDate)),
                        'time.nnf_reply-time@datetime'  => gmdate ('r', strtotime ($reply->pubDate)),
                        '.nnf_reply-author'             => $reply->author,
                        'a.nnf_reply-number'            => sprintf (THEME_REPLYNO, ++$no),
                        'a.nnf_reply-number@href'       => url (PATH_URL, $FILE, $PAGE).strstr ($reply->link,'#'),
                        'a.nnf_reply-append@href'       => url (PATH_URL, $FILE, $PAGE, 'append',
                                                                substr (strstr ($reply->link, '#'), 1)).'#append',
                        'a.nnf_reply-delete@href'       => url (PATH_URL, $FILE, $PAGE, 'delete',
                                                                substr (strstr ($reply->link, '#'), 1))
                ))->remove (array (
                        //has the reply been deleted (blanked)?
                        './@class'                      => $reply->xpath ('category[.="deleted"]') ? false : 'nnf_deleted',
                ))->remove (array (
                        //is this reply from the person who started the thread?
                        './@class'                      => strtolower ($reply->author) == strtolower ($author) ? false :'nnf_op'
                ))->remove (array (
                        //if the user who made the reply is a mod, also mark the whole post as by a mod
                        //(you might want to style any posts made by a mod differently)
                        './@class, .nnf_reply-author@class' => isMod ($reply->author) ? false : 'nnf_mod'
                ))->remove (array (
                        //if the current user in the curent forum can append/delete the current reply:
                        '.nnf_reply-append, .nnf_reply-delete' => !(CAN_REPLY && (
                                //moderators can always see append/delete links on all replies
                                IS_MOD ||
                                //if you are not signed in, all append/delete links are shown (if forum/thread locking is off)
                                //if you are signed in, then only links on replies with your name will show
                                !AUTH_HTTP ||
                                //if this reply is the by the owner (they can append/delete to their own replies)
                                (strtolower (NAME) == strtolower ($reply->author) && (
                                        //if the forum is post-locked, they must be a member to append/delete their own replies
                                        (FORUM_LOCK != 'posts') || IS_MEMBER
                                ))
                        )),
                        //append link not available when the reply has been deleted
                        '.nnf_reply-append' => $reply->xpath ('category[.="deleted"]'),
                        //delete link not available when the reply has been deleted, except to mods
                        '.nnf_reply-delete' => $reply->xpath ('category[.="deleted"]') && !IS_MOD
                ));
                
                try {   //insert the post-text, dealing with an invalid HTML error
                        $item->setValue ('.nnf_reply-text', $reply->description, true);
                        $item->remove (array ('./@class' => 'nnf_error'));
                } catch (Exception $e) {
                        //if the HTML was invalid, replace with the corruption message
                        $item->setValue ('.nnf_reply-text', THEME_HTML_ERROR, true);
                        //remove the append button
                        $item->remove ('.nnf_reply-append');
                }
                
                $item->next ();
        }
}

/* reply form
   ---------------------------------------------------------------------------------------------------------------------- */
if (CAN_REPLY) $template->set (array (
        //set the field values from what was typed in before
        'input#nnf_name-field-http@value'       => NAME, //set the maximum field sizes
        'input#nnf_name-field@value'            => NAME, 'input#nnf_name-field@maxlength'       => SIZE_NAME,
        'input#nnf_pass-field@value'            => PASS, 'input#nnf_pass-field@maxlength'       => SIZE_PASS,
        'textarea#nnf_text-field'               => TEXT, 'textarea#nnf_text-field@maxlength'    => SIZE_TEXT
        
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
        '#nnf_error-auth'  => !FORM_SUBMIT || !TEXT || !NAME || !PASS || AUTH,
        //if the password is valid, remove the error message
        '#nnf_error-pass'  => !FORM_SUBMIT || !TEXT || !NAME || PASS,
        //if the name is valid, remove the error message
        '#nnf_error-name'  => !FORM_SUBMIT || !TEXT || NAME,
        //if the message text is valid, remove the error message
        '#nnf_error-text'  => !FORM_SUBMIT || TEXT
));

//call the theme-specific templating function, in 'theme.php', before outputting
theme_custom ($template);
exit ($template);

?>