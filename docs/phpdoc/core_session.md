Core\Session
===============






* Class name: Session
* Namespace: Core
* This class implements: SessionHandlerInterface




Properties
----------


### $Instance

    public \Core\Session $Instance





* Visibility: **public**
* This property is **static**.


### $Externals

    public array $Externals = array()





* Visibility: **public**
* This property is **static**.


### $_IsReady

    private mixed $_IsReady = false





* Visibility: **private**
* This property is **static**.


Methods
-------


### __construct

    mixed Core\Session::__construct()





* Visibility: **public**




### close

    boolean Core\Session::close()

PHP >= 5.4.0<br/>
Close the session



* Visibility: **public**




### open

    boolean Core\Session::open(string $save_path, string $session_id)

PHP >= 5.4.0<br/>
Initialize session



* Visibility: **public**


#### Arguments
* $save_path **string** - &lt;p&gt;The path where to store/retrieve the session.&lt;/p&gt;
* $session_id **string** - &lt;p&gt;The session id.&lt;/p&gt;



### destroy

    boolean Core\Session::destroy(integer $session_id)

PHP >= 5.4.0<br/>
Destroy a session



* Visibility: **public**


#### Arguments
* $session_id **integer** - &lt;p&gt;The session ID being destroyed.&lt;/p&gt;



### read

    string Core\Session::read(string $session_id)

PHP >= 5.4.0<br/>
Read session data



* Visibility: **public**


#### Arguments
* $session_id **string** - &lt;p&gt;The session id to read data for.&lt;/p&gt;



### write

    boolean Core\Session::write(string $session_id, string $session_data)

PHP >= 5.4.0<br/>
Write session data



* Visibility: **public**


#### Arguments
* $session_id **string** - &lt;p&gt;The session id.&lt;/p&gt;
* $session_data **string** - &lt;p&gt;
The encoded session data. This data is the
result of the PHP internally encoding
the $_SESSION superglobal to a serialized
string and passing it as this parameter.
Please note sessions use an alternative serialization method.
&lt;/p&gt;



### gc

    boolean Core\Session::gc(integer $maxlifetime)

PHP >= 5.4.0<br/>
Cleanup old sessions



* Visibility: **public**


#### Arguments
* $maxlifetime **integer** - &lt;p&gt;
Sessions that have not updated for
the last maxlifetime seconds will be removed.
&lt;/p&gt;



### SetUser

    mixed Core\Session::SetUser(\UserModel $u)

Set the current session to be owned by the given user,
effectively logging the user in.

Drop support for the User class in favour of UserModel after pre-2.8.x is no longer supported.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $u **[UserModel](usermodel.md)**



### DestroySession

    mixed Core\Session::DestroySession()

Shortcut static function to call that will destroy the current session and logout the user.



* Visibility: **public**
* This method is **static**.




### ForceSave

    mixed Core\Session::ForceSave()

Force the saving of the contents of $_SESSION back to the database.



* Visibility: **public**
* This method is **static**.




### CleanupExpired

    boolean Core\Session::CleanupExpired()

Cleanup any expired sessions from the database.



* Visibility: **public**
* This method is **static**.




### Get

    mixed Core\Session::Get(string $key, null|mixed $default)

Get the value of a key from the session or $default if that key is explicitly not set.

If the key "example/*" is provided, then all subkeys under the example array are returned.
If no keys exist, an empty array is returned.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**
* $default **null|mixed**



### Set

    mixed Core\Session::Set(string $key, mixed $value)

Set the value of a key to some value.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**
* $value **mixed**



### UnsetKey

    mixed Core\Session::UnsetKey($key)

Explictly unset a key from the session.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **mixed** - &lt;p&gt;string&lt;/p&gt;



### _GetInstance

    \Core\Session Core\Session::_GetInstance()





* Visibility: **private**
* This method is **static**.




### _GetModel

    \SessionModel Core\Session::_GetModel(string $session_id)

Get the Model for this current session.

This method will NOT cache the results of the model.  This is due to race conditions at some point...

* Visibility: **private**
* This method is **static**.


#### Arguments
* $session_id **string** - &lt;p&gt;The session id to read the model for.&lt;/p&gt;


