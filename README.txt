NoNonsense Forum v18 © Copyright (CC-BY) Kroc Camen 2012
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
[0]     Requirements
[1]     Install
[1.1]   Install from a download
[1.2]   Install using GIT
[1.3]   Installation (continued)
[1.4]   Updating NoNonsense Forum
[2]     Things to note
[2.1]   Post appending
[2.2]   Post deleting
[2.3]   Markup
[3]     Sub-forums
[4]     Sticky threads
[5]     Moderators
[5.1]   Sign-in
[6]     Forum locking
[6.1]   Members
[6.2]   A note on private forums
[7]     Customising
[7.1]   'config.php'
[7.2]   'theme.config.php'
[7.3]   Favicon and touch-icon
[7.4]   Adding a forum introduction (about.html)
[7.5]   Custom CSS
[8]     Acknowledgements
------------------------------------------------------------------------


[0]     Requirements:
========================================================================
*       An up to date Apache installation, 2.1 or above
*       PHP 5.2.3 or above

Browser support:
*       IE6, 7, 8, 9+
*       Firefox 3+, Camino 2+
*       Chrome Stable, Chrome Dev
*       Safari 3+
*       Opera 9+
*       Lynx, other text browsers

*       iOS 4.0+ (iOS 3 untested yet)
*       Android (all versions AFAIK)
*       Firefox Mobile
*       Opera Mobile & Mini
*       Amazon Kindle

Unsupported:
*       Firefox 2 or earlier, Camino 1
*       IE5.5 and below (including IE:Mac)
*       IE7/Mobile (Windows Phone 7)


[1]     Install:
========================================================================
NoNonsense Forum can run on its own domain / sub-domain (preferred),
or within a sub-folder (e.g. '/forum/').

[1.1]   Install from download:
------------------------------------------------------------------------
If you downloaded NoNonsense Forum as a ZIP / archive file:

*       Copy this folder and all files to the web-root of a sub-domain
        (like 'forum.---.com'), virtual host or other dedicated
        web-root for the forum, or as a sub-folder therein.

NOTE:   On some platforms, the ".htaccess" file may be invisible.
        Please search the Internet for instructions on how to show
        hidden files on your operating system if you cannot see the
        ".htaccess" file. It must be included with the other files for
        NoNonsense Forum to work

[1.2]   Install using GIT:
------------------------------------------------------------------------
The best way to install NoNonsense Forum (and keep it up to date) is to
use GIT <git-scm.org>. If you have GIT installed, then from the command
line / terminal enter:

git clone https://github.com/Kroc/NoNonsenseForum.git nononsense_forum

The code will be downloaded into a "nononsense_forum" folder in the
current directory.

Move this folder, or its contents to the desired location (as described
in "Install from download" above).

[1.3]   Installation (continued):
------------------------------------------------------------------------
*       Ensure the web-root / folder and all sub-folders (especially
        "users") have write permission for PHP, the code will save new
        threads in the same folder as the installation

*       Visit the site in your browser. If all is well, you should have
        an empty, but functional forum. If you're having problems you
        can ask for help on the forums: <forum.camendesign.com>

NOTE:   Please ensure ".htaccess" files are enabled on your web-server.
        For example XAMPP does not execute ".htaccess" files by deafult

NOTE:   If you run NoNonsense Forum in a sub-folder, any existing
        ".htaccess" rules from the web-root might 'leak' into the
        folder and prevent the forum from working correctly.
        It is recommended to use a sub-domain if you are not sure how
        to manually edit ".htaccess" rules

Optional:

*       Copy the 'config.default.php' file to a 'config.php' file and
        customise the options within to your liking. Available options
        are explained within 'config.default.php'.
        
        You should leave the 'config.default.php' as-is to avoid
        conflicts with future updates

[1.4]   Updating NoNonsense Forum:
------------------------------------------------------------------------
Using GIT you can update NoNonsense Forum in-place without having to
re-download and install it. From the command line / terminal, enter:
(ensure that you are within the NoNonsense Forum installation folder)

git pull origin master

GIT will download and apply new features and fixes, without touching
your configuration or existing threads. This will not work if you have
modified any NoNonsense Forum files.

You should *always* check the change log before you update in case you
are required to alter your configuration or be aware of the impact of
some new feature.

<github.com/Kroc/NoNonsenseForum/commits/master>


[2]     Things to note:
========================================================================
[2.1]   Post appending:
------------------------------------------------------------------------
Because threads are RSS feeds, the text to HTML conversion that happens
when you post is one-way. You cannot edit posts, other than manually
editing the RSS file. Instead, text can be appended to the end of posts.

*       Users can append to their own posts

*       Moderators can append to any posts

[2.2]   Post deleting:
------------------------------------------------------------------------
To avoid abuse, users cannot permenantly delete their posts.

*       When a user deletes their post, the text is removed and
        replaced with a message like "This post was deleted by its
        owner" (or "a moderator"), the name and time remains

*       A moderator can delete any post, likewise

*       A blanked-out deleted post can be removed permenantly from the
        thread by a moderator by deleting it again, but only if the
        post is on the last page of replies -- so as to not break any
        permalinks by rearranging page boundaries

[2.3]   Markup:
------------------------------------------------------------------------
NoNonsense Forum has simple and innovative markup, see "markup.txt"
for details.


[3]     Sub-forums:
========================================================================
Sub-forums are simply folders.

*       The name of the folder is the name of the sub-forum, it can
        contain any letters allowed by your server's OS except for
        ".", "<", ">" and "&"

*       Make sure the folder has write-permissions

*       Nested sub-folders are supported, to any reasonable depth
        (E.g. '/Music/Techno/')


[4]     Sticky threads:
========================================================================
Sticky threads remain at the top of a forum, regardless of page.

*       Create a "sticky.txt" file in the forum / sub-forum

*       Add the filename of each thread you would like to sticky on
        separate lines, including the ".rss" file extension.
        For example:

the_rules.rss
f_a_q_.rss


[5]     Moderators:
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

[5.1]   Sign-in:
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


[6]     Forum Locking:
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

[6.1]   Members:
------------------------------------------------------------------------
Members are users who are not restricted in a locked forum, but do not
have moderator powers; they are your participants in restricted forums.

*       Create a "members.txt" file in the forum / sub-forum

*       Add the user name of each member on a separate line

*       Members of the root forum are not automatically members of all
        sub-forums (unlike mods)
        
*       Members must sign-in to be able to post in locked forums

[6.2]   A note on private forums:
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


[7]     Customising:
========================================================================
[7.1]   'config.php':
------------------------------------------------------------------------
The 'config.default.php' file contains default settings for the forum,
make a copy of this file and name it 'config.php' -- DO NOT modify,
delete or rename 'config.default.php' as it is still required.

Modify your 'config.php' to your liking, the settings are explained
within.

[7.2]   'theme.config.php':
------------------------------------------------------------------------
The `FORUM_THEME` option in 'config.php' (default: "greyscale"), refers
to the name of the theme in the 'themes' folder. Inside the theme is a
'theme.config.default.php' file; make a copy of this file and rename to
'theme.config.php'.

Modify your 'theme.config.php' to your liking, the settings are
explained within.

DO NOT modify, delete or rename 'theme.config.default.php' as it is
still required.

[7.3]   Favicon and touch-icon:
------------------------------------------------------------------------
The site will use '/favicon.default.ico' as the favicon and
'/apple-touch-icon.deafult.png' for iOS home-screen icons by default,
just provide your own 'favicon.ico' and 'apple-touch-icon.png' files
to override. Each theme will have examples you can use in the theme
folder, just copy those to webroot to use.

[7.4]   Adding a forum introduction ('about.html'):
------------------------------------------------------------------------
You can add a custom message to the top of the forum index / sub-forum
index pages by creating an 'about.html' file in the folder and
populating it with HTML code.

This feature is primarily designed for you to add a description at the
top of each forum to tell visitors what the forum is used for, rules
etc., but since any HTML can be used you can add whatever you want --
social buttons, links, images and so forth.

[7.5]   Custom CSS:
------------------------------------------------------------------------
Within the theme folder (that is where 'theme.css' is for the chosen
theme) create a 'custom.css' file and it will be automatically included.


[8]     Acknowledgements:
========================================================================
See LICENCE.txt for licence details

*       Jon Gjengset            - Original theme / mobile theme
*       "JJ"                    - Quote syntax
*       "Martijn"               - Lynx support
*       "Temukki"               - Bugs
*       Richard van Velzen      - subfolder support / other fixes,
                                  additional markup implementation
*       "Fyra"                  - UTF-8 fix for templates

*       The users of Camen Design Forum <forum.camendesign.com>
        for testing and support