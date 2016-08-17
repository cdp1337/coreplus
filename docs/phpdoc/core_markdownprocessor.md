Core\MarkdownProcessor
===============

A short teaser of what MarkdownProcessor does.

More lengthy description of what MarkdownProcessor does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: MarkdownProcessor
* Namespace: Core
* Parent class: [Michelf\MarkdownExtra](michelf_markdownextra.md)



Constants
----------


### MARKDOWNLIB_VERSION

    const MARKDOWNLIB_VERSION = "1.5.0"





Properties
----------


### $urlCallback

    public mixed $urlCallback = null





* Visibility: **public**


### $meta

    private array $meta = array()





* Visibility: **private**


### $_headerCount

    private mixed $_headerCount





* Visibility: **private**


### $_metasPlural

    private array $_metasPlural = array('author', 'keywords')





* Visibility: **private**


### $_metasSynonyms

    private mixed $_metasSynonyms = array('summary' => 'description', 'subject' => 'title', 'authors' => 'author')





* Visibility: **private**


### $fn_id_prefix

    public mixed $fn_id_prefix = ""





* Visibility: **public**


### $fn_link_title

    public mixed $fn_link_title = ""





* Visibility: **public**


### $fn_backlink_title

    public mixed $fn_backlink_title = ""





* Visibility: **public**


### $fn_link_class

    public mixed $fn_link_class = "footnote-ref"





* Visibility: **public**


### $fn_backlink_class

    public mixed $fn_backlink_class = "footnote-backref"





* Visibility: **public**


### $fn_backlink_html

    public mixed $fn_backlink_html = '&#8617;&#xFE0E;'





* Visibility: **public**


### $table_align_class_tmpl

    public mixed $table_align_class_tmpl = ''





* Visibility: **public**


### $code_class_prefix

    public mixed $code_class_prefix = ""





* Visibility: **public**


### $code_attr_on_pre

    public mixed $code_attr_on_pre = false





* Visibility: **public**


### $predef_abbr

    public mixed $predef_abbr = array()





* Visibility: **public**


### $footnotes

    protected mixed $footnotes = array()





* Visibility: **protected**


### $footnotes_ordered

    protected mixed $footnotes_ordered = array()





* Visibility: **protected**


### $footnotes_ref_count

    protected mixed $footnotes_ref_count = array()





* Visibility: **protected**


### $footnotes_numbers

    protected mixed $footnotes_numbers = array()





* Visibility: **protected**


### $abbr_desciptions

    protected mixed $abbr_desciptions = array()





* Visibility: **protected**


### $abbr_word_re

    protected mixed $abbr_word_re = ''





* Visibility: **protected**


### $footnote_counter

    protected mixed $footnote_counter = 1





* Visibility: **protected**


### $id_class_attr_catch_re

    protected mixed $id_class_attr_catch_re = '\{((?:[ ]*[#.a-z][-_:a-zA-Z0-9=]+){1,})[ ]*\}'





* Visibility: **protected**


### $id_class_attr_nocatch_re

    protected mixed $id_class_attr_nocatch_re = '\{(?:[ ]*[#.a-z][-_:a-zA-Z0-9=]+){1,}[ ]*\}'





* Visibility: **protected**


### $block_tags_re

    protected mixed $block_tags_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption|figure'





* Visibility: **protected**


### $context_block_tags_re

    protected mixed $context_block_tags_re = 'script|noscript|style|ins|del|iframe|object|source|track|param|math|svg|canvas|audio|video'





* Visibility: **protected**


### $contain_span_tags_re

    protected mixed $contain_span_tags_re = 'p|h[1-6]|li|dd|dt|td|th|legend|address'





* Visibility: **protected**


### $clean_tags_re

    protected mixed $clean_tags_re = 'script|style|math|svg'





* Visibility: **protected**


### $auto_close_tags_re

    protected mixed $auto_close_tags_re = 'hr|img|param|source|track'





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


### $em_strong_prepared_relist

    protected mixed $em_strong_prepared_relist





* Visibility: **protected**


### $utf8_strlen

    protected mixed $utf8_strlen = 'mb_strlen'





* Visibility: **protected**


Methods
-------


### __construct

    mixed Michelf\Markdown::__construct()





* Visibility: **public**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)




### getMeta

    null|string|array Core\MarkdownProcessor::getMeta($key)

Get a requested metafield for this document.



* Visibility: **public**


#### Arguments
* $key **mixed** - &lt;p&gt;string The meta field to retrieve&lt;/p&gt;



### generateHeaderID

    string Core\MarkdownProcessor::generateHeaderID($text)

Generate an ID from the text contents



* Visibility: **public**


#### Arguments
* $text **mixed** - &lt;p&gt;string&lt;/p&gt;



### doMeta

    string Core\MarkdownProcessor::doMeta($text)

Process any metafields in this document



* Visibility: **public**


#### Arguments
* $text **mixed** - &lt;p&gt;string The original document&lt;/p&gt;



### doMetaPost

    string Core\MarkdownProcessor::doMetaPost($text)

Final checks for any meta fields that may be derived from the data.



* Visibility: **public**


#### Arguments
* $text **mixed** - &lt;p&gt;string&lt;/p&gt;



### doLink

    string Core\MarkdownProcessor::doLink(string $url)

Resolve a URL to its Core version, (or call the urlCallback function if defined).



* Visibility: **public**


#### Arguments
* $url **string**



### doTOC

    string Core\MarkdownProcessor::doTOC($text)

Adds TOC support by including the following on a single line:

[TOC]

TOC Requirements:
* Only headings 2-6
* Headings must have an ID
* Builds TOC with headings _after_ the [TOC] tag

* Visibility: **public**


#### Arguments
* $text **mixed**



### setup

    mixed Michelf\Markdown::setup()





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)




### teardown

    mixed Michelf\Markdown::teardown()





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)




### doExtraAttributes

    mixed Michelf\MarkdownExtra::doExtraAttributes($tag_name, $attr, $defaultIdValue, $classes)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $tag_name **mixed**
* $attr **mixed**
* $defaultIdValue **mixed**
* $classes **mixed**



### stripLinkDefinitions

    mixed Michelf\Markdown::stripLinkDefinitions($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _stripLinkDefinitions_callback

    mixed Michelf\Markdown::_stripLinkDefinitions_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### hashHTMLBlocks

    mixed Michelf\Markdown::hashHTMLBlocks($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _hashHTMLBlocks_inMarkdown

    mixed Michelf\MarkdownExtra::_hashHTMLBlocks_inMarkdown($text, $indent, $enclosing_tag_re, $span)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**
* $indent **mixed**
* $enclosing_tag_re **mixed**
* $span **mixed**



### _hashHTMLBlocks_inHTML

    mixed Michelf\MarkdownExtra::_hashHTMLBlocks_inHTML($text, $hash_method, $md_attr)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**
* $hash_method **mixed**
* $md_attr **mixed**



### hashClean

    mixed Michelf\MarkdownExtra::hashClean($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### doAnchors

    mixed Michelf\Markdown::doAnchors($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doAnchors_reference_callback

    mixed Michelf\Markdown::_doAnchors_reference_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _doAnchors_inline_callback

    mixed Michelf\Markdown::_doAnchors_inline_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### doImages

    mixed Michelf\Markdown::doImages($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doImages_reference_callback

    mixed Michelf\Markdown::_doImages_reference_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _doImages_inline_callback

    mixed Michelf\Markdown::_doImages_inline_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### doHeaders

    mixed Michelf\Markdown::doHeaders($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doHeaders_callback_setext

    mixed Michelf\Markdown::_doHeaders_callback_setext($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _doHeaders_callback_atx

    mixed Michelf\Markdown::_doHeaders_callback_atx($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### doTables

    mixed Michelf\MarkdownExtra::doTables($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _doTable_leadingPipe_callback

    mixed Michelf\MarkdownExtra::_doTable_leadingPipe_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### _doTable_makeAlignAttr

    mixed Michelf\MarkdownExtra::_doTable_makeAlignAttr($alignname)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $alignname **mixed**



### _doTable_callback

    mixed Michelf\MarkdownExtra::_doTable_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### doDefLists

    mixed Michelf\MarkdownExtra::doDefLists($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _doDefLists_callback

    mixed Michelf\MarkdownExtra::_doDefLists_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### processDefListItems

    mixed Michelf\MarkdownExtra::processDefListItems($list_str)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $list_str **mixed**



### _processDefListItems_callback_dt

    mixed Michelf\MarkdownExtra::_processDefListItems_callback_dt($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### _processDefListItems_callback_dd

    mixed Michelf\MarkdownExtra::_processDefListItems_callback_dd($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### doFencedCodeBlocks

    mixed Michelf\MarkdownExtra::doFencedCodeBlocks($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _doFencedCodeBlocks_callback

    mixed Michelf\MarkdownExtra::_doFencedCodeBlocks_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### _doFencedCodeBlocks_newlines

    mixed Michelf\MarkdownExtra::_doFencedCodeBlocks_newlines($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### formParagraphs

    mixed Michelf\Markdown::formParagraphs($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### stripFootnotes

    mixed Michelf\MarkdownExtra::stripFootnotes($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _stripFootnotes_callback

    mixed Michelf\MarkdownExtra::_stripFootnotes_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### doFootnotes

    mixed Michelf\MarkdownExtra::doFootnotes($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### appendFootnotes

    mixed Michelf\MarkdownExtra::appendFootnotes($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _appendFootnotes_callback

    mixed Michelf\MarkdownExtra::_appendFootnotes_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### stripAbbreviations

    mixed Michelf\MarkdownExtra::stripAbbreviations($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _stripAbbreviations_callback

    mixed Michelf\MarkdownExtra::_stripAbbreviations_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### doAbbreviations

    mixed Michelf\MarkdownExtra::doAbbreviations($text)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $text **mixed**



### _doAbbreviations_callback

    mixed Michelf\MarkdownExtra::_doAbbreviations_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\MarkdownExtra](michelf_markdownextra.md)


#### Arguments
* $matches **mixed**



### DefaultTransform

    mixed Michelf\Markdown::DefaultTransform($text)





* Visibility: **public**
* This method is **static**.
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### transform

    mixed Michelf\MarkdownInterface::transform($text)





* Visibility: **public**
* This method is defined by [Michelf\MarkdownInterface](michelf_markdowninterface.md)


#### Arguments
* $text **mixed**



### _hashHTMLBlocks_callback

    mixed Michelf\Markdown::_hashHTMLBlocks_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### hashPart

    mixed Michelf\Markdown::hashPart($text, $boundary)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**
* $boundary **mixed**



### hashBlock

    mixed Michelf\Markdown::hashBlock($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### runBlockGamut

    mixed Michelf\Markdown::runBlockGamut($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### runBasicBlockGamut

    mixed Michelf\Markdown::runBasicBlockGamut($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### doHorizontalRules

    mixed Michelf\Markdown::doHorizontalRules($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### runSpanGamut

    mixed Michelf\Markdown::runSpanGamut($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### doHardBreaks

    mixed Michelf\Markdown::doHardBreaks($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doHardBreaks_callback

    mixed Michelf\Markdown::_doHardBreaks_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _generateIdFromHeaderValue

    mixed Michelf\Markdown::_generateIdFromHeaderValue($headerValue)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $headerValue **mixed**



### doLists

    mixed Michelf\Markdown::doLists($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doLists_callback

    mixed Michelf\Markdown::_doLists_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### processListItems

    mixed Michelf\Markdown::processListItems($list_str, $marker_any_re)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $list_str **mixed**
* $marker_any_re **mixed**



### _processListItems_callback

    mixed Michelf\Markdown::_processListItems_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### doCodeBlocks

    mixed Michelf\Markdown::doCodeBlocks($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doCodeBlocks_callback

    mixed Michelf\Markdown::_doCodeBlocks_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### makeCodeSpan

    mixed Michelf\Markdown::makeCodeSpan($code)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $code **mixed**



### prepareItalicsAndBold

    mixed Michelf\Markdown::prepareItalicsAndBold()





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)




### doItalicsAndBold

    mixed Michelf\Markdown::doItalicsAndBold($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### doBlockQuotes

    mixed Michelf\Markdown::doBlockQuotes($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doBlockQuotes_callback

    mixed Michelf\Markdown::_doBlockQuotes_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _doBlockQuotes_callback2

    mixed Michelf\Markdown::_doBlockQuotes_callback2($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### encodeAttribute

    mixed Michelf\Markdown::encodeAttribute($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### encodeURLAttribute

    mixed Michelf\Markdown::encodeURLAttribute($url, $text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $url **mixed**
* $text **mixed**



### encodeAmpsAndAngles

    mixed Michelf\Markdown::encodeAmpsAndAngles($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### doAutoLinks

    mixed Michelf\Markdown::doAutoLinks($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _doAutoLinks_url_callback

    mixed Michelf\Markdown::_doAutoLinks_url_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _doAutoLinks_email_callback

    mixed Michelf\Markdown::_doAutoLinks_email_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### encodeEntityObfuscatedAttribute

    mixed Michelf\Markdown::encodeEntityObfuscatedAttribute($text, $tail, $head_length)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**
* $tail **mixed**
* $head_length **mixed**



### parseSpan

    mixed Michelf\Markdown::parseSpan($str)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $str **mixed**



### handleSpanToken

    mixed Michelf\Markdown::handleSpanToken($token, $str)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $token **mixed**
* $str **mixed**



### outdent

    mixed Michelf\Markdown::outdent($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### detab

    mixed Michelf\Markdown::detab($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _detab_callback

    mixed Michelf\Markdown::_detab_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### _initDetab

    mixed Michelf\Markdown::_initDetab()





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)




### unhash

    mixed Michelf\Markdown::unhash($text)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $text **mixed**



### _unhash_callback

    mixed Michelf\Markdown::_unhash_callback($matches)





* Visibility: **protected**
* This method is defined by [Michelf\Markdown](michelf_markdown.md)


#### Arguments
* $matches **mixed**



### defaultTransform

    mixed Michelf\MarkdownInterface::defaultTransform($text)





* Visibility: **public**
* This method is **static**.
* This method is defined by [Michelf\MarkdownInterface](michelf_markdowninterface.md)


#### Arguments
* $text **mixed**


