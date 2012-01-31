<h2>Installation Options</h2>

<form action="" method="POST">
	<label for="filestore_backend">Filestore Backend</label>
	<select id="filestore_backend" name="/core/filestore/backend">
		<option value="local" {if("%/core/filestore/backend%" == "local")} checked='checked' {/if}>Local Traditional Storage</option>
		<option value="aws" {if("%/core/filestore/backend%" == "aws")}checked='checked'{/if}>Amazon Web Services S3 Bucket</option>
	</select>
	<p class="formhelp">This option controls where static assets such as images, javascript and stylesheets are loaded from.  If you choose Amazon, you'll need a valid AWS account with an S3 subscription.</p>
	
	<fieldset id="local-options">
		<legend> Local Options </legend>
		
		<p class="message-note">If you provide an FTP username and password, it will ensure that files are created with your user's permission.</p>
		
		<label for="ftp_username">FTP username</label>
		<input type="text" id="ftp_username" name="/core/ftp/username" value="%/core/ftp/username%"/>
		<p class="formhelp">FTP username to use when writing local files.</p>
		
		<label for="ftp_password">FTP password</label>
		<input type="password" id="ftp_password" name="/core/ftp/password" value="%/core/ftp/password%"/>
		<p class="formhelp">FTP password to use when writing local files.</p>
		
		<label for="ftp_path">Relative FTP Path</label>
		<input type="text" id="ftp_path" name="/core/ftp/path" value="%/core/ftp/path%"/>
		<p class="formhelp">Relative FTP path to use when writing local files.  Please ensure to end with a '/'.</p>
	</fieldset>
	
	<fieldset id="aws-options" style="display:none;">
		<legend> AWS Options </legend>
		
		<label for="aws_key">Amazon Web Services Key</label>
		<input type="text" id="aws_key" name="/core/aws/key" value="%/core/aws/key%"/>
		<p class="formhelp">Amazon Web Services Key. Found in the AWS Security Credentials.</p>
		
		<label for="aws_secretkey">Amazon Web Services Secret Key</label>
		<input type="password" id="aws_secretkey" name="/core/aws/secretkey" value="%/core/aws/secretkey%"/>
		<p class="formhelp">Amazon Web Services Secret Key. Found in the AWS Security Credentials.</p>
		
		<label for="aws_accountid">Amazon Account ID</label>
		<input type="text" id="aws_accountid" name="/core/aws/accountid" value="%/core/aws/accountid%"/>
		<p class="formhelp">Amazon Account ID without dashes. Used for identification with Amazon EC2. Found in the AWS Security Credentials.</p>
		
		<label for="aws_canonicalid">Canonical User ID</label>
		<input type="text" id="aws_canonicalid" name="/core/aws/canonicalid" value="%/core/aws/canonicalid%"/>
		<p class="formhelp">Your Canonical User ID. Used for setting access control settings in AmazonS3. Found in the AWS Security Credentials.</p>
		
		<label for="aws_canonicalname">Canonical User Display Name</label>
		<input type="text" id="aws_canonicalname" name="/core/aws/canonicalname" value="%/core/aws/canonicalname%"/>
		<p class="formhelp">Your Canonical User Display Name. Used for setting access control settings in AmazonS3. Found in the AWS Security Credentials (i.e. "Welcome, AWS_CANONICAL_NAME").</p>
		<!--
		<label for="aws_asset_bucket">Asset Bucket</label>
		<input type="text" id="aws_asset_bucket" name="/core/aws/asset_bucket" value="%/core/aws/asset_bucket%"/>
		<p class="formhelp">The bucket name to create.</p>
		-->
	</fieldset>
	
	<fieldset>
		<legend> Common Options </legend>
		
		<label for="asset_dir">Asset Directory</label>
		<input type="text" id="asset_dir" name="/core/filestore/assetdir" value="%/core/filestore/assetdir%"/>
		<p class="formhelp">The directory, (or bucket), to use for asset files such as stylesheets, javascript and images.  This should just be the directory relative to the installation directory.</p>
		
		<label for="public_dir">Public Directory</label>
		<input type="text" id="public_dir" name="/core/filestore/publicdir" value="%/core/filestore/publicdir%"/>
		<p class="formhelp">The directory, (or bucket), to use user-supplied uploads.  This should just be the directory relative to the installation directory.</p>
	</fieldset>
	
	<input type="hidden" name="mode" value="configs"/>
	<input type="submit" value="Save"/>
</form>


<script>
	$(function(){
		$('#filestore_backend').change(function(){
			switch($(this).val()){
				case 'local':
					$('#local-options').show();
					$('#aws-options').hide();
					break;
				case 'aws':
					$('#local-options').hide();
					$('#aws-options').show();
					break;
			}
		});
		
		$('#filestore_backend').change();
	});
</script>

