Core\CLI\Arguments
===============

Provides a utility layer to easily manage and use command line arguments.

<h3>Usage Examples</h3>

$arguments = new \Core\CLI\Arguments(
    [
        'help' => [
            'description' => 'Display help and exit.',
            'value' => false,
            'shorthand' => ['?', 'h'],
        ],
        'component' => [
            'description' => 'Operate on a component with the given name.',
            'value' => true,
            'shorthand' => 'c',
            ],
        ],
    ]
);
$arguments->usageHeader = 'A little more information to the user as to what this script does.';

// Process and validate those arguments now.
$arguments->processArguments();

if($arguments->getArgumentValue('help')){
    $arguments->printUsage();
    exit;
}

if($arguments->getArgumentValue('component')){
    // Do something with this option.
}


* Class name: Arguments
* Namespace: Core\CLI





Properties
----------


### $_args

    protected mixed $_args = array()





* Visibility: **protected**


### $usageHeader

    public string $usageHeader = 'Usage:'





* Visibility: **public**


### $_processed

    private boolean $_processed = false





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\CLI\Arguments::__construct(array $allowed_arguments)

Construct a new Arguments set with the set of arguments allowed.



* Visibility: **public**


#### Arguments
* $allowed_arguments **array** - &lt;p&gt;Allowed arguments to send.&lt;/p&gt;



### addAllowedArgument

    mixed Core\CLI\Arguments::addAllowedArgument(array $argument_data)

Add a new allowed argument to the script.

The array supports the following keys:
<dl>

<dt>name (required)</dt>
<dd>Name of this argument, required and must not contain spaces, used to generate the "--[name]" context.

<dt>description (recommended)</dt>
<dd>Help text to print to the user when executing printUsage().</dd>

<dt>value</dt>
<dd>
True/false if this argument requires or supports a value set.
If false, simply --[name] is used.
If true, --[name]="blah" is used.
</dd>

<dt>shorthand</dt>
<dd>
Any shorthand arguments that are allowed, these are exposed via a single dash on the CLI.
Can be either an array or a string.
</dd>

<dt>multiple</dt>
<dd>
Set to true if multiple instances of the same argument are allowed.
This is useful if you need to allow the user to provide a list of something.
Setting this to true will force getVal and getArgumentValue to always return an array.
</dd>

</dl>

* Visibility: **public**


#### Arguments
* $argument_data **array** - &lt;p&gt;Array data of the argument to add&lt;/p&gt;



### printUsage

    mixed Core\CLI\Arguments::printUsage()

Print usage of this argument set to STDOUT.



* Visibility: **public**




### processArguments

    mixed Core\CLI\Arguments::processArguments()

Process the actual arguments passed in from the user.

This is a required step if you want this system to work as intended.

* Visibility: **public**




### getArguments

    array Core\CLI\Arguments::getArguments()

Get all the arguments as an array.



* Visibility: **public**




### getArgument

    \Core\CLI\Argument|null Core\CLI\Arguments::getArgument(string $name)

Get the argument object itself, or null if it doesn't exist.



* Visibility: **public**


#### Arguments
* $name **string**



### getArgumentValue

    mixed Core\CLI\Arguments::getArgumentValue(string $name)

Get the argument value or null if it doesn't exist.



* Visibility: **public**


#### Arguments
* $name **string**



### getVal

    mixed Core\CLI\Arguments::getVal(string $name)

Shortcut of getArgumentValue.



* Visibility: **public**


#### Arguments
* $name **string**



### printError

    mixed Core\CLI\Arguments::printError(string $error)

Print an error to the STDOUT and exit the script.



* Visibility: **public**


#### Arguments
* $error **string**


