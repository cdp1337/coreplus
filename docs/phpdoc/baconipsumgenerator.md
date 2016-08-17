BaconIpsumGenerator
===============

Bacon Ipsum Generator.

<h3>Usage</h3>
<code>
// Create a new object
$bacon = new BaconIpsumGenerator();

// Do we want filler or just meat?
$bacon->includeFiller = true;
// $bacon->includeFiller = false;

// Give me.... 4 sentences!
echo $bacon->getParagraph(4);

// How 'bout... a bunch of paragraphs!  I need to fill up a blog article with BACON!
echo $bacon->getParagraphsAsMarkup(25);

</code>


* Class name: BaconIpsumGenerator
* Namespace: 





Properties
----------


### $includeFiller

    public boolean $includeFiller = true





* Visibility: **public**


### $meat

    private mixed $meat = array('beef', 'chicken', 'pork', 'bacon', 'chuck', 'short loin', 'sirloin', 'shank', 'flank', 'sausage', 'pork belly', 'shoulder', 'cow', 'pig', 'ground round', 'hamburger', 'meatball', 'tenderloin', 'strip steak', 't-bone', 'ribeye', 'shankle', 'tongue', 'tail', 'pork chop', 'pastrami', 'corned beef', 'jerky', 'ham', 'fatback', 'ham hock', 'pancetta', 'pork loin', 'short ribs', 'spare ribs', 'beef ribs', 'drumstick', 'tri-tip', 'ball tip', 'venison', 'turkey', 'biltong', 'rump', 'jowl', 'salami', 'bresaola', 'meatloaf', 'brisket', 'boudin', 'andouille', 'capicola', 'swine', 'kielbasa', 'frankfurter', 'prosciutto', 'filet mignon', 'leberkas', 'turducken', 'doner')





* Visibility: **private**


### $filler

    private mixed $filler = array('consectetur', 'adipisicing', 'elit', 'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore', 'magna', 'aliqua', 'ut', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud', 'exercitation', 'ullamco', 'laboris', 'nisi', 'ut', 'aliquip', 'ex', 'ea', 'commodo', 'consequat', 'duis', 'aute', 'irure', 'dolor', 'in', 'reprehenderit', 'in', 'voluptate', 'velit', 'esse', 'cillum', 'dolore', 'eu', 'fugiat', 'nulla', 'pariatur', 'excepteur', 'sint', 'occaecat', 'cupidatat', 'non', 'proident', 'sunt', 'in', 'culpa', 'qui', 'officia', 'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum')





* Visibility: **private**


Methods
-------


### getWord

    string BaconIpsumGenerator::getWord(integer $count)





* Visibility: **public**


#### Arguments
* $count **integer**



### getSentence

    string BaconIpsumGenerator::getSentence()

Get a complete sentence.



* Visibility: **public**




### getParagraph

    string BaconIpsumGenerator::getParagraph(integer $length)

Get a complete paragraph



* Visibility: **public**


#### Arguments
* $length **integer** - &lt;p&gt;Number of sentences to include, set to 0 for random.&lt;/p&gt;



### getParagraphs

    array BaconIpsumGenerator::getParagraphs(integer $number, string $prefix)

Get a set of complete paragraphs as an array



* Visibility: **public**


#### Arguments
* $number **integer** - &lt;p&gt;Number of paragraphs to return&lt;/p&gt;
* $prefix **string** - &lt;p&gt;Prefix string, (or blank), to start it with.&lt;/p&gt;



### getParagraphsAsMarkup

    string BaconIpsumGenerator::getParagraphsAsMarkup(integer $number, string $prefix)

Get a set of complete paragraphs as an HTML encoded string



* Visibility: **public**


#### Arguments
* $number **integer** - &lt;p&gt;Number of paragraphs to return&lt;/p&gt;
* $prefix **string** - &lt;p&gt;Prefix string, (or blank), to start it with.&lt;/p&gt;



### GetWords

    mixed BaconIpsumGenerator::GetWords($type)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $type **mixed**



### Make_a_Sentence

    mixed BaconIpsumGenerator::Make_a_Sentence($type)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $type **mixed**



### Make_a_Paragraph

    mixed BaconIpsumGenerator::Make_a_Paragraph($type)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $type **mixed**



### Make_Some_Meaty_Filler

    mixed BaconIpsumGenerator::Make_Some_Meaty_Filler($type, $number_of_paragraphs, $start_with_lorem, $number_of_sentences)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $type **mixed**
* $number_of_paragraphs **mixed**
* $start_with_lorem **mixed**
* $number_of_sentences **mixed**



### _CompatFactory

    \BaconIpsumGenerator BaconIpsumGenerator::_CompatFactory(string $type)

Create a new Bacon based on a legacy type.  Useful for the compatibility methods.



* Visibility: **private**
* This method is **static**.


#### Arguments
* $type **string**



### _getWords

    array BaconIpsumGenerator::_getWords()

Get a shuffled set of words from the dictionary set.



* Visibility: **private**



