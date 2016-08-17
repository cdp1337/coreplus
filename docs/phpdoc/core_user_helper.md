Core\User\Helper
===============






* Class name: Helper
* Namespace: Core\User
* This is an **abstract** class





Properties
----------


### $AuthDrivers

    public array $AuthDrivers = array('datastore' => '\\Core\\User\\AuthDrivers\\datastore')





* Visibility: **public**
* This property is **static**.


Methods
-------


### RecordActivity

    mixed Core\User\Helper::RecordActivity()

Function to record activity, ie: a page view.



* Visibility: **public**
* This method is **static**.




### RegisterHandler

    boolean|string Core\User\Helper::RegisterHandler(\Form $form)

Form handler for the rest of the user system, (auth handler has already been executed).



* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### UpdateHandler

    mixed Core\User\Helper::UpdateHandler(\Form $form)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $form **[Form](form.md)**



### PurgeUserActivityCron

    mixed Core\User\Helper::PurgeUserActivityCron()

Method to purge the user activity cron.

This is useful because on an extremely busy site, this table can grow to several gigs within not much time.

* Visibility: **public**
* This method is **static**.




### GetRegistrationForm

    \Form Core\User\Helper::GetRegistrationForm()

Get the form object for registrations.



* Visibility: **public**
* This method is **static**.




### GetEditForm

    \Form Core\User\Helper::GetEditForm(\UserModel $user)

Get the form object for editing users.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $user **[UserModel](usermodel.md)**



### GetForm

    \Form Core\User\Helper::GetForm(\UserModel|null $user)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $user **[UserModel](usermodel.md)|null**



### ForceSessionSync

    boolean Core\User\Helper::ForceSessionSync(\UserModel $user)

Called from the /user/postsave hook with the one argument of the UserModel.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $user **[UserModel](usermodel.md)**



### GetEnabledAuthDrivers

    mixed Core\User\Helper::GetEnabledAuthDrivers()





* Visibility: **public**
* This method is **static**.



