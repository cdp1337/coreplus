Html2Text\Html2Text
===============






* Class name: Html2Text
* Namespace: Html2Text



Constants
----------


### ENCODING

    const ENCODING = 'UTF-8'





Properties
----------


### $html

    protected mixed $html

Contains the HTML content to convert.



* Visibility: **protected**


### $text

    protected mixed $text

Contains the converted, formatted text.



* Visibility: **protected**


### $width

    protected mixed $width = 70

Maximum width of the formatted text, in columns.

Set this value to 0 (or less) to ignore word wrapping
and not constrain text to a fixed-width column.

* Visibility: **protected**


### $search

    protected mixed $search = array("/\r/", "/[\n\t]+/", '/<head[^>]*>.*?<\/head>/i', '/<script[^>]*>.*?<\/script>/i', '/<style[^>]*>.*?<\/style>/i', '/<p[^>]*>/i', '/<br[^>]*>/i', '/<i[^>]*>(.*?)<\/i>/i', '/<em[^>]*>(.*?)<\/em>/i', '/(<ul[^>]*>|<\/ul>)/i', '/(<ol[^>]*>|<\/ol>)/i', '/(<dl[^>]*>|<\/dl>)/i', '/<li[^>]*>(.*?)<\/li>/i', '/<dd[^>]*>(.*?)<\/dd>/i', '/<dt[^>]*>(.*?)<\/dt>/i', '/<li[^>]*>/i', '/<hr[^>]*>/i', '/<div[^>]*>/i', '/(<table[^>]*>|<\/table>)/i', '/(<tr[^>]*>|<\/tr>)/i', '/<td[^>]*>(.*?)<\/td>/i', '/<span class="_html2text_ignore">.+?<\/span>/i')

List of preg* regular expression patterns to search for,
used in conjunction with $replace.



* Visibility: **protected**


### $replace

    protected mixed $replace = array('', ' ', '', '', '', "\n\n", "\n", '_\\1_', '_\\1_', "\n\n", "\n\n", "\n\n", "\t* \\1\n", " \\1\n", "\t* \\1", "\n\t* ", "\n-------------------------\n", "<div>\n", "\n\n", "\n", "\t\t\\1\n", "")

List of pattern replacements corresponding to patterns searched.



* Visibility: **protected**


### $entSearch

    protected mixed $entSearch = array('/&#153;/i', '/&#151;/i', '/&(amp|#38);/i', '/[ ]{2,}/')

List of preg* regular expression patterns to search for,
used in conjunction with $entReplace.



* Visibility: **protected**


### $entReplace

    protected mixed $entReplace = array('™', '—', '|+|amp|+|', ' ')

List of pattern replacements corresponding to patterns searched.



* Visibility: **protected**


### $callbackSearch

    protected mixed $callbackSearch = array('/<(a) [^>]*href=("|\')([^"\']+)\2([^>]*)>(.*?)<\/a>/i', '/<(h)[123456]( [^>]*)?>(.*?)<\/h[123456]>/i', '/<(b)( [^>]*)?>(.*?)<\/b>/i', '/<(strong)( [^>]*)?>(.*?)<\/strong>/i', '/<(th)( [^>]*)?>(.*?)<\/th>/i')

List of preg* regular expression patterns to search for
and replace using callback function.



* Visibility: **protected**


### $preSearch

    protected mixed $preSearch = array("/\n/", "/\t/", '/ /', '/<pre[^>]*>/', '/<\/pre>/')

List of preg* regular expression patterns to search for in PRE body,
used in conjunction with $preReplace.



* Visibility: **protected**


### $preReplace

    protected mixed $preReplace = array('<br>', '&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;', '', '')

List of pattern replacements corresponding to patterns searched for PRE body.



* Visibility: **protected**


### $preContent

    protected mixed $preContent = ''

Temporary workspace used during PRE processing.



* Visibility: **protected**


### $baseurl

    protected mixed $baseurl = ''

Contains the base URL that relative links should resolve to.



* Visibility: **protected**


### $converted

    protected mixed $converted = false

Indicates whether content in the $html variable has been converted yet.



* Visibility: **protected**


### $linkList

    protected mixed $linkList = array()

Contains URL addresses from links to be rendered in plain text.



* Visibility: **protected**


### $options

    protected mixed $options = array('do_links' => 'inline', 'width' => 70)

Various configuration options (able to be set in the constructor)



* Visibility: **protected**


Methods
-------


### legacyConstruct

    mixed Html2Text\Html2Text::legacyConstruct($html, $fromFile, array $options)





* Visibility: **private**


#### Arguments
* $html **mixed**
* $fromFile **mixed**
* $options **array**



### __construct

    mixed Html2Text\Html2Text::__construct(string $html, array $options)





* Visibility: **public**


#### Arguments
* $html **string** - &lt;p&gt;Source HTML&lt;/p&gt;
* $options **array** - &lt;p&gt;Set configuration options&lt;/p&gt;



### setHtml

    mixed Html2Text\Html2Text::setHtml(string $html)

Set the source HTML



* Visibility: **public**


#### Arguments
* $html **string** - &lt;p&gt;HTML source content&lt;/p&gt;



### set_html

    mixed Html2Text\Html2Text::set_html($html, $from_file)





* Visibility: **public**


#### Arguments
* $html **mixed**
* $from_file **mixed**



### getText

    string Html2Text\Html2Text::getText()

Returns the text, converted from HTML.



* Visibility: **public**




### get_text

    mixed Html2Text\Html2Text::get_text()





* Visibility: **public**




### print_text

    mixed Html2Text\Html2Text::print_text()





* Visibility: **public**




### p

    mixed Html2Text\Html2Text::p()





* Visibility: **public**




### setBaseUrl

    mixed Html2Text\Html2Text::setBaseUrl(string $baseurl)

Sets a base URL to handle relative links.



* Visibility: **public**


#### Arguments
* $baseurl **string**



### set_base_url

    mixed Html2Text\Html2Text::set_base_url($baseurl)





* Visibility: **public**


#### Arguments
* $baseurl **mixed**



### convert

    mixed Html2Text\Html2Text::convert()





* Visibility: **protected**




### converter

    mixed Html2Text\Html2Text::converter($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### buildlinkList

    string Html2Text\Html2Text::buildlinkList(string $link, string $display, null $linkOverride)

Helper function called by preg_replace() on link replacement.

Maintains an internal list of links to be displayed at the end of the
text, with numeric indices to the original point in the text they
appeared. Also makes an effort at identifying and handling absolute
and relative links.

* Visibility: **protected**


#### Arguments
* $link **string** - &lt;p&gt;URL of the link&lt;/p&gt;
* $display **string** - &lt;p&gt;Part of the text to associate number with&lt;/p&gt;
* $linkOverride **null**



### convertPre

    mixed Html2Text\Html2Text::convertPre($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### convertBlockquotes

    mixed Html2Text\Html2Text::convertBlockquotes(string $text)

Helper function for BLOCKQUOTE body conversion.



* Visibility: **protected**


#### Arguments
* $text **string** - &lt;p&gt;HTML content&lt;/p&gt;



### pregCallback

    string Html2Text\Html2Text::pregCallback(array $matches)

Callback function for preg_replace_callback use.



* Visibility: **protected**


#### Arguments
* $matches **array** - &lt;p&gt;PREG matches&lt;/p&gt;



### pregPreCallback

    string Html2Text\Html2Text::pregPreCallback(array $matches)

Callback function for preg_replace_callback use in PRE content handler.



* Visibility: **protected**


#### Arguments
* $matches **array** - &lt;p&gt;PREG matches&lt;/p&gt;



### toupper

    string Html2Text\Html2Text::toupper(string $str)

Strtoupper function with HTML tags and entities handling.



* Visibility: **private**


#### Arguments
* $str **string** - &lt;p&gt;Text to convert&lt;/p&gt;



### strtoupper

    string Html2Text\Html2Text::strtoupper(string $str)

Strtoupper multibyte wrapper function with HTML entities handling.



* Visibility: **private**


#### Arguments
* $str **string** - &lt;p&gt;Text to convert&lt;/p&gt;


