<?php
include_once( dirname(__FILE__).DS.'worpit_options_helper.php' );
include_once( dirname(__FILE__).DS.'widgets'.DS.'worpit_widgets.php' );
?>
<div class="wrap">
	<div class="bootstrap-wpadmin">
		<div class="page-header">
			<a href="http://www.icontrolwp.com/"><div class="icon32" id="worpit-icon">&nbsp;</div></a>
			<h2>cPanel Manager (from iControlWP) :: cPanel Encryption Option</h2>
		</div>
		<div class="row">
			<div class="span9">
			<?php 
				if ( isset( $_COOKIE[ $worpit_sak_cookie_name ] ) ) { //the user hasn't created an encryption salt
			?>
					<div class="alert alert-info">
						<p>You are currently authorized to access your cPanel Manager functions with this plugin.</p>
						<p>You will be returned here once your session times out.</p>
						<form method="post" action="<?php echo $worpit_form_action; ?>" class="form-horizontal">
							<?php wp_nonce_field( $worpit_nonce_field ); ?>
							<input type="hidden" name="cpm_form_submit" value="1" />
							<button type="submit" class="btn btn-primary" name="submit_remove_access">End cPanel Manager Session Now</button>
						</form>
					</div>
			<?php 	
				}
				else {
			?>
				<div class="well">
					<h3>What should you enter here?</h3>
					<p>If you have already given a <strong>Security Access Key</strong> in the past, enter this again to get access to your cPanel Manager.</p>
					<p>If this is your first time here, or you have reset your credentials, enter a new <strong>Security Access Key</strong> below.</p>
				</div>
				<form method="post" action="<?php echo $worpit_form_action; ?>" class="form-horizontal">
					<?php
						wp_nonce_field( $worpit_nonce_field );
						printAllPluginOptionsForm( $worpit_aAllOptions, $worpit_var_prefix, 1 );
					?>
					<div class="form-actions">
						<input type="hidden" name="cpm_form_submit" value="1" />
						<input type="hidden" name="<?php echo $worpit_var_prefix.'all_options_input'; ?>" value="<?php echo $worpit_all_options_input; ?>" />
						<button type="submit" class="btn btn-primary" name="submit">Submit Encryption Password</button>
						<button type="submit" class="btn btn-warning" name="submit_reset">Reset All Credentials</button>
					</div>
				</form>
				<?php 
				}
				?>
				<div class="well">
					<h3>How cPanel Manager encryption works</h3>
					<p>Your cPanel username and password are sensitive pieces of information.</p>
					<p>So we don't want to store them in your WordPress database without encrypting them first.</p>
					<p>To do this, we need you to supply an <strong>encryption password</strong>. We will use this password to <strong>mash up</strong> your cPanel
					details before storing them to the database. In this way, if anyone nasty gets a hold of your WordPress database
					they wont be able to learn anything about your cPanel administrator accounts.</p>
					<p>Before we let you save your cPanel login details, you must <strong>first submit a Security Access Key</strong> (below).</p>
					<p>When you come back in a few hours/days/weeks to use this plugin we will force you to <strong>resupply the same Key</strong>.</p>
					<p>If you do forget it, just simply hit "Reset", provide a new Key, and then re-enter your cPanel details.</p>
					<p><strong>If you don't have the necessary PHP encryption library (mcrypt) the plugin will run as normal, but your data wont be encrypted</strong></p>
				</div>
			</div><!-- / span9 -->
			<div class="span3" id="side_widgets">
	  			<?php echo getWidgetIframeHtml( 'cpm-side-widgets' ); ?>
			</div>
		</div>
	</div><!-- / bootstrap-wpadmin -->
	<?php include_once( dirname(__FILE__).'/worpit_options_js.php' ); ?>
</div><!-- / wrap -->
