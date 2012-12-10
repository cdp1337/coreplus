# Core::ResolveLink()

Resolve link is a main url resolving utility, used extensively throughout the system.  It can accept a variety of strings
and will return either the fully resolved URL, the root url (home page), or '#'.

If the URL cannot be resolved, simply the ROOT_URL (home page) is returned.

Usage:

    Core::ResolveLink('/mycontroller/action');
    // Resolves a registered controller and action to an absolute URL.

    Core::ResolveLink('/');
    // Resolves the home page to its absolute URL.

    Core::ResolveLink('?blah=something');
    // Appends the requested query string onto the current page.

    Core::ResolveLink('#');
    // Just return '#'.  (used in templates and {a} tags).
