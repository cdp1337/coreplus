Core\i18n\I18NLoader
===============

A short teaser of what Loader does.

More lengthy description of what Loader does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: I18NLoader
* Namespace: Core\i18n





Properties
----------


### $Strings

    protected mixed $Strings





* Visibility: **protected**
* This property is **static**.


### $IsLoaded

    protected mixed $IsLoaded = false





* Visibility: **protected**
* This property is **static**.


### $DefaultLanguage

    protected mixed $DefaultLanguage = null





* Visibility: **protected**
* This property is **static**.


### $UserLanguage

    protected mixed $UserLanguage = null





* Visibility: **protected**
* This property is **static**.


### $FallbackLanguage

    protected mixed $FallbackLanguage = 'en'





* Visibility: **protected**
* This property is **static**.


### $_StringLookupCache

    protected mixed $_StringLookupCache = array()





* Visibility: **protected**
* This property is **static**.


### $LocaleConv

    protected array $LocaleConv = array()

decimal_point 	Decimal point character
thousands_sep 	Thousands separator
grouping 	Array containing numeric groupings
int_curr_symbol 	International currency symbol (i.e. USD)
currency_symbol 	Local currency symbol (i.e. $)
mon_decimal_point 	Monetary decimal point character
mon_thousands_sep 	Monetary thousands separator
mon_grouping 	Array containing monetary groupings
positive_sign 	Sign for positive values
negative_sign 	Sign for negative values
int_frac_digits 	International fractional digits
frac_digits 	Local fractional digits
p_cs_precedes 	TRUE if currency_symbol precedes a positive value, FALSE if it succeeds one
p_sep_by_space 	TRUE if a space separates currency_symbol from a positive value, FALSE otherwise
n_cs_precedes 	TRUE if currency_symbol precedes a negative value, FALSE if it succeeds one
n_sep_by_space 	TRUE if a space separates currency_symbol from a negative value, FALSE otherwise
p_sign_posn
    0 - Parentheses surround the quantity and currency_symbol
    1 - The sign string precedes the quantity and currency_symbol
    2 - The sign string succeeds the quantity and currency_symbol
    3 - The sign string immediately precedes the currency_symbol
    4 - The sign string immediately succeeds the currency_symbol
n_sign_posn
    0 - Parentheses surround the quantity and currency_symbol
    1 - The sign string precedes the quantity and currency_symbol
    2 - The sign string succeeds the quantity and currency_symbol
    3 - The sign string immediately precedes the currency_symbol
    4 - The sign string immediately succeeds the currency_symbol



* Visibility: **protected**
* This property is **static**.


### $Languages

    protected mixed $Languages = array('af' => array('lang' => 'STRING_LANG_Afrikaans', 'dialect' => 'STRING_DIALECT_Afrikaans', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ar' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Arabic', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_AE' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_UAE', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_BH' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Bahrain', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_DZ' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Algeria', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_EG' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Egypt', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_IQ' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Iraq', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_JO' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Jordan', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_KW' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Kuwait', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_LB' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Lebanon', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_LY' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Libya', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_MA' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Morocco', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_OM' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Oman', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_QA' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Qatar', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_SA' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Saudi Arabia', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_SY' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Syria', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_TN' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Tunisia', 'charset' => 'utf-8', 'dir' => 'rtl'), 'ar_YE' => array('lang' => 'STRING_LANG_Arabic', 'dialect' => 'STRING_DIALECT_Yemen', 'charset' => 'utf-8', 'dir' => 'rtl'), 'be' => array('lang' => 'STRING_LANG_Byelorussian', 'dialect' => 'STRING_DIALECT_Byelorussian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'bg' => array('lang' => 'STRING_LANG_Bulgarian', 'dialect' => 'STRING_DIALECT_Bulgarian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'bo' => array('lang' => 'STRING_LANG_Tibetan', 'dialect' => 'STRING_DIALECT_Tibetan', 'charset' => 'utf-8', 'dir' => 'ltr'), 'bo_CN' => array('lang' => 'STRING_LANG_Tibetan', 'dialect' => 'STRING_DIALECT_China', 'charset' => 'utf-8', 'dir' => 'ltr'), 'bo_IN' => array('lang' => 'STRING_LANG_Tibetan', 'dialect' => 'STRING_DIALECT_India', 'charset' => 'utf-8', 'dir' => 'ltr'), 'bs' => array('lang' => 'STRING_LANG_Bosnian', 'dialect' => 'STRING_DIALECT_Bosnian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ca' => array('lang' => 'STRING_LANG_Catalan', 'dialect' => 'STRING_DIALECT_Catalan', 'charset' => 'utf-8', 'dir' => 'ltr'), 'cs' => array('lang' => 'STRING_LANG_Czech', 'dialect' => 'STRING_DIALECT_Czech', 'charset' => 'utf-8', 'dir' => 'ltr'), 'da' => array('lang' => 'STRING_LANG_Danish', 'dialect' => 'STRING_DIALECT_Danish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'de' => array('lang' => 'STRING_LANG_German', 'dialect' => 'STRING_DIALECT_Standard', 'charset' => 'utf-8', 'dir' => 'ltr'), 'de_AT' => array('lang' => 'STRING_LANG_German', 'dialect' => 'STRING_DIALECT_Austria', 'charset' => 'utf-8', 'dir' => 'ltr'), 'de_CH' => array('lang' => 'STRING_LANG_German', 'dialect' => 'STRING_DIALECT_Swiss', 'charset' => 'utf-8', 'dir' => 'ltr'), 'de_DE' => array('lang' => 'STRING_LANG_German', 'dialect' => 'STRING_DIALECT_Germany', 'charset' => 'utf-8', 'dir' => 'ltr'), 'de_LI' => array('lang' => 'STRING_LANG_German', 'dialect' => 'STRING_DIALECT_Liechtenstein', 'charset' => 'utf-8', 'dir' => 'ltr'), 'de_LU' => array('lang' => 'STRING_LANG_German', 'dialect' => 'STRING_DIALECT_Luxembourg', 'charset' => 'utf-8', 'dir' => 'ltr'), 'el' => array('lang' => 'STRING_LANG_Greek', 'dialect' => 'STRING_DIALECT_Greek', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_English', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_AU' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Australian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_BZ' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Belize', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_CA' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Canadian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_GB' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_British', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_IE' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Ireland', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_JM' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Jamaica', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_NZ' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_New Zealand', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_TT' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Trinidad', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_US' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_United States', 'charset' => 'utf-8', 'dir' => 'ltr'), 'en_ZA' => array('lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_South Africa', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Spain - Traditional', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_AR' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Argentina', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_BO' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Bolivia', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_CL' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Chile', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_CO' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Colombia', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_CR' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Costa Rica', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_DO' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Dominican Republic', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_EC' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Ecuador', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_ES' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Spain', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_GT' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Guatemala', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_HN' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Honduras', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_MX' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Mexican', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_NI' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Nicaragua', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_PA' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Panama', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_PE' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Peru', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_PR' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Puerto Rico', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_PY' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Paraguay', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_SV' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_El Salvador', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_UY' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Uruguay', 'charset' => 'utf-8', 'dir' => 'ltr'), 'es_VE' => array('lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Venezuela', 'charset' => 'utf-8', 'dir' => 'ltr'), 'et' => array('lang' => 'STRING_LANG_Estonian', 'dialect' => 'STRING_DIALECT_Estonian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'eu' => array('lang' => 'STRING_LANG_Basque', 'dialect' => 'STRING_DIALECT_Basque', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fa' => array('lang' => 'STRING_LANG_Farsi', 'dialect' => 'STRING_DIALECT_Farsi', 'charset' => 'utf-8', 'dir' => 'rtl'), 'fi' => array('lang' => 'STRING_LANG_Finnish', 'dialect' => 'STRING_DIALECT_Finnish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fo' => array('lang' => 'STRING_LANG_Faeroese', 'dialect' => 'STRING_DIALECT_Faeroese', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fr' => array('lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Standard', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fr_BE' => array('lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Belgium', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fr_CA' => array('lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Canadian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fr_CH' => array('lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Swiss', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fr_FR' => array('lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_France', 'charset' => 'utf-8', 'dir' => 'ltr'), 'fr_LU' => array('lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Luxembourg', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ga' => array('lang' => 'STRING_LANG_Irish', 'dialect' => 'STRING_DIALECT_Irish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'gd' => array('lang' => 'STRING_LANG_Gaelic', 'dialect' => 'STRING_DIALECT_Scots', 'charset' => 'utf-8', 'dir' => 'ltr'), 'gd_IE' => array('lang' => 'STRING_LANG_Gaelic', 'dialect' => 'STRING_DIALECT_Irish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'gl' => array('lang' => 'STRING_LANG_Galician', 'dialect' => 'STRING_DIALECT_Galician', 'charset' => 'utf-8', 'dir' => 'ltr'), 'he' => array('lang' => 'STRING_LANG_Hebrew', 'dialect' => 'STRING_DIALECT_Hebrew', 'charset' => 'utf-8', 'dir' => 'rtl'), 'hi' => array('lang' => 'STRING_LANG_Hindi', 'dialect' => 'STRING_DIALECT_Hindi', 'charset' => 'utf-8', 'dir' => 'ltr'), 'hr' => array('lang' => 'STRING_LANG_Croatian', 'dialect' => 'STRING_DIALECT_Croatian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'hu' => array('lang' => 'STRING_LANG_Hungarian', 'dialect' => 'STRING_DIALECT_Hungarian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'hy' => array('lang' => 'STRING_LANG_Armenian - Armenia', 'dialect' => 'STRING_DIALECT_Armenian - Armenia', 'charset' => 'utf-8', 'dir' => 'ltr'), 'id' => array('lang' => 'STRING_LANG_Indonesian', 'dialect' => 'STRING_DIALECT_Indonesian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'in' => array('lang' => 'STRING_LANG_Indonesian', 'dialect' => 'STRING_DIALECT_Indonesian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'is' => array('lang' => 'STRING_LANG_Icelandic', 'dialect' => 'STRING_DIALECT_Icelandic', 'charset' => 'utf-8', 'dir' => 'ltr'), 'it' => array('lang' => 'STRING_LANG_Italian', 'dialect' => 'STRING_DIALECT_Italian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'it_CH' => array('lang' => 'STRING_LANG_Italian (Swiss) ', 'dialect' => 'STRING_DIALECT_Italian (Swiss) ', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ja' => array('lang' => 'STRING_LANG_Japanese', 'dialect' => 'STRING_DIALECT_Japanese', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ko' => array('lang' => 'STRING_LANG_Korean', 'dialect' => 'STRING_DIALECT_Korean', 'charset' => 'kr', 'dir' => 'ltr'), 'ko_KP' => array('lang' => 'STRING_LANG_Korea', 'dialect' => 'STRING_DIALECT_North', 'charset' => 'kr', 'dir' => 'ltr'), 'ko_KR' => array('lang' => 'STRING_LANG_Korea', 'dialect' => 'STRING_DIALECT_South', 'charset' => 'kr', 'dir' => 'ltr'), 'lt' => array('lang' => 'STRING_LANG_Lithuanian', 'dialect' => 'STRING_DIALECT_Lithuanian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'lv' => array('lang' => 'STRING_LANG_Latvian', 'dialect' => 'STRING_DIALECT_Latvian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'mk' => array('lang' => 'STRING_LANG_FYRO Macedonian', 'dialect' => 'STRING_DIALECT_FYRO Macedonian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'mk_MK' => array('lang' => 'STRING_LANG_Macedonian', 'dialect' => 'STRING_DIALECT_Macedonian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ms' => array('lang' => 'STRING_LANG_Malaysian', 'dialect' => 'STRING_DIALECT_Malaysian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'mt' => array('lang' => 'STRING_LANG_Maltese', 'dialect' => 'STRING_DIALECT_Maltese', 'charset' => 'utf-8', 'dir' => 'ltr'), 'nb' => array('lang' => 'STRING_LANG_Norwegian Bokmal', 'dialect' => 'STRING_DIALECT_Norwegian Bokmal', 'charset' => 'utf-8', 'dir' => 'ltr'), 'nl' => array('lang' => 'STRING_LANG_Dutch', 'dialect' => 'STRING_DIALECT_Standard', 'charset' => 'utf-8', 'dir' => 'ltr'), 'nl_BE' => array('lang' => 'STRING_LANG_Dutch', 'dialect' => 'STRING_DIALECT_Belgium', 'charset' => 'utf-8', 'dir' => 'ltr'), 'nn' => array('lang' => 'STRING_LANG_Norwegian Nynorsk', 'dialect' => 'STRING_DIALECT_Norwegian Nynorsk', 'charset' => 'utf-8', 'dir' => 'ltr'), 'no' => array('lang' => 'STRING_LANG_Norwegian', 'dialect' => 'STRING_DIALECT_Norwegian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'pl' => array('lang' => 'STRING_LANG_Polish', 'dialect' => 'STRING_DIALECT_Polish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'pt' => array('lang' => 'STRING_LANG_Portuguese', 'dialect' => 'STRING_DIALECT_Portugal', 'charset' => 'utf-8', 'dir' => 'ltr'), 'pt_BR' => array('lang' => 'STRING_LANG_Portuguese', 'dialect' => 'STRING_DIALECT_Brazil', 'charset' => 'utf-8', 'dir' => 'ltr'), 'rm' => array('lang' => 'STRING_LANG_Rhaeto Romanic', 'dialect' => 'STRING_DIALECT_Rhaeto Romanic', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ro' => array('lang' => 'STRING_LANG_Romanian', 'dialect' => 'STRING_DIALECT_Romanian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ro_MO' => array('lang' => 'STRING_LANG_Romanian', 'dialect' => 'STRING_DIALECT_Moldavia', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ru' => array('lang' => 'STRING_LANG_Russian', 'dialect' => 'STRING_DIALECT_Russian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ru_MO' => array('lang' => 'STRING_LANG_Russian', 'dialect' => 'STRING_DIALECT_Moldavia', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sb' => array('lang' => 'STRING_LANG_Sorbian', 'dialect' => 'STRING_DIALECT_Sorbian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sk' => array('lang' => 'STRING_LANG_Slovak', 'dialect' => 'STRING_DIALECT_Slovak', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sl' => array('lang' => 'STRING_LANG_Slovenian', 'dialect' => 'STRING_DIALECT_Slovenian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sq' => array('lang' => 'STRING_LANG_Albanian', 'dialect' => 'STRING_DIALECT_Albanian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sr' => array('lang' => 'STRING_LANG_Serbian', 'dialect' => 'STRING_DIALECT_Serbian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sv' => array('lang' => 'STRING_LANG_Swedish', 'dialect' => 'STRING_DIALECT_Swedish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sv_FI' => array('lang' => 'STRING_LANG_Swedish', 'dialect' => 'STRING_DIALECT_Finland', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sx' => array('lang' => 'STRING_LANG_Sutu', 'dialect' => 'STRING_DIALECT_Sutu', 'charset' => 'utf-8', 'dir' => 'ltr'), 'sz' => array('lang' => 'STRING_LANG_Sami', 'dialect' => 'STRING_DIALECT_Lappish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'th' => array('lang' => 'STRING_LANG_Thai', 'dialect' => 'STRING_DIALECT_Thai', 'charset' => 'utf-8', 'dir' => 'ltr'), 'tn' => array('lang' => 'STRING_LANG_Tswana', 'dialect' => 'STRING_DIALECT_Tswana', 'charset' => 'utf-8', 'dir' => 'ltr'), 'tr' => array('lang' => 'STRING_LANG_Turkish', 'dialect' => 'STRING_DIALECT_Turkish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ts' => array('lang' => 'STRING_LANG_Tsonga', 'dialect' => 'STRING_DIALECT_Tsonga', 'charset' => 'utf-8', 'dir' => 'ltr'), 'uk' => array('lang' => 'STRING_LANG_Ukrainian', 'dialect' => 'STRING_DIALECT_Ukrainian', 'charset' => 'utf-8', 'dir' => 'ltr'), 'ur' => array('lang' => 'STRING_LANG_Urdu', 'dialect' => 'STRING_DIALECT_Urdu', 'charset' => 'utf-8', 'dir' => 'rtl'), 've' => array('lang' => 'STRING_LANG_Venda', 'dialect' => 'STRING_DIALECT_Venda', 'charset' => 'utf-8', 'dir' => 'ltr'), 'vi' => array('lang' => 'STRING_LANG_Vietnamese', 'dialect' => 'STRING_DIALECT_Vietnamese', 'charset' => 'utf-8', 'dir' => 'ltr'), 'cy' => array('lang' => 'STRING_LANG_Welsh', 'dialect' => 'STRING_DIALECT_Welsh', 'charset' => 'utf-8', 'dir' => 'ltr'), 'xh' => array('lang' => 'STRING_LANG_Xhosa', 'dialect' => 'STRING_DIALECT_Xhosa', 'charset' => 'utf-8', 'dir' => 'ltr'), 'yi' => array('lang' => 'STRING_LANG_Yiddish', 'dialect' => 'STRING_DIALECT_Yiddish', 'charset' => 'utf-8', 'dir' => 'ltr'), 'zh' => array('lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Chinese', 'charset' => 'utf-8', 'dir' => 'ltr'), 'zh_CN' => array('lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_PRC', 'charset' => 'GB2312', 'dir' => 'ltr'), 'zh_HK' => array('lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Hong Kong', 'charset' => 'utf-8', 'dir' => 'ltr'), 'zh_SG' => array('lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Singapore', 'charset' => 'utf-8', 'dir' => 'ltr'), 'zh_TW' => array('lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Taiwan', 'charset' => 'utf-8', 'dir' => 'ltr'), 'zu' => array('lang' => 'STRING_LANG_Zulu', 'dialect' => 'STRING_DIALECT_Zulu', 'charset' => 'utf-8', 'dir' => 'ltr'))





* Visibility: **protected**
* This property is **static**.


Methods
-------


### Init

    mixed Core\i18n\I18NLoader::Init()





* Visibility: **public**
* This method is **static**.




### Get

    array Core\i18n\I18NLoader::Get(string $key, string|null $lang)

Lookup a translation string with the requested language.

Will return an array with the located string, (if any), and some useful metadata for the lookup.

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **string**
* $lang **string|null**



### FormatNumber

    mixed Core\i18n\I18NLoader::FormatNumber($number, $precision)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $number **mixed**
* $precision **mixed**



### GetLocaleConv

    mixed Core\i18n\I18NLoader::GetLocaleConv($option)





* Visibility: **public**
* This method is **static**.


#### Arguments
* $option **mixed**



### GetUsersLanguage

    string Core\i18n\I18NLoader::GetUsersLanguage()

Get the user's currently selected language/locale.



* Visibility: **public**
* This method is **static**.




### GetFallbackLanguage

    string Core\i18n\I18NLoader::GetFallbackLanguage()

Get the fallback language



* Visibility: **public**
* This method is **static**.




### GetBaseLanguagesAsOptions

    array Core\i18n\I18NLoader::GetBaseLanguagesAsOptions()

Get just the base languages available, (without dialects), as a valid optionset.



* Visibility: **public**
* This method is **static**.




### GetLanguagesAsOptions

    array Core\i18n\I18NLoader::GetLanguagesAsOptions()

Get the languages available on this system as form options



* Visibility: **public**
* This method is **static**.




### GetLocalesAvailable

    array Core\i18n\I18NLoader::GetLocalesAvailable()

Get an array of locales currently available on the base system.



* Visibility: **public**
* This method is **static**.




### GetLocalesEnabled

    array Core\i18n\I18NLoader::GetLocalesEnabled()

Get an array (locale => [lang, dialect, charset, dir]) of all locales enabled on the system,
XORed from the list of currently available locales on the native system.



* Visibility: **public**
* This method is **static**.




### GetAllStrings

    array Core\i18n\I18NLoader::GetAllStrings($lang)

Method to get all strings currently loaded in the system



* Visibility: **public**
* This method is **static**.


#### Arguments
* $lang **mixed**



### KeyifyString

    string Core\i18n\I18NLoader::KeyifyString(string $string)

"Keyify" a given string, usually from a /config or /permission directive.

This will capitalize the string, replace all "/" characters to "_", and ltrim any prepending "_".

* Visibility: **public**
* This method is **static**.


#### Arguments
* $string **string**



### _SearchForString

    array Core\i18n\I18NLoader::_SearchForString(string $string, string $lang)

Search for a string given a set language.

Will match an exact locale key and the base language if that's not found.

* Visibility: **protected**
* This method is **static**.


#### Arguments
* $string **string** - &lt;p&gt;The string to find&lt;/p&gt;
* $lang **string** - &lt;p&gt;The language to search on&lt;/p&gt;


