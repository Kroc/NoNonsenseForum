BUG:	Can let a spam through by not including the e-mail field at all

CODE:	Change home page RSS feed to track all changes, since ctime is not a reliable "creation date" on UNIX

CODE:	Lock file when opening XML and unlock when writing it back

CODE:	Do not allow deleting if forum is disabled?
	(could be used to clean up spam)

FEATURE:Anti-spam v1. Allow mods to delete posts permanently and void usernames
	-	Requires unique post IDs, otherwise removing a post entirely will break existing bookmarks
	
CODE:	When a post is deleted or spammed, don’t change the modified date of the file
	(set it back to the timestamp on the last post)

CODE:	Change delete buttons to use POST instead of GET
	(removes need for noindex, nofollow and will allow for switching to single click deletion in the future)
	-	Requires some re-engineering of the form submission / HTML

DESIGN:	Template the hyperlink, so that the abbreviation <abc.com/…> is theme-specific
	
DESIGN:	Link to go to the last post on a page
	
DESIGN:	IE support (no reason why not)
DESIGN:	Mobile support (iPhone / iPad / Android)

CODE:	Add ‘touch’ page to restore from backup by setting the created and modified dates on the XML files
	according to their content

DESIGN: Add pattern attribute to form fields for validation, style validation

FEATURE:Add option for ignoring additional folders
	-	Relies on .htaccess and PHP. messy