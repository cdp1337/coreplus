<div id="tabs-group">
	<ul>
		<li>
			<a href="#email-config-group" class="formtabsgroup-tab-link"><span>{t 'STRING_CONFIGURATION'}</span></a>
		</li>
		<li>
			<a href="#email-test-group" class="formtabsgroup-tab-link"><span>{t 'STRING_TEST'}</span></a>
		</li>
	</ul>

	<div id="email-config-group">
		{$form->render()}
	</div>

	<div id="email-test-group">
		{if $email_enabled}
			<p class="message-tutorial">
				{t 'MESSAGE_PLEASE_SAVE_EMAIL_CONFIGURATION_TUTORIAL_TO_TEST'}
			</p>

			<form action="{link '/admin/email/test'}" method="post" target="test-log" id="test-form">
				<input type="text" placeholder="{t 'STRING_EMAIL_DESTINATION'}" name="email"/>
				<input type="submit" value="{t 'STRING_TEST'}"/>
			</form>
			<br/>

			{progress_log_iframe name="test-log" form="test-form"}
		{else}
			<p class="message-error">
				{t 'MESSAGE_EMAIL_SENDING_DISABLED_TEST_NOT_POSSIBLE'}
			</p>
		{/if}
	</div>
</div>

{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
		$('#tabs-group').tabs();
		
		// Hide options based on what's selected.
		var $mailer        = $('#formselectinput-config-core-email-mailer'),
			$smtpAuth      = $('#formselectinput-config-core-email-smtpauth'),
			$smtpSecurity  = $('#formselectinput-config-core-email-smtpsecurity'),
			$smtpPort      = $('#formtextinput-config-core-email-smtpport'),
			$sendmails     = $('.formtextinput-config-core-email-sendmailpath'),
			$smtpDomain    = $('.formtextinput-config-core-email-smtpdomain'),
			$smtpAuthOther = $(
				'.formtextinput-config-core-email-smtpuser,' +
				'.formtextinput-config-core-email-smtppassword'
			),
			$smtps =  $(
				'.formselectinput-config-core-email-smtpauth,' + 
				'.formtextinput-config-core-email-smtphost,' + 
				'.formtextinput-config-core-email-smtpdomain,' +
				'.formtextinput-config-core-email-smtpuser,' +
				'.formtextinput-config-core-email-smtppassword,' +
				'.formtextinput-config-core-email-smtpport,' +
				'.formselectinput-config-core-email-smtpsecurity'
			);
		
		$mailer.change(function() {
			var v = $(this).val();
			if(v == 'mail'){
				$sendmails.hide();
				$smtps.hide();
				$smtpAuth.val('NONE');
			}
			else if(v == 'smtp'){
				$sendmails.hide();
				$smtps.show();
				
				$smtpAuth.change();
			}
			else if(v == 'sendmail'){
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

