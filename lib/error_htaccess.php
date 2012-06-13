<!DOCTYPE html>
<title>".htaccess" is Missing or Disabled</title>
<h1>NoNonsense Forum</h2>
<h2>Error: ".htaccess" is Missing or Disabled</h2>
<p>
	One of the files that makes up NoNonsense Forum (".htaccess") is missing, or HTAccess functionality is disabled
	(or not present) on the web server.
</p>
<dl>

<dt><strong>The ".htaccess" file could be missing:</strong></dt>
<dd><p>
	On Mac OSX, Linux and most other operating systems, files that begin with a dot are invisible by default.
	Check that a file named ".htaccess" is present in the NoNonsenseForum folder, or was correctly uploaded to your
	server. You may need to search the 'Web for instructions on how to show hidden files on your operating system.
</p></dd>
	
<dt><strong>Your server does not support HTAccess:</strong></dt>
<dd><p>
	HTAccess functionality is required for NoNonsense Forum to function. It is a part of the Apache Web Server.
	Please verify that the web server being used to run NoNonsense Forum is using Apache v2.1 or greater.
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
<?php exit (1); ?>