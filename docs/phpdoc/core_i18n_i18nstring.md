Core\i18n\I18NString
===============






* Class name: I18NString
* Namespace: Core\i18n





Properties
----------


### $_key

    private string $_key





* Visibility: **private**


### $_params

    private array $_params = array()





* Visibility: **private**


### $_lang

    private null $_lang = null





* Visibility: **private**


### $_resultIsFound

    private mixed $_resultIsFound





* Visibility: **private**


### $_resultMatchedLang

    private mixed $_resultMatchedLang





* Visibility: **private**


### $_resultMatchedString

    private mixed $_resultMatchedString





* Visibility: **private**


### $_resultAllResults

    private mixed $_resultAllResults





* Visibility: **private**


Methods
-------


### __construct

    mixed Core\i18n\I18NString::__construct(string $key)

Create a new String translation request based on the given string key



* Visibility: **public**


#### Arguments
* $key **string**



### setParameters

    mixed Core\i18n\I18NString::setParameters($params)

Set the parameters for this translation key



* Visibility: **public**


#### Arguments
* $params **mixed**



### setLanguage

    mixed Core\i18n\I18NString::setLanguage($lang)





* Visibility: **public**


#### Arguments
* $lang **mixed**



### getTranslation

    string Core\i18n\I18NString::getTranslation()

Get the translation for this requested string in the requested language

Will also update the resolved metadata on this object.

* Visibility: **public**



