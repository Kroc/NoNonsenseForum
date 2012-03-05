<?php //bootstraps the forum
/* ====================================================================================================================== */
/* NoNonsense Forum v18 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/

//correct PHP version?
if (version_compare (PHP_VERSION, '5.2.3') < 0) die (
	'PHP version 5.2.3 or greater required, yours is: '.PHP_VERSION
);

//is the htaccess working properly?
//(.htaccess sets this variable for us)
if (!@$_SERVER['HTTP_HTACCESS']) die (
	"'.htaccess' file is missing, or not enabled."
);

require_once 'lib/functions.php';	//import shared functions
require_once 'lib/domtemplate.php';	//import the templating engine

/* configuration:
   ---------------------------------------------------------------------------------------------------------------------- */
//default UTF-8 throughout
mb_internal_encoding ('UTF-8');
mb_regex_encoding    ('UTF-8');

//try set the forum owner’s personal config ('config.php'), if it exists
@include './config.php';
//include the defaults: (for anything missing from the user’s config)
//see that file for descriptions of the different available options
@(include './config.default.php') or die ('config.default.php missing!');

//PHP 5.3 issues a warning if the timezone is not set when using date commands
//(`FORUM_TIMEZONE` is set in the config and defaults to 'UTC')
date_default_timezone_set (FORUM_TIMEZONE);

/* constants: some stuff we don’t expect to change
   ---------------------------------------------------------------------------------------------------------------------- */
define ('FORUM_ROOT',		dirname (__FILE__));		//full server-path for absolute references
define ('FORUM_PATH', 		str_replace (			//relative from webroot--if running in a folder:
	array ('\\', '//'), '/',				//- replace Windows forward-slash with backslash
	dirname ($_SERVER['SCRIPT_NAME']).'/'			//- always starts with a slash and ends in one
));
define ('FORUM_URL',		'http'.				//base URL to produce hyperlinks throughout:
	(FORUM_HTTPS || @$_SERVER['HTTPS'] == 'on' ? 's' : '').	//- if HTTPS is enforced, links in RSS will use it
	'://'.$_SERVER['HTTP_HOST']
);

//load the user’s theme configuration, if it exists
@include FORUM_ROOT.'/themes/'.FORUM_THEME.'/theme.config.php';
//include the theme defaults
@(include FORUM_ROOT.'/themes/'.FORUM_THEME.'/theme.config.default.php') or die ('theme.config.default.php missing!');


/* common input
   ====================================================================================================================== */
//all our pages use 'path' (often optional) to specify the sub-forum being viewed, so this is done here
define ('PATH',     preg_match ('/^(?:[^\.\/&]+\/)+$/', @$_GET['path']) ? $_GET['path'] : '');
//these two get used an awful lot
define ('PATH_URL', !PATH ? FORUM_PATH : safeURL (FORUM_PATH.PATH, false));	//when outputting as part of a URL
define ('PATH_DIR', !PATH ? '/' : '/'.PATH);					//serverside, like `chdir` / `unlink`
//if we are in nested sub-folders, the name of the current sub-forum, exluding the rest
define ('SUBFORUM', @end (explode ('/', trim (PATH, '/'))));

//we have to change directory for `is_dir` to work, see <uk3.php.net/manual/en/function.is-dir.php#70005>
//being in the right directory is also assumed for reading 'mods.txt' and when generating the RSS (`indexRSS`)
//(oddly with `chdir` the path must end in a slash)
@chdir (FORUM_ROOT.PATH_DIR) or die ('Invalid path');


/* access control
   ====================================================================================================================== */
/* name / password authorisation:
   ---------------------------------------------------------------------------------------------------------------------- */
//for HTTP authentication (sign-in):
//- CGI workaround <orangejuiceliberationfront.com/http-auth-with-php-in-cgi-mode-e-g-on-dreamhost/>
if (@$_SERVER['HTTP_AUTHORIZATION']) list ($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode (
	':', base64_decode (substr ($_SERVER['HTTP_AUTHORIZATION'], 6))
);

//all pages can accept a name / password when committing actions (new thread / reply &c.)
//in the case of HTTP authentication (sign in), these are provided in the request header instead
define ('NAME', safeGet (@$_SERVER['PHP_AUTH_USER'] ? @$_SERVER['PHP_AUTH_USER'] : @$_POST['username'], SIZE_NAME));
define ('PASS', safeGet (@$_SERVER['PHP_AUTH_PW']   ? @$_SERVER['PHP_AUTH_PW']   : @$_POST['password'], SIZE_PASS, false));

if ((	//if HTTP authentication is used, we don’t need to validate the form fields
	@$_SERVER['PHP_AUTH_USER'] && @$_SERVER['PHP_AUTH_PW']
) || (	//if an input form was submitted:
	//are the name and password non-blank?
	NAME && PASS &&
	//the email check is a fake hidden field in the form to try and fool spam bots
	isset ($_POST['email']) && @$_POST['email'] == 'example@abc.com' &&
	//I wonder what this does...?
	(isset ($_POST['x'], $_POST['y']) || isset ($_POST['submit_x'], $_POST['submit_y']))
)) {
	//users are stored as text files based on the hash of the given name
	$name = hash ('sha512', strtolower (NAME));
	$user = FORUM_ROOT."/users/$name.txt";
	
	//create the user, if new:
	//- if registrations are allowed (`FORUM_NEWBIES` is true)
	//- you can’t create new users with the HTTP_AUTH sign in
	if (FORUM_NEWBIES && !isset ($_SERVER['PHP_AUTH_USER']) && !file_exists ($user))
		file_put_contents ($user, hash ('sha512', $name.PASS))
	;
	
	//does password match?
	define ('AUTH', @file_get_contents ($user) == hash ('sha512', $name.PASS));
	
	//if signed in with HTTP_AUTH, confirm that it’s okay to use
	//(e.g. the user could still have given the wrong password with HTTP_AUTH)
	define ('HTTP_AUTH', @$_SERVER['PHP_AUTH_USER'] ? AUTH : false);
} else {
	define ('AUTH',      false);
	define ('HTTP_AUTH', false);
}

/* access rights
   ---------------------------------------------------------------------------------------------------------------------- */
//get the lock status of the current forum we’re in:
//"threads"	- only users in "mods.txt" / "members.txt" can start threads, but anybody can reply
//"posts"	- only users in "mods.txt" / "members.txt" can start threads or reply
define ('FORUM_LOCK', trim (@file_get_contents ('locked.txt')));

//get the list of moderators:
//(`file` returns NULL if the file doesn’t exist; casting that to an array creates an array with a blank element, and
//`array_filter` removes blank elements, including blank lines in the text file; we could use the `FILE_SKIP_EMPTY_LINES`
//flag, but `array_filter` kills two birds with one stone since we don’t have to check if the file exists beforehand.)
$MODS = array (
	//'mods.txt' on root for mods on all sub-forums
	'GLOBAL'=>        array_filter ((array) @file (FORUM_ROOT.'/mods.txt', FILE_IGNORE_NEW_LINES)),
	//if in a sub-forum, the local 'mods.txt'
	'LOCAL'	=> PATH ? array_filter ((array) @file ('mods.txt', FILE_IGNORE_NEW_LINES)) : array ()
);

//get the list (if any) of users allowed to access this current forum
$MEMBERS = array_filter ((array) @file ('members.txt', FILE_IGNORE_NEW_LINES));

//is the current user a moderator in this forum?
define ('IS_MOD',    isMod (NAME));
//is the current user a member of this forum?
define ('IS_MEMBER', isMember (NAME));

//can the current user post new threads in the current forum?
//(posting replies is dependent on the the thread -- if locked -- so tested in 'thread.php')
define ('CAN_POST', FORUM_ENABLED && (
	//- if the user is a moderator or member of the current forum, they can post
	IS_MOD || IS_MEMBER ||
	//- if the forum is unlocked (mods will have to log in to see the form)
	!FORUM_LOCK
));


/* send HTTP headers
   ====================================================================================================================== */
//if enabled, enforce HTTPS
if (FORUM_HTTPS) if (@$_SERVER['HTTPS'] == 'on') {
	//if forced-HTTPS is on and a HTTPS connection is being used, send the 30-day HSTS header
	//see <en.wikipedia.org/wiki/Strict_Transport_Security> for more details
	header ('Strict-Transport-Security: max-age=2592000');
} else {
	//if forced-HTTPS is on and a HTTPS connection is not being used, redirect to the HTTPS version of the current page
	//(we don’t die here so that should the redirect be ignored, the HTTP version of the page will still be given)
	header ('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
}

//if the sign-in link was clicked, (and they're not already signed-in), invoke a HTTP_AUTH request in the browser:
//the browser will pop up a login box itself (no HTML involved) and continue to send the name & password with each request
//(these are trapped higher up as HTTP_AUTH_UN and HTTP_AUTH_PW and are authenticated the same as the regular post form)
if (!HTTP_AUTH && isset ($_GET['signin'])) {
	header ('WWW-Authenticate: Basic');
	header ('HTTP/1.0 401 Unauthorized');
	//we don't die here so that if they cancel the login prompt, they won't get a blank page
}

//stop browsers caching, so you don’t have to refresh every time to see changes
header ('Cache-Control: no-cache', true);
header ('Expires: 0', true);

?>