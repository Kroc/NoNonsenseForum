<?php //theme configuration defaults
/* ====================================================================================================================== */
/* NoNonsense Forum v26 © Copyright (CC-BY) Kroc Camen 2010-2015
   licenced under Creative Commons Attribution 3.0 <creativecommons.org/licenses/by/3.0/deed.en_GB>
   you may do whatever you want to this code as long as you give credit to Kroc Camen, <camendesign.com>
*/


/* --- copy this file to 'theme.config.php' and customise to your liking,
       DON’T DELETE OR MODIFY 'theme.config.default.php'! --- */


/* required options: (used internally by NoNonsense Forum itself, all themes must provide these)
   ====================================================================================================================== */
//a space-delimited list of theme translations the user can choose. each item should be a standard language code
//(see <w3.org/International/questions/qa-choosing-language-tags> for information on choosing a language code)
//which therefore refers to the matching 'lang.*.php' file, e.g. 'fr de es it' for French, German, Spanish & Italian.
//see 'lang.example.php' for info on translations
@define ('THEME_LANGS',         '');
//(if you change the text in the theme ['*.html' files], you might want to change this option to blank '' so that users
//can’t use the other translations--they may no longer match up with your default language's text--unless you intend to
//update the additonal translations too!)

//the translation to use by default. leave blank to use the theme's default language (which is what is written directly
//into the HTML files), otherwise, enter the translation's language code to change the default language of your forum
@define ('THEME_LANG',          '');


/* optional: (options unique to this theme)
   ====================================================================================================================== */
//filename of the image to use as the site logo (assumed to be within the theme's folder)
//- for this theme, it should be 64x64 px (HiDPI) but will appear as 32x32 on lo-DPI screens
@define ('THEME_LOGO',          'logo.png');

//colour for Windows 8 to use on the Start Screen when a user pins the site.
//note that this colour is not guaranteed to be used as-is, Windows 8 changes the colour into a nearby colour that it knows
//warning: you can’t use shorthand colour notation (i.e. "#222")
@define ('METRO_COLOUR',        '#222222');

?>