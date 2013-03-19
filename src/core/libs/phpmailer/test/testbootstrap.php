<?php
/**
 * This is called within the scope of phpmailerTest, so $this->Mail points to a valid mail object!
 *
 * @var $this phpmailerTest
 */



// Load in some default options for this email based on the configuration options.
$this->Mail->From = ConfigHandler::Get('/core/email/from');
if (!$this->Mail->From) $this->Mail->From = 'website@' . $_SERVER['HTTP_HOST'];

$this->Mail->FromName = ConfigHandler::Get('/core/email/from_name');
$this->Mail->Mailer   = ConfigHandler::Get('/core/email/mailer');

$this->Mail->Sendmail = ConfigHandler::Get('/core/email/sendmail_path');
if ($this->Mail->Mailer == 'smtp') {
	$this->Mail->Host       = ConfigHandler::Get('/core/email/smtp_host');
	$this->Mail->Port       = ConfigHandler::Get('/core/email/smtp_port');
	$this->Mail->Username   = ConfigHandler::Get('/core/email/smtp_user');
	$this->Mail->Password   = ConfigHandler::Get('/core/email/smtp_password');
	$this->Mail->SMTPSecure =
		(ConfigHandler::Get('/core/email/smtp_security') == 'none') ?
			'' : ConfigHandler::Get('/core/email/smtp_security');
	if ($this->Mail->Username != '') $this->Mail->SMTPAuth = true;
}

$this->SetAddress('root@localhost', 'Test User', 'to');
$this->Mail->Sender = $this->Mail->From;