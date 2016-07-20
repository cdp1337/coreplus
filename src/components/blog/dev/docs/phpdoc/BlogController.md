BlogController
===============

Created by JetBrains PhpStorm.

User: powellc
Date: 7/29/12
Time: 9:52 PM
To change this template use File | Settings | File Templates.


* Class name: BlogController
* Namespace: 
* Parent class: Controller_2_1







Methods
-------


### index

    mixed BlogController::index()

The frontend listing page that displays all blog articles that are published across the system.



* Visibility: **public**




### editindex

    mixed BlogController::editindex()

Edit the index listing page.



* Visibility: **public**




### view

    mixed BlogController::view()

This is the main function responsible for displaying nearly all public content.

This is because the entries will be sub URLs of this one, thus preserving URL structures.

* Visibility: **public**




### create

    mixed BlogController::create()

Create a new blog page



* Visibility: **public**




### update

    mixed BlogController::update()

Update an existing blog page



* Visibility: **public**




### import

    integer BlogController::import()

View to import a given feed into the system.



* Visibility: **public**




### delete

    mixed BlogController::delete()

Delete a blog



* Visibility: **public**




### article_create

    mixed BlogController::article_create()

Create a new blog article



* Visibility: **public**




### article_update

    mixed BlogController::article_update()

Update an existing blog article



* Visibility: **public**




### article_publish

    mixed BlogController::article_publish()

Shortcut for publishing an article.



* Visibility: **public**




### article_unpublish

    mixed BlogController::article_unpublish()

Shortcut for unpublishing an article.



* Visibility: **public**




### article_delete

    mixed BlogController::article_delete()

Delete a blog article



* Visibility: **public**




### article_view

    mixed BlogController::article_view()

New articles that have their own rewrite url will call this method directly.



* Visibility: **public**




### _viewBlog

    mixed BlogController::_viewBlog(\BlogModel $blog)

View controller for a blog article listing page.

This will only display articles under this same blog.

* Visibility: **private**


#### Arguments
* $blog **[BlogModel](BlogModel.md)**



### _viewBlogArticle

    mixed BlogController::_viewBlogArticle(\BlogModel $blog, \BlogArticleModel $article)





* Visibility: **private**


#### Arguments
* $blog **[BlogModel](BlogModel.md)**
* $article **[BlogArticleModel](BlogArticleModel.md)**


