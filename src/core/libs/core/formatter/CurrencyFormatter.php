<?php
/**
 * DESCRIPTION
 *
 * @package Core\Formatter
 * @since 2.5.6
 * @author Charlie Powell <charlie@evalagency.com>
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

namespace Core\Formatter;

/**
 * Description of CurrencyFormatter
 *
 * @author charlie
 */
class CurrencyFormatter {
	public static function USD($value, $format = \View::CTYPE_HTML){
		return '$' . number_format($value, 2, t('FORMAT_DECIMAL_POINT'), t('FORMAT_THOUSANDS_SEP'));
	}
	
	public static function EUR($value, $format = \View::CTYPE_HTML){
		return '€' . number_format($value, 2, t('FORMAT_DECIMAL_POINT'), t('FORMAT_THOUSANDS_SEP'));
	}
	
	public static function BTC($value, $format = \View::CTYPE_HTML){
		return number_format($value, 8, t('FORMAT_DECIMAL_POINT'), '') . ' ₿';
	}
	
	public static function GBP($value, $format = \View::CTYPE_HTML){
		return '£' . number_format($value, 2, t('FORMAT_DECIMAL_POINT'), t('FORMAT_THOUSANDS_SEP'));
	}
}
