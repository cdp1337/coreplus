BlogArticleModel
===============

Created by JetBrains PhpStorm.

User: powellc
Date: 7/29/12
Time: 9:32 PM
To change this template use File | Settings | File Templates.


* Class name: BlogArticleModel
* Namespace: 
* Parent class: Model





Properties
----------


### $Schema

    public mixed $Schema = array('id' => array('type' => \Model::ATT_TYPE_UUID), 'blogid' => array('type' => \Model::ATT_TYPE_UUID_FK, 'form' => array('type' => 'system')), 'authorid' => array('type' => \Model::ATT_TYPE_UUID_FK, 'form' => array('type' => 'system')), 'guid' => array('type' => \Model::ATT_TYPE_STRING, 'maxlength' => '128', 'comment' => 'External feeds have a GUID attached to this article.', 'formtype' => 'disabled'), 'link' => array('type' => \Model::ATT_TYPE_STRING, 'maxlength' => '256', 'comment' => 'External feeds have a link back to the original article.', 'formtype' => 'disabled'), 'title' => array('type' => \Model::ATT_TYPE_STRING, 'required' => true, 'maxlength' => '256', 'comment' => 'This is cached from the Page title.'), 'image' => array('type' => \Model::ATT_TYPE_STRING, 'required' => false, 'form' => array('type' => 'file', 'accept' => 'image/*', 'basedir' => 'public/blog', 'description' => 'An optional image to showcase for this article.', 'group' => 'Basic', 'browsable' => true)), 'body' => array('type' => \Model::ATT_TYPE_TEXT, 'required' => true, 'form' => array('type' => 'wysiwyg', 'description' => 'The main body of this blog article.', 'group' => 'Basic')), 'fb_account_id' => array('type' => \Model::ATT_TYPE_STRING, 'formtype' => 'hidden'), 'fb_post_id' => array('type' => \Model::ATT_TYPE_STRING, 'formtype' => 'hidden'), 'created' => array('type' => \Model::ATT_TYPE_CREATED, 'null' => false), 'updated' => array('type' => \Model::ATT_TYPE_UPDATED, 'null' => false))





* Visibility: **public**
* This property is **static**.


### $Indexes

    public mixed $Indexes = array('primary' => array('id'))





* Visibility: **public**
* This property is **static**.


### $HasSearch

    public mixed $HasSearch = true





* Visibility: **public**
* This property is **static**.


Methods
-------


### __construct

    mixed BlogArticleModel::__construct($key)





* Visibility: **public**


#### Arguments
* $key **mixed**



### get

    mixed BlogArticleModel::get($k)





* Visibility: **public**


#### Arguments
* $k **mixed**



### set

    mixed BlogArticleModel::set($k, $v)





* Visibility: **public**


#### Arguments
* $k **mixed**
* $v **mixed**



### getTeaser

    mixed BlogArticleModel::getTeaser()

Get a teaser or snippet of this article.

This will return at most 500 characters of the body or the description.

* Visibility: **public**




### getImage

    \Core\Filestore\File|null BlogArticleModel::getImage()

Get the image object or null



* Visibility: **public**




### getAuthor

    null|\User_Backend BlogArticleModel::getAuthor()

Get the author of this article



* Visibility: **public**




### getResolvedLink

    string BlogArticleModel::getResolvedLink()

Get the resolved link for this blog article.  Will be remote if it's a remote article.



* Visibility: **public**




### isPublished

    boolean BlogArticleModel::isPublished()

Get if this article is published AND not set to a future published date.



* Visibility: **public**



