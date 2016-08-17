DB
===============

[PAGE DESCRIPTION HERE]




* Class name: DB
* Namespace: 
* This class implements: [ISingleton](isingleton.md)




Properties
----------


### $instance

    private mixed $instance = null





* Visibility: **private**
* This property is **static**.


### $connection

    public \ADOConnection $connection





* Visibility: **public**


### $counter

    public mixed $counter





* Visibility: **public**


Methods
-------


### __construct

    mixed DB::__construct()





* Visibility: **private**




### GetConnection

    mixed DB::GetConnection()





* Visibility: **public**
* This method is **static**.




### GetConn

    mixed DB::GetConn()





* Visibility: **public**
* This method is **static**.




### Singleton

    \ISingleton ISingleton::Singleton()





* Visibility: **public**
* This method is **static**.
* This method is defined by [ISingleton](isingleton.md)




### GetInstance

    mixed DB::GetInstance()





* Visibility: **public**
* This method is **static**.




### Execute

    \ADORecordSet DB::Execute(\sql $sql, \[inputarr] $inputarr)

Execute SQL



* Visibility: **public**
* This method is **static**.


#### Arguments
* $sql **sql** - &lt;p&gt;SQL statement to execute, or possibly an array holding prepared statement ($sql[0] will hold sql text)&lt;/p&gt;
* $inputarr **[inputarr]** - &lt;p&gt;holds the input data to bind to. Null elements will be set to null.&lt;/p&gt;



### qstr

    mixed DB::qstr($string)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $string **mixed**



### Insert_ID

    mixed DB::Insert_ID($table, $column)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $table **mixed**
* $column **mixed**



### Error

    mixed DB::Error()





* Visibility: **public**
* This method is **static**.




### TableExists

    mixed DB::TableExists($tablename)

Basic function to check if a given table exists without causing an error.

Useful for those installation scripts that alter the db schema.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $tablename **mixed**



### CreateSQLFromHash

    \unknown_type DB::CreateSQLFromHash($table, $isNew, $hashTable, $primaryKeys)

Create an SQL string for a given table and hash



* Visibility: **public**
* This method is **static**.


#### Arguments
* $table **mixed** - &lt;p&gt;string&lt;/p&gt;
* $isNew **mixed** - &lt;p&gt;boolean | &#039;auto&#039;&lt;/p&gt;
* $hashTable **mixed**
* $primaryKeys **mixed** - &lt;p&gt;array&lt;/p&gt;


