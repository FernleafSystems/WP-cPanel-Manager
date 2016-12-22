<?php 
	include_once( dirname(__FILE__).'/widgets/worpit_widgets.php' );
?>

<div class="wrap">
	<div class="bootstrap-wpadmin">

	<div class="page-header">
		<a href="http://www.icontrolwp.com/"><div class="icon32" id="worpit-icon"><br /></div></a>
		<h2>Dashboard :: cPanel Manager (from iControlWP)</h2>
	</div>

	<div class="row">
	  <div class="span12">
		<div class="alert alert-error">
 		 <h4 class="alert-heading">Important Notice</h4>
 		 You need to go to the <a href="admin.php?page=<?php echo $this->getSubmenuId('main') ?>">main plugin Settings page</a> to enable the plugin features as they are no longer enabled by default.</div>
	  </div><!-- / span12 -->
	</div><!-- / row -->

	<div class="row">
	  <div class="span12">
		  <div class="well">
		  	<div class="row">
		  		<div class="span6">
		  			<h3>Do you like the cPanel Manager plugin?</h3>
					<p>Help <u>spread the word</u> or check out what else we do ...</p>
		  		</div>
		  		<div class="span4" style="margin-top: 20px;">
					<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wordpress.org/extend/plugins/cpanel-manager-from-worpit/" data-text="Manage your cPanel hosting from inside your WordPress!" data-via="iControlWP" data-size="large" data-hashtags="#cPanel">Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		  		</div>
		  	</div>
			<div class="row">
				<div class="span5">
					<ul>
						<li><a href="http://bit.ly/MB8P9h" target="_blank"><strong>All-new WordPress Admin For Multiple Sites!</strong></a></li>
						<li><a href="http://bit.ly/MB8TG3">Our WordPress Twitter Bootstrap Plugin</a></li>
					</ul>
				</div>
				<div class="span6">
					<ul>
						<li><a href="http://bit.ly/MB8IdX " target="_blank"><strong>Read about our new WordPress backup and restore service: WorpDrive</strong></a>.</li>
						<li><a href="http://wordpress.org/extend/plugins/custom-content-by-country/" target="_blank">Give this plugin a 5 star rating on WordPress.org.</a></li>
					<!-- <li><a href="http://bit.ly/owxOjJ">Get Quality Wordpress Web Hosting</a></li>  -->
					</ul>
				</div>
			</div>
		  </div><!-- / well -->
	  </div><!-- / span12 -->
	</div><!-- / row -->

		<div class="row" id="worpit_promo">
		  <div class="span12">
		  	<?php echo getWidgetIframeHtml('dashboard-widget-worpit'); ?>
		  </div>
		</div><!-- / row -->

		<div class="row" id="developer_channel_promo">
		  <div class="span12">
		  	<?php echo getWidgetIframeHtml('dashboard-widget-developerchannel'); ?>
		  </div>
		</div><!-- / row -->
		
		
		<div class="row">
		  <div class="span6">
		  </div><!-- / span6 -->
		  <div class="span6">
		  	<p></p>
		  </div><!-- / span6 -->
		</div><!-- / row -->
		
	</div><!-- / bootstrap-wpadmin -->

</div><!-- / wrap -->