NoNonsense Forum v7 © Copyright CC-BY 3.0 Kroc Camen of Camen Design
====================================================================
A simple forum that focuses on discussion and simplicity.


Requirements:
-------------
*	An up to date Apache installation, 2.1 or above
*	PHP 5.2.6 or above (5.3+ preferred)


Install:
--------
1.	NoNonsense Forum can run on it’s own domain / sub-domain
	(preferred), or within a sub-folder (e.g. '/forum/')
	
	Copy this directory and all files to the web-root of a
	sub-domain (like 'forum.---.com'), virtual host or other
	dedicated web-root for the forum, or as a sub-folder therein
	
	NOTE: On some platforms, the ".htaccess" file may be invisible.
	      Please search the Internet for instructions on how to
	      show hidden files on your operating system if you cannot
	      see the ".htaccess" file. It must be included with the
	      other files for NoNonsense Forum to work
	
	NOTE: If you run NoNonsense Forum in a sub-folder of a domain,
	      any existing ".htaccess" rules might 'leak' into the 
	      folder and prevent the forum from working correctly.
	      It is recommended to use a sub-domain if you are not sure
	      how to manually edit ".htaccess" rules

2.	Ensure the web-root / folder and all sub-folders (especially
	"users") have write permission for PHP, the code will save new
	threads directly to web-root

3.	Visit the site in your browser. If all is well, you should have
	an empty, but functional forum. If you're having problems you
	can ask for help on the forums: <forum.camendesign.com>
	
	NOTE: Please ensure ".htaccess" files are enabled on your
	      web-server. For example, XAMPP does not execute
	      ".htaccess" files by deafult

Optional:

4.	Rename the 'config.example.php' file to 'config.php' and
	customise the options within to your liking. Available options
	are explained within 'config.example.php'


Browser support:
----------------
*	IE6, 7, 8, 9+
*	Firefox 3+, Camino 2+
*	Chrome Stable, Chrome Dev
*	Safari 3+
*	Opera 9+
*	Lynx

*	iOS 4.0+ (iOS 3 untested yet)
*	Android (all versions AFAIK)
*	Firefox Mobile
*	Opera Mobile & Mini

Unsupported:
*	Firefox 2 or earlier, Camino 1
*	IE5.5 and below (including IE:Mac)
*	IE7/Mobile (Windows Phone 7)


Creating Sub-Forums:
--------------------
If you would like to organise your forum into sub-forums for different
topics just create a folder on webroot with the desired name (it can
contain any letters allowed by your server's OS except for ".", "<",
">" and "&"). Make sure the folder has write-permissions.

Second-level sub-folders are not supported. (E.g. '/Music/Techno/').


Stickying Threads:
------------------
If you would like a thread to always remain at the top of the threads
list, also regardless of page number, create a file "sticky.txt" in the
webroot or particular folder.

In your "sticky.txt" file add the filename of each thread you would
like to sticky on separate lines, including the ".rss" file extension.

For example:

the_rules.rss
f_a_q_.rss


Understanding Name Reservation:
-------------------------------
There is no login or registration of the traditional kind. In order to
prevent someone's desired name / nickname / alias / handle being reused
by the wrong person, you enter your desired name and a password
whenever you post. This name and password form a pair that have to
match in order to use that name again in the future. Therefore you
don't have to log in beforehand, or pre-register before posting.

The single most important thing to bear in mind is that the name
reservation system is not the same as authentication. Any person can
enter any name they want and one person could just as easily use many
names. It in no way ties one person to one browser session like a login
does.

Whenever a name is reserved a text file is created in the users folder.
The filename is a hash of the name and the file contains the hash of
the password. Names are not case-sensitive, but passwords are.


Appending to a Post:
--------------------
There is no ability to edit posts (other than manually editing the RSS
feed). This is because the text to HTML conversion when a post is made
is one-way. NoNonsense Forum allows you only to append to existing
posts. Only the original author or a moderator can append to posts
(see Adding Moderators further down for details on moderators).

Appending to a post does not change the last-modified date of the
thread, and therefore does not bump it to the top of the index list.


Deleting a Thread or Post:
--------------------------
The person who made a thread (or a moderator) can delete their thread
by clicking on the delete button in the first post of a thread.
They then have to enter the name and password pair that was originally
used to post to delete the thread. The entire thread is then
permanently deleted.

Deleting a post works the same way, by clicking the delete button on
the particular post. Either the original author of the post or a
moderator can delete the post, however individual posts are not
permanently removed like threads. Upon deletion the post entry will
remain but its text content will be stripped out and a message along
the lines of "This post was deleted by the original poster"
(or "a moderator") will replace it.

A tick box on the delete page, usually labelled "remove completely",
will allow moderators to remove the post from the thread without
leaving the blanked out text. This will only work if the post is on the
last page of replies (so as to not break permalinks by changing the
length of previous pages). If the box is ticked, but the user is not a
moderator, or the post is not on the last page of replies, then the
post is blanked out as usual.


Adding Moderators:
------------------
Moderators can delete threads that were not originally made by them.
To add moderators to your website, first have them post at least once
in order to reserve the name. Then create a "mods.txt" file in web-root
and populate it with the reserved names to allow moderator rights,
one on each line. E.g.

Kroc
theraje
SpeedoJoe

The moderators you specify will be able to delete threads and posts in
all folders, including root, of the forum. If you would like to set a
moderator who can only delete within a certain folder, create a
'mods.txt' file within the folder and specify the desired names.
These moderators will not be able delete threads or posts in the forum
root, or other folders.


Locking / Unlocking Threads:
----------------------------
Moderators can lock threads to prevent any further replies (as well as
post appends and deletes). Simply visit the thread, click the "Lock"
button and enter the name / password. Visit the thread again and click
"Unlock" to allow replies once more.


Markup:
-------
Some simple markup is provided for hyperlinks, quotes and more;
see 'markup.txt' for details.


Acknowledgements:
=================
*	Jon Gjengset		-	Original theme / mobile theme
*	"JJ"			-	Quote syntax
*	"Martijn"		-	Lynx support
*	"Temukki"		-	Bugs
*	Richard van Velzen	-	subfolder support / other fixes

*	The users of Camen Design Forum <forum.camendesign.com>
	for testing and support