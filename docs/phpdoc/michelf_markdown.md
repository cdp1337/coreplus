Michelf\Markdown
===============






* Class name: Markdown
* Namespace: Michelf
* This class implements: [Michelf\MarkdownInterface](michelf_markdowninterface.md)


Constants
----------


### MARKDOWNLIB_VERSION

    const MARKDOWNLIB_VERSION = "1.5.0"





Properties
----------


### $empty_element_suffix

    public mixed $empty_element_suffix = " />"





* Visibility: **public**


### $tab_width

    public mixed $tab_width = 4





* Visibility: **public**


### $no_markup

    public mixed $no_markup = false





* Visibility: **public**


### $no_entities

    public mixed $no_entities = false





* Visibility: **public**


### $predef_urls

    public mixed $predef_urls = array()





* Visibility: **public**


### $predef_titles

    public mixed $predef_titles = array()





* Visibility: **public**


### $url_filter_func

    public mixed $url_filter_func = null





* Visibility: **public**


### $header_id_func

    public mixed $header_id_func = null





* Visibility: **public**


### $code_block_content_func

    public mixed $code_block_content_func = null





* Visibility: **public**


### $enhanced_ordered_list

    public mixed $enhanced_ordered_list = false





* Visibility: **public**


### $nested_brackets_depth

    protected mixed $nested_brackets_depth = 6





* Visibility: **protected**


### $nested_brackets_re

    protected mixed $nested_brackets_re





* Visibility: **protected**


### $nested_url_parenthesis_depth

    protected mixed $nested_url_parenthesis_depth = 4





* Visibility: **protected**


### $nested_url_parenthesis_re

    protected mixed $nested_url_parenthesis_re





* Visibility: **protected**


### $escape_chars

    protected mixed $escape_chars = '\`*_{}[]()>#+-.!'





* Visibility: **protected**


### $escape_chars_re

    protected mixed $escape_chars_re





* Visibility: **protected**


### $urls

    protected mixed $urls = array()





* Visibility: **protected**


### $titles

    protected mixed $titles = array()





* Visibility: **protected**


### $html_hashes

    protected mixed $html_hashes = array()





* Visibility: **protected**


### $in_anchor

    protected mixed $in_anchor = false





* Visibility: **protected**


### $document_gamut

    protected mixed $document_gamut = array("stripLinkDefinitions" => 20, "runBasicBlockGamut" => 30)





* Visibility: **protected**


### $block_gamut

    protected mixed $block_gamut = array("doHeaders" => 10, "doHorizontalRules" => 20, "doLists" => 40, "doCodeBlocks" => 50, "doBlockQuotes" => 60)





* Visibility: **protected**


### $span_gamut

    protected mixed $span_gamut = array("parseSpan" => -30, "doImages" => 10, "doAnchors" => 20, "doAutoLinks" => 30, "encodeAmpsAndAngles" => 40, "doItalicsAndBold" => 50, "doHardBreaks" => 60)





* Visibility: **protected**


### $list_level

    protected mixed $list_level





* Visibility: **protected**


### $em_relist

    protected mixed $em_relist = array('' => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?![\.,:;]?\s)', '*' => '(?<![\s*])\*(?!\*)', '_' => '(?<![\s_])_(?!_)')





* Visibility: **protected**


### $strong_relist

    protected mixed $strong_relist = array('' => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?![\.,:;]?\s)', '**' => '(?<![\s*])\*\*(?!\*)', '__' => '(?<![\s_])__(?!_)')





* Visibility: **protected**


### $em_strong_relist

    protected mixed $em_strong_relist = array('' => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?![\.,:;]?\s)', '***' => '(?<![\s*])\*\*\*(?!\*)', '___' => '(?<![\s_])___(?!_)')





* Visibility: **protected**


### $em_strong_prepared_relist

    protected mixed $em_strong_prepared_relist





* Visibility: **protected**


### $utf8_strlen

    protected mixed $utf8_strlen = 'mb_strlen'





* Visibility: **protected**


Methods
-------


### DefaultTransform

    mixed Michelf\Markdown::DefaultTransform($text)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $text **mixed**



### __construct

    mixed Michelf\Markdown::__construct()





* Visibility: **public**




### setup

    mixed Michelf\Markdown::setup()





* Visibility: **protected**




### teardown

    mixed Michelf\Markdown::teardown()





* Visibility: **protected**




### transform

    mixed Michelf\MarkdownInterface::transform($text)





* Visibility: **public**
* This method is defined by [Michelf\MarkdownInterface](michelf_markdowninterface.md)


#### Arguments
* $text **mixed**



### stripLinkDefinitions

    mixed Michelf\Markdown::stripLinkDefinitions($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _stripLinkDefinitions_callback

    mixed Michelf\Markdown::_stripLinkDefinitions_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### hashHTMLBlocks

    mixed Michelf\Markdown::hashHTMLBlocks($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _hashHTMLBlocks_callback

    mixed Michelf\Markdown::_hashHTMLBlocks_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### hashPart

    mixed Michelf\Markdown::hashPart($text, $boundary)





* Visibility: **protected**


#### Arguments
* $text **mixed**
* $boundary **mixed**



### hashBlock

    mixed Michelf\Markdown::hashBlock($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### runBlockGamut

    mixed Michelf\Markdown::runBlockGamut($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### runBasicBlockGamut

    mixed Michelf\Markdown::runBasicBlockGamut($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### doHorizontalRules

    mixed Michelf\Markdown::doHorizontalRules($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### runSpanGamut

    mixed Michelf\Markdown::runSpanGamut($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### doHardBreaks

    mixed Michelf\Markdown::doHardBreaks($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doHardBreaks_callback

    mixed Michelf\Markdown::_doHardBreaks_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### doAnchors

    mixed Michelf\Markdown::doAnchors($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doAnchors_reference_callback

    mixed Michelf\Markdown::_doAnchors_reference_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _doAnchors_inline_callback

    mixed Michelf\Markdown::_doAnchors_inline_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### doImages

    mixed Michelf\Markdown::doImages($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doImages_reference_callback

    mixed Michelf\Markdown::_doImages_reference_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _doImages_inline_callback

    mixed Michelf\Markdown::_doImages_inline_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### doHeaders

    mixed Michelf\Markdown::doHeaders($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doHeaders_callback_setext

    mixed Michelf\Markdown::_doHeaders_callback_setext($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _doHeaders_callback_atx

    mixed Michelf\Markdown::_doHeaders_callback_atx($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _generateIdFromHeaderValue

    mixed Michelf\Markdown::_generateIdFromHeaderValue($headerValue)





* Visibility: **protected**


#### Arguments
* $headerValue **mixed**



### doLists

    mixed Michelf\Markdown::doLists($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doLists_callback

    mixed Michelf\Markdown::_doLists_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### processListItems

    mixed Michelf\Markdown::processListItems($list_str, $marker_any_re)





* Visibility: **protected**


#### Arguments
* $list_str **mixed**
* $marker_any_re **mixed**



### _processListItems_callback

    mixed Michelf\Markdown::_processListItems_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### doCodeBlocks

    mixed Michelf\Markdown::doCodeBlocks($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doCodeBlocks_callback

    mixed Michelf\Markdown::_doCodeBlocks_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### makeCodeSpan

    mixed Michelf\Markdown::makeCodeSpan($code)





* Visibility: **protected**


#### Arguments
* $code **mixed**



### prepareItalicsAndBold

    mixed Michelf\Markdown::prepareItalicsAndBold()





* Visibility: **protected**




### doItalicsAndBold

    mixed Michelf\Markdown::doItalicsAndBold($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### doBlockQuotes

    mixed Michelf\Markdown::doBlockQuotes($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doBlockQuotes_callback

    mixed Michelf\Markdown::_doBlockQuotes_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _doBlockQuotes_callback2

    mixed Michelf\Markdown::_doBlockQuotes_callback2($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### formParagraphs

    mixed Michelf\Markdown::formParagraphs($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### encodeAttribute

    mixed Michelf\Markdown::encodeAttribute($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### encodeURLAttribute

    mixed Michelf\Markdown::encodeURLAttribute($url, $text)





* Visibility: **protected**


#### Arguments
* $url **mixed**
* $text **mixed**



### encodeAmpsAndAngles

    mixed Michelf\Markdown::encodeAmpsAndAngles($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### doAutoLinks

    mixed Michelf\Markdown::doAutoLinks($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _doAutoLinks_url_callback

    mixed Michelf\Markdown::_doAutoLinks_url_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _doAutoLinks_email_callback

    mixed Michelf\Markdown::_doAutoLinks_email_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### encodeEntityObfuscatedAttribute

    mixed Michelf\Markdown::encodeEntityObfuscatedAttribute($text, $tail, $head_length)





* Visibility: **protected**


#### Arguments
* $text **mixed**
* $tail **mixed**
* $head_length **mixed**



### parseSpan

    mixed Michelf\Markdown::parseSpan($str)





* Visibility: **protected**


#### Arguments
* $str **mixed**



### handleSpanToken

    mixed Michelf\Markdown::handleSpanToken($token, $str)





* Visibility: **protected**


#### Arguments
* $token **mixed**
* $str **mixed**



### outdent

    mixed Michelf\Markdown::outdent($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### detab

    mixed Michelf\Markdown::detab($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _detab_callback

    mixed Michelf\Markdown::_detab_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### _initDetab

    mixed Michelf\Markdown::_initDetab()





* Visibility: **protected**




### unhash

    mixed Michelf\Markdown::unhash($text)





* Visibility: **protected**


#### Arguments
* $text **mixed**



### _unhash_callback

    mixed Michelf\Markdown::_unhash_callback($matches)





* Visibility: **protected**


#### Arguments
* $matches **mixed**



### defaultTransform

    mixed Michelf\MarkdownInterface::defaultTransform($text)





* Visibility: **public**
* This method is **static**.
* This method is defined by [Michelf\MarkdownInterface](michelf_markdowninterface.md)


#### Arguments
* $text **mixed**


