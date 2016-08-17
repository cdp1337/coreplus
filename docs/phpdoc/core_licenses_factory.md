Core\Licenses\Factory
===============

A short teaser of what Factory does.

More lengthy description of what Factory does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Factory
* Namespace: Core\Licenses







Methods
-------


### GetAsOptions

    array Core\Licenses\Factory::GetAsOptions()

Get the available licenses as an array pre-formatted for use as options.



* Visibility: **public**
* This method is **static**.




### GetLicense

    null|array Core\Licenses\Factory::GetLicense(string $key)

Get a specific license by its key, title, URL, ?or alias?



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**



### DetectLicense

    array|null Core\Licenses\Factory::DetectLicense(string $contents)

Try to detect a license based on its contents.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $contents **string** - &lt;p&gt;The full contents of the license to autodetect from.&lt;/p&gt;



### GetLicenses

    array Core\Licenses\Factory::GetLicenses()

Get the entire list of licenses registered in Core.



* Visibility: **public**
* This method is **static**.



