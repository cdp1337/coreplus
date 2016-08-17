PasswordHash
===============

The main hasher class.

The following code illustrates how to use this hashing library.
<code>
    $t_hasher = new PasswordHash(8, FALSE);

    $correct = 'test12345';
    $hash = $t_hasher->hashPassword($correct);

    print 'Hash: ' . $hash . "\n";

    $check = $t_hasher->checkPassword($correct, $hash);
    if ($check) echo "Good!";
</code>


* Class name: PasswordHash
* Namespace: 





Properties
----------


### $itoa64

    private mixed $itoa64





* Visibility: **private**


### $iteration_count_log2

    private mixed $iteration_count_log2





* Visibility: **private**


### $portable_hashes

    private mixed $portable_hashes





* Visibility: **private**


### $random_state

    private mixed $random_state





* Visibility: **private**


Methods
-------


### __construct

    mixed PasswordHash::__construct($iteration_count_log2, $portable_hashes)





* Visibility: **public**


#### Arguments
* $iteration_count_log2 **mixed**
* $portable_hashes **mixed**



### hashPassword

    string PasswordHash::hashPassword(string $password)

Hash a given string.



* Visibility: **public**


#### Arguments
* $password **string**



### checkPassword

    boolean PasswordHash::checkPassword(string $password, string $stored_hash)

Check if a given password string matches the stored hash.



* Visibility: **public**


#### Arguments
* $password **string** - &lt;p&gt;(as plain text)&lt;/p&gt;
* $stored_hash **string** - &lt;p&gt;(as encrypted hash)&lt;/p&gt;



### get_random_bytes

    mixed PasswordHash::get_random_bytes($count)





* Visibility: **private**


#### Arguments
* $count **mixed**



### encode64

    mixed PasswordHash::encode64($input, $count)





* Visibility: **private**


#### Arguments
* $input **mixed**
* $count **mixed**



### gensalt_private

    mixed PasswordHash::gensalt_private($input)





* Visibility: **private**


#### Arguments
* $input **mixed**



### crypt_private

    mixed PasswordHash::crypt_private($password, $setting)





* Visibility: **private**


#### Arguments
* $password **mixed**
* $setting **mixed**



### gensalt_extended

    mixed PasswordHash::gensalt_extended($input)





* Visibility: **private**


#### Arguments
* $input **mixed**



### gensalt_blowfish

    mixed PasswordHash::gensalt_blowfish($input)





* Visibility: **private**


#### Arguments
* $input **mixed**


