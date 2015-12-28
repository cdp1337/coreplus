# Forms and Models Integration

Since forms can be directly associated with the low-level data, (and therefore Models), the two systems can be very tightly integrated.

The following code can be put into a controller to create a form directly based off a model:

    $form = new Form();
    $model = new SomeModel();
    $form->addModel($model);

If multiple models need to be present on the same form, that can be achieved as well:

    // Create a new form object
    $form = new Form();
    
    // And get the models from wherever necessary.
    $model1 = new PageModel();
    $model2 = new SomeModel();
    
    // Adding them with a second argument provides a `prefix` name
    // for that model and all associated form elements.
    $form->addModel($model1, 'page');
    $form->addModel($model2, 'model');

## Internal Logic

By default, the model's schema is used for building form elements.  In addition, custom logic can be performed for nested models or related data directly from within the Model.

1. [Form::addModel()]
    * Called directly from the controller or external script.
    * Gets the schema for the requested [Model]
    * For each element to be created from the schema, calls [Model::setToFormElement()] many times.
    * After all automatic elements added, calls [Model::addToFormPost()] once.

[Form]: http://docs.corepl.us/phpdoc/classes/Form.html
[Form::addModel()]: http://docs.corepl.us/phpdoc/classes/Form.html#method_addModel
[Model]: http://docs.corepl.us/phpdoc/classes/Model.html
[Model::setToFormElement()]: http://docs.corepl.us/phpdoc/classes/Model.html#method_setToFormElement
[Model::addToFormPost()]:http://docs.corepl.us/phpdoc/classes/Model.html#method_addToFormPost