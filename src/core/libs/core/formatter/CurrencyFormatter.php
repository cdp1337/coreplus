<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
