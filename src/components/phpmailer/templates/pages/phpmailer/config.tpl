{$form->render()}


{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
		// Hide options based on what's selected.
		var $mailer        = $('#formselectinput-config-core-email-mailer'),
			$smtpAuth      = $('#formselectinput-config-phpmailer-smtp-auth'),
			$smtpSecurity  = $('#formselectinput-config-phpmailer-smtp-security'),
			$smtpPort      = $('#formtextinput-config-phpmailer-smtp-port'),
			$sendmails     = $('.formtextinput-config-phpmailer-sendmail-path'),
			$smtpDomain    = $('.formtextinput-config-phpmailer-smtp-domain'),
			$smtpAuthOther = $(
				'.formtextinput-config-phpmailer-smtp-user,' +
				'.formtextinput-config-phpmailer-smtp-password'
			),
			$smtps =  $(
				'.formselectinput-config-phpmailer-smtp-auth,' + 
				'.formtextinput-config-phpmailer-smtp-host,' + 
				'.formtextinput-config-phpmailer-smtp-domain,' +
				'.formtextinput-config-phpmailer-smtp-user,' +
				'.formtextinput-config-phpmailer-smtp-password,' +
				'.formtextinput-config-phpmailer-smtp-port,' +
				'.formselectinput-config-phpmailer-smtp-security'
			);
		
		$mailer.change(function() {
			var v = $(this).val();
			if(v == 'PHPMailerCore\\Mail'){
				$sendmails.hide();
				$smtps.hide();
				$smtpAuth.val('NONE');
			}
			else if(v == 'PHPMailerCore\\SMTP'){
				$sendmails.hide();
				$smtps.show();
				
				$smtpAuth.change();
			}
			else if(v == 'PHPMailerCore\\Sendmail'){
				$sendmails.show();
				$smtps.hide();
				$smtpAuth.val('NONE');
			}
		}).change();

		$smtpAuth.change(function() {
			var v = $(this).val();
			
			if(v == 'NTLM'){
				$smtpDomain.show();
				$smtpAuthOther.show();
				
				if($smtpSecurity.val() == 'tls'){
					$smtpPort.val(25);
				}
			}
			else if(v == 'NONE'){
				$smtpDomain.hide();
				$smtpAuthOther.hide();
			}
			else{
				$smtpDomain.hide();
				$smtpAuthOther.show();
			}
		}).change();

		$smtpSecurity.change(function() {
			var v = $(this).val();
			if(v == 'none'){
				$smtpPort.val(25);
			}
			else if(v == 'ssl'){
				$smtpPort.val(465);
			}
			else if(v == 'tls' && $smtpAuth.val() == 'NTLM'){
				// Exchange uses TLS on the same port as NONE.
				$smtpPort.val(25);
			}
			else if(v == 'tls'){
				$smtpPort.val(587);
			}
		});
		
		if($smtpPort.val() == ''){
			// Set something by default!
			$smtpSecurity.change();
		}
	});
</script>{/script}