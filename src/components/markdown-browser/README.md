# Markdown Browser README

The markdown browser component allows seamless listing and rendering of markdown
files on the server's local filesystem to web users.

This is extremely useful for technical documentation where your developers insist
on writing docs in their native IDE of choice and then SFTP or rsync the files to
the production servers. :p

## Features / Usage

Most simple usage is just create a directory on the server,
point the site configuration to that absolute path,
and upload files there!

The uploaded files show up under the admin and can be viewed and/or 
registered as full local pages, 
(for permission editing and including in navigation, sitemaps, and searches).

### Sub-Directories

Directories can be created inside the main md-root in whatever organizational
structure is preferred.  The markdown browser will display them as they are created.

	# Example:
	Physical Root: "/var/www/md-files"
	Physical File: "/var/www/md-files/thing/topic/subtopic.md"
	
	# URL of
	/markdownbrowser/view/thing/topic/subtopic

### Indexes

Indexes can be provided with a file named `index.md` inside directories.

	# Example:
    Physical Root: "/var/www/md-files"
    Physical File: "/var/www/md-files/thing/topic/subtopic2/index.md"
    
    # URL of
    /markdownbrowser/view/thing/topic/subtopic2

### Images

Images are resolved either via absolute or relative paths; UNC's are also supported.

To define an absolute path, prefix the filename with `/`.

To define a relative path, simply omit the prepended `/`.

### Links

Links to fully resolved files are allowed without modification.
	
	[GOOGLE](http://google.com)
	
	# Resolves to
	
	<a href="http://google.com">GOOGLE</a>

Links to a virtual, rewrite, or alias URL resolve to the fully resolved URL.

	[Home Page](/)
	
	# Resolves to
	
	<a href="http://site.tld/">Home Page</a>

Links to other markdown files resolve to a view link to that file.

	[see this doc](other.md)
	
	# Resolves to
	
	<a href="http://site.tld/markdownbrowser/view/other">see this doc</a>

Links to arbitrary files inside the markdown root directory resolve to a download link.
	
	Download the [attached tarball](something.tgz)
	
	# Resolves to
	
	Download the <a href="http://site.tld/markdownbrowser/download/something.tgz">attached tarball</a>

## Caveats

Some caveats of this system include:

* Filenames must be lowercase
* Filenames cannot contain spaces
* Extra dots in filenames should be avoided