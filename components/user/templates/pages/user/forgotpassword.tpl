{if $step == 1}
	<p>Please enter your email address that is registered.  You will be sent an email containing instructions to reset your password.</p>
	<form action="" method="POST">
		<div class="formelement formtextinput">
			<label for="email-to-reset">Email</label>
			<input type="text" name="email" id="email-to-reset"/>
		</div>
		<div class="formelement formsubmitinput">
			<input type="submit" value="Send Reset Instructions"/>
		</div>
	</form>
{/if}
{if $step == 2}
	<p>Enter a new password</p>
	<form action="" method="POST">
		<div class="formelement formpasswordinput">
			<label>Password</label>
			<input type="password" name="p1"/>
		</div>
		
		<div class="formelement formpasswordinput">
			<label>Confirm</label>
			<input type="password" name="p2"/>
		</div>
		
		<div class="formelement formsubmitinput">
			<input type="submit" value="Set Password"/>
		</div>
	</form>
{/if}