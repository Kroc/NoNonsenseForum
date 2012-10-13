<?php //bootstraps the forum
/* ====================================================================================================================== */
/* NoNonsense Forum v22 © Copyright (CC-BY) Kroc Camen 2012
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*//*
   what gets defined here:
	
	const / var	attribs	description
	--------------------------------------------------------------------------------------------------------------------
	key:		/	ends with a slash
			//	begins and ends with a slash
			   ?	OS-dependent slashes (use `DIRECTORY_SEPERATOR` to concatenate)
			   U	URL-encoded. use for HTML, do not use for server-side paths
	--------------------------------------------------------------------------------------------------------------------
	FORUM_ROOT	   ?	full server path to NNF's folder
	FORUM_LIB	/  ?	full server path to the 'lib' folder
	FORUM_PATH	// U	relative URL from the web-root, to NNF
				if NNF is at root, this would be "/", otherwise the "/sub-folder/" NNF is within
	HTACCESS		boolean if the ".htaccess" file is present and enabled or not
	
	-- everything in 'config.php' (if present) and 'config.default.php' --
	
	FORUM_URL		fully-qualified domain URL, e.g. "http://forum.camendesign.com"
	PAGE			page-number given in the querystring -- not necessarily a valid page number!
	PATH		/	current sub-forum the viewer is in
				this is often used to test if the user is in a sub-forum or not
	PATH_URL	/  U	URL-encoded version of `PATH` for use in constructing URLs
	PATH_DIR	// ?	relative server path from NNF's root (`FORUM_ROOT`) to the current sub-forum
	SUBFORUM		the name of the current sub-forum (regardless of nesting), not URL-encoded
	
	NAME			username given
	PASS			password given
	AUTH			boolean, if the username / password are correct
	AUTH_HTTP		boolean, if the authentication was via HTTP_AUTH *and* was correct
				(will be false if the username / password were wrong, even if HTTP_AUTH was used)
	
	FORUM_LOCK		the contents of 'locked.txt' which sets restrictions on the forum / sub-forums
				see section 5 in the README file
	$MODS			array of the names of moderators for the whole forum, and the current sub-forum
	$MEMBERS		array of the names of members for the current sub-forum
	IS_MOD			if the current viewer is a moderator for the current forum
	IS_MEMBER		if the current viewer is a member of the current forum
	
	THEME_ROOT	/  ?	full server path to the currently selected theme
	
	-- everything in 'theme.config.php' (if present) and 'theme.config.default.php' --
	
	$LANG			array of language translations. see 'lang.example.php' for details
	LANG			currently selected language, '' for default
*/	


/* server configutation
   ====================================================================================================================== */
//default UTF-8 throughout
mb_internal_encoding ('UTF-8');
mb_regex_encoding    ('UTF-8');

//full server path for absolute references, this includes the any sub-folders NNF might be in
define ('FORUM_ROOT',		dirname (__FILE__));
//location of the 'lib' folder, full server path
define ('FORUM_LIB', 		FORUM_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);

//correct PHP version?
if (version_compare (PHP_VERSION, '5.2.3') < 0) require FORUM_LIB.'error_phpver.php';

require_once FORUM_LIB.'functions.php';				//import shared functions
require_once FORUM_LIB.'domtemplate/domtemplate.php';		//import the templating engine

//location of NNF relative to the webroot, i.e. if NNF is in a sub-folder or not
//we URL-encode this as it’s never used for server-side paths, `FORUM_ROOT` / `FORUM_LIB` are for that
define ('FORUM_PATH', 		safeURL (str_replace (
	array ('\\', '//'), '/',				//- replace Windows forward-slash with backslash
	dirname ($_SERVER['SCRIPT_NAME']).'/'			//- always starts with a slash and ends in one
)));

/* site configuration
   ---------------------------------------------------------------------------------------------------------------------- */
//try set the forum owner’s personal config ('config.php'), if it exists
@(include './config.php');
//include the defaults: (for anything missing from the user’s config)
//see that file for descriptions of the different available options
@(include './config.default.php') or require FORUM_LIB.'error_configdefault.php';

//PHP 5.3 issues a warning if the timezone is not set when using date commands
//(`FORUM_TIMEZONE` is set in the config and defaults to 'UTC')
date_default_timezone_set (FORUM_TIMEZONE);

//the full URL of the site is dependant on HTTPS configuration, so we wait until now to define it
define ('FORUM_URL',		'http'.				//base URL to produce hyperlinks throughout:
	(FORUM_HTTPS || @$_SERVER['HTTPS'] == 'on' ? 's' : '').	//- if HTTPS is enforced, links in RSS will use it
	'://'.$_SERVER['HTTP_HOST']
);

//is the htaccess working properly?
//(.htaccess sets this variable for us)
define ('HTACCESS', (bool) @$_SERVER['HTTP_HTACCESS']);
//if ".htaccess" is missing or disabled, and the "users" folder is in an insecure location, warn the site admin to move it
if (!HTACCESS && FORUM_USERS == 'users') require FORUM_LIB.'error_htaccess.php';

/* common input
   ---------------------------------------------------------------------------------------------------------------------- */
//most pages allow for a page number; note that this is merely the user-input, it is not necessarily a valid page number!
define ('PAGE',     preg_match ('/^[1-9][0-9]*$/', @$_GET['page']) ? (int) $_GET['page'] : false);
//all our pages use 'path' (often optional) to specify the sub-forum being viewed, so this is done here
define ('PATH',     preg_match ('/^(?:[^\.\/&]+\/)+$/', @$_GET['path']) ? $_GET['path'] : '');
//a shorthand for when PATH is used in URL construction for HTML use
define ('PATH_URL', safeURL (PATH));
//for serverside use, like `chdir` / `unlink` (must replace the URL forward-slashes with backslashes on Windows)
define ('PATH_DIR', !PATH ? DIRECTORY_SEPARATOR : DIRECTORY_SEPARATOR.str_replace ('/', DIRECTORY_SEPARATOR, PATH));
//if we are in nested sub-folders, the name of the current sub-forum, exluding the rest
//(not used in URLs, so we use `PATH` instead of `PATH_URL`)
define ('SUBFORUM', @end (explode ('/', trim (PATH, '/'))));

//we have to change directory for `is_dir` to work, see <uk3.php.net/manual/en/function.is-dir.php#70005>
//being in the right directory is also assumed for reading 'mods.txt' and when generating the RSS (`indexRSS`)
//(oddly with `chdir` the path must end in a slash)
@chdir (FORUM_ROOT.PATH_DIR) or die ('Invalid path');
//TODO: that should generate a 404, but we can't create a 404 in PHP that will send the server's provided 404 page.
//      I may revist this if I create an NNF-provided 404 page


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
	$user = FORUM_ROOT.DIRECTORY_SEPARATOR.FORUM_USERS.DIRECTORY_SEPARATOR."$name.txt";
	
	//create the user, if new:
	//- if registrations are allowed (`FORUM_NEWBIES` is true)
	//- you can’t create new users with the HTTP_AUTH sign in
	if (FORUM_NEWBIES && !isset ($_SERVER['PHP_AUTH_USER']) && !file_exists ($user))
		file_put_contents ($user, hash ('sha512', $name.PASS)) or require FORUM_LIB.'error_permissions.php'
	;
	
	//does password match?
	define ('AUTH', @file_get_contents ($user) == hash ('sha512', $name.PASS));
	
	//if signed in with HTTP_AUTH, confirm that it’s okay to use
	//(e.g. the user could still have given the wrong password with HTTP_AUTH)
	define ('AUTH_HTTP', @$_SERVER['PHP_AUTH_USER'] ? AUTH : false);
} else {
	define ('AUTH',      false);
	define ('AUTH_HTTP', false);
}

/* access rights
   ---------------------------------------------------------------------------------------------------------------------- */
//get the lock status of the current forum we’re in:
//"threads"	- only users in "mods.txt" / "members.txt" can start threads, but anybody can reply
//"posts"	- only users in "mods.txt" / "members.txt" can start threads or reply
define ('FORUM_LOCK', trim (@file_get_contents ('locked.txt')));

//get the list of moderators:
//(`file` returns NULL if the file doesn’t exist; casting that to an array creates an array with a blank element, and
// `array_filter` removes blank elements, including blank lines in the text file; we could use the `FILE_SKIP_EMPTY_LINES`
// flag, but `array_filter` kills two birds with one stone since we don’t have to check if the file exists beforehand.)
$MODS = array (
	//'mods.txt' on root for mods on all sub-forums
	'GLOBAL'=>        array_filter ((array) @file (FORUM_ROOT.DIRECTORY_SEPARATOR.'mods.txt', FILE_IGNORE_NEW_LINES)),
	//if in a sub-forum, the local 'mods.txt'
	'LOCAL'	=> PATH ? array_filter ((array) @file ('mods.txt', FILE_IGNORE_NEW_LINES)) : array ()
);

//get the list (if any) of users allowed to access this current forum
$MEMBERS = array_filter ((array) @file ('members.txt', FILE_IGNORE_NEW_LINES));

//is the current user a moderator in this forum?
define ('IS_MOD',    AUTH && isMod (NAME));
//is the current user a member of this forum?
define ('IS_MEMBER', AUTH && isMember (NAME));


/* theme & translation
   ====================================================================================================================== */
//shorthand to the server-side location of the particular theme folder (this gets used a lot)
define ('THEME_ROOT', FORUM_ROOT.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.FORUM_THEME.DIRECTORY_SEPARATOR);
//load the theme-specific functions
@(include THEME_ROOT.'theme.php') or require FORUM_LIB.'error_theme.php';
//load the user’s theme configuration, if it exists
@(include THEME_ROOT.'theme.config.php');
//include the theme defaults
@(include THEME_ROOT.'theme.config.default.php') or require FORUM_LIB.'error_configtheme.php';

//include the language translations
$LANG = array ();
foreach (explode (' ', THEME_LANGS) as $lang) @include THEME_ROOT."lang.$lang.php";

//get / set the language to use
define ('LANG',
	//if the language selector has been used to choose a language:
	isset ($_POST['lang']) && setcookie (
	//- set the language cookie for 1 year
	"lang", $_POST['lang'], time ()+60*60*24*365, FORUM_PATH, $_SERVER['HTTP_HOST'], FORUM_HTTPS
)	? $_POST['lang']
	//otherwise, does a cookie already exist to set the language?
	: (@$_COOKIE['lang'] ? $_COOKIE['lang'] : (
	//otherwise, try detect the language sent by the browser:
	$lang = @array_shift (array_intersect (
		//- find language codes in the HTTP header and compare with the theme provided languages
		preg_replace ('/^([a-z0-9-]+).*/i', '$1', explode (',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])),
		explode (' ', THEME_LANGS)
	)) ? $lang
	//all else failing, use the default language
	: THEME_LANG))
);
//don’t treat language choice as an invalid form error
if (isset ($_POST['lang'])) unset ($_POST);


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
if (!AUTH_HTTP && isset ($_GET['signin'])) {
	header ('WWW-Authenticate: Basic');
	header ('HTTP/1.0 401 Unauthorized');
	//we don't die here so that if they cancel the login prompt, they shouldn't get a blank page
}

//stop browsers caching, so you don’t have to refresh every time to see changes
header ('Cache-Control: no-cache', true);
header ('Expires: 0', true);

?>