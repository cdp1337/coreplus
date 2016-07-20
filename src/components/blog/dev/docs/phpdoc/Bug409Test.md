Bug409Test
===============

Class Bug409Test

Ensure that RSS Feeds validate with W3C.


* Class name: Bug409Test
* Namespace: 
* Parent class: PHPUnit_Framework_TestCase





Properties
----------


### $blog

    protected \BlogModel $blog





* Visibility: **protected**


### $article

    protected \BlogArticleModel $article





* Visibility: **protected**


Methods
-------


### setUp

    mixed Bug409Test::setUp()





* Visibility: **protected**




### testRSSPage

    mixed Bug409Test::testRSSPage()

Test that I can load the RSS page and that it returns valid XML.

The XMLLoader will take care of the validation, since it should be a valid document anyway.

* Visibility: **public**




### testATOMPage

    mixed Bug409Test::testATOMPage()

Test that I can load the ATOM page and that it returns valid XML.

The XMLLoader will take care of the validation, since it should be a valid document anyway.

* Visibility: **public**




### tearDown

    mixed Bug409Test::tearDown()





* Visibility: **protected**



