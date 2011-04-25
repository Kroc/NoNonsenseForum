NoNonsense Forum © Copyright CC-BY 3.0 Kroc Camen of Camen Design
=================================================================
A simple forum that focuses on discussion and simplicity


Requirements:
-------------
*	An up to date Apache installation, 2.2 or above
*	PHP 5.2.1 or above
*	A sub domain (like 'forum.---.com'), virtual host or otherwise dedicated web-root for the forum.
	it will not run in a sub-folder


Install:
--------
1.	Copy this directory and all files to the web-root

2.	Create a folder named "users" on webroot

3.	Ensure the webroot and all sub-folders (especially "users") have write permission for PHP,
	the code will save new threads directly to webroot

4.	Visit the site in your browser. if all is well, you should have an empty, but functional forum
	If you’re having problems you can ask for help on the forums: <forum.camendesign.com>

Optional:

5.	Rename the 'config.example.php' file to 'config.php' and customise the options within to your liking.
	Available options are explained within 'config.example.php'.


Creating Sub-Forums:
--------------------
If you would like to organise your forum into sub-forums for different topics just create a folder on webroot with the desired name (it can contain any letters allowed by your server’s OS except for "." and "&" -- ampersand due to this bug: <shauninman.com/archive/2005/05/11/unexpected_get_contents>). Make sure the folder has write-permissions.

Second-level sub-folders are not supported. (E.g. '/Music/Techno/')


Stickying Threads:
------------------
If you would like a thread to always remain at the top of the threads list, also regardless of page number, create a file "sticky.txt" in the webroot or particular folder.

In your sticky.txt file add the filename of each thread you would like to sticky on separate lines, including the ".rss" file extension. For example:

the_rules.rss
f_a_q_.rss


Understanding Name Reservation:
-------------------------------
There is no login or registration of the traditional kind. In order to prevent someone's desired name / nickname / alias / handle being reused by the wrong person, you enter your desired name and a password whenever you post. This name and password form a pair that have to match in order to use that name again in the future. Therefore you don’t have to log in beforehand, or pre-register before posting.

The single most important thing to bear in mind is that the name reservation system is not the same as authentication. Any person can enter any name they want and one person could just as easily use many names. It in no way ties one person to one browser session like a login does.

Whenever a name is reserved a text file is created in the users folder. The filename is a hash of the name and the file contains the hash of the password. Names are not case-sensitive, but passwords are.


Appending to a Post:
--------------------
There is no ability to edit posts (other than manually editing the RSS feed). This is because the text to HTML conversion, when a post is made, is one-way. NoNonsense Forum allows you only to append to existing posts. Only the original author or a moderator can append to posts (see Adding Moderators further down for details on moderators).

Appending to a post does not change the last-modified date of the thread, and therefore does not bump it to the top of the index list.


Deleting a Thread or Post:
--------------------------
The person who made a thread (or a moderator) can delete their thread by clicking on the delete button in the first post of a thread. They then have to enter the name and password pair that was originally used to post to delete the thread. The entire thread is then permanently deleted.

Deleting a post works the same way, by clicking the delete button on the particular post. Either the original author of the post or a moderator can delete the post, however individual posts are not permanently removed like threads. Upon deletion the post entry will remain but its text content will be stripped out and a message along the lines of "This post was deleted by the original poster" (or "a moderator") will replace it.


Adding Moderators:
------------------
Moderators can delete threads that were not originally made by them. To add moderators to your website, first have them post at least once in order to reserve the name. Then create a "mods.txt" file in webroot and populate it with the reserved names to allow moderator rights, one on each line. E.g.

Kroc
theraje
SpeedoJoe

The moderators you specify will be able to delete threads and posts in all folders, including root, of the forum. If you would like to set a moderator who can only delete within a certain folder, create a mods.txt file within the folder and specify the desired names. These moderators will not be able delete threads or posts in the forum root, or other folders.


Quoting Posts:
--------------
NoNonsenseForum has a unique and simple quoting mechanism. To turn any text into a styled blockquote once posted, begin and end a line with speech marks. There must be a blank line between the quote and any other text.

E.g.

	here is some text.
	
	"This is a quote."
	
	Some more text.

Quotes can be nested. Start a line with a speech mark, and then don’t place the closing speech mark until the end of the block you want quoted.

E.g.

	"Start of first quote.
	
	"This is a nested quote."
	
	End of the first quote."
	
You can simply copy and paste quotes from other posts and the nesting will be retained for you because the blockquotes  include their own quote marks that get copied across. In this case, there may be some whitespace before the quote marks, or some quote marks may be alone on their own line. Don’t worry about this, NoNonsenseForum strips the extra whitespace when processing quotes.

You may use standard ASCII quotes "…", 'curly' quotes “…” and guillemots «…», but you may not start a quote with one style and end it with another.
