ViewMeta_title
===============

Just a tiny class for handling control links in the main page view.

These are usually tiny icons or snippets of text that provide a bit of inline administrative
functionality for pages.


* Class name: ViewMeta_title
* Namespace: 
* Parent class: [ViewMeta](viewmeta.md)



Constants
----------


### BASE_META

    const BASE_META = 'meta'





### BASE_LINK

    const BASE_LINK = 'link'





Properties
----------


### $base

    public string $base = \ViewMeta::BASE_META

The base type of this view tag, change as necessary in your tag.



* Visibility: **public**


### $href

    public string $href = ''

Links support href attributes.



* Visibility: **public**


### $property

    public string $property = ''

This is the property attribute for meta tags and the rel attribute for link tags.



* Visibility: **public**


### $contentkey

    public string $contentkey = ''

Some content has a key to link the data, ie: the user and tags.  That value is here.

This is directly mapped to the meta_value attribute

* Visibility: **public**


### $content

    public string $content = ''

The content or value, usually for meta tags.

If single, This is directly mapped to the meta_value_title attribute
If multiple values are present, this is a key/value paired array of keys and their titles.

* Visibility: **public**


### $otherattributes

    public array $otherattributes = array()

Any other attributes for the a tag.



* Visibility: **public**


### $parent

    public \ViewMetas $parent

The ViewMetas parent object, may be useful for some elements to be able to cross reference elements.



* Visibility: **public**


### $multiple

    public boolean $multiple = false

Set to true to allow this meta attribute to have multiple values.



* Visibility: **public**


Methods
-------


### __toString

    string ViewMeta::__toString()

Get this ViewMeta as a flat string.



* Visibility: **public**
* This method is defined by [ViewMeta](viewmeta.md)




### fetch

    array ViewMeta::fetch()

Get this control as HTML



* Visibility: **public**
* This method is defined by [ViewMeta](viewmeta.md)




### _fetchMeta

    string ViewMeta::_fetchMeta()

Internal function to render <meta/> tags.



* Visibility: **private**
* This method is defined by [ViewMeta](viewmeta.md)




### _fetchLink

    string ViewMeta::_fetchLink()

Internal function to render <link/> tags



* Visibility: **private**
* This method is defined by [ViewMeta](viewmeta.md)




### Factory

    \ViewMeta ViewMeta::Factory($property)

Create a new property



* Visibility: **public**
* This method is **static**.
* This method is defined by [ViewMeta](viewmeta.md)


#### Arguments
* $property **mixed**


