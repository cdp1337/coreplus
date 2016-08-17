SQL_Parser
===============

A sql parser




* Class name: SQL_Parser
* Namespace: 



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


### __construct

    mixed SQL_Parser::__construct(string $string, string $dialect)

Constructor



* Visibility: **public**


#### Arguments
* $string **string** - &lt;p&gt;the SQL query to parse&lt;/p&gt;
* $dialect **string** - &lt;p&gt;the SQL dialect&lt;/p&gt;



### initLexer

    mixed SQL_Parser::initLexer($string)





* Visibility: **public**


#### Arguments
* $string **mixed**



### setDialect

    mixed SQL_Parser::setDialect(string $dialect)

loads SQL dialect specific data



* Visibility: **public**


#### Arguments
* $dialect **string** - &lt;p&gt;the SQL dialect to use&lt;/p&gt;



### getParams

    mixed SQL_Parser::getParams(array $values, array $types, integer $i)

extracts parameters from a function call

this function should be called if an opening brace is found,
so the first call to $this->getTok() will return first param
or the closing )

* Visibility: **public**


#### Arguments
* $values **array** - &lt;p&gt;&amp;$values to set it&lt;/p&gt;
* $types **array** - &lt;p&gt;&amp;$types  to set it&lt;/p&gt;
* $i **integer** - &lt;p&gt;position&lt;/p&gt;



### raiseError

    mixed SQL_Parser::raiseError(string $message)





* Visibility: **public**


#### Arguments
* $message **string** - &lt;p&gt;error message&lt;/p&gt;



### isType

    boolean SQL_Parser::isType()

Returns true if current token is a variable type name, otherwise false



* Visibility: **public**




### isVal

    boolean SQL_Parser::isVal()

Returns true if current token is a value, otherwise false



* Visibility: **public**




### isFunc

    boolean SQL_Parser::isFunc()

Returns true if current token is a function, otherwise false



* Visibility: **public**




### isCommand

    boolean SQL_Parser::isCommand()

Returns true if current token is a command, otherwise false



* Visibility: **public**




### isReserved

    boolean SQL_Parser::isReserved()

Returns true if current token is a reserved word, otherwise false



* Visibility: **public**




### isOperator

    boolean SQL_Parser::isOperator()

Returns true if current token is an operator, otherwise false



* Visibility: **public**




### getTok

    void SQL_Parser::getTok()

retrieves next token



* Visibility: **public**




### parseFieldOptions

    array SQL_Parser::parseFieldOptions()

Parses field/column options, usually  for an CREATE or ALTER TABLE statement



* Visibility: **public**




### parseCondition

    array SQL_Parser::parseCondition()

parses conditions usually used in WHERE or ON



* Visibility: **public**




### parseSelectExpression

    mixed SQL_Parser::parseSelectExpression()





* Visibility: **public**




### parseFieldList

    mixed SQL_Parser::parseFieldList()





* Visibility: **public**




### parseFunctionOpts

    mixed SQL_Parser::parseFunctionOpts()

Parses parameters in a function call



* Visibility: **public**




### parseCreate

    mixed SQL_Parser::parseCreate()





* Visibility: **public**




### parseInsert

    mixed SQL_Parser::parseInsert()





* Visibility: **public**




### parseUpdate

    mixed SQL_Parser::parseUpdate()

UPDATE tablename SET (colname = (value|colname) (,|WHERE searchclause))+



* Visibility: **public**




### parseTableFactor

    mixed SQL_Parser::parseTableFactor()





* Visibility: **public**




### parseTableReference

    mixed SQL_Parser::parseTableReference()





* Visibility: **public**




### parseFrom

    mixed SQL_Parser::parseFrom()





* Visibility: **public**




### parseDelete

    mixed SQL_Parser::parseDelete()

DELETE FROM tablename WHERE searchclause



* Visibility: **public**




### parseDrop

    mixed SQL_Parser::parseDrop()





* Visibility: **public**




### parseIdentifier

    mixed SQL_Parser::parseIdentifier($type)

[[db.].table].column [[AS] alias]



* Visibility: **public**


#### Arguments
* $type **mixed**



### parseSelect

    mixed SQL_Parser::parseSelect($subSelect)





* Visibility: **public**


#### Arguments
* $subSelect **mixed**



### parseLock

    mixed SQL_Parser::parseLock()

tbl_name [[AS] alias] lock_type[, .

..]

* Visibility: **public**




### parseLockType

    mixed SQL_Parser::parseLockType()

READ [LOCAL] | [LOW_PRIORITY] WRITE



* Visibility: **public**




### parseQuery

    array SQL_Parser::parseQuery()





* Visibility: **public**




### parse

    array SQL_Parser::parse(null $string)





* Visibility: **public**


#### Arguments
* $string **null**


