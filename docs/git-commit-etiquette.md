# GIT Commit Etiquette

As of Core 2.6.1, GIT commit messages can be directly used to generate a changelog for the core and all components.  To assist this, (and because it *is* a nice practice after all), here are some guidelines for proper commit message etiquette, kindly borrowed from [Tim Pope's blog post](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html).

> Capitalized, short (50 chars or less) summary
> 
> More detailed explanatory text, if necessary.  Wrap it to about 72
> characters or so.  In some contexts, the first line is treated as the
> subject of an email and the rest of the text as the body.  The blank
> line separating the summary from the body is critical (unless you omit
> the body entirely); tools like rebase can get confused if you run the
> two together.
> 
> Write your commit message in the imperative: "Fix bug" and not "Fixed bug"
> or "Fixes bug."  This convention matches up with commit messages generated
> by commands like git merge and git revert.
> 
> Further paragraphs come after blank lines.
> 
> - Bullet points are okay, too
> 
> - Typically a hyphen or asterisk is used for the bullet, preceded by a
>   single space, with blank lines in between, but conventions vary here
> 
> - Use a hanging indent

## One Bug, One Commit

Each commit should pertain to **exactly one** issue, feature, or fix.  This makes tracking changes and merge requests simplier with less chance of errors slipping through cracks.  Beyond the source code management even, keeping one commit attached to exactly one issue allows for another thing, being able to use that data in other applications.

As of Core 2.6.1, the short summary on line 1 of the commit message is used as a changelog entry for that new version.  This follows virtually every other git tool, (so it shouldn't be anything new).  As such, if there are 4 fixes and 2 new features for a given release, there should be 6 commits, and therefore 6 changelog entries.

## Subject

The subject line should be short, sweet, and to the point.  If it addressed Bug #123, it should include "Fix Bug #123"!  If the commit provides new functionality, it should contain "New feature blah".  Performance and security tweaks should also be noted as such.  Although the 50-character limit is not imposed anywhere, and changelogs handle line wrapping automatically... no body wants to receive an email with a paragraph as the subject line!

## Subject Keywords

There are a few keywords that have special meanings.

* "bug #*{number}*" - Commit addresses bug number *{number}* in the tracker.
* "feature #*{number}*" - Commit addresses feature number *{number}* in the tracker.
* "fix bug" - Commit set as a "Bug" type in the changelog.
* "new feature" - Commit set as a "Feature" type in the changelog.
* "performance" - Commit set as a "Performance" type in the changelog.
* "faster" - Commit set as a "Performance" type in the changelog.
* "secure" - Commit set as a "Security"type in the changelog.
* "security" - Commit set as a "Security"type in the changelog.

## Detailed Explanation

The more detailed explanation text should be used to describe why the fix is being applied or how it was broken in the first place.  This should match code documentation, everyone can perfectly read *what* the code is doing, but the real mystery often is **why** the code is doing what its doing!
