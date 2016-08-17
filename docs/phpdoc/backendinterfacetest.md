BackendInterfaceTest
===============

This will test the currently selected BackendInterface in the system.




* Class name: BackendInterfaceTest
* Namespace: 
* Parent class: PHPUnit_Framework_TestCase





Properties
----------


### $DMI

    public \Core\Datamodel\BackendInterface $DMI





* Visibility: **public**
* This property is **static**.


### $DMIName

    public mixed $DMIName





* Visibility: **public**
* This property is **static**.


### $Schema

    public mixed $Schema





* Visibility: **public**
* This property is **static**.


Methods
-------


### testCreateTable

    mixed BackendInterfaceTest::testCreateTable()





* Visibility: **public**




### testModifyTable

    mixed BackendInterfaceTest::testModifyTable()





* Visibility: **public**




### testExecute

    mixed BackendInterfaceTest::testExecute()

Test that the Execute method properly handles a generic Dataset object.



* Visibility: **public**




### testTableExists

    mixed BackendInterfaceTest::testTableExists()

Ensure that tableExists returns true for a table that exists.



* Visibility: **public**




### testTableExistsNot

    mixed BackendInterfaceTest::testTableExistsNot()

The inverse of tableExists true.  If a table does not exist, it needs to indicate such!



* Visibility: **public**




### testShowTables

    mixed BackendInterfaceTest::testShowTables()

Test the showTables method of the interface



* Visibility: **public**




### testDropTable

    mixed BackendInterfaceTest::testDropTable()





* Visibility: **public**




### testReadCount

    mixed BackendInterfaceTest::testReadCount()

By this stage, there should be some read count



* Visibility: **public**




### testWriteCount

    mixed BackendInterfaceTest::testWriteCount()

By this stage, there should be some write count



* Visibility: **public**




### testQueryLog

    mixed BackendInterfaceTest::testQueryLog()





* Visibility: **public**




### setUpBeforeClass

    mixed BackendInterfaceTest::setUpBeforeClass()

Ensure that a valid DMI object can be created in the system.



* Visibility: **public**
* This method is **static**.



