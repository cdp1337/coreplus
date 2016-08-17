Core\GPG\PrimaryKey
===============

A short teaser of what PrimaryKey does.

More lengthy description of what PrimaryKey does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: PrimaryKey
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


### $uids

    public array $uids = array()





* Visibility: **public**


### $subkeys

    public array $subkeys = array()





* Visibility: **public**


### $_photos

    private null $_photos = null





* Visibility: **private**


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


### isValid

    boolean Core\GPG\Key::isValid()

Check and see if this key is currently valid and not expired nor revoked.



* Visibility: **public**
* This method is defined by [Core\GPG\Key](core_gpg_key.md)




### getUID

    \Core\GPG\UID|null Core\GPG\PrimaryKey::getUID($email)





* Visibility: **public**


#### Arguments
* $email **mixed**



### getName

    string Core\GPG\PrimaryKey::getName()

Get the name of the first valid UID on this primary key.



* Visibility: **public**




### getEmail

    string Core\GPG\PrimaryKey::getEmail()

Get the email of the first valid UID on this primary key.



* Visibility: **public**




### getPhotos

    array Core\GPG\PrimaryKey::getPhotos()

Get an array containing all the photos for this public key



* Visibility: **public**




### getPhoto

    \Core\Filestore\File|null Core\GPG\PrimaryKey::getPhoto()

Get the FIRST photo in this key



* Visibility: **public**




### getAscii

    string Core\GPG\PrimaryKey::getAscii()

Get the ASCII representation of this key



* Visibility: **public**




### parseLine

    mixed Core\GPG\PrimaryKey::parseLine(string $line)

Parse a line from --with-colons output.

<pre>
pub:f:1024:17:6C7EE1B8621CC013:899817715:1055898235::m:::scESC:
fpr:::::::::ECAF7590EB3443B5C7CF3ACB6C7EE1B8621CC013:
uid:f::::::::Werner Koch <wk@g10code.com>:
uid:f::::::::Werner Koch <wk@gnupg.org>:
sub:f:1536:16:06AD222CADF6A6E1:919537416:1036177416:::::e:
fpr:::::::::CF8BCC4B18DE08FCD8A1615906AD222CADF6A6E1:
sub:r:1536:20:5CE086B5B5A18FF4:899817788:1025961788:::::esc:
fpr:::::::::AB059359A3B81F410FCFF97F5CE086B5B5A18FF4:

The double --with-fingerprint prints the fingerprint for the subkeys
too. --fixed-list-mode is the modern listing way printing dates in
seconds since Epoch and does not merge the first userID with the pub
record; gpg2 does this by default and the option is a dummy.


1. Field:  Type of record
pub = public key
crt = X.509 certificate
crs = X.509 certificate and private key available
sub = subkey (secondary key)
sec = secret key
ssb = secret subkey (secondary key)
uid = user id (only field 10 is used).
uat = user attribute (same as user id except for field 10).
sig = signature
rev = revocation signature
fpr = fingerprint: (fingerprint is in field 10)
pkd = public key data (special field format, see below)
grp = keygrip
rvk = revocation key
tru = trust database information
spk = signature subpacket

2. Field:  A letter describing the calculated validity. This is a single
letter, but be prepared that additional information may follow
in some future versions. (not used for secret keys)
o = Unknown (this key is new to the system)
i = The key is invalid (e.g. due to a missing self-signature)
d = The key has been disabled
(deprecated - use the 'D' in field 12 instead)
r = The key has been revoked
e = The key has expired
- = Unknown validity (i.e. no value assigned)
q = Undefined validity
'-' and 'q' may safely be treated as the same
value for most purposes
n = The key is valid
m = The key is marginal valid.
f = The key is fully valid
u = The key is ultimately valid.  This often means
that the secret key is available, but any key may
be marked as ultimately valid.

If the validity information is given for a UID or UAT
record, it describes the validity calculated based on this
user ID.  If given for a key record it describes the best
validity taken from the best rated user ID.

For X.509 certificates a 'u' is used for a trusted root
certificate (i.e. for the trust anchor) and an 'f' for all
other valid certificates.

3. Field:  length of key in bits.

4. Field:  Algorithm:	1 = RSA
16 = Elgamal (encrypt only)
17 = DSA (sometimes called DH, sign only)
20 = Elgamal (sign and encrypt - don't use them!)
(for other id's see include/cipher.h)

5. Field:  KeyID

6. Field:  Creation Date (in UTC).  For UID and UAT records, this is
the self-signature date.  Note that the date is usally
printed in seconds since epoch, however, we are migrating
to an ISO 8601 format (e.g. "19660205T091500").  This is
currently only relevant for X.509.  A simple way to detect
the new format is to scan for the 'T'.

7. Field:  Key or user ID/user attribute expiration date or empty if none.

8. Field:  Used for serial number in crt records (used to be the Local-ID).
For UID and UAT records, this is a hash of the user ID contents
used to represent that exact user ID.  For trust signatures,
this is the trust depth seperated by the trust value by a
space.

9. Field:  Ownertrust (primary public keys only)
This is a single letter, but be prepared that additional
information may follow in some future versions.  For trust
signatures with a regular expression, this is the regular
expression value, quoted as in field 10.

10. Field:  User-ID.  The value is quoted like a C string to avoid
control characters (the colon is quoted "\x3a").
For a "pub" record this field is not used on --fixed-list-mode.
A UAT record puts the attribute subpacket count here, a
space, and then the total attribute subpacket size.
In gpgsm the issuer name comes here
An FPR record stores the fingerprint here.
The fingerprint of an revocation key is stored here.

11. Field:  Signature class as per RFC-4880.  This is a 2 digit
hexnumber followed by either the letter 'x' for an
exportable signature or the letter 'l' for a local-only
signature.  The class byte of an revocation key is also
given here, 'x' and 'l' is used the same way.  IT is not
used for X.509.

12. Field:  Key capabilities:
e = encrypt
s = sign
c = certify
a = authentication
A key may have any combination of them in any order.  In
addition to these letters, the primary key has uppercase
versions of the letters to denote the _usable_
capabilities of the entire key, and a potential letter 'D'
to indicate a disabled key.

13. Field:  Used in FPR records for S/MIME keys to store the
fingerprint of the issuer certificate.  This is useful to
build the certificate path based on certificates stored in
the local keyDB; it is only filled if the issuer
certificate is available. The root has been reached if
this is the same string as the fingerprint. The advantage
of using this value is that it is guaranteed to have been
been build by the same lookup algorithm as gpgsm uses.
For "uid" records this lists the preferences in the same
way the gpg's --edit-key menu does.
For "sig" records, this is the fingerprint of the key that
issued the signature.  Note that this is only filled in if
the signature verified correctly.  Note also that for
various technical reasons, this fingerprint is only
available if --no-sig-cache is used.

14. Field   Flag field used in the --edit menu output:

15. Field   Used in sec/sbb to print the serial number of a token
(internal protect mode 1002) or a '#' if that key is a
simple stub (internal protect mode 1001)
16. Field:  For sig records, this is the used hash algorithm:
2 = SHA-1
8 = SHA-256
(for other id's see include/cipher.h)

All dates are displayed in the format yyyy-mm-dd unless you use the
option --fixed-list-mode in which case they are displayed as seconds
since Epoch.  More fields may be added later, so parsers should be
prepared for this. When parsing a number the parser should stop at the
first non-number character so that additional information can later be
added.

If field 1 has the tag "pkd", a listing looks like this:
pkd:0:1024:B665B1435F4C2 .... FF26ABB:
!  !   !-- the value
!  !------ for information number of bits in the value
!--------- index (eg. DSA goes from 0 to 3: p,q,g,y)
</pre>

* Visibility: **public**


#### Arguments
* $line **string**



### _ParseKeyLine13

    mixed Core\GPG\PrimaryKey::_ParseKeyLine13($parts, \Core\GPG\Key $key)





* Visibility: **protected**
* This method is **static**.


#### Arguments
* $parts **mixed**
* $key **[Core\GPG\Key](core_gpg_key.md)**



### _ParseKeyLine11

    mixed Core\GPG\PrimaryKey::_ParseKeyLine11($parts, \Core\GPG\Key $key)





* Visibility: **protected**
* This method is **static**.


#### Arguments
* $parts **mixed**
* $key **[Core\GPG\Key](core_gpg_key.md)**



### _ParseKeyLine7

    mixed Core\GPG\PrimaryKey::_ParseKeyLine7($parts, \Core\GPG\Key $key)

Parse a colon-delimited string for a 7-part data field, (usually from remote sources).



* Visibility: **protected**
* This method is **static**.


#### Arguments
* $parts **mixed**
* $key **[Core\GPG\Key](core_gpg_key.md)**



### _ParseSubUIDLine11

    mixed Core\GPG\PrimaryKey::_ParseSubUIDLine11($parts, \Core\GPG\UID $uid)





* Visibility: **protected**
* This method is **static**.


#### Arguments
* $parts **mixed**
* $uid **[Core\GPG\UID](core_gpg_uid.md)**



### _ParseSubUIDLine5

    mixed Core\GPG\PrimaryKey::_ParseSubUIDLine5($parts, \Core\GPG\UID $uid)





* Visibility: **protected**
* This method is **static**.


#### Arguments
* $parts **mixed**
* $uid **[Core\GPG\UID](core_gpg_uid.md)**


