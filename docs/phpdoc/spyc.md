Spyc
===============

The Simple PHP YAML Class.

This class can be used to read a YAML file and convert its contents
into a PHP array.  It currently supports a very limited subsection of
the YAML spec.

Usage:
<code>
  $Spyc  = new Spyc;
  $array = $Spyc->load($file);
</code>
or:
<code>
  $array = Spyc::YAMLLoad($file);
</code>
or:
<code>
  $array = spyc_load_file($file);
</code>


* Class name: Spyc
* Namespace: 



Constants
----------


### REMPTY

    const REMPTY = "\0\0\0\0\0"





Properties
----------


### $setting_dump_force_quotes

    public boolean $setting_dump_force_quotes = false

Setting this to true will force YAMLDump to enclose any string value in
quotes.  False by default.



* Visibility: **public**


### $setting_use_syck_is_possible

    public boolean $setting_use_syck_is_possible = false

Setting this to true will forse YAMLLoad to use syck_load function when
possible. False by default.



* Visibility: **public**


### $_dumpIndent

    private mixed $_dumpIndent





* Visibility: **private**


### $_dumpWordWrap

    private mixed $_dumpWordWrap





* Visibility: **private**


### $_containsGroupAnchor

    private mixed $_containsGroupAnchor = false





* Visibility: **private**


### $_containsGroupAlias

    private mixed $_containsGroupAlias = false





* Visibility: **private**


### $path

    private mixed $path





* Visibility: **private**


### $result

    private mixed $result





* Visibility: **private**


### $LiteralPlaceHolder

    private mixed $LiteralPlaceHolder = '___YAML_Literal_Block___'





* Visibility: **private**


### $SavedGroups

    private mixed $SavedGroups = array()





* Visibility: **private**


### $indent

    private mixed $indent





* Visibility: **private**


### $delayedPath

    private array $delayedPath = array()

Path modifier that should be applied after adding current element.



* Visibility: **private**


### $_nodeId

    public mixed $_nodeId





* Visibility: **public**


Methods
-------


### load

    array Spyc::load(string $input)

Load a valid YAML string to Spyc.



* Visibility: **public**


#### Arguments
* $input **string**



### loadFile

    array Spyc::loadFile(string $file)

Load a valid YAML file to Spyc.



* Visibility: **public**


#### Arguments
* $file **string**



### YAMLLoad

    array Spyc::YAMLLoad(string $input)

Load YAML into a PHP array statically

The load method, when supplied with a YAML stream (string or file),
will do its best to convert YAML in a file into a PHP array.  Pretty
simple.
 Usage:
 <code>
  $array = Spyc::YAMLLoad('lucky.yaml');
  print_r($array);
 </code>

* Visibility: **public**
* This method is **static**.


#### Arguments
* $input **string** - &lt;p&gt;Path of YAML file or string containing YAML&lt;/p&gt;



### YAMLLoadString

    array Spyc::YAMLLoadString(string $input)

Load a string of YAML into a PHP array statically

The load method, when supplied with a YAML string, will do its best
to convert YAML in a string into a PHP array.  Pretty simple.

Note: use this function if you don't want files from the file system
loaded and processed as YAML.  This is of interest to people concerned
about security whose input is from a string.

 Usage:
 <code>
  $array = Spyc::YAMLLoadString("---\n0: hello world\n");
  print_r($array);
 </code>

* Visibility: **public**
* This method is **static**.


#### Arguments
* $input **string** - &lt;p&gt;String containing YAML&lt;/p&gt;



### YAMLDump

    string Spyc::YAMLDump(array $array, integer $indent, integer $wordwrap, integer $no_opening_dashes)

Dump YAML from PHP array statically

The dump method, when supplied with an array, will do its best
to convert the array into friendly YAML.  Pretty simple.  Feel free to
save the returned string as nothing.yaml and pass it around.

Oh, and you can decide how big the indent is and what the wordwrap
for folding is.  Pretty cool -- just pass in 'false' for either if
you want to use the default.

Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
you can turn off wordwrap by passing in 0.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $array **array** - &lt;p&gt;PHP array&lt;/p&gt;
* $indent **integer** - &lt;p&gt;Pass in false to use the default, which is 2&lt;/p&gt;
* $wordwrap **integer** - &lt;p&gt;Pass in 0 for no wordwrap, false for default (40)&lt;/p&gt;
* $no_opening_dashes **integer** - &lt;p&gt;Do not start YAML file with &quot;---\n&quot;&lt;/p&gt;



### dump

    string Spyc::dump(array $array, integer $indent, integer $wordwrap, $no_opening_dashes)

Dump PHP array to YAML

The dump method, when supplied with an array, will do its best
to convert the array into friendly YAML.  Pretty simple.  Feel free to
save the returned string as tasteful.yaml and pass it around.

Oh, and you can decide how big the indent is and what the wordwrap
for folding is.  Pretty cool -- just pass in 'false' for either if
you want to use the default.

Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
you can turn off wordwrap by passing in 0.

* Visibility: **public**


#### Arguments
* $array **array** - &lt;p&gt;PHP array&lt;/p&gt;
* $indent **integer** - &lt;p&gt;Pass in false to use the default, which is 2&lt;/p&gt;
* $wordwrap **integer** - &lt;p&gt;Pass in 0 for no wordwrap, false for default (40)&lt;/p&gt;
* $no_opening_dashes **mixed**



### _yamlize

    string Spyc::_yamlize($key, $value, $indent, $previous_key, $first_key, $source_array)

Attempts to convert a key / value array item to YAML



* Visibility: **private**


#### Arguments
* $key **mixed** - &lt;p&gt;The name of the key&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value of the item&lt;/p&gt;
* $indent **mixed** - &lt;p&gt;The indent of the current node&lt;/p&gt;
* $previous_key **mixed**
* $first_key **mixed**
* $source_array **mixed**



### _yamlizeArray

    string Spyc::_yamlizeArray($array, $indent)

Attempts to convert an array to YAML



* Visibility: **private**


#### Arguments
* $array **mixed** - &lt;p&gt;The array you want to convert&lt;/p&gt;
* $indent **mixed** - &lt;p&gt;The indent of the current level&lt;/p&gt;



### _dumpNode

    string Spyc::_dumpNode($key, $value, $indent, $previous_key, $first_key, $source_array)

Returns YAML from a key and a value



* Visibility: **private**


#### Arguments
* $key **mixed** - &lt;p&gt;The name of the key&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value of the item&lt;/p&gt;
* $indent **mixed** - &lt;p&gt;The indent of the current node&lt;/p&gt;
* $previous_key **mixed**
* $first_key **mixed**
* $source_array **mixed**



### _doLiteralBlock

    string Spyc::_doLiteralBlock($value, $indent)

Creates a literal block for dumping



* Visibility: **private**


#### Arguments
* $value **mixed**
* $indent **mixed** - &lt;p&gt;int The value of the indent&lt;/p&gt;



### _doFolding

    string Spyc::_doFolding($value, $indent)

Folds a string of text, if necessary



* Visibility: **private**


#### Arguments
* $value **mixed** - &lt;p&gt;The string you wish to fold&lt;/p&gt;
* $indent **mixed**



### isTrueWord

    mixed Spyc::isTrueWord($value)





* Visibility: **private**


#### Arguments
* $value **mixed**



### isFalseWord

    mixed Spyc::isFalseWord($value)





* Visibility: **private**


#### Arguments
* $value **mixed**



### isNullWord

    mixed Spyc::isNullWord($value)





* Visibility: **private**


#### Arguments
* $value **mixed**



### isTranslationWord

    mixed Spyc::isTranslationWord($value)





* Visibility: **private**


#### Arguments
* $value **mixed**



### coerceValue

    mixed Spyc::coerceValue($value)

Coerce a string into a native type
Reference: http://yaml.org/type/bool.html
TODO: Use only words from the YAML spec.



* Visibility: **private**


#### Arguments
* $value **mixed** - &lt;p&gt;The value to coerce&lt;/p&gt;



### getTranslations

    mixed Spyc::getTranslations($words)

Given a set of words, perform the appropriate translations on them to
match the YAML 1.1 specification for type coercing.



* Visibility: **private**
* This method is **static**.


#### Arguments
* $words **mixed** - &lt;p&gt;The words to translate&lt;/p&gt;



### __load

    mixed Spyc::__load($input)





* Visibility: **private**


#### Arguments
* $input **mixed**



### __loadString

    mixed Spyc::__loadString($input)





* Visibility: **private**


#### Arguments
* $input **mixed**



### loadWithSource

    mixed Spyc::loadWithSource($Source)





* Visibility: **private**


#### Arguments
* $Source **mixed**



### loadFromSource

    mixed Spyc::loadFromSource($input)





* Visibility: **private**


#### Arguments
* $input **mixed**



### loadFromString

    mixed Spyc::loadFromString($input)





* Visibility: **private**


#### Arguments
* $input **mixed**



### _parseLine

    array Spyc::_parseLine(string $line)

Parses YAML code and returns an array for a node



* Visibility: **private**


#### Arguments
* $line **string** - &lt;p&gt;A line from the YAML file&lt;/p&gt;



### _toType

    mixed Spyc::_toType(string $value)

Finds the type of the passed value, returns the value as the new type.



* Visibility: **private**


#### Arguments
* $value **string**



### _inlineEscape

    array Spyc::_inlineEscape($inline)

Used in inlines to check for more inlines or quoted strings



* Visibility: **private**


#### Arguments
* $inline **mixed**



### literalBlockContinues

    mixed Spyc::literalBlockContinues($line, $lineIndent)





* Visibility: **private**


#### Arguments
* $line **mixed**
* $lineIndent **mixed**



### referenceContentsByAlias

    mixed Spyc::referenceContentsByAlias($alias)





* Visibility: **private**


#### Arguments
* $alias **mixed**



### addArrayInline

    mixed Spyc::addArrayInline($array, $indent)





* Visibility: **private**


#### Arguments
* $array **mixed**
* $indent **mixed**



### addArray

    mixed Spyc::addArray($incoming_data, $incoming_indent)





* Visibility: **private**


#### Arguments
* $incoming_data **mixed**
* $incoming_indent **mixed**



### startsLiteralBlock

    mixed Spyc::startsLiteralBlock($line)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $line **mixed**



### greedilyNeedNextLine

    mixed Spyc::greedilyNeedNextLine($line)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $line **mixed**



### addLiteralLine

    mixed Spyc::addLiteralLine($literalBlock, $line, $literalBlockStyle, $indent)





* Visibility: **private**


#### Arguments
* $literalBlock **mixed**
* $line **mixed**
* $literalBlockStyle **mixed**
* $indent **mixed**



### revertLiteralPlaceHolder

    mixed Spyc::revertLiteralPlaceHolder($lineArray, $literalBlock)





* Visibility: **public**


#### Arguments
* $lineArray **mixed**
* $literalBlock **mixed**



### stripIndent

    mixed Spyc::stripIndent($line, $indent)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $line **mixed**
* $indent **mixed**



### getParentPathByIndent

    mixed Spyc::getParentPathByIndent($indent)





* Visibility: **private**


#### Arguments
* $indent **mixed**



### clearBiggerPathValues

    mixed Spyc::clearBiggerPathValues($indent)





* Visibility: **private**


#### Arguments
* $indent **mixed**



### isComment

    mixed Spyc::isComment($line)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $line **mixed**



### isEmpty

    mixed Spyc::isEmpty($line)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $line **mixed**



### isArrayElement

    mixed Spyc::isArrayElement($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### isHashElement

    mixed Spyc::isHashElement($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### isLiteral

    mixed Spyc::isLiteral($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### unquote

    mixed Spyc::unquote($value)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $value **mixed**



### startsMappedSequence

    mixed Spyc::startsMappedSequence($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### returnMappedSequence

    mixed Spyc::returnMappedSequence($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### checkKeysInValue

    mixed Spyc::checkKeysInValue($value)





* Visibility: **private**


#### Arguments
* $value **mixed**



### returnMappedValue

    mixed Spyc::returnMappedValue($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### startsMappedValue

    mixed Spyc::startsMappedValue($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### isPlainArray

    mixed Spyc::isPlainArray($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### returnPlainArray

    mixed Spyc::returnPlainArray($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### returnKeyValuePair

    mixed Spyc::returnKeyValuePair($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### returnArrayElement

    mixed Spyc::returnArrayElement($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### nodeContainsGroup

    mixed Spyc::nodeContainsGroup($line)





* Visibility: **private**


#### Arguments
* $line **mixed**



### addGroup

    mixed Spyc::addGroup($line, $group)





* Visibility: **private**


#### Arguments
* $line **mixed**
* $group **mixed**



### stripGroup

    mixed Spyc::stripGroup($line, $group)





* Visibility: **private**


#### Arguments
* $line **mixed**
* $group **mixed**


