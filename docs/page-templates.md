# Page Templates

Page templates are ones that are for a particular page (or type of page), linked to a controller/method pair.

By default, the filename should match pages/[controller]/[method].tpl.
For example, UserController::View has the template pages/user/view.tpl.
This however can be changed in the method via the `$view->templatename` variable, (or whatever \Core\view() is assigned to).

## Widget Areas

Pages can define a `{widgetarea}` tag within the template, where widgets can be installed via the admin interface.

* installable

If the installable key is defined in the tag, only global widgets and widgets tagged with that installable value can be installed there.
This is useful for pages that provide special functionality, such as subject-matter content like the specific User or topic.