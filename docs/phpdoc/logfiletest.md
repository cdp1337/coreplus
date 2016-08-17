LogFileTest
===============

Test file to ensure that logs can be written and are correctly written.




* Class name: LogFileTest
* Namespace: 
* Parent class: PHPUnit_Framework_TestCase







Methods
-------


### testWrite

    \Core\Utilities\Logger\LogFile LogFileTest::testWrite()

Test that a message can be written to a log file.



* Visibility: **public**




### testArchive

    mixed LogFileTest::testArchive(\Core\Utilities\Logger\LogFile $log)

Test that archiving a log works.



* Visibility: **public**


#### Arguments
* $log **[Core\Utilities\Logger\LogFile](core_utilities_logger_logfile.md)**



### testDelete

    mixed LogFileTest::testDelete(\Core\Utilities\Logger\LogFile $log)

Remove the test log file left on the filesystem.



* Visibility: **public**


#### Arguments
* $log **[Core\Utilities\Logger\LogFile](core_utilities_logger_logfile.md)**


