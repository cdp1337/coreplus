<?php


class Email{

	/**
	 * The template to render this email with.
	 * 
	 * @var Template
	 */
	private $_template = null;
	
	/**
	 * The mailer object to send this email with.
	 * 
	 * @var PHPMailer
	 */
	private $_mailer;

	/**
	 * The template to render this view with.
	 * Should be the partial path of the template, including emails/
	 * 
	 * @example emails/mycomponent/thishappened.tpl
	 * @var string
	 */
	public $templatename;
	
	
	public function __construct(){
		
	}
	
	
	/**
	 * Get the template responsible for rendering this email.
	 * 
	 * @return Template
	 */
	public function getTemplate(){
		if(!$this->_template){
			$this->_template = new Template();
			//$this->_template->setBaseURL($this->baseurl);
		}

		return $this->_template;
	}
	
	/**
	 * Get the mailer responsible for sending this email.
	 * 
	 * @return PHPMailer
	 */
	public function getMailer(){
		if(!$this->_mailer){
			$this->_mailer = new PHPMailer(true);
			
			// Load in some default options for this email based on the configuration options.
			$this->_mailer->From = ConfigHandler::GetValue('/core/email/from');
			if(!$this->_mailer->From) $this->_mailer->From = 'website@' . $_SERVER['HTTP_HOST'];
			
			$this->_mailer->FromName = ConfigHandler::GetValue('/core/email/from_name');
			$this->_mailer->Mailer = ConfigHandler::GetValue('/core/email/mailer');
			$this->_mailer->Sendmail = ConfigHandler::GetValue('/core/email/sendmail_path');
			if($this->_mailer->Mailer == 'smtp'){
				$this->_mailer->Host = ConfigHandler::GetValue('/core/email/smtp_host');
				$this->_mailer->Port = ConfigHandler::GetValue('/core/email/smtp_port');
				$this->_mailer->Username = ConfigHandler::GetValue('/core/email/smtp_user');
				$this->_mailer->Password = ConfigHandler::GetValue('/core/email/smtp_password');
				if($this->_mailer->Username != '') $this->SMTPAuth = true;
			}
			
			// Tack on some anti-abuse headers.
			// If these bug you, feel free to safely remove them, as they don't /actually/ do anything.
			// @todo If this bugs enough people, I might add it as a config option.
			
			$this->_mailer->AddCustomHeader('X-AntiAbuse: This header was added to track abuse, please include it with any abuse report');
			if(Core::User()->exists()){
				$this->_mailer->AddCustomHeader('X-AntiAbuse: User_id - ' . Core::User()->get('id'));
				$this->_mailer->AddCustomHeader('X-AntiAbuse: User_name - ' . Core::User()->getDisplayName());
			}
			
			$this->_mailer->AddCustomHeader('X-AntiAbuse: Original Domain - ' . SERVERNAME);
			$this->_mailer->AddCustomHeader('X-AntiAbuse: Sitename - ' . SITENAME);
			$this->_mailer->AddCustomHeader('MimeOLE: Core Plus');
			
			// Application version stamp.  For security reasons only attach when in DEV mode.
			if(DEVELOPMENT_MODE) $this->_mailer->AddCustomHeader('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());

			// Default to set the formatting to HTML.
			//$this->_mailer->isHTML(true);
		}
		
		return $this->_mailer;
	}




	/**
	 * Assign a value to this emails' template.
	 * 
	 * Just serves as a pass-through for the Template::assign() method.
	 * 
	 * @param string $key
	 * @param mixed $val 
	 */
	public function assign($key, $val){
		$this->getTemplate()->assign($key, $val);
	}
	
	/**
	 * Get the rendered body (taking the template into consideration)
	 * 
	 * @return string (HTML or plain text) 
	 */
	public function renderBody(){
		if($this->templatename){
			return $this->getTemplate()->fetch($this->templatename);
		}
		else{
			// I can't render a template with no template....
			return $this->getMailer()->Body;
		}
	}
	
	/**
	 * Set the "to" address for this email.
	 * 
	 * Will clear out any other to address.
	 * 
	 * @param string $address
	 * @param string $name 
	 */
	public function to($address, $name = ''){
		// Reset any "to" address already on the mailer.
		$m = $this->getMailer();
		$m->ClearAddresses();
		$m->AddAddress($address, $name);
	}
	
	/**
	 * Add a "to" address for this email.
	 * 
	 * Can be called multiple times for sending to multiple people, will
	 * add addresses to the "to" recipient on each call.
	 * 
	 * @param string $address
	 * @param string $name 
	 */
	public function addAddress($address, $name = ''){
		$this->getMailer()->AddAddress($address, $name);
	}
	
	/**
	 * Set the body for this email.
	 * 
	 * This is typically not used, as the Template system should be used whenever possible,
	 * but this is available for simple emails, ie: administrative "IT BROKE!" emails.
	 * 
	 * @param string $body 
	 * @param boolean $ishtml Set to true if the $body is HTML.
	 */
	public function setBody($body, $ishtml = false){
		$m = $this->getMailer();
		
		// Is the body HTML and there's already a non-HTML body?
		if($ishtml){
			if($m->ContentType == 'text/plain' && $m->Body) $m->AltBody = $m->Body; // Switch it!
			$m->IsHTML(true);
			$m->Body = $body;
		}
		else{
			// If the mailer is already an HTML email, set this on the ALT body.
			// Otherwise it'll be the regular body.
			if($m->ContentType == 'text/html') $m->AltBody = $body;
			else $m->Body = $body;
		}
	}
	
	/**
	 * Set the subject of this email.
	 * 
	 * @param string $subject 
	 */
	public function setSubject($subject){
		$this->getMailer()->Subject = $subject;
	}

	/**
	 * Send the message
	 * 
	 * @throws phpmailerException
	 * @return type 
	 */
	public function send(){
		// Now, do some formatting to the body, ie: it NEEDS an alt body!
		//if(!$this->AltBody && $this->ContentType == 'text/html'){
		//	$this->MsgHTML($this->Body);
		//}
		
		// Set the body first.
		if($this->templatename){
			$this->setBody($this->getTemplate()->fetch($this->templatename), true);
		}

		return $this->getMailer()->Send();
	}
}