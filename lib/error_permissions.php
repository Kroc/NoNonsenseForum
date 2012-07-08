<!DOCTYPE html>
<title>Unable to Save; Permission Denied</title>
<h1>NoNonsense Forum</h2>
<h2>Error: Unable to Save; Permission Denied</h2>
<p>
	The data could not be saved because the folder is not writable.
	Make the folder writable and refresh this page to save the data.
</p><p>
	This error will occur if either a thread cannot be saved because the forum / sub-forum folder is non-writable
	or because a new user cannot be registered because the "users" folder is either non-writable, or your `FORUM_USERS`
	path is incorrect.
</p>
<dl>

<dt><strong>Change permissions:</strong></dt>
<dd><p>
	The root folder of NoNonsense Forum, and all sub folders, must be writable. You may need to `<code>chmod</code>`
	the folder(s), usually mode '755' or '655' will work. If you only have FTP access to your server, try
	right-clicking on the folder(s) in your FTP software and changing permissions there.
</p></dd>

<dt><strong>Change ownership:</strong></dt>
<dd><p>
	In most cases changing permissions is sufficient. If this fails to work you may need to change ownership of the
	folder(s) to make them writable. The folders should be owned by the Apache process, sometimes this is '_www' or
	'nobody'.
</p></dd>

</dl>
<?php exit (1); ?>