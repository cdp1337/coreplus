<?php
/**
 * whois registration file for us. TLD
 *
 * @package phpwhois
 *
 * @copyright 1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @author David Saez
 * @link http://www.phpwhois.org Original version of phpwhois
 *
 * @author Dmitry Lukashin <http://lukashin.ru/en/>
 * @link http://phpwhois.pw/ Revisited version of phpwhois
 *
 * @author Charlie Powell
 *
 * @license GPLv2
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace phpwhois\whois\domain;

class us_handler {

	private static $CountryCodes = [
		'Afghanistan' => 'AF',
		'Albania' => 'AL',
		'Algeria' => 'DZ',
		'American Samoa' => 'AS',
		'Andorra' => 'AD',
		'Angola' => 'AO',
		'Anguilla' => 'AI',
		'Antarctica' => 'AQ',
		'Antigua and Barbuda' => 'AG',
		'Argentina' => 'AR',
		'Armenia' => 'AM',
		'Aruba' => 'AW',
		'Australia' => 'AU',
		'Austria' => 'AT',
		'Azerbaijan' => 'AZ',
		'Bahamas' => 'BS',
		'Bahrain' => 'BH',
		'Bangladesh' => 'BD',
		'Barbados' => 'BB',
		'Belarus' => 'BY',
		'Belgium' => 'BE',
		'Belize' => 'BZ',
		'Benin' => 'BJ',
		'Bermuda' => 'BM',
		'Bhutan' => 'BT',
		'Bolivia' => 'BO',
		'Bosnia and Herzegovina' => 'BA',
		'Botswana' => 'BW',
		'Bouvet Island' => 'BV',
		'Brazil' => 'BR',
		'British Indian Ocean Territory' => 'IO',
		'British Virgin Islands' => 'VG',
		'Brunei' => 'BN',
		'Bulgaria' => 'BG',
		'Burkina Faso' => 'BF',
		'Burundi' => 'BI',
		'Cambodia' => 'KH',
		'Cameroon' => 'CM',
		'Canada' => 'CA',
		'Cape Verde' => 'CV',
		'Cayman Islands' => 'KY',
		'Central African Republic' => 'CF',
		'Chad' => 'TD',
		'Chile' => 'CL',
		'China' => 'CN',
		'Christmas Island' => 'CX',
		'Cocos Islands' => 'CC',
		'Colombia' => 'CO',
		'Comoros' => 'KM',
		'Congo' => 'CG',
		'Cook Islands' => 'CK',
		'Costa Rica	' => 'R',
		'Côte d\'Ivoire' => 'CI',
		'Croatia' => 'HR',
		'Cuba' => 'CU',
		'Cyprus' => 'CY',
		'Czech Republic' => 'CZ',
		'Democratic Republic of the Congo' => 'CD',
		'Denmark' => 'DK',
		'Djibouti' => 'DJ',
		'Dominica' => 'DM',
		'Dominican Republic' => 'DO',
		'Ecuador' => 'EC',
		'Egypt' => 'EG',
		'El Salvador' => 'SV',
		'Equatorial Guinea' => 'GQ',
		'Eritrea' => 'ER',
		'Estonia' => 'EE',
		'Ethiopia' => 'ET',
		'Faeroe Islands' => 'FO',
		'Falkland Islands' => 'FK',
		'Fiji' => 'FJ',
		'Finland' => 'FI',
		'France' => 'FR',
		'French Guiana' => 'GF',
		'French Polynesia' => 'PF',
		'French Southern Territories' => 'TF',
		'Gabon' => 'GA',
		'Gambia' => 'GM',
		'Georgia' => 'GE',
		'Germany' => 'DE',
		'Ghana' => 'GH',
		'Gibraltar' => 'GI',
		'Greece' => 'GR',
		'Greenland' => 'GL',
		'Grenada' => 'GD',
		'Guadeloupe' => 'GP',
		'Guam' => 'GU',
		'Guatemala' => 'GT',
		'Guinea' => 'GN',
		'Guinea-Bissau' => 'GW',
		'Guyana' => 'GY',
		'Haiti' => 'HT',
		'Heard Island and McDonald Island' => 'HM',
		'Honduras' => 'HN',
		'Hong Kong' => 'HK',
		'Hungary' => 'HU',
		'Iceland' => 'IS',
		'India' => 'IN',
		'Indonesia' => 'ID',
		'Iran' => 'IR',
		'Iraq' => 'IQ',
		'Ireland' => 'IE',
		'Israel' => 'IL',
		'Italy' => 'IT',
		'Jamaica' => 'JM',
		'Japan' => 'JP',
		'Jordan' => 'JO',
		'Kazakhstan' => 'KZ',
		'Kenya' => 'KE',
		'Kiribati' => 'KI',
		'Kuwait' => 'KW',
		'Kyrgyzstan' => 'KG',
		'Laos' => 'LA',
		'Latvia' => 'LV',
		'Lebanon' => 'LB',
		'Lesotho' => 'LS',
		'Liberia' => 'LR',
		'Libya' => 'LY',
		'Liechtenstein' => 'LI',
		'Lithuania' => 'LT',
		'Luxembourg' => 'LU',
		'Macau' => 'MO',
		'Macedonia' => 'MK',
		'Madagascar' => 'MG',
		'Malawi' => 'MW',
		'Malaysia' => 'MY',
		'Maldives' => 'MV',
		'Mali' => 'ML',
		'Malta' => 'MT',
		'Marshall Islands' => 'MH',
		'Martinique' => 'MQ',
		'Mauritania' => 'MR',
		'Mauritius' => 'MU',
		'Mayotte' => 'YT',
		'Mexico' => 'MX',
		'Micronesia' => 'FM',
		'Moldova' => 'MD',
		'Monaco' => 'MC',
		'Mongolia' => 'MN',
		'Montserrat' => 'MS',
		'Morocco' => 'MA',
		'Mozambique' => 'MZ',
		'Myanmar' => 'MM',
		'Namibia' => 'NA',
		'Nauru' => 'NR',
		'Nepal' => 'NP',
		'Netherlands' => 'NL',
		'Netherlands Antilles' => 'AN',
		'New Caledonia' => 'NC',
		'New Zealand' => 'NZ',
		'Nicaragua' => 'NI',
		'Niger' => 'NE',
		'Nigeria' => 'NG',
		'Niue' => 'NU',
		'Norfolk Island' => 'NF',
		'North Korea' => 'KP',
		'Northern Marianas' => 'MP',
		'Norway' => 'NO',
		'Oman' => 'OM',
		'Pakistan' => 'PK',
		'Palau' => 'PW',
		'Panama' => 'PA',
		'Papua New Guinea' => 'PG',
		'Paraguay' => 'PY',
		'Peru' => 'PE',
		'Philippines' => 'PH',
		'Pitcairn Islands' => 'PN',
		'Poland' => 'PL',
		'Portugal' => 'PT',
		'Puerto Rico' => 'PR',
		'Qatar' => 'QA',
		'Réunion' => 'RE',
		'Romania' => 'RO',
		'Russia' => 'RU',
		'Rwanda' => 'RW',
		'Saint Helena' => 'SH',
		'Saint Kitts and Nevis' => 'KN',
		'Saint Lucia' => 'LC',
		'Saint Pierre and Miquelon' => 'PM',
		'Saint Vincent and the Grenadines' => 'VC',
		'Samoa' => 'WS',
		'San Marino' => 'SM',
		'Saudi Arabia' => 'SA',
		'São Tomé and Príncipe' => 'ST',
		'Senegal' => 'SN',
		'Serbia and Montenegro' => 'CS',
		'Seychelles' => 'SC',
		'Sierra Leone' => 'SL',
		'Singapore' => 'SG',
		'Slovakia' => 'SK',
		'Slovenia' => 'SI',
		'Solomon Islands' => 'SB',
		'Somalia' => 'SO',
		'South Africa' => 'ZA',
		'South Georgia and the South Sand' => 'GS',
		'South Korea' => 'KR',
		'Spain' => 'ES',
		'Sri Lanka' => 'LK',
		'Sudan' => 'SD',
		'Suriname' => 'SR',
		'Svalbard and Jan Mayen' => 'SJ',
		'Swaziland' => 'SZ',
		'Sweden' => 'SE',
		'Switzerland' => 'CH',
		'Syria' => 'SY',
		'Taiwan' => 'TW',
		'Tajikistan' => 'TJ',
		'Tanzania' => 'TZ',
		'Thailand' => 'TH',
		'Timor-Leste' => 'TL',
		'Togo' => 'TG',
		'Tokelau' => 'TK',
		'Tonga' => 'TO',
		'Trinidad and Tobago' => 'TT',
		'Tunisia' => 'TN',
		'Turkey' => 'TR',
		'Turkmenistan' => 'TM',
		'Turks and Caicos Islands' => 'TC',
		'Tuvalu' => 'TV',
		'Uganda' => 'UG',
		'Ukraine' => 'UA',
		'United Arab Emirates' => 'AE',
		'United Kingdom' => 'GB',
		'United States' => 'US',
		'United States Minor Outlying Isl' => 'UM',
		'Uruguay' => 'UY',
		'US Virgin Islands' => 'VI',
		'Uzbekistan' => 'UZ',
		'Vanuatu' => 'VU',
		'Vatican City' => 'VA',
		'Venezuela' => 'VE',
		'Vietnam' => 'VN',
		'Wallis and Futuna' => 'WF',
		'Western Sahara' => 'EH',
		'Yemen' => 'YE',
		'Zambia' => 'ZM',
		'Zimbabwe' => 'ZW',
	];

	function parse($data_str, $query) {
		$r['regrinfo'] = \phpwhois\generic_parser_b($data_str['rawdata'], false, '-md--y');
		$r['regyinfo'] = [
			'referrer'  => 'http://www.neustar.us',
			'registrar' => 'NEUSTAR INC.'
		];

		$this->_countrycodes($r);

		return $r;
	}

	private function _countrycodes(&$array){
		foreach($array as $k => $val){
			if(is_array($val)){
				$this->_countrycodes($array[$k]);
			}
			elseif(
				$k == 'country'
				&& isset(self::$CountryCodes[$val])
			){
				$array[$k] = self::$CountryCodes[$val];
			}
		}
	}
}
