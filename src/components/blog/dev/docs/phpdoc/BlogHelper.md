BlogHelper
===============

Created by JetBrains PhpStorm.

User: powellc
Date: 7/29/12
Time: 10:13 PM
To change this template use File | Settings | File Templates.


* Class name: BlogHelper
* Namespace: 
* This is an **abstract** class







Methods
-------


### GetArticleForm

    \Form BlogHelper::GetArticleForm(\BlogArticleModel $article)

Get the form for article creation and updating.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $article **[BlogArticleModel](BlogArticleModel.md)**



### BlogFormHandler

    string BlogHelper::BlogFormHandler(\Form $form)

Helper function to save blog pages, both new and existing.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **Form**



### BlogArticleFormHandler

    string BlogHelper::BlogArticleFormHandler(\Form $form)

Helper function to save a blog article, both new and existing.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **Form**



### BlogIndexFormHandler

    boolean|mixed|null BlogHelper::BlogIndexFormHandler(\Form $form)

Save handler for the index edit form.

This form just manages the page data for the /blog listing.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **Form**



### CronRetrieveRemoteFeeds

    mixed BlogHelper::CronRetrieveRemoteFeeds()

Helper method to be called on cron events to pull in the latest feeds for all the remote articles.



* Visibility: **public**
* This method is **static**.



