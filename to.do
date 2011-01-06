CODE:	Do not allow deleting if forum is disabled?
	(could be used to clean up spam)

FEATURE:Anti-spam v1. Allow mods to delete posts permanently and void usernames
	-	Requires unique post IDs, otherwise removing a post entirely will break existing bookmarks
	
CODE:	When a post is deleted or spammed, don’t change the modified date of the file
	(set it back to the timestamp on the last post)

CODE:	Change delete buttons to use POST instead of GET
	(removes need for noindex, nofollow and will allow for switching to single click deletion in the future)

DESIGN:	Template the hyperlink, so that the abbreviation <abc.com/…> is theme-specific
	
DESIGN:	Link to go to the last post on a page
	
FEATURE:An RSS feed for threads in a folder ordered by updated time? (see all updates happening)
	
CODE:	Error message if thread already exists (could be paged out of view)
	-	Could be done with an interstitial page, point user to the thread
	-	Also, if file exists, could append a number to flattened title to allow more than one thread of same name
	
CODE:	Sitemap
	
FEATURE:Post appending (can’t edit because `formatText` is one way)
	
DESIGN:	IE support (no reason why not)
DESIGN:	Mobile support (iPhone / iPad / Android)
	
FEATURE:Read-only (lock) threads and folders. Use file-permissions? Read-only category on <channel>?

CODE:	Add ‘touch’ page to restore from backup by setting the created and modified dates on the XML files
	according to their content

DESIGN: Add pattern attribute to form fields for validation, style validation

FEATURE:Add option for ignoring additional folders
	-	Relies on .htaccess and PHP. messy