<div class='ayah-col-right'>
	
	<div class='ayah-box'>
		<div class='inside'>
			<h2>Need Help?</h2>
			<p>We&rsquo;d be more than happy to help you get set up. <a href="http://support.areyouahuman.com" target="_blank">Contact our friendly Support Humans here</a>.</p>
			
		</div><!--inside-->
	</div><!--ayah-box-->

	<div class='ayah-box'>
		<div class='inside'>
			<h2>Spread the Word!</h2>
			<p>We&rsquo;re a small company, and we grow mostly by word-of-mouth. If you like PlayThru, please help us out by <a href="http://areyouahuman.com/spread-the-word/" target="_blank">spreading the word</a>!</p>
			
		</div><!--inside-->
	</div><!--ayah-box-->
	
</div><!--ayah-col-right-->

<div class='ayah-col-left'>
	<div class='ayah-box'>
		<div class='inside'>
			<p>Select where you&rsquo;d like PlayThru to appear on your site.</p>
			<form action="<?php echo admin_url( 'options-general.php?page=are-you-a-human'); ?>" method='POST' id='playthru-options'>
				<fieldset>
					<label>Enable PlayThru on:</label>
					<p><input type='checkbox' name='ayah[enable_register_form]' value='1' <?php echo $chk_enable_register_form; ?> /> Registration Form</p>
					<p><input type='checkbox' name='ayah[enable_lost_password_form]' value='1' <?php echo $chk_enable_lost_password_form; ?> /> Lost Password Form</p>
					<p><input type='checkbox' name='ayah[enable_comment_form]' value='1' <?php echo $chk_enable_comment_form; ?> /> Comment Form</p>
				</fieldset>
				<fieldset>
					<label>Hide from registered users?</label>
					<p><input type='checkbox' name='ayah[hide_registered_users]' value='1' <?php echo $chk_hide_registered_users; ?> /> Yes</p>
				</fieldset>
				
				<?php if (!ayah_is_key_missing()): ?>
				<p>These are your current keys. They should not need to be changed once set.</p>
				<?php else: ?>
				<p>You can find your keys by <a href="http://portal.areyouahuman.com/dashboard" target="_blank">logging into your account</a> on our site. Don&rsquo;t have an account? <a href="http://areyouahuman.com/signup" target="_blank">Sign up for free here</a>.</p>
				<?php endif; ?>
				<fieldset>
					<label>Publisher Key:</label>
					<input type='text' name='ayah[publisher_key]' value='<?php echo $ayah_options['publisher_key']; ?>'/>
				</fieldset>
				<fieldset>
					<label>Scoring Key:</label>
					<input type='text' name='ayah[scoring_key]' value='<?php echo $ayah_options['scoring_key']; ?>'/>
				</fieldset>
				
				<h2>Advanced Settings</h2>
				<fieldset>
					<label>Comment Submit Button ID</label>
					<p>If PlayThru is showing up below your comment&rsquo;s submit button, enter the ID of that button here.</p>
					<p>For example, if the code for your submit button looks like this...<br/>
					<pre><code>&lt;input name='submit_name' type='submit' id='submit_id' value='Submit Comment' /&gt;</code></pre>
					..then the ID of the submit button is <strong>submit_id</strong>.</p>
					<input type='text' name='ayah[submit_id]' value='<?php echo $ayah_options['submit_id']; ?>' />
				</fieldset>
				<fieldset>
					<input type='hidden' name='page' value='<?php echo $_GET['page']; ?>' />
					<input type='hidden' name='ayah[action]' value='<?php echo $action; ?>' />
					<button type='submit' class='button-primary'><?php echo $button; ?></button>
				</fieldset>
			</form>
		</div>
	</div>
</div>
