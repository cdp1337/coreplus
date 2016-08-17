Core\GPG\UID
===============

A short teaser of what KeyUID does.

More lengthy description of what KeyUID does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: UID
* Namespace: Core\GPG
* Parent class: [Core\GPG\Key](core_gpg_key.md)



Constants
----------


### ENCRYPTION_TYPE_RSA

    const ENCRYPTION_TYPE_RSA = 'RSA'





### ENCRYPTION_TYPE_DSA

    const ENCRYPTION_TYPE_DSA = 'DSA'





### ENCRYPTION_TYPE_ELGAMAL

    const ENCRYPTION_TYPE_ELGAMAL = 'Elgamal'





Properties
----------


### $fullname

    public string $fullname





* Visibility: **public**


### $comment

    public string $comment





* Visibility: **public**


### $email

    public string $email





* Visibility: **public**


### $sigs

    public array $sigs = array()





* Visibility: **public**


### $validity

    public string $validity

o = Unknown (this key is new to the system)
i = The key is invalid (e.g. due to a missing self-signature)
d = The key has been disabled (deprecated - use the 'D' in field 12 instead)
r = The key has been revoked
e = The key has expired
- = Unknown validity (i.e. no value assigned)
q = Undefined validity
'-' and 'q' may safely be treated as the same value for most purposes
n = The key is valid
m = The key is marginal valid.

f = The key is fully valid
u = The key is ultimately valid.  This often means that the secret key is available, but any key may be marked as ultimately valid.

* Visibility: **public**


### $encryptionBits

    public integer $encryptionBits





* Visibility: **public**


### $encryptionType

    public string $encryptionType





* Visibility: **public**


### $id

    public string $id





* Visibility: **public**


### $id_short

    public string $id_short





* Visibility: **public**


### $serial

    public string $serial





* Visibility: **public**


### $fingerprint

    public string $fingerprint





* Visibility: **public**


### $created

    public integer $created





* Visibility: **public**


### $expires

    public integer $expires





* Visibility: **public**


Methods
-------


### _parseSig

    mixed Core\GPG\UID::_parseSig($parts)





* Visibility: **public**


#### Arguments
* $parts **mixed**



### getTrustLevel

    mixed Core\GPG\UID::getTrustLevel()





* Visibility: **public**




### isValid

    boolean Core\GPG\Key::isValid()

Check and see if this key is currently valid and not expired nor revoked.



* Visibility: **public**
* This method is defined by [Core\GPG\Key](core_gpg_key.md)



