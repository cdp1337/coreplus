BlogModel
===============






* Class name: BlogModel
* Namespace: 
* Parent class: Model





Properties
----------


### $Schema

    public mixed $Schema = array('id' => array('type' => \Model::ATT_TYPE_UUID), 'site' => array('type' => \Model::ATT_TYPE_SITE, 'formtype' => 'system'), 'type' => array('type' => \Model::ATT_TYPE_ENUM, 'options' => array('local', 'remote'), 'default' => 'local', 'form' => array('title' => 'Type of Blog', 'description' => 'If this is a remote feed, change to remote here, otherwise local is sufficient.', 'group' => 'Basic')), 'manage_articles_permission' => array('type' => \Model::ATT_TYPE_STRING, 'default' => '!*', 'form' => array('type' => 'access', 'title' => 'Article Management Permission', 'description' => 'Which groups can add, edit, and remove blog articles in this blog.', 'group' => 'Access & Advanced')), 'remote_url' => array('type' => \Model::ATT_TYPE_STRING, 'form' => array('title' => 'Remote URL', 'description' => 'For remote feeds, this must be the URL of the remote RSS or Atom feed.', 'group' => 'Basic')))





* Visibility: **public**
* This property is **static**.


### $Indexes

    public mixed $Indexes = array('primary' => array('id'))





* Visibility: **public**
* This property is **static**.


Methods
-------


### __construct

    mixed BlogModel::__construct($key)





* Visibility: **public**


#### Arguments
* $key **mixed**



### get

    mixed BlogModel::get($k)





* Visibility: **public**


#### Arguments
* $k **mixed**



### importFeed

    array BlogModel::importFeed(boolean $verbose)

Helper utility to import a given remote blog.



* Visibility: **public**


#### Arguments
* $verbose **boolean** - &lt;p&gt;Set to true to enable real-time verbose output of the operation.&lt;/p&gt;


