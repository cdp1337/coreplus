SQL_Parser_Compiler
===============

A SQL parse tree compiler.




* Class name: SQL_Parser_Compiler
* Namespace: 





Properties
----------


### $tree

    public mixed $tree





* Visibility: **public**


Methods
-------


### SQL_Parser_Compiler

    mixed SQL_Parser_Compiler::SQL_Parser_Compiler($array)





* Visibility: **public**


#### Arguments
* $array **mixed**



### getWhereValue

    mixed SQL_Parser_Compiler::getWhereValue($arg)





* Visibility: **public**


#### Arguments
* $arg **mixed**



### getParams

    mixed SQL_Parser_Compiler::getParams($arg)





* Visibility: **public**


#### Arguments
* $arg **mixed**



### compileFunctionOpts

    mixed SQL_Parser_Compiler::compileFunctionOpts($arg)





* Visibility: **public**


#### Arguments
* $arg **mixed**



### compileSearchClause

    mixed SQL_Parser_Compiler::compileSearchClause($where_clause)





* Visibility: **public**


#### Arguments
* $where_clause **mixed**



### compileSelect

    mixed SQL_Parser_Compiler::compileSelect()





* Visibility: **public**




### compileUpdate

    mixed SQL_Parser_Compiler::compileUpdate()





* Visibility: **public**




### compileDelete

    mixed SQL_Parser_Compiler::compileDelete()





* Visibility: **public**




### compileInsert

    mixed SQL_Parser_Compiler::compileInsert()





* Visibility: **public**




### compile

    mixed SQL_Parser_Compiler::compile($array)





* Visibility: **public**


#### Arguments
* $array **mixed**


