NoNonsense Forum v19 © Copyright (CC-BY) Kroc Camen 2012
========================================================================
A simple forum that focuses on discussion and simplicity.
http://camendesign.com/nononsense_forum


How NoNonsense differs from other forums:
------------------------------------------------------------------------
:: No database
        Threads are just RSS feeds. When a reply is added, a new item
        is added to the feed.

:: No hoops to jump through
        No registration, no e-mail confirmation, no CAPTCHA. Just type
        your message, give a name and password you want to use and your
        post is made. Use the same name / password pair in the future
        to keep the same name, it’s that simple.

:: No clutter
        No user profiles. No "status updates". No signatures.
        No user ranks. Just pure discussion with none of the cruft.


Contents:
========================================================================
[1]     Things to note
[1.1]   Post appending
[1.2]   Post deleting
[2]     Sub-forums
[3]     Sticky threads
[4]     Moderators
[4.1]   Sign-in
[5]     Forum locking
[5.1]   Members
[5.2]   A note on private forums
[6]     Acknowledgements
------------------------------------------------------------------------


[1]     Things to note:
========================================================================
[1.1]   Post appending:
------------------------------------------------------------------------
Because threads are RSS feeds, the text to HTML conversion that happens
when you post is one-way. You cannot edit posts, other than manually
editing the RSS file. Instead, text can be appended to the end of posts.

*       Users can append to their own posts

*       Moderators can append to any posts

[1.2]   Post deleting:
------------------------------------------------------------------------
To avoid abuse, users cannot permenantly delete their own posts.

*       When a user deletes their post, the text is removed and
        replaced with a message like "This post was deleted by its
        owner" (or "a moderator"), the name and time remains

*       A moderator can delete any post, likewise

*       A blanked-out deleted post can be removed permenantly from the
        thread by a moderator by deleting it again, but only if the
        post is on the last page of replies -- so as to not break any
        permalinks by rearranging page boundaries


[2]     Sub-forums:
========================================================================
Sub-forums are simply folders.

*       The name of the folder is the name of the sub-forum, it can
        contain any letters allowed by your server's OS except for
        ".", "<", ">" and "&"

*       Make sure the folder has write-permissions

*       Nested sub-folders are supported, to any reasonable depth
        (E.g. '/Music/Techno/')


[3]     Sticky threads:
========================================================================
Sticky threads remain at the top of a forum, regardless of page.

*       Create a "sticky.txt" file in the forum / sub-forum

*       Add the filename of each thread you would like to sticky on
        separate lines, including the ".rss" file extension.
        For example:

the_rules.rss
f_a_q_.rss


[4]     Moderators:
========================================================================
Moderators can delete / append to any posts and delete / lock / unlock
threads.

*       Create a "mods.txt" file in the forum / sub-forum

*       Add the user name of each mod on a separate line.
        e.g.

Kroc
theraje
SpeedoJoe

*       Mods in the root forum ("/mods.txt") can moderate in all
        sub-forums, including locked ones

*       Mods in sub-forums ("/news/mods.txt") can only moderate in that
        sub-forum

[4.1]   Sign-in:
------------------------------------------------------------------------
Some moderator actions require the user to sign-in.

*       Click the "sign in" link at the bottom of the page and enter
        your name / password

*       Threads can be locked and unlocked with the relevant link at
        the bottom of the page

*       Once signed in, you must quit your browser fully (not just
        close the tab or window), or clear your browser's cache to
        sign out

*	Unfortunately, due to a flaw in HTTP authentication, users with
	accented / unicode letters in their name will not be able to
	sign-in. Moderators and members must limit their chosen names
	to basic letters, numbers and punctuation


[5]     Forum Locking:
========================================================================
The root forum and / or sub-forums can be individually restricted:

*       Create a "locked.txt" file in the forum / sub-forum

*       In the file write one of the following words: (sans-quotes)

"threads"
*       Only moderators or members of a forum can start new threads,
        but anybody can reply

"posts"
*       Only moderators or members of a forum can start new threads and
        reply. Anybody can view the forum, but won't be able to post
        unless added as a member or moderator

[5.1]   Members:
------------------------------------------------------------------------
Members are users who are not restricted in a locked forum, but do not
have moderator powers; they are your participants in restricted forums.

*       Create a "members.txt" file in the forum / sub-forum

*       Add the user name of each member on a separate line

*       Members of the root forum are not automatically members of all
        sub-forums (unlike mods)
        
*       Members must sign-in to be able to post in locked forums

[5.2]   A note on private forums:
------------------------------------------------------------------------
NoNonsense Forum does not provide an option for private forums (can
only be accessed by members) because the index RSS feed (which includes
the text of posts being made in the forum) cannot be protected without
the use of ".htpasswd" based password-protection.

If you want a private forum / sub-forum, protect the relevant directory
using ".htpasswd". Instructions are not provided here on how to
configure ".htpasswd" protection as it requires basic server admin
skills and some knowledge of using ".htaccess" files.

The users defined in the ".htpasswd" file will have to have the same
password as their NoNonsense Forum username.


[6]     Acknowledgements:
========================================================================
See LICENCE.txt for licence details

*       Jon Gjengset            - Original theme / mobile theme
*       "JJ"                    - Quote syntax
*       "Martijn"               - Lynx support
*       "Temukki"               - Bugs
*       Richard van Velzen      - subfolder support / other fixes,
                                  additional markup implementation
*       "Fyra"                  - UTF-8 fix for templates
*       "Sani"                  - Bugs

*       The users of Camen Design Forum <forum.camendesign.com>
        for testing and support