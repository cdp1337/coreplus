SQL_Parser_Dataset
===============

A Core-specific build of the SQL_Parser customized to return a valid Dataset object instead.

<h3>Usage Examples</h3>


<h4>Example 1</h4>
<p>Standard Usage</p>
<code>
// Some code for example 1
$contents = '-- some sql code';
$parser = new SQL_Parser_Dataset($contents, SQL_Parser::DIALECT_MYSQL);
$datasets = $parser->parse();
foreach($datasets as $ds){
    $ds->execute();
}
</code>


* Class name: SQL_Parser_Dataset
* Namespace: 
* Parent class: [SQL_Parser](sql_parser.md)



Constants
----------


### DIALECT_ANSI

    const DIALECT_ANSI = 'ANSI'





### DIALECT_MYSQL

    const DIALECT_MYSQL = 'MySQL'





Properties
----------


### $lexer

    public \SQL_Parser_Lexer $lexer





* Visibility: **public**


### $token

    public string $token





* Visibility: **public**


### $functions

    public array $functions = array()





* Visibility: **public**


### $types

    public array $types = array()





* Visibility: **public**


### $symbols

    public array $symbols = array()





* Visibility: **public**


### $operators

    public array $operators = array()





* Visibility: **public**


### $synonyms

    public array $synonyms = array()





* Visibility: **public**


### $lexeropts

    public array $lexeropts = array()





* Visibility: **public**


### $parseropts

    public array $parseropts = array()





* Visibility: **public**


### $comments

    public array $comments = array()





* Visibility: **public**


### $quotes

    public array $quotes = array()





* Visibility: **public**


### $dialects

    public array $dialects = array('ANSI', 'MySQL')





* Visibility: **public**
* This property is **static**.


### $notes

    public mixed $notes = array()





* Visibility: **public**


Methods
-------


### parseSelect

    mixed SQL_Parser::parseSelect($subSelect)





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $subSelect **mixed**



### parseInsert

    mixed SQL_Parser::parseInsert()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseUpdate

    mixed SQL_Parser::parseUpdate()

UPDATE tablename SET (colname = (value|colname) (,|WHERE searchclause))+



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseDelete

    mixed SQL_Parser::parseDelete()

DELETE FROM tablename WHERE searchclause



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseQuery

    array SQL_Parser::parseQuery()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseWhereCondition

    array SQL_Parser_Dataset::parseWhereCondition()

parses conditions usually used in WHERE



* Visibility: **public**




### ConstructAndParse

    array SQL_Parser_Dataset::ConstructAndParse(null $string, string $dialect)

Shorthand for creating a new object and calling parse.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $string **null**
* $dialect **string**



### __construct

    mixed SQL_Parser::__construct(string $string, string $dialect)

Constructor



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $string **string** - &lt;p&gt;the SQL query to parse&lt;/p&gt;
* $dialect **string** - &lt;p&gt;the SQL dialect&lt;/p&gt;



### initLexer

    mixed SQL_Parser::initLexer($string)





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $string **mixed**



### setDialect

    mixed SQL_Parser::setDialect(string $dialect)

loads SQL dialect specific data



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $dialect **string** - &lt;p&gt;the SQL dialect to use&lt;/p&gt;



### getParams

    mixed SQL_Parser::getParams(array $values, array $types, integer $i)

extracts parameters from a function call

this function should be called if an opening brace is found,
so the first call to $this->getTok() will return first param
or the closing )

* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $values **array** - &lt;p&gt;&amp;$values to set it&lt;/p&gt;
* $types **array** - &lt;p&gt;&amp;$types  to set it&lt;/p&gt;
* $i **integer** - &lt;p&gt;position&lt;/p&gt;



### raiseError

    mixed SQL_Parser::raiseError(string $message)





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $message **string** - &lt;p&gt;error message&lt;/p&gt;



### isType

    boolean SQL_Parser::isType()

Returns true if current token is a variable type name, otherwise false



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### isVal

    boolean SQL_Parser::isVal()

Returns true if current token is a value, otherwise false



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### isFunc

    boolean SQL_Parser::isFunc()

Returns true if current token is a function, otherwise false



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### isCommand

    boolean SQL_Parser::isCommand()

Returns true if current token is a command, otherwise false



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### isReserved

    boolean SQL_Parser::isReserved()

Returns true if current token is a reserved word, otherwise false



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### isOperator

    boolean SQL_Parser::isOperator()

Returns true if current token is an operator, otherwise false



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### getTok

    void SQL_Parser::getTok()

retrieves next token



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseFieldOptions

    array SQL_Parser::parseFieldOptions()

Parses field/column options, usually  for an CREATE or ALTER TABLE statement



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseCondition

    array SQL_Parser::parseCondition()

parses conditions usually used in WHERE or ON



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseSelectExpression

    mixed SQL_Parser::parseSelectExpression()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseFieldList

    mixed SQL_Parser::parseFieldList()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseFunctionOpts

    mixed SQL_Parser::parseFunctionOpts()

Parses parameters in a function call



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseCreate

    mixed SQL_Parser::parseCreate()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseTableFactor

    mixed SQL_Parser::parseTableFactor()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseTableReference

    mixed SQL_Parser::parseTableReference()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseFrom

    mixed SQL_Parser::parseFrom()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseDrop

    mixed SQL_Parser::parseDrop()





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseIdentifier

    mixed SQL_Parser::parseIdentifier($type)

[[db.].table].column [[AS] alias]



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $type **mixed**



### parseLock

    mixed SQL_Parser::parseLock()

tbl_name [[AS] alias] lock_type[, .

..]

* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parseLockType

    mixed SQL_Parser::parseLockType()

READ [LOCAL] | [LOW_PRIORITY] WRITE



* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)




### parse

    array SQL_Parser::parse(null $string)





* Visibility: **public**
* This method is defined by [SQL_Parser](sql_parser.md)


#### Arguments
* $string **null**


