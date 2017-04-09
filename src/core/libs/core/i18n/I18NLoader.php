<?php
/**
 * File for class Loader definition in the coreplus project
 * 
 * @package Core\i18n
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140326.2321
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

namespace Core\i18n;
use Core\Cache;


/**
 * A short teaser of what Loader does.
 *
 * More lengthy description of what Loader does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Loader
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Core\i18n
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class I18NLoader {
	protected static $Strings;

	protected static $IsLoaded = false;

	protected static $DefaultLanguage = null;
	protected static $UserLanguage = null;
	protected static $FallbackLanguage = 'en';
	
	protected static $_StringLookupCache = [];
	/**
	 * decimal_point 	Decimal point character
	 * thousands_sep 	Thousands separator
	 * grouping 	Array containing numeric groupings
	 * int_curr_symbol 	International currency symbol (i.e. USD)
	 * currency_symbol 	Local currency symbol (i.e. $)
	 * mon_decimal_point 	Monetary decimal point character
	 * mon_thousands_sep 	Monetary thousands separator
	 * mon_grouping 	Array containing monetary groupings
	 * positive_sign 	Sign for positive values
	 * negative_sign 	Sign for negative values
	 * int_frac_digits 	International fractional digits
	 * frac_digits 	Local fractional digits
	 * p_cs_precedes 	TRUE if currency_symbol precedes a positive value, FALSE if it succeeds one
	 * p_sep_by_space 	TRUE if a space separates currency_symbol from a positive value, FALSE otherwise
	 * n_cs_precedes 	TRUE if currency_symbol precedes a negative value, FALSE if it succeeds one
	 * n_sep_by_space 	TRUE if a space separates currency_symbol from a negative value, FALSE otherwise
	 * p_sign_posn
	 *     0 - Parentheses surround the quantity and currency_symbol
	 *     1 - The sign string precedes the quantity and currency_symbol
	 *     2 - The sign string succeeds the quantity and currency_symbol
	 *     3 - The sign string immediately precedes the currency_symbol
	 *     4 - The sign string immediately succeeds the currency_symbol
	 * n_sign_posn
	 *     0 - Parentheses surround the quantity and currency_symbol
	 *     1 - The sign string precedes the quantity and currency_symbol
	 *     2 - The sign string succeeds the quantity and currency_symbol
	 *     3 - The sign string immediately precedes the currency_symbol
	 *     4 - The sign string immediately succeeds the currency_symbol
	 *
	 * @var array
	 */
	protected static $LocaleConv = [];
	protected static $Languages = [
		'af' =>     ['lang' => 'STRING_LANG_Afrikaans', 'dialect' => 'STRING_DIALECT_Afrikaans',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'ar' =>     ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Arabic',                        'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_AE' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_UAE',               'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_BH' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Bahrain',              'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_DZ' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Algeria',              'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_EG' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Egypt',                'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_IQ' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Iraq',                 'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_JO' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Jordan',               'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_KW' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Kuwait',               'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_LB' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Lebanon',              'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_LY' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Libya',                'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_MA' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Morocco',              'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_OM' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Oman',                 'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_QA' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Qatar',                'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_SA' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Saudi Arabia',         'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_SY' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Syria',                'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_TN' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Tunisia',              'charset' => 'utf-8',  'dir' => 'rtl'],
		'ar_YE' =>  ['lang' => 'STRING_LANG_Arabic',    'dialect' => 'STRING_DIALECT_Yemen',                'charset' => 'utf-8',  'dir' => 'rtl'],
		'be' =>     ['lang' => 'STRING_LANG_Byelorussian', 'dialect' => 'STRING_DIALECT_Byelorussian',                  'charset' => 'utf-8',  'dir' => 'ltr'],
		'bg' =>     ['lang' => 'STRING_LANG_Bulgarian', 'dialect' => 'STRING_DIALECT_Bulgarian',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'bo' =>     ['lang' => 'STRING_LANG_Tibetan', 'dialect' => 'STRING_DIALECT_Tibetan',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'bo_CN' =>  ['lang' => 'STRING_LANG_Tibetan', 'dialect' => 'STRING_DIALECT_China',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'bo_IN' =>  ['lang' => 'STRING_LANG_Tibetan', 'dialect' => 'STRING_DIALECT_India',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'bs' =>     ['lang' => 'STRING_LANG_Bosnian', 'dialect' => 'STRING_DIALECT_Bosnian',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'ca' =>     ['lang' => 'STRING_LANG_Catalan', 'dialect' => 'STRING_DIALECT_Catalan',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'cs' =>     ['lang' => 'STRING_LANG_Czech',   'dialect' => 'STRING_DIALECT_Czech',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'da' =>     ['lang' => 'STRING_LANG_Danish',  'dialect' => 'STRING_DIALECT_Danish',                        'charset' => 'utf-8',  'dir' => 'ltr'],
		'de' =>     ['lang' => 'STRING_LANG_German',  'dialect' => 'STRING_DIALECT_Standard',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'de_AT' =>  ['lang' => 'STRING_LANG_German',  'dialect' => 'STRING_DIALECT_Austria',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'de_CH' =>  ['lang' => 'STRING_LANG_German',  'dialect' => 'STRING_DIALECT_Swiss',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'de_DE' =>  ['lang' => 'STRING_LANG_German',  'dialect' => 'STRING_DIALECT_Germany',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'de_LI' =>  ['lang' => 'STRING_LANG_German',  'dialect' => 'STRING_DIALECT_Liechtenstein',        'charset' => 'utf-8',  'dir' => 'ltr'],
		'de_LU' =>  ['lang' => 'STRING_LANG_German',  'dialect' => 'STRING_DIALECT_Luxembourg',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'el' =>     ['lang' => 'STRING_LANG_Greek',   'dialect' => 'STRING_DIALECT_Greek',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'en' =>     ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_English',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_AU' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Australian',          'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_BZ' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Belize',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_CA' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Canadian',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_GB' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_British',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_IE' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Ireland',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_JM' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Jamaica',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_NZ' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_New Zealand',         'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_TT' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_Trinidad',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_US' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_United States',       'charset' => 'utf-8',  'dir' => 'ltr'],
		'en_ZA' =>  ['lang' => 'STRING_LANG_English', 'dialect' => 'STRING_DIALECT_South Africa',        'charset' => 'utf-8',  'dir' => 'ltr'],
		'es' =>     ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Spain - Traditional', 'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_AR' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Argentina',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_BO' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Bolivia',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_CL' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Chile',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_CO' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Colombia',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_CR' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Costa Rica',          'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_DO' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Dominican Republic',  'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_EC' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Ecuador',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_ES' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Spain',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_GT' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Guatemala',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_HN' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Honduras',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_MX' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Mexican',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_NI' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Nicaragua',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_PA' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Panama',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_PE' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Peru',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_PR' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Puerto Rico',         'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_PY' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Paraguay',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_SV' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_El Salvador',         'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_UY' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Uruguay',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'es_VE' =>  ['lang' => 'STRING_LANG_Spanish', 'dialect' => 'STRING_DIALECT_Venezuela',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'et' =>     ['lang' => 'STRING_LANG_Estonian', 'dialect' => 'STRING_DIALECT_Estonian',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'eu' =>     ['lang' => 'STRING_LANG_Basque', 'dialect' => 'STRING_DIALECT_Basque',                        'charset' => 'utf-8',  'dir' => 'ltr'],
		'fa' =>     ['lang' => 'STRING_LANG_Farsi', 'dialect' => 'STRING_DIALECT_Farsi',                         'charset' => 'utf-8',  'dir' => 'rtl'],
		'fi' =>     ['lang' => 'STRING_LANG_Finnish', 'dialect' => 'STRING_DIALECT_Finnish',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'fo' =>     ['lang' => 'STRING_LANG_Faeroese', 'dialect' => 'STRING_DIALECT_Faeroese',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'fr' =>     ['lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Standard',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'fr_BE' =>  ['lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Belgium',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'fr_CA' =>  ['lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Canadian',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'fr_CH' =>  ['lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Swiss',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'fr_FR' =>  ['lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_France',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'fr_LU' =>  ['lang' => 'STRING_LANG_French', 'dialect' => 'STRING_DIALECT_Luxembourg',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'ga' =>     ['lang' => 'STRING_LANG_Irish', 'dialect' => 'STRING_DIALECT_Irish',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'gd' =>     ['lang' => 'STRING_LANG_Gaelic', 'dialect' => 'STRING_DIALECT_Scots',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'gd_IE' =>  ['lang' => 'STRING_LANG_Gaelic', 'dialect' => 'STRING_DIALECT_Irish',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'gl' =>     ['lang' => 'STRING_LANG_Galician', 'dialect' => 'STRING_DIALECT_Galician',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'he' =>     ['lang' => 'STRING_LANG_Hebrew', 'dialect' => 'STRING_DIALECT_Hebrew',                        'charset' => 'utf-8',  'dir' => 'rtl'],
		'hi' =>     ['lang' => 'STRING_LANG_Hindi', 'dialect' => 'STRING_DIALECT_Hindi',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'hr' =>     ['lang' => 'STRING_LANG_Croatian', 'dialect' => 'STRING_DIALECT_Croatian',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'hu' =>     ['lang' => 'STRING_LANG_Hungarian', 'dialect' => 'STRING_DIALECT_Hungarian',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'hy' =>     ['lang' => 'STRING_LANG_Armenian - Armenia', 'dialect' => 'STRING_DIALECT_Armenian - Armenia',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'id' =>     ['lang' => 'STRING_LANG_Indonesian', 'dialect' => 'STRING_DIALECT_Indonesian',                    'charset' => 'utf-8',  'dir' => 'ltr'],
		'in' =>     ['lang' => 'STRING_LANG_Indonesian', 'dialect' => 'STRING_DIALECT_Indonesian',                    'charset' => 'utf-8',  'dir' => 'ltr'],
		'is' =>     ['lang' => 'STRING_LANG_Icelandic', 'dialect' => 'STRING_DIALECT_Icelandic',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'it' =>     ['lang' => 'STRING_LANG_Italian', 'dialect' => 'STRING_DIALECT_Italian',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'it_CH' =>  ['lang' => 'STRING_LANG_Italian (Swiss) ', 'dialect' => 'STRING_DIALECT_Italian (Swiss) ',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'ja' =>     ['lang' => 'STRING_LANG_Japanese', 'dialect' => 'STRING_DIALECT_Japanese',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'ko' =>     ['lang' => 'STRING_LANG_Korean', 'dialect' => 'STRING_DIALECT_Korean',                        'charset' => 'kr',     'dir' => 'ltr'],
		'ko_KP' =>  ['lang' => 'STRING_LANG_Korea', 'dialect' => 'STRING_DIALECT_North',                 'charset' => 'kr',     'dir' => 'ltr'],
		'ko_KR' =>  ['lang' => 'STRING_LANG_Korea', 'dialect' => 'STRING_DIALECT_South',                 'charset' => 'kr',     'dir' => 'ltr'],
		'lt' =>     ['lang' => 'STRING_LANG_Lithuanian', 'dialect' => 'STRING_DIALECT_Lithuanian',                    'charset' => 'utf-8',  'dir' => 'ltr'],
		'lv' =>     ['lang' => 'STRING_LANG_Latvian', 'dialect' => 'STRING_DIALECT_Latvian',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'mk' =>     ['lang' => 'STRING_LANG_FYRO Macedonian', 'dialect' => 'STRING_DIALECT_FYRO Macedonian',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'mk_MK' =>  ['lang' => 'STRING_LANG_Macedonian', 'dialect' => 'STRING_DIALECT_Macedonian',                    'charset' => 'utf-8',  'dir' => 'ltr'],
		'ms' =>     ['lang' => 'STRING_LANG_Malaysian', 'dialect' => 'STRING_DIALECT_Malaysian',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'mt' =>     ['lang' => 'STRING_LANG_Maltese', 'dialect' => 'STRING_DIALECT_Maltese',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'nb' =>     ['lang' => 'STRING_LANG_Norwegian Bokmal', 'dialect' => 'STRING_DIALECT_Norwegian Bokmal',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'nl' =>     ['lang' => 'STRING_LANG_Dutch', 'dialect' => 'STRING_DIALECT_Standard',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'nl_BE' =>  ['lang' => 'STRING_LANG_Dutch', 'dialect' => 'STRING_DIALECT_Belgium',               'charset' => 'utf-8',  'dir' => 'ltr'],
		'nn' =>     ['lang' => 'STRING_LANG_Norwegian Nynorsk', 'dialect' => 'STRING_DIALECT_Norwegian Nynorsk',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'no' =>     ['lang' => 'STRING_LANG_Norwegian', 'dialect' => 'STRING_DIALECT_Norwegian',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'pl' =>     ['lang' => 'STRING_LANG_Polish', 'dialect' => 'STRING_DIALECT_Polish',                        'charset' => 'utf-8',  'dir' => 'ltr'],
		'pt' =>     ['lang' => 'STRING_LANG_Portuguese', 'dialect' => 'STRING_DIALECT_Portugal',         'charset' => 'utf-8',  'dir' => 'ltr'],
		'pt_BR' =>  ['lang' => 'STRING_LANG_Portuguese', 'dialect' => 'STRING_DIALECT_Brazil',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'rm' =>     ['lang' => 'STRING_LANG_Rhaeto Romanic', 'dialect' => 'STRING_DIALECT_Rhaeto Romanic',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'ro' =>     ['lang' => 'STRING_LANG_Romanian', 'dialect' => 'STRING_DIALECT_Romanian',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'ro_MO' =>  ['lang' => 'STRING_LANG_Romanian', 'dialect' => 'STRING_DIALECT_Moldavia',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'ru' =>     ['lang' => 'STRING_LANG_Russian', 'dialect' => 'STRING_DIALECT_Russian',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'ru_MO' =>  ['lang' => 'STRING_LANG_Russian', 'dialect' => 'STRING_DIALECT_Moldavia',            'charset' => 'utf-8',  'dir' => 'ltr'],
		'sb' =>     ['lang' => 'STRING_LANG_Sorbian', 'dialect' => 'STRING_DIALECT_Sorbian',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'sk' =>     ['lang' => 'STRING_LANG_Slovak', 'dialect' => 'STRING_DIALECT_Slovak',                        'charset' => 'utf-8',  'dir' => 'ltr'],
		'sl' =>     ['lang' => 'STRING_LANG_Slovenian', 'dialect' => 'STRING_DIALECT_Slovenian',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'sq' =>     ['lang' => 'STRING_LANG_Albanian', 'dialect' => 'STRING_DIALECT_Albanian',                      'charset' => 'utf-8',  'dir' => 'ltr'],
		'sr' =>     ['lang' => 'STRING_LANG_Serbian', 'dialect' => 'STRING_DIALECT_Serbian',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'sv' =>     ['lang' => 'STRING_LANG_Swedish', 'dialect' => 'STRING_DIALECT_Swedish',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'sv_FI' =>  ['lang' => 'STRING_LANG_Swedish', 'dialect' => 'STRING_DIALECT_Finland',             'charset' => 'utf-8',  'dir' => 'ltr'],
		'sx' =>     ['lang' => 'STRING_LANG_Sutu', 'dialect' => 'STRING_DIALECT_Sutu',                          'charset' => 'utf-8',  'dir' => 'ltr'],
		'sz' =>     ['lang' => 'STRING_LANG_Sami', 'dialect' => 'STRING_DIALECT_Lappish',                'charset' => 'utf-8',  'dir' => 'ltr'],
		'th' =>     ['lang' => 'STRING_LANG_Thai', 'dialect' => 'STRING_DIALECT_Thai',                          'charset' => 'utf-8',  'dir' => 'ltr'],
		'tn' =>     ['lang' => 'STRING_LANG_Tswana', 'dialect' => 'STRING_DIALECT_Tswana',                        'charset' => 'utf-8',  'dir' => 'ltr'],
		'tr' =>     ['lang' => 'STRING_LANG_Turkish', 'dialect' => 'STRING_DIALECT_Turkish',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'ts' =>     ['lang' => 'STRING_LANG_Tsonga', 'dialect' => 'STRING_DIALECT_Tsonga',                        'charset' => 'utf-8',  'dir' => 'ltr'],
		'uk' =>     ['lang' => 'STRING_LANG_Ukrainian', 'dialect' => 'STRING_DIALECT_Ukrainian',                     'charset' => 'utf-8',  'dir' => 'ltr'],
		'ur' =>     ['lang' => 'STRING_LANG_Urdu', 'dialect' => 'STRING_DIALECT_Urdu',                          'charset' => 'utf-8',  'dir' => 'rtl'],
		've' =>     ['lang' => 'STRING_LANG_Venda', 'dialect' => 'STRING_DIALECT_Venda',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'vi' =>     ['lang' => 'STRING_LANG_Vietnamese', 'dialect' => 'STRING_DIALECT_Vietnamese',                    'charset' => 'utf-8',  'dir' => 'ltr'],
		'cy' =>     ['lang' => 'STRING_LANG_Welsh', 'dialect' => 'STRING_DIALECT_Welsh',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'xh' =>     ['lang' => 'STRING_LANG_Xhosa', 'dialect' => 'STRING_DIALECT_Xhosa',                         'charset' => 'utf-8',  'dir' => 'ltr'],
		'yi' =>     ['lang' => 'STRING_LANG_Yiddish', 'dialect' => 'STRING_DIALECT_Yiddish',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'zh' =>     ['lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Chinese',                       'charset' => 'utf-8',  'dir' => 'ltr'],
		'zh_CN' =>  ['lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_PRC',                 'charset' => 'GB2312', 'dir' => 'ltr'],
		'zh_HK' =>  ['lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Hong Kong',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'zh_SG' =>  ['lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Singapore',           'charset' => 'utf-8',  'dir' => 'ltr'],
		'zh_TW' =>  ['lang' => 'STRING_LANG_Chinese', 'dialect' => 'STRING_DIALECT_Taiwan',              'charset' => 'utf-8',  'dir' => 'ltr'],
		'zu' =>     ['lang' => 'STRING_LANG_Zulu', 'dialect' => 'STRING_DIALECT_Zulu',                          'charset' => 'utf-8',  'dir' => 'ltr']
	];

	public static function Init(){

		if(self::$IsLoaded){
			return;
		}

		self::$DefaultLanguage = \ConfigHandler::Get('/core/language/site_default');

		// What locales are currently available on the system?
		$localesAvailable = self::GetLocalesAvailable();
		// The first value is all I want, as that is the user's preference.
		$preferred = \PageRequest::GetSystemRequest()->getPreferredLocale();

		// If this language is not available on the local system, then revert back to the system default!
		if(!isset($localesAvailable[$preferred])){
			$preferred = self::$DefaultLanguage;
		}

		$preferredAlt = $preferred . '.utf-8'; // Try to allow for variations of the different charsets.
		                                       // We don't actually care too much about which charset is used.
		// With this preferred value, set PHP's preference so its internal functions have the correct language.
		$res1 = setlocale(LC_COLLATE, $preferred, $preferredAlt);
		$res2 = setlocale(LC_CTYPE, $preferred, $preferredAlt);
		$res3 = setlocale(LC_NUMERIC, $preferred, $preferredAlt);
		$res4 = setlocale(LC_TIME, $preferred, $preferredAlt);
		$res5 = setlocale(LC_MESSAGES, $preferred, $preferredAlt);

		// DEBUG var_dump($preferred, $localesAvailable, $res1, $res2, $res3, $res4, $res5); die();
		// Currency does not get set to follow the user's preference, as the site admin determines what format to save and display currencies in.

		// Remember what the user's preferred language is so that I don't have to query the systemRequest again
		self::$UserLanguage = $preferred;

		// Cache this so number_format and money_format have the data available.
		self::$LocaleConv = localeconv();

		self::$IsLoaded = true;

		$cachekey = 'core-i18n-strings';
		$cached = Cache::Get($cachekey, 604800); // Cache here is good for one week.
		if(!DEVELOPMENT_MODE && $cached){
			// If the site is NOT in development mode and there is a valid cache, return the cached version!
			// The development mode check is to allow devs to update i18n strings without purging cache every two minutes.
			self::$Strings = $cached;
			return;
		}

		$files = [];
		$dirChecks = [];

		\Core\log_verbose('I18NLoader: Scanning components for strings.yml files');
		foreach(\Core::GetComponents() as $c){
			/** @var \Component_2_1 $c */
			if($c->getName() == 'core'){
				$dir = ROOT_PDIR . 'core/i18n/';
			}
			else{
				$dir = $c->getBaseDir() . 'i18n/';
			}

			$dirChecks[] = $dir;
		}

		// Include the active theme and custom overrides
		$t = \ConfigHandler::Get('/theme/selected');
		$dirChecks[] = ROOT_PDIR . 'themes/' . $t . '/i18n/';
		$dirChecks[] = ROOT_PDIR . 'themes/custom/i18n/';

		foreach($dirChecks as $dir){
			if( is_dir($dir) && file_exists($dir . 'strings.yml') && is_readable($dir . 'strings.yml')){
				$files[] = $dir . 'strings.yml';
			}
		}
		
		\Core\log_verbose('I18NLoader: ' . sizeof($files) . ' strings.yml files located!');
		self::$Strings = [];

		foreach($files as $f){
			$yml = new \Spyc();
			$r = $yml->loadFile($f);
			\Core\log_verbose('I18NLoader: Spyc loaded ' . $f);
			foreach($r as $k => $dat){
				foreach($dat as $lang => $s){
					// k is the string KEY
					// lang is the language it's set to
					// s is the string's value for this language.
					if(!isset(self::$Strings[$lang])){
						self::$Strings[$lang] = [];
					}
					
					self::$Strings[$lang][$k] = $s;
				}
			}
		}

		Cache::Set($cachekey, self::$Strings, 604800); // Cache here is good for one week.
	}

	/**
	 * Lookup a translation string with the requested language.
	 *
	 * Will return an array with the located string, (if any), and some useful metadata for the lookup.
	 *
	 * @param string $key
	 * @param string|null $lang
	 *
	 * @return array
	 */
	public static function Get($key, $lang = null){

		// Ensure that this system has been loaded
		// Will simply return if already loaded.
		self::Init();

		if($lang === null){
			// Set to the user-preferred language.
			$lang = self::$UserLanguage;
		}

		if(strpos($lang, '_') !== false){
			// Determine the base language as a fallback.
			$base = substr($lang, 0, strpos($lang, '_'));
		}
		else{
			$base = null;
		}

		$match    = self::_SearchForString($key, $lang);
		$default  = self::_SearchForString($key, self::$DefaultLanguage);
		$fallback = self::_SearchForString($key, self::$FallbackLanguage);

		$result = [
			'key'       => $key,
			'lang'      => $lang,
			'found'     => $match['string'] !== null,
			'match_key' => $match['locale'],
			'match_str' => $match['string'],
		    'results'   => [
			    'DEFAULT'   => $default['string'],
			    'FALLBACK'  => $fallback['string'],
		    ],
		];

		// Ensure that something is returned!
		if($result['match_str'] === null && $default['string'] !== null){
			$result['match_key'] = $default['locale'];
			$result['match_str'] = $default['string'];
		}
		elseif($result['match_str'] === null && $fallback['string'] !== null){
			$result['match_key'] = $fallback['locale'];
			$result['match_str'] = $fallback['string'];
		}

		// Load in all the versions of this string that are available in the system!
		// This may be required because the calling script may be inquiring as to what all is available.
		$locales = self::GetLocalesAvailable();
		foreach($locales as $k => $dat){
			$result['results'][$k] = self::_SearchForString($key, $k)['string'];
		}

		return $result;
	}

	public static function FormatNumber($number, $precision = 0){
		self::Init();

		return number_format($number, $precision, self::$LocaleConv['decimal_point'], self::$LocaleConv['thousands_sep']);
	}

	public static function GetLocaleConv($option){
		self::Init();

		return isset(self::$LocaleConv[$option]) ? self::$LocaleConv[$option] : null;
	}

	/**
	 * Get the user's currently selected language/locale.
	 *
	 * @return string
	 */
	public static function GetUsersLanguage(){
		self::Init();

		return self::$UserLanguage;
	}

	/**
	 * Get the fallback language
	 *
	 * @return string
	 */
	public static function GetFallbackLanguage(){
		self::Init();

		return self::$FallbackLanguage;
	}

	/**
	 * Get just the base languages available, (without dialects), as a valid optionset.
	 *
	 * @return array
	 */
	public static function GetBaseLanguagesAsOptions(){
		$localesAvailable = self::GetLocalesEnabled();
		$ret = [];
		foreach($localesAvailable as $k => $d){
			$base = strpos($k, '_') === false ? $k : substr($k, 0, strpos($k, '_'));
			$ret[$base] = t($d['lang']);
		}
		return $ret;
	}

	/**
	 * Get the languages available on this system as form options
	 *
	 * @return array
	 */
	public static function GetLanguagesAsOptions(){
		$localesAvailable = self::GetLocalesEnabled();
		$ret = [];
		foreach($localesAvailable as $k => $d){
			$ret[$k] = t($d['lang']) . ($d['dialect'] ? ' (' . t($d['dialect']) . ')' : '');
		}
		return $ret;
	}

	/**
	 * Get an array of locales currently available on the base system.
	 *
	 * @return array
	 */
	public static function GetLocalesAvailable(){
		$cacheKey = 'core-i18n-locales';
		$cacheTime = DEVELOPMENT_MODE ? 3600 : 604800;
		$cached = Cache::Get($cacheKey, $cacheTime);
		if($cached){
			return $cached;
		}

		exec('locale -a', $output);
		$locales = [];
		foreach($output as $line){
			if($line == 'C' || $line == 'POSIX' || $line == 'C.UTF-8'){
				// Yeah, skip these!
				continue;
			}
			if(($dotpos = strpos($line, '.')) !== false){
				// Trim anything after the ".", this is the charset which we, (in theory), don't need.
				// ex: .UTF-8 or .UTF-16.
				$line = substr($line, 0, $dotpos);
			}
			
			if(isset(self::$Languages[$line])){
				$locales[$line] = self::$Languages[$line];
			}
		}

		// Cache this so I don't have to execute the command and lookup the values all over again!
		Cache::Set($cacheKey, $locales, $cacheTime);
		return $locales;
	}

	/**
	 * Get an array (locale => [lang, dialect, charset, dir]) of all locales enabled on the system,
	 * XORed from the list of currently available locales on the native system.
	 * 
	 * @return array
	 */
	public static function GetLocalesEnabled(){
		$all = self::GetLocalesAvailable();

		$enabled = \ConfigHandler::Get('/core/language/languages_enabled');
		// This is expected to be a pipe-seperated list of languages/locales enabled.
		$enabled = array_map('trim', explode('|', $enabled));
		
		// Return any enabled locale as long as it's available on the system.
		// Remap them to an array to ensure that the locale description/label is returned too.
		// This comes from the original GetLocalesAvaiable method.
		$out = [];
		foreach($enabled as $v){
			if(isset($all[$v])){
				$out[$v] = $all[$v];
			}
		}
		
		return $out;
	}

	/**
	 * Method to get all strings currently loaded in the system
	 *
	 * @return array
	 */
	public static function GetAllStrings($lang = null){
		self::Init();

		if($lang === null){
			$lang = self::$UserLanguage;
		}

		$return = [];

		// I need to run through each string one-by-one because a requested locale may not have all the included strings!
		$strings = self::$Strings[self::$FallbackLanguage];
		foreach($strings as $key => $str){
			$res = self::Get($key, $lang);

			$return[] = $res;
		}

		return $return;
	}


	/**
	 * "Keyify" a given string, usually from a /config or /permission directive.
	 *
	 * This will capitalize the string, replace all "/" characters to "_", and ltrim any prepending "_".
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function KeyifyString($string){
		return ltrim(str_replace('/', '_', strtoupper($string)), '_');
	}

	/**
	 * Search for a string given a set language.
	 *
	 * Will match an exact locale key and the base language if that's not found.
	 *
	 * @param string $string The string to find
	 * @param string $lang   The language to search on
	 *
	 * @return array
	 */
	protected static function _SearchForString($string, $lang){
		
		if(!isset(self::$_StringLookupCache[$lang])){
			self::$_StringLookupCache[$lang] = [];
		}
		
		// Cached copy?
		if(isset(self::$_StringLookupCache[$lang][$string])){
			return self::$_StringLookupCache[$lang][$string];
		}
		
		if(strpos($lang, '_') !== false){
			// Determine the base language as a fallback.
			// This is to allow en_US to match anything in en if an exact match isn't available.
			// Because having duplicate entries for en_US and en_GB is just silly!
			$base = substr($lang, 0, strpos($lang, '_'));
		}
		else{
			$base = null;
		}

		if(isset(self::$Strings[$lang]) && isset(self::$Strings[$lang][$string]) && self::$Strings[$lang][$string] !== ''){
			self::$_StringLookupCache[$lang][$string] = [
				'locale' => $lang,
				'string' => self::$Strings[$lang][$string],
			]; 
		}
		elseif($base && isset(self::$Strings[$base]) && isset(self::$Strings[$base][$string]) && self::$Strings[$base][$string] !== ''){
			self::$_StringLookupCache[$lang][$string] = [
				'locale' => $base,
				'string' => self::$Strings[$base][$string],
			];
		}
		else{
			self::$_StringLookupCache[$lang][$string] = [
				'locale' => null,
			    'string' => null,
			];
		}
		
		return self::$_StringLookupCache[$lang][$string];
	}
} 