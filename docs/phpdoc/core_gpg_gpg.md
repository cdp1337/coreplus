Core\GPG\GPG
===============

A short teaser of what GPG does.

More lengthy description of what GPG does and why it's fantastic.

<h3>Usage Examples</h3>

<h4>Example 1</h4>
<p>Description 1</p>
<code>
// Some code for example 1
$a = $b;
</code>


<h4>Example 2</h4>
<p>Description 2</p>
<code>
// Some code for example 2
$b = $a;
</code>

### Compatibility with GnuPG

gnupg_​adddecryptkey

gnupg_​addencryptkey
:    // Incorporated into encryptData and encryptFile.

gnupg_​addsignkey

gnupg_​cleardecryptkeys

gnupg_​clearencryptkeys

gnupg_​clearsignkeys

gnupg_​decrypt

gnupg_​decryptverify

gnupg_​encrypt
:    $gpg = new Core\GPG\GPG();
     $gpg->encryptData($plaintext, $fingerprint);
     // OR
     $gpg->encryptData($plaintext, [$fingerprint1, $fingerprint2]);

gnupg_​encryptsign

gnupg_​export

gnupg_​geterror

gnupg_​getprotocol

gnupg_​import
:    $gpg = new Core\GPG\GPG();
     $gpg->importKey($keydata);

gnupg_​init
:    new Core\GPG\GPG();

gnupg_​keyinfo
gnupg_​setarmor
gnupg_​seterrormode
gnupg_​setsignmode
gnupg_​sign
gnupg_​verify

0 => &
object(ReflectionMethod)[992]
public 'name' => string 'keyinfo' (length=7)
public 'class' => string 'gnupg' (length=5)
1 => &
object(ReflectionMethod)[1009]
public 'name' => string 'verify' (length=6)
public 'class' => string 'gnupg' (length=5)
2 => &
object(ReflectionMethod)[991]
public 'name' => string 'geterror' (length=8)
public 'class' => string 'gnupg' (length=5)
3 => &
object(ReflectionMethod)[990]
public 'name' => string 'clearsignkeys' (length=13)
public 'class' => string 'gnupg' (length=5)
4 => &
object(ReflectionMethod)[989]
public 'name' => string 'clearencryptkeys' (length=16)
public 'class' => string 'gnupg' (length=5)
5 => &
object(ReflectionMethod)[988]
public 'name' => string 'cleardecryptkeys' (length=16)
public 'class' => string 'gnupg' (length=5)
6 => &
object(ReflectionMethod)[987]
public 'name' => string 'setarmor' (length=8)
public 'class' => string 'gnupg' (length=5)
7 => &
object(ReflectionMethod)[986]
public 'name' => string 'encrypt' (length=7)
public 'class' => string 'gnupg' (length=5)
8 => &
object(ReflectionMethod)[985]
public 'name' => string 'decrypt' (length=7)
public 'class' => string 'gnupg' (length=5)
9 => &
object(ReflectionMethod)[984]
public 'name' => string 'export' (length=6)
public 'class' => string 'gnupg' (length=5)
10 => &
object(ReflectionMethod)[983]
public 'name' => string 'import' (length=6)
public 'class' => string 'gnupg' (length=5)
11 => &
object(ReflectionMethod)[982]
public 'name' => string 'getprotocol' (length=11)
public 'class' => string 'gnupg' (length=5)
12 => &
object(ReflectionMethod)[981]
public 'name' => string 'setsignmode' (length=11)
public 'class' => string 'gnupg' (length=5)
13 => &
object(ReflectionMethod)[980]
public 'name' => string 'sign' (length=4)
public 'class' => string 'gnupg' (length=5)
14 => &
object(ReflectionMethod)[979]
public 'name' => string 'encryptsign' (length=11)
public 'class' => string 'gnupg' (length=5)
15 => &
object(ReflectionMethod)[978]
public 'name' => string 'decryptverify' (length=13)
public 'class' => string 'gnupg' (length=5)
16 => &
object(ReflectionMethod)[977]
public 'name' => string 'addsignkey' (length=10)
public 'class' => string 'gnupg' (length=5)
17 => &
object(ReflectionMethod)[976]
public 'name' => string 'addencryptkey' (length=13)
public 'class' => string 'gnupg' (length=5)
18 => &
object(ReflectionMethod)[975]
public 'name' => string 'adddecryptkey' (length=13)
public 'class' => string 'gnupg' (length=5)
19 => &
object(ReflectionMethod)[974]
public 'name' => string 'deletekey' (length=9)
public 'class' => string 'gnupg' (length=5)
20 => &
object(ReflectionMethod)[973]
public 'name' => string 'gettrustlist' (length=12)
public 'class' => string 'gnupg' (length=5)
21 => &
object(ReflectionMethod)[972]
public 'name' => string 'listsignatures' (length=14)
public 'class' => string 'gnupg' (length=5)
22 => &
object(ReflectionMethod)[971]
public 'name' => string 'seterrormode' (length=12)
public 'class' => string 'gnupg' (length=5)


* Class name: GPG
* Namespace: Core\GPG





Properties
----------


### $keyserver

    public string $keyserver = 'hkp://pool.sks-keyservers.net'





* Visibility: **public**


### $keyserverOptions

    public mixed $keyserverOptions = array('timeout' => 6)





* Visibility: **public**


### $homedir

    public string $homedir





* Visibility: **public**


### $ignorePermissionWarnings

    public boolean $ignorePermissionWarnings = true





* Visibility: **public**


### $_executable

    private string $_executable





* Visibility: **private**


### $_localKeys

    private array $_localKeys





* Visibility: **private**


### $_secretKeys

    private array $_secretKeys





* Visibility: **private**


### $_gnupg

    private null $_gnupg = null





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\GPG\GPG::__construct()





* Visibility: **public**




### getKey

    \Core\GPG\PrimaryKey|null Core\GPG\GPG::getKey($key)

Retrieve a key from the local store.



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string Key ID to retrieve&lt;/p&gt;



### getSecretKey

    \Core\GPG\PrimaryKey|null Core\GPG\GPG::getSecretKey($key)

Retrieve a secret key from the local store.



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string Key ID to retrieve&lt;/p&gt;



### listKeys

    array Core\GPG\GPG::listKeys()

List the local keys installed on the system



* Visibility: **public**




### listSecretKeys

    array Core\GPG\GPG::listSecretKeys()

List the local SECRET keys installed on the system



* Visibility: **public**




### searchRemoteKeys

    array Core\GPG\GPG::searchRemoteKeys($query)

Search for a remote key, usually by email.

This method has no gnupg equivalent!

* Visibility: **public**


#### Arguments
* $query **mixed** - &lt;p&gt;string String to look for on the remote server&lt;/p&gt;



### importKey

    \Core\GPG\PrimaryKey Core\GPG\GPG::importKey(string $key)

Import a key from a remote keyserver



* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;ID, Fingerprint, or full key data of the key to import&lt;/p&gt;



### examineKey

    \Core\GPG\PrimaryKey|array Core\GPG\GPG::examineKey(string $key)

Examine a key WITHOUT IMPORTING IT

If multiple keys are provided, then an array of keys will be returned, otherwise a single key is returned.

* Visibility: **public**


#### Arguments
* $key **string** - &lt;p&gt;Full key data of the key to examine&lt;/p&gt;



### deleteKey

    boolean Core\GPG\GPG::deleteKey(string $fingerprint)

Delete a key from the keyring.

You must specify the key by its full fingerprint to prevent accidental deletion of multiple keys.

* Visibility: **public**


#### Arguments
* $fingerprint **string** - &lt;p&gt;The fingerprint to delete&lt;/p&gt;



### deleteSecretKey

    boolean Core\GPG\GPG::deleteSecretKey(string $fingerprint)

Delete a secret (private) key from the keyring.

You must specify the key by its full fingerprint to prevent accidental deletion of multiple keys.

* Visibility: **public**


#### Arguments
* $fingerprint **string** - &lt;p&gt;The fingerprint to delete&lt;/p&gt;



### encryptData

    string Core\GPG\GPG::encryptData(string|mixed $data, string|array $recipients)

Encrypt a piece of information as either a string or binary data for one or more recipients



* Visibility: **public**


#### Arguments
* $data **string|mixed**
* $recipients **string|array**



### decryptData

    mixed Core\GPG\GPG::decryptData(string $data)

Decrypt a piece of information to the original source.

This is useful for both encrypted AND signed content.

* Visibility: **public**


#### Arguments
* $data **string**



### signData

    string Core\GPG\GPG::signData(string $data, string|\Core\GPG\PrimaryKey $signingKey)

Sign a piece of information with the given key and return the ASCII armoured text.



* Visibility: **public**


#### Arguments
* $data **string**
* $signingKey **string|[string](core_gpg_primarykey.md)**



### verifySignedData

    \Core\GPG\Signature Core\GPG\GPG::verifySignedData(string $data)

Verify an attached signature of a given source.

Will return just the Signature object.

* Visibility: **public**


#### Arguments
* $data **string**



### verifyFileSignature

    \Core\GPG\Signature Core\GPG\GPG::verifyFileSignature(string|\Core\Filestore\File $file, string|\Core\Filestore\File $verifyFile)

Verify the signature on a given file

If only one argument is provided, it is expected that file contains both the file and signature as an attached sig.

If two arguments are provided, the detached signature is the first argument and the content to verify is the second.

* Visibility: **public**


#### Arguments
* $file **string|[string](core_filestore_file.md)** - &lt;p&gt;Filename or File object of the file to verify&lt;/p&gt;
* $verifyFile **string|[string](core_filestore_file.md)** - &lt;p&gt;Filename or File object of any detached signature&lt;/p&gt;



### verifyDataSignature

    \Core\GPG\Signature Core\GPG\GPG::verifyDataSignature(string $signature, string $content)

Verify that some given data has a valid signature.

Calls verifyFileSignature internally!

* Visibility: **public**


#### Arguments
* $signature **string**
* $content **string**



### generateKey

    \Core\GPG\PrimaryKey|null Core\GPG\GPG::generateKey(string $name, string $email, string $comment, string $keyType, integer $keyLength, string $expires)

Generate a public+secret key on this server, useful for installation and licensing.



* Visibility: **public**


#### Arguments
* $name **string** - &lt;p&gt;The full name for this key&lt;/p&gt;
* $email **string** - &lt;p&gt;The email for this key&lt;/p&gt;
* $comment **string** - &lt;p&gt;An optional comment for this key&lt;/p&gt;
* $keyType **string** - &lt;p&gt;Either &#039;DSA&#039;, &#039;RSA&#039;, or any of the other supported algorithms&lt;/p&gt;
* $keyLength **integer** - &lt;p&gt;The key length, DSA should be limited to 3072 and RSA limited to 4096&lt;/p&gt;
* $expires **string** - &lt;p&gt;Expiration date of this key
Valid values are &quot;0&quot; for no expiration, a number followed by the letter  d (for  days),
w (for weeks), m (for months), or y (for years)
(for example &quot;2m&quot; for two months, or &quot;5y&quot; for five years),
or an absolute date in the form YYYY-MM-DD.&lt;/p&gt;



### _exec

    array Core\GPG\GPG::_exec(string $arguments, null|mixed $inputData)





* Visibility: **public**


#### Arguments
* $arguments **string** - &lt;p&gt;The command line arguments to run on the gpg binary&lt;/p&gt;
* $inputData **null|mixed** - &lt;p&gt;Any data to send over STDIN&lt;/p&gt;



### _execRemote

    array Core\GPG\GPG::_execRemote(string $arguments)

Execute gpg against a remote server, (adds the necessary options)



* Visibility: **private**


#### Arguments
* $arguments **string**



### _parseOutputLines

    array Core\GPG\GPG::_parseOutputLines(array $output)

Internal function to parse output lines, (usually from gpg --recv-keys or gpg --list-keys),
into an array of valid Key and SecretKey objects.



* Visibility: **private**


#### Arguments
* $output **array**



### ParseAuthorString

    mixed Core\GPG\GPG::ParseAuthorString($string)

Parse an author string, (such as "Name Last (comment about this key) <email@domain.tld>")



* Visibility: **public**
* This method is **static**.


#### Arguments
* $string **mixed**



### FormatFingerprint

    string Core\GPG\GPG::FormatFingerprint(string $fingerprint, boolean $html, boolean $oneLine)

Format a given fingerprint in pretty format.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $fingerprint **string** - &lt;p&gt;The raw fingerprint&lt;/p&gt;
* $html **boolean** - &lt;p&gt;Set to true to return HTML elements instead of plain text.&lt;/p&gt;
* $oneLine **boolean** - &lt;p&gt;Set to true to return only 1 lines instead of 2.&lt;/p&gt;


