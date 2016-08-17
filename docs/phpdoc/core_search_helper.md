Core\Search\Helper
===============

A short teaser of what ModelHelper does.

More lengthy description of what ModelHelper does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Helper
* Namespace: Core\Search







Methods
-------


### GetSkipWords

    array Core\Search\Helper::GetSkipWords()

Get an array of words to skip in search and indexing.



* Visibility: **public**
* This method is **static**.




### ModelPreSaveHandler

    boolean Core\Search\Helper::ModelPreSaveHandler(\Model $model)

Hook handler to save the index data for a given model record.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)**



### GetWhereClause

    \Core\Datamodel\DatasetWhereClause Core\Search\Helper::GetWhereClause(string $query)

Translate a query string to a populated where clause based on the search index criteria.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $query **string**


