•	Error message if thread already exists (could be paged out of view)
	-	Could be done with an interstitial page, point user to the thread
•	Link title to home page
•	A way to regenerate index.rss, if a thread is deleted, for example (may have to make index.rss dynamic)

•	Search:
	
	<form method="get" action="http://google.com/search">
		<input type="hidden" name="as_sitesearch" value="&__HOST__;" />
		<input type="search" name="as_q" />
		<input type="submit" value="Search" />
	</form>

•	Move to a fully templated theme, like camendesign?
	(will allow people to theme directly, without having to modify PHP)

•	Post editing
•	Post deleting (problem with IDs reshuffling, need to create permalink IDs)
