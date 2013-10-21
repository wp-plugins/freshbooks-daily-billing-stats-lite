
<div class="wrap">  
<?php 
	$afbspre_receiver_email = get_option( 'afbspre_receiver_email', ' ');
	$afbspre_freshbooks_subdomain = get_option( 'afbspre_freshbooks_subdomain', ' ');
	$afbspre_freshbooks_token = get_option( 'afbspre_freshbooks_token', ' ');
?>
		<?php screen_icon('themes'); ?> <h2>FreshBooks Daily Billing Stats Lite</h2><span style="padding-left: 45px;">Version 1.0</span>

		<form>
            <table class="form-table">  
                <tr valign="top">  
                    <th scope="row">  
                        <label for="afbspre_freshbooks_subdomain" style="font-weight:bold;">Subdomain</label>   
                    </th>  
                    <td>  
                        <input name="afbspre_freshbooks_subdomain" type="text" id="afbspre_freshbooks_subdomain" size="25" maxlength="150" value=<?php echo $afbspre_freshbooks_subdomain; ?> />  
						<label for="afbspre_freshbooks_subdomain">.freshbooks.com</label> 
                    </td>  
                </tr>

				<tr valign="top">  
                    <th scope="row">  
                        <label for="afbspre_freshbooks_token" style="font-weight:bold;">Authentication Token</label>   
                    </th>  
                    <td>  
                        <input name="afbspre_freshbooks_token" type="password" id="afbspre_freshbooks_token" size="50" maxlength="150" value=<?php echo $afbspre_freshbooks_token; ?> /> 
                    </td>  
                </tr>
			</table>
				<div style="width:100%; border-top: 1px solid #DFDFDF; margin-top:20px;">&nbsp;</div>
			<table class="form-table">
				<tr valign="top">  
                    <th scope="row">  
                        <label for="afbspre_receiver_email" style="font-weight:bold;">Send report to</label>   
                    </th>  
                    <td>  
                        <input name="afbspre_receiver_email" type="text" id="afbspre_receiver_email" size="50" maxlength="150" value=<?php echo $afbspre_receiver_email; ?> />
                    </td>  
                </tr>
				
				<tr valign="top">  
                    <td scope="row" colspan="2" >
						<span style="font-weight:bold;">Note:</span> you must select the Save Settings button below if you have updated any of the above fields.
                    </td>  
                </tr>
				
				<tr valign="top">  
                    <th scope="row">
                        <input type="submit" name="SubmitNewEmail" value="<?php _e('Save Settings' ) ?>" />   
                    </th>  
                    <td>  
                        &nbsp;
                    </td>  
                </tr>
				
            </table>  
        </form> 
		
	
		<p></p>

		<form>
            <table class="form-table">  
                <tr valign="top">  
                    <th scope="row">  
                        <input type="submit" name="sendReport" value="<?php _e('Send report now') ?>" />   
                    </th>   
                </tr>

			</table>
				<div style="width:100%; border-top: 1px solid #DFDFDF; margin-top:20px;">&nbsp;</div>
			<table class="form-table">
				<tr valign="top">
					<td style="text-align:left;">Powered by <a style="color: rgb(0, 164, 233);"href="http://www.opmc.com.au" target="_blank">OPMC</a></td>
					<td style="text-align:right;"><a style="color: rgb(0, 164, 233);"href="http://www.opmc.com.au" target="_blank"><img height="60px" src="http://www.opmc.com.au/site/wp-content/uploads/opmc_logo1.png"/></a></td>
				</tr>
            </table>  
		</form>


</div>  

<?php

function cron_actionButton_javascript() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {

	jQuery('input[name="SubmitNewEmail"]').click(function() { // bind click event to link
		jQuery('input[name="SubmitNewEmail"]').attr("disabled", "disabled");
		
		var data = { 
			action: 'cron_actionSaveData', 
			SubmitNewEmail:'sumitted', 
			afbspre_freshbooks_subdomain:	jQuery('input[name="afbspre_freshbooks_subdomain"]').val(), 
			afbspre_freshbooks_token:		jQuery('input[name="afbspre_freshbooks_token"]').val(), 
			afbspre_receiver_email:			jQuery('input[name="afbspre_receiver_email"]').val() 
		};
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {
			if(response == 1 )
				alert('Your settings have been updated.');
				jQuery('input[name="SubmitNewEmail"]').removeAttr("disabled"); 
		});
		return false;
	});
	
	jQuery('input[name="sendReport"]').click(function() { // bind click event to link
		jQuery('input[name="sendReport"]').attr("disabled", "disabled");
		var data = { action: 'cron_actionButton', whatever: 5 };
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, function(response) {
			if(response == 1 )
				alert('Your FreshBooks Daily Billing Stats Email has been sent.');
				jQuery('input[name="sendReport"]').removeAttr("disabled"); 
		});
		return false;
	});
	
});
</script>
<?php
}
add_action( 'admin_footer', 'cron_actionButton_javascript' );



