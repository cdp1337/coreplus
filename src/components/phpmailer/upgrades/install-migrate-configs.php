<?php
/**
 * Install file to convert the Core email directives to phpmailer prefixed versions.
 */

$mappings = [
	'/core/email/sendmail_path' => '/phpmailer/sendmail/path',
	'/core/email/smtp_host'     => '/phpmailer/smtp/host',
	'/core/email/smtp_security' => '/phpmailer/smtp/security',
	'/core/email/smtp_port'     => '/phpmailer/smtp/port',
	'/core/email/smtp_auth'     => '/phpmailer/smtp/auth',
	'/core/email/smtp_domain'   => '/phpmailer/smtp/domain',
	'/core/email/smtp_user'     => '/phpmailer/smtp/user',
	'/core/email/smtp_password' => '/phpmailer/smtp/password',
];

foreach($mappings as $old => $new){
	\Core\log_info("Migrating config option " . $old . " to " . $new);
	$value = \ConfigHandler::Get($old);
	\ConfigHandler::Set($new, $value);
}

// Lastly, perform the change for the mailer type.
// This one doesn't get remapped, but it does get updated with the new location.
switch(\ConfigHandler::Get('/core/email/mailer')){
	case 'smtp':
		\Core\log_info('Switching Core emailer to PHPMailerCore\\SMTP');
		\ConfigHandler::Set('/core/email/mailer', 'PHPMailerCore\\SMTP');
		break;
	case 'sendmail':
		\Core\log_info('Switching Core emailer to PHPMailerCore\\Sendmail');
		\ConfigHandler::Set('/core/email/mailer', 'PHPMailerCore\\Sendmail');
		break;
	case 'mail':
		\Core\log_info('Switching Core emailer to PHPMailerCore\\Mail');
		\ConfigHandler::Set('/core/email/mailer', 'PHPMailerCore\\Mail');
		break;
	default:
		\Core\log_info('Skipping Core emailer translation.');
}