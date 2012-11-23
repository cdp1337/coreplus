## utilities/compiler.php

The compiler will sift through the entire core application and assemble a "compiled" version of the PHP files.  This does
not heavily modify the source code or generate bytecode like some other more sophisticated compilers, but simply copies
them to a bootstrap.compiled.php file located in core/.  This is done to reduce the number of disk IO operations required
on page execution by reducing the number of files needed to be opened.

To execute, simply run the script (utilities/compiler.php) from the terminal and it will do its magic as necessary.

If you are working on the core framework and do not wish to use the compiled version, (or if it is giving you problems),
simply open index.php and swap out the lines:

	// When working on the core, it's best to switch this back to core/bootstrap.php!
	//require_once('core/bootstrap.php');
	require_once('core/bootstrap.compiled.php');

with

	// When working on the core, it's best to switch this back to core/bootstrap.php!
	require_once('core/bootstrap.php');
	//require_once('core/bootstrap.compiled.php');