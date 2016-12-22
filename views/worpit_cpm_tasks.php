<?php
include_once( dirname(__FILE__).DS.'worpit_options_helper.php' );
include_once( dirname(__FILE__).DS.'widgets'.DS.'worpit_widgets.php' );

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
?>
<div class="wrap">
	<div class="bootstrap-wpadmin">
		<div class="page-header">
			<a href="http://www.icontrolwp.com/"><div class="icon32" id="worpit-icon">&nbsp;</div></a>
			<h2>cPanel Tasks :: cPanel Manager (from iControlWP)</h2>
		</div>
		
		<div class="row">
			<div class="span12">
				<?php
				$fEnabled = $worpit_cpanel_enabled === 'Y';
				if ( $fEnabled ) {
					?><!-- <div class="alert alert-info">Below you'll find details on your current cPanel Connection.</div> --><?php
				}
				else {
					?><div class="alert alert-error">You need to <a href="admin.php?page=<?php echo $worpit_page_link_options; ?>">enable the cPanel Manager feature</a> before using this section.</div><?php
				}
				?>
			</div>
		</div>
		
		<div class="row">
			<div class="span9">
			<?php
				if ( $fEnabled ) {
					
					if ( !isset( $_COOKIE[ $worpit_sak_cookie_name ] ) ) { //the user hasn't created an encryption salt
					?>
						<div class="alert alert-info">
						<p>Before you can use this plugin, you need to provide your Security Access Key. This will be used to encrypt
						your cPanel username and cPanel password information that is stored in your WordPress database.</p>
						<p>The reason we enforce this, is obvious - security. If your WordPress site is ever compromised, then your cPanel
						login details will be safe.</p>
						<p><strong><a href="admin.php?page=<?php echo $worpit_page_link_security ?>">Enter your Security Access Key here</a></strong>.</p>
						</div>
					<?php
					}
					else {
						include_once( dirname(__FILE__).'/worpit_cpm_tasks_cpanel.php' );
					}
				}
				else {
					echo '<p>Nothing to see here unless you enable the feature.</p>';
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
<?php
	
