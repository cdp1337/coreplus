Core\CLI\CLI
===============






* Class name: CLI
* Namespace: Core\CLI







Methods
-------


### PromptUser

    boolean|string Core\CLI\CLI::PromptUser(string $question, array|string $answers, string|boolean $default)

Prompt the user a question and return the result.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $question **string** - &lt;p&gt;The question to prompt to the user.&lt;/p&gt;
* $answers **array|string** - &lt;p&gt;What answers to provide to the user.
array           - Will prompt the user with the value of each pair, returning the key.
&quot;boolean&quot;       - Will ask for a yes/no response and return true/false.
&quot;text&quot;          - Open-ended text input, user can type in anything and that input is returned.
&quot;text-required&quot; - Open-ended text input, user can type in anything (non-blank), and that value is returned.&lt;/p&gt;
* $default **string|boolean** - &lt;p&gt;string The default answer if the user simply presses &quot;enter&quot;. [optional]&lt;/p&gt;



### RequireEditor

    mixed Core\CLI\CLI::RequireEditor()

Set the 'EDITOR' variable to be set.

This is a linux-specific thing that svn shares also.

The user will usually set their preferred EDITOR, be it vi/vim, emacs or nano.
If they didn't, ask the user for their choice.

* Visibility: **public**
* This method is **static**.




### PrintHeader

    mixed Core\CLI\CLI::PrintHeader(string $line, integer $maxlen)

Print a stylized header to stdout



* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **string** - &lt;p&gt;The header string to output&lt;/p&gt;
* $maxlen **integer** - &lt;p&gt;Maximum length of the line&lt;/p&gt;



### PrintLine

    mixed Core\CLI\CLI::PrintLine(string|array $line, string $color)

Print a single line or multiple lines of text to the screen or console.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **string|array** - &lt;p&gt;Line (or array of lines) to output&lt;/p&gt;
* $color **string** - &lt;p&gt;Colour to render the output with&lt;/p&gt;



### PrintError

    mixed Core\CLI\CLI::PrintError(string $line)

Print an error to stdout



* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **string**



### PrintSuccess

    mixed Core\CLI\CLI::PrintSuccess(string $line)

Print a success message to stdout



* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **string**



### PrintWarning

    mixed Core\CLI\CLI::PrintWarning(string $line)

Print a warning to stdout



* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **string**



### PrintDebug

    mixed Core\CLI\CLI::PrintDebug(string $line)

Print a debug message to stdout



* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **string**



### PrintActionStart

    mixed Core\CLI\CLI::PrintActionStart($line, integer $maxlen, string $suffix)

Print an action start line

This is usually rendered in the format of

```
Performing Some Action ...           [ OK ]
```

* Visibility: **public**
* This method is **static**.


#### Arguments
* $line **mixed**
* $maxlen **integer**
* $suffix **string**



### PrintActionStatus

    mixed Core\CLI\CLI::PrintActionStatus(string|integer|boolean $status)

Print the result of a previous "ActionStart" command.

If the param is TRUE, 1, or 'OK', then '[  OK  ]' is rendered.
If the param is FALSE, 0, or 'fail', then '[  !!  ]' is rendered.
If the param is 'skip', then '[ SKIP ]' is rendered.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $status **string|integer|boolean**



### PrintProgressBar

    mixed Core\CLI\CLI::PrintProgressBar($percent)

Print a progress bar and/or a status update on an existing progress bar.

If an absolute value is set, then the bar is jumped to that new value.
Any absolute value less than the current will reset the progress bar to a new line.

A relative percentage can be set via the prefix '+'.
For example, '+1' will bump the progress bar up by 1%.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $percent **mixed**



### LoadSettingsFile

    boolean Core\CLI\CLI::LoadSettingsFile(string $filebasename)

This can be used to load a saved session from the user's home directory.

It's useful for saving common per-user data across different executions.

Since CLI scripts are per-user and localhost only, these can, and should be, saved locally.

Note, no error is generated if file doesn't exist, but false is returned instead of true.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $filebasename **string** - &lt;p&gt;The basename of the settings file, the .php is added automatically.&lt;/p&gt;



### SaveSettingsFile

    mixed Core\CLI\CLI::SaveSettingsFile(string $filebasename, \Core\CLI\... $variables)

Save the user settings back to the settings file.

Any parameter given after the first is written to the settings file.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $filebasename **string** - &lt;p&gt;The basename of the settings file, the .php is added automatically.&lt;/p&gt;
* $variables **Core\CLI\...** - &lt;p&gt;Any variables to save.&lt;/p&gt;



### _FlushRequired

    mixed Core\CLI\CLI::_FlushRequired()

Get if an obflush is required to send output to the end browser.

If another output buffer is active other than the primary one, do not flush anything!

* Visibility: **private**
* This method is **static**.



