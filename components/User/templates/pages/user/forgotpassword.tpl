{if $step == 1}
	<p>Enter your email to be sent a link which will allow you to reset the password.</p>
	<form action="" method="POST">
		<div class="formelement formtextinput">
			<input type="text" name="email"/>
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