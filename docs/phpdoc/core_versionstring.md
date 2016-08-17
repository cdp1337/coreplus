Core\VersionString
===============

A short teaser of what VersionString does.

More lengthy description of what VersionString does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: VersionString
* Namespace: Core
* This class implements: ArrayAccess




Properties
----------


### $major

    public integer $major





* Visibility: **public**


### $minor

    public integer $minor





* Visibility: **public**


### $point

    public integer $point





* Visibility: **public**


### $user

    public string $user





* Visibility: **public**


### $stability

    public string $stability





* Visibility: **public**


### $build

    public string $build





* Visibility: **public**


### $core

    public string $core





* Visibility: **public**


Methods
-------


### __construct

    mixed Core\VersionString::__construct($version)





* Visibility: **public**


#### Arguments
* $version **mixed**



### __toString

    string Core\VersionString::__toString()

Get this version as a string



* Visibility: **public**




### parseString

    array Core\VersionString::parseString(string $version)

Break a version string into the corresponding parts.

Major Version
Minor Version
Point Release
Core Version
Developer-Specific Version
Development Status

Optimized 2013.08.17

* Visibility: **public**


#### Arguments
* $version **string**



### setMajor

    mixed Core\VersionString::setMajor(integer $int)





* Visibility: **public**


#### Arguments
* $int **integer**



### setMinor

    mixed Core\VersionString::setMinor(integer $int)





* Visibility: **public**


#### Arguments
* $int **integer**



### setPoint

    mixed Core\VersionString::setPoint(integer $int)





* Visibility: **public**


#### Arguments
* $int **integer**



### setUser

    mixed Core\VersionString::setUser(string|null $string)





* Visibility: **public**


#### Arguments
* $string **string|null**



### setBuild

    mixed Core\VersionString::setBuild(string|null $string)





* Visibility: **public**


#### Arguments
* $string **string|null**



### setStability

    mixed Core\VersionString::setStability(string|null $type)





* Visibility: **public**


#### Arguments
* $type **string|null**



### compare

    boolean|integer Core\VersionString::compare(string|\Core\VersionString $other, null|string $operation)

Mimic php's version_compare, only with more advanced and accurate version comparisons.

This has the additional support for Debian-style version strings.

* Visibility: **public**


#### Arguments
* $other **string|[string](core_versionstring.md)** - &lt;p&gt;Version to compare against&lt;/p&gt;
* $operation **null|string** - &lt;p&gt;Operation to use or null&lt;/p&gt;



### offsetExists

    boolean Core\VersionString::offsetExists(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Whether a offset exists



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
                     An offset to check for.
                     &lt;/p&gt;



### offsetGet

    mixed Core\VersionString::offsetGet(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to retrieve



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
                     The offset to retrieve.
                     &lt;/p&gt;



### offsetSet

    void Core\VersionString::offsetSet(mixed $offset, mixed $value)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to set



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
                     The offset to assign the value to.
                     &lt;/p&gt;
* $value **mixed** - &lt;p&gt;
                     The value to set.
                     &lt;/p&gt;



### offsetUnset

    void Core\VersionString::offsetUnset(mixed $offset)

(PHP 5 &gt;= 5.0.0)<br/>
Offset to unset



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;
                     The offset to unset.
                     &lt;/p&gt;


