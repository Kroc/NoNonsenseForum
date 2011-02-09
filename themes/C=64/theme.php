<?php //defines the website theme, keeping HTML in one place
/* ====================================================================================================================== */
/* NoNonsenseForum © Copyright (CC-BY) Kroc Camen 2011
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* common strings used throughout or for non-HTML purposes
   ---------------------------------------------------------------------------------------------------------------------- */
//the `date` format code used to print human readable dates into the HTML,
//see <php.net/manual/en/function.date.php> for documentation
define ('DATE_FORMAT', "d-M'y H:i");

//prepended to the thread title for each reply (like in e-mail)
define ('TEMPLATE_RE',				'RE: ');


/* the deletion page
   ====================================================================================================================== */
//the text left behind when a post is deleted
define ('TEMPLATE_DELETE_USER', '<p>This post was deleted by its owner</p>');
define ('TEMPLATE_DELETE_MOD',  '<p>This post was deleted by a moderator</p>');


?>