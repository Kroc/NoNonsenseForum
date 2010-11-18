CODE:	Change delete buttons to use POST instead of GET
	(removes need for noindex, nofollow and will allow for switching to single click deletion in the future)

FEATURE:Add option for ignoring additional folders

DESIGN:	Template the hyperlink, so that the abbreviation <abc.com/…> is theme-specific
	
DESIGN:	Link to go to the last post on a page
	
FEATURE:An RSS feed for threads in a folder ordered by updated time? (see all updates happening)
	
CODE:	Error message if thread already exists (could be paged out of view)
	-	Could be done with an interstitial page, point user to the thread
	-	Also, if file exists, could append a number to flattened title to allow more than one thread of same name
	
DESIGN:	Search:
	
	<form method="get" action="http://google.com/search">
		<input type="hidden" name="as_sitesearch" value="&__HOST__;" />
		<input type="search" name="as_q" />
		<input type="submit" value="Search" />
	</form>
	
CODE:	Sitemap
	
FEATURE:Post appending (can’t edit because `formatText` is one way)
	
DESIGN:	IE support (no reason why not)
DESIGN:	Mobile support (iPhone / iPad / Android)
	
FEATURE:Read-only (lock) threads and folders. Use file-permissions? Read-only category on <channel>?

CODE:	Add ‘touch’ page to restore from backup by setting the created and modified dates on the XML files
	according to their content

DESIGN: Add pattern attribute to form fields for validation, style validation