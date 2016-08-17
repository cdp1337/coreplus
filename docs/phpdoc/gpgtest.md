GPGTest
===============

Created by PhpStorm.

User: charlie
Date: 11/17/15
Time: 3:05 PM


* Class name: GPGTest
* Namespace: 
* Parent class: PHPUnit_Framework_TestCase





Properties
----------


### $fingerprint

    public mixed $fingerprint = '4E7330EB2A84D7479B719FF33F20C906B04EFAD6'





* Visibility: **public**


### $key

    public mixed $key = 'B04EFAD6'





* Visibility: **public**


### $email

    public mixed $email = 'invalid-donotuse@corepl.us'





* Visibility: **public**


### $name

    public mixed $name = 'Core Plus Test Key'





* Visibility: **public**


### $comment

    public mixed $comment = 'Just a test key for phpunit'





* Visibility: **public**


### $bits

    public mixed $bits = 1536





* Visibility: **public**


### $enc

    public mixed $enc = 'RSA'





* Visibility: **public**


### $notGPGFile

    public mixed $notGPGFile = ROOT_PDIR . 'core/tests/ivak_TV_Test_Screen.png'





* Visibility: **public**


### $verifyFile

    public mixed $verifyFile = ROOT_PDIR . 'core/tests/ivak_TV_Test_Screen.png.gpg'





* Visibility: **public**


### $pubkey

    public mixed $pubkey





* Visibility: **public**


### $sigDetached

    public mixed $sigDetached





* Visibility: **public**


Methods
-------


### testSearchRemoteKeys

    mixed GPGTest::testSearchRemoteKeys()





* Visibility: **public**




### testImportShort

    mixed GPGTest::testImportShort()

Test importing the short version of a key ID



* Visibility: **public**




### testImportPublicKey

    mixed GPGTest::testImportPublicKey()

Test importing the full public key text



* Visibility: **public**




### testPrettyFormatFingerprints

    mixed GPGTest::testPrettyFormatFingerprints()





* Visibility: **public**




### testEncryptData

    mixed GPGTest::testEncryptData()

Test the encrypt data method



* Visibility: **public**




### testVerifyFile

    mixed GPGTest::testVerifyFile()

Test the verify file method



* Visibility: **public**




### testVerifyString

    mixed GPGTest::testVerifyString()

Test the verify file method



* Visibility: **public**




### testDeleteKey

    mixed GPGTest::testDeleteKey()

Test the verify file method



* Visibility: **public**



