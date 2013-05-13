<!DOCTYPE html>
<title>Apache 2.1 Required</title>
<h1>NoNonsense Forum</h2>
<h2>Error: Apache 2.1 Required</h2>
<p>
	A minimum Apache Server version of 2.1 is required to run NoNonsense Forum.
	Your server reports it has version "<?php echo apache_get_version () ?>".
</p>
<dl>

<dt><strong>Upgrade the Apache software, use a different server software (e.g IIS) or change hosting company:</strong></dt>
<dd><p>
	1&amp;1 hosting is known to be limited to Apache v1. You should change hosting company if using them.
</p></dd>

</dl>
<?php exit (1); ?>