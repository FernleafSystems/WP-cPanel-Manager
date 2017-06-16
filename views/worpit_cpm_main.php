<?php
include_once( dirname(__FILE__).ICWP_DS.'worpit_options_helper.php' );
include_once( dirname(__FILE__).ICWP_DS.'widgets'.ICWP_DS.'worpit_widgets.php' );
?>
<div class="wrap">
	<div class="bootstrap-wpadmin">
		<div class="page-header">
			<a href="http://www.icontrolwp.com/"><div class="icon32" id="worpit-icon">&nbsp;</div></a>
			<h2>cPanel Manager (from iControlWP) :: cPanel Connect Options</h2>
		</div>
		<div class="row">
			<div class="span9">
			
			<?php
			
			if ( !isset( $_COOKIE[ $worpit_sak_cookie_name] ) ) { //the user hasn't created an encryption salt
				
				echo '<div class="alert alert-info">
				<p>Before you can use this plugin, you need to provide your Security Access Key. This will be used to encrypt
				your cPanel username and cPanel password information that is stored in your WordPress database.</p>
				<p>The reason we enforce this, is obvious - security. If your WordPress site is ever compromised, then your cPanel
				login details will be safe.</p>
				<p><strong><a href="admin.php?page='.$worpit_page_link_security.'">Enter your Security Access Key here</a></strong>.</p>
				</div>';
				
			}
			else {
			?>
				<form method="post" action="<?php echo $worpit_form_action; ?>" class="form-horizontal">
					<?php
						wp_nonce_field( $worpit_nonce_field );
						printAllPluginOptionsForm( $worpit_aAllOptions, $worpit_var_prefix, 1 );
					?>
					<div class="form-actions">
						<input type="hidden" name="cpm_form_submit" value="1" />
						<input type="hidden" name="<?php echo $worpit_var_prefix.'all_options_input'; ?>" value="<?php echo $worpit_all_options_input; ?>" />
						<button type="submit" class="btn btn-primary" name="submit">Save All Settings</button>
					</div>
				</form>
			<?php
			}
			?>
			
			</div><!-- / span9 -->
			<div class="span3" id="side_widgets">
	  			<?php echo getWidgetIframeHtml( 'cpm-side-widgets' ); ?>
			</div>
		</div>
	</div><!-- / bootstrap-wpadmin -->
	<?php include_once( dirname(__FILE__).'/worpit_options_js.php' ); ?>
</div><!-- / wrap -->
