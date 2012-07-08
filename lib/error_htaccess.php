<!DOCTYPE html>
<title>".htaccess" is Missing or Disabled</title>
<h1>NoNonsense Forum</h2>
<h2>Error: ".htaccess" is Missing or Disabled, or the "users" Folder is in an Insecure Location</h2>
<p>
	One of the files that makes up NoNonsense Forum (".htaccess") is missing, or HTAccess functionality is disabled
	(or not present) on the web server. If you intend to run NoNonsense Forum without HTAccess, the "users" folder
	must be relocated away from the default, insecure location.
</p>

<h3>A. If your webserver supports HTAccess, but you are getting this message:</h3>
<p>
	HTAccess functionality is preferred for NoNonsense Forum to function. It is a part of the Apache Web Server.
	Please verify that the web server being used to run NoNonsense Forum is using Apache v2.1 or greater.
</p>

<dl>

<dt><strong>The ".htaccess" file could be missing:</strong></dt>
<dd><p>
	On Mac OSX, Linux and most other operating systems, files that begin with a dot are invisible by default.
	Check that a file named ".htaccess" is present in the NoNonsenseForum folder, or was correctly uploaded to your
	server. You may need to search the 'Web for instructions on how to show hidden files on your operating system.
</p></dd>

<dt><strong>HTAccess could be disabled:</strong></dt>
<dd><p>
	Sometimes ".htaccess" files are disabled by default (for example, if you are using XAMPP).
	If the ".htaccess" file is present and you are still getting this error message, search the 'Web for instructions
	on how to enable ".htaccess" files for your web server. The solution usually entails modifying the "httpd.conf"
	server configuration file. If you are renting from a hosting company and do not have access to the server
	configuration, contact them directly for assitance.
</p></dd>

</dl>

<h3>B. If you intend to run NoNonsense Forum without HTAccess:</h3>
<p>
	You can choose to run NoNonsense Forum without HTAccess, but won’t get "pretty" URLs.
	Running without HTAccess happens automatically, you do not need to specifically tell NoNonsense Forum to do so,
	however HTAccess is used to secure the "users" folder where passwords are stored, therefore you will have to
	relocate the "users" folder first to continue. See the instructions below.
</p>

<dl>

<dt><strong>Change the "users" folder to a private location:</strong></dt>
<ol>

<li><p>
	Create a folder in a private location on the web server that is not exposed to the web. On many web hosts, your web
	files go into a folder named "wwwroot", "public_html" or similar. Files and folders within that folder are public,
	but the files and folders on the same level are not. You may be able to create your private folder there
</p></li><li><p>
	If you have not already, create a duplicate of 'config.default.php' and rename it 'config.php'
</p></li><li><p>
	Modify the `FORUM_USERS` option to point to the location of the private folder, relative to NoNonsense Forum.
	E.g. "../private" to go up, out of the public folder and into the private folder. If NoNonsense Forum is in a
	sub-folder, then you would use do "../../private" to go up further.
</p><p>
	Do not include a starting or ending slash, and note that you should use forward slashes for UNIX hosts and backward
	slashes for Windows hosts. The OS you use on your computer does not matter, it's the OS on the server that matters.
</p></li>

</ol>

<?php exit (1); ?>