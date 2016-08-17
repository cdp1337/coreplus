ModelSupplemental
===============

The Supplemental Model base interface.

Provides all placeholder methods that extending classes can utilize.


* Interface name: ModelSupplemental
* Namespace: 
* This is an **interface**






Methods
-------


### PreSaveHook

    void ModelSupplemental::PreSaveHook(\Model $model)

Called prior to save completion.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)** - &lt;p&gt;The base model that is being saved&lt;/p&gt;



### PostSaveHook

    void ModelSupplemental::PostSaveHook(\Model $model)

Called immediately after the model has been saved to the database.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)**



### PreDeleteHook

    void ModelSupplemental::PreDeleteHook(\Model $model)

Called before the model is deleted from the database.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)**



### GetControlLinks

    array ModelSupplemental::GetControlLinks(\Model $model)

Called during getControlLinks to return additional links in the controls.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $model **[Model](model.md)**


