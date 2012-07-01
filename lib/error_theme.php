<!DOCTYPE html>
<title>File "theme.php" Cannot Be Found</title>
<h1>NoNonsense Forum</h2>
<h2>Error: File "theme.php" Cannot Be Found</h2>
<p>
	The file "theme.php" cannot be found at the expected location "/themes/<?php echo FORUM_THEME; ?>/theme.php".
</p>
<dl>

<dt><strong>Is your `FORUM_THEME` option correct?</strong></dt>
<dd><p>
	The `FORUM_THEME` option, within "config.php" (if it exists), specifies the name of the theme to use.
	That theme should exist as a folder in the "themes" folder (e.g. "greyscale"). If you are using another theme,
	ensure that it exists in the "themes" folder, and that `FORUM_THEME` is correctly set.
</p></dd>

<dt><strong>Do not modify or rename the "greyscale" theme:</strong></dt>
<dd><p>
	You should not modify the default theme beyond creating a "theme.config.php" or "custom.css" file.
	If you wish to modify any of the HTML of the theme, you should duplicate the "greyscale" theme, rename that,
	and set the `FORUM_THEME` option accordingly.
</p></dd>

</dl>
<?php exit (1); ?>