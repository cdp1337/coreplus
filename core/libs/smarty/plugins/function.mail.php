<?php
/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {mail} function plugin
 *
 * Type:     function<br>
 * Name:     mail<br>
 * Date:     August 1, 2012
 * Purpose:  automate mailto address link creation, and optionally encode them.<br>
 * Params:
 * <pre>
 * - address    - (required) - e-mail address
 * - text       - (optional) - text to display, default is address
 * - encode     - (optional) - can be one of:
 *                             * none : no encoding (default)
 *                             * javascript : encode with javascript
 *                             * javascript_charcode : encode with javascript charcode
 *                             * hex : encode with hexidecimal (no javascript)
 * - cc         - (optional) - address(es) to carbon copy
 * - bcc        - (optional) - address(es) to blind carbon copy
 * - subject    - (optional) - e-mail subject
 * - newsgroups - (optional) - newsgroup(s) to post to
 * - followupto - (optional) - address(es) to follow up to
 * - extra      - (optional) - extra tags for the href link
 * </pre>
 * Examples:
 * <pre>
 * {mail address="me@domain.com"}
 * </pre>
 *
 * @link http://www.smarty.net/manual/en/language.function.mailto.php {mailto}
 *          (Smarty online manual)
 * @version 1.2
 * @author Monte Ohrt <monte at ohrt dot com>
 * @author credits to Jason Sweat (added cc, bcc and subject functionality)
 * @param array                    $params   parameters
 * @param Smarty_Internal_Template $template template object
 * @return string
 */
function smarty_function_mail($params, $template)
{
    static $_allowed_encoding = array('javascript' => true, 'javascript_charcode' => true, 'hex' => true, 'none' => true);
    $extra = '';

    if (empty($params['address'])) {
        trigger_error("mail: missing 'address' parameter",E_USER_WARNING);
        return;
    } else {
        $address = $params['address'];
    }

    $text = $address;
    // netscape and mozilla do not decode %40 (@) in BCC field (bug?)
    // so, don't encode it.
    $search = array('%40', '%2C');
    $replace = array('@', ',');
    $mail_parms = array();
    foreach ($params as $var => $value) {
        switch ($var) {
            case 'cc':
            case 'bcc':
            case 'followupto':
                if (!empty($value))
                    $mail_parms[] = $var . '=' . str_replace($search, $replace, rawurlencode($value));
                break;

            case 'subject':
            case 'newsgroups':
                $mail_parms[] = $var . '=' . rawurlencode($value);
                break;

            case 'extra':
            case 'text':
                $$var = $value;

            default:
        }
    }

    if ($mail_parms) {
        $address .= '?' . join('&', $mail_parms);
    }
    
    $encode = (empty($params['encode'])) ? 'none' : $params['encode'];
	$elementid = 'email-' . Core::RandomHex(10);

	$user = str_rot13( substr($address, 0, strpos($address, '@')) );
	$tld = substr($address, strrpos($address, '.') + 1);
	$domain = substr($address, strlen($user) + 1, 0 - (strlen($tld) + 1));

	$return = '<a href="#" id="' . $elementid . '" user="'.$user.'" domain="'.$domain.'" tld="'.$tld.'"></a>';
	$return .='<script>Core.Email.assemble($("#' . $elementid . '"));</script>';

	// Set this page to require the script library core strings!
	Core::_AttachCoreStrings();

	return $return;

        $js_encode = '';
        for ($x = 0, $_length = strlen($string); $x < $_length; $x++) {
            $js_encode .= '%' . bin2hex($string[$x]);
        }

}

?>