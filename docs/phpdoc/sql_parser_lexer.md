SQL_Parser_Lexer
===============

A lexigraphical analyser inspired by the msql lexer




* Class name: SQL_Parser_Lexer
* Namespace: 





Properties
----------


### $symbols

    public mixed $symbols = array()





* Visibility: **public**


### $comments

    public mixed $comments = array()





* Visibility: **public**


### $quotes

    public mixed $quotes = array()





* Visibility: **public**


### $tokPtr

    public mixed $tokPtr





* Visibility: **public**


### $tokStart

    public mixed $tokStart





* Visibility: **public**


### $tokLen

    public mixed $tokLen





* Visibility: **public**


### $tokText

    public mixed $tokText = ''





* Visibility: **public**


### $lineNo

    public mixed $lineNo





* Visibility: **public**


### $lineBegin

    public mixed $lineBegin





* Visibility: **public**


### $string

    public mixed $string = ''





* Visibility: **public**


### $stringLen

    public mixed $stringLen





* Visibility: **public**


### $tokAbsStart

    public mixed $tokAbsStart





* Visibility: **public**


### $skipText

    public mixed $skipText = ''





* Visibility: **public**


### $lookahead

    public mixed $lookahead





* Visibility: **public**


### $tokenStack

    public mixed $tokenStack = array()





* Visibility: **public**


### $stackPtr

    public mixed $stackPtr





* Visibility: **public**


Methods
-------


### __construct

    mixed SQL_Parser_Lexer::__construct($string, $lookahead, $lexeropts)





* Visibility: **public**


#### Arguments
* $string **mixed**
* $lookahead **mixed**
* $lexeropts **mixed**



### get

    mixed SQL_Parser_Lexer::get()





* Visibility: **public**




### unget

    mixed SQL_Parser_Lexer::unget()





* Visibility: **public**




### skip

    mixed SQL_Parser_Lexer::skip()





* Visibility: **public**




### revert

    mixed SQL_Parser_Lexer::revert()





* Visibility: **public**




### isCompop

    mixed SQL_Parser_Lexer::isCompop($c)





* Visibility: **public**


#### Arguments
* $c **mixed**



### pushBack

    mixed SQL_Parser_Lexer::pushBack()





* Visibility: **public**




### lex

    mixed SQL_Parser_Lexer::lex()





* Visibility: **public**




### nextToken

    mixed SQL_Parser_Lexer::nextToken()





* Visibility: **public**



