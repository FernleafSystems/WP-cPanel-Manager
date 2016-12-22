<?php

function getContent_DomainsTab( $inaConnectionData, $inoCpanelApi ) {
	
	$aHtml = array();
	$sHtml = '';
	list($sServerAddress, $sServerPort, $sUsername, $sPassword, $sNonce, $sFormAction ) = $inaConnectionData;
	
	//Perform Main cPanel FTP API query
	$inoCpanelApi->doApiFunction( 'SubDomain', 'listsubdomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
		
	$aAllSubDomainData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'subdomain' );
	$aAllSubDomainsList = Worpit_CPanelTransformer::GetListFromData( $oLastResponse, 'domain' );

	$sHtml = '
		<h4>All Sub Domains</h4>
		<div class="well">
	';
	
	if ( !empty($aAllSubDomainData) ) {

		$sHtml .= '<ul>';
		foreach( $aAllSubDomainData as $aSubDomain ) {
			$sSubDomain = $aSubDomain[ 'subdomain' ];
			$sRootDomain = $aSubDomain[ 'rootdomain' ];
			$sRootDir = $aSubDomain[ 'dir' ];
			$sStatus = $aSubDomain[ 'status' ];
			$sHtml .= "<h5>$sSubDomain<small>.$sRootDomain</small></h5>";
			$sHtml .= "
				<ul>
					<li><span class=\"user_homedir\">$sRootDir</span></li>
					<li>Redirection Status: $sStatus</li>
				</ul>
			";
			
		}
		$sHtml .= '</ul>';
	}
	else {
		$sHtml .= "There doesn't appear to be any.";
	}
	$sHtml .= '</div>';
	
	$inoCpanelApi->doApiFunction( 'Park', 'listparkeddomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
		
	$aAllParkedDomainsData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'domain' );
	$aAllParkedDomainsList = Worpit_CPanelTransformer::GetListFromData( $oLastResponse, 'domain' );
	
	$sHtml .= '
		<h4>All Parked Domains</h4>
		<div class="well">
	';
	
	if ( !empty($aAllParkedDomainsData) ) {

		$sHtml .= '<ul>';
		foreach( $aAllParkedDomainsData as $aDomain ) {
			$sDomain = $aDomain[ 'domain' ];
			$sRootDir = $aDomain[ 'dir' ];
			$sStatus = $aDomain[ 'status' ];
			$sHtml .= "<h5>$sDomain</h5>";
			$sHtml .= "
				<ul>
					<li><span class=\"user_homedir\">$sRootDir</span></li>
					<li>Redirection Status: $sStatus</li>
				</ul>
			";
			
		}
		$sHtml .= '</ul>';


	} else {
		$sHtml .= "There doesn't appear to be any Parked Domains.";
	}
	$sHtml .= '</div>';

	//Perform Main cPanel Addon Domains API query
	$inoCpanelApi->doApiFunction( 'AddonDomain', 'listaddondomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();

	$aAllAddonDomainsData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'domain' );
	$aAllAddonDomainsList = Worpit_CPanelTransformer::GetListFromData( $oLastResponse, 'domain' );

	$sHtml .= '
		<h4>All Addon Domains</h4>
		<div class="well">
	';
	
	if ( !empty($aAllAddonDomainsData) ) {

		$sHtml .= '<ul>';
		foreach( $aAllAddonDomainsData as $aAddonDomain ) {
			$sAddonDomain = $aAddonDomain[ 'domain' ];
			$sSubDomain = $aAddonDomain[ 'subdomain' ];
			$sRootDir = $aAddonDomain[ 'dir' ];
			$sStatus = $aAddonDomain[ 'status' ];
			$sHtml .= "<h5>$sAddonDomain</h5>";
			$sHtml .= "
				<ul>
					<li><span class=\"user_homedir\">$sRootDir</span></li>
					<li>Sub Domain: $sSubDomain</li>
					<li>Redirection Status: $sStatus</li>
				</ul>
			";
			
		}
		$sHtml .= '</ul>';


	} else {
		$sHtml .= "There doesn't appear to be any Addon Domains";
	}
	$sHtml .= '</div>';
		
	$aHtml[ 'DomainsInfo' ] = $sHtml;
	
	/*
	 * Create HTML for Tab: SubDomainsNew
	 */
	ob_start();

	$inoCpanelApi->doApiFunction( 'DomainLookup', 'getbasedomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
	$aBaseDomains = Worpit_CPanelTransformer::GetListFromData( $oLastResponse, 'domain' );
	
	$inoCpanelApi->doApiFunction( 'Ftp', 'listftp' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
	$aMainFtpUser = Worpit_CPanelTransformer::GetData_MainFtpUser( $oLastResponse );
	$sHomeDir = $aMainFtpUser['homedir'];

	?>
		<legend>Create New SubDomain</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="subdomain_new_domain">New Sub Domain</label>
				<div class="controls">
					<input type="text" name="subdomain_new_domain" id="subdomain_new_domain" placeholder="Sub Domain Name" class="span3"
					value="<?php echo isset($_POST['subdomain_new_domain'])? $_POST['subdomain_new_domain'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="subdomain_parent_domain">Parent Domain</label>
				<div class="controls">
					<select name="subdomain_parent_domain" id="subdomain_parent_domain">
						<?php foreach( $aBaseDomains as $sBaseDomain ) { echo "<option name=\"$sBaseDomain\" value=\"$sBaseDomain\">$sBaseDomain</option>";	} ?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="subdomain_document_root">Document Root</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on"><?php echo $sHomeDir.'/'; ?></span><input type="text" name="subdomain_document_root" id="subdomain_document_root"
						placeholder="Document Root Directory" class="span3"
						value="<?php echo isset($_POST['subdomain_document_root'])? $_POST['subdomain_document_root'] : '' ?>" />
					</div>
					
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="domain_create_subdomain" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new Sub Domain?')">Create New Sub Domain</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'SubDomainsNew' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: SubDomainsDelete
	 */
	ob_start();
	?>
		<legend>Delete Sub Domains</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			
			<?php

			if ( !empty($aAllSubDomainsList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="subdomains_to_delete_names">Choose Sub Domains</label>
					<div class="controls">
						<select class="span5" multiple="multiple" size="<?php echo count($aAllSubDomainsList) ?>" name="subdomains_to_delete_names[]" id="subdomains_to_delete_names">
			<?php
				foreach( $aAllSubDomainsList as $sSubDomainName ) {
					echo "<option name=\"$sSubDomainName\" value=\"$sSubDomainName\">$sSubDomainName</option>";
				}
			?>
						</select>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="domain_delete_subdomains" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the selected Sub Domain(s)?')">Delete Sub Domain(s)</button>
				</div>
			<?php
			}
			else {
				echo "<p>There doesn't appear to be any Sub Domains on this cPanel account available for deletion.</p>";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'SubDomainsDelete' ] = ob_get_contents();
	ob_end_clean();
	
	
	$inoCpanelApi->doApiFunction( 'Park', 'listparkeddomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
	
	$aAllDomainData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'domain' );
	$aAllParkedDomainsList = Worpit_CPanelTransformer::GetListFromData( $oLastResponse, 'domain' );
	/*
	 * Create HTML for Tab: ParkedDomainsNew
	 */
	ob_start();
	
	$inoCpanelApi->getPrimaryDomain();
	$sMainDomain = Worpit_CPanelTransformer::GetPrimaryDomain( $inoCpanelApi->getLastResponse() );

	?>
		<legend>Create New Parked Domain</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="parkeddomain_new_domain">New Parked Domain</label>
				<div class="controls">
					<input type="text" name="parkeddomain_new_domain" id="parkeddomain_new_domain" placeholder="Parked Domain Name" class="span3"
					value="<?php echo isset($_POST['parkeddomain_new_domain'])? $_POST['parkeddomain_new_domain'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="parkeddomain_top_domain">Domain To Park On</label>
				<div class="controls">
					<select class="span5" name="parkeddomain_top_domain" id="parkeddomain_top_domain">
						<option name="" value=""><?php echo $sMainDomain; ?></option>
						<?php foreach( $aAllSubDomainsList as $sSubDomain ) { echo "<option name=\"$sSubDomain\" value=\"$sSubDomain\">$sSubDomain</option>";	} ?>
					</select>
				</div>
			</div>
	<?php
	/** THE API IS BROKEN - cannot redirect
			<div class="control-group">
				<label class="control-label" for="parkeddomain_redirect_http">Redirect URL</label>
				<div class="controls">
					<select style="width:90px" name="parkeddomain_redirect_http" id="parkeddomain_redirect_http">
						<option name="http" value="http://">http://</option>
						<option name="https" value="https://">https://</option>
					</select>

					<input type="text" name="parkeddomain_redirect_url" id="parkeddomain_redirect_url"
					placeholder="Leave Empty To Redirect To Main Domain" class="span3"
					value="<?php echo isset($_POST['parkeddomain_redirect_url'])? $_POST['parkeddomain_redirect_url'] : '' ?>" />
				</div>
			</div>
	**/
	?>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="domain_create_parkeddomain" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new Parked Domain?')">Create New Parked Domain</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'ParkedDomainsNew' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: ParkedDomainsDelete
	 */
	ob_start();
	?>
		<legend>Delete Parked Domains</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			
			<?php

			if ( !empty($aAllParkedDomainsList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="parkeddomains_to_delete_names">Choose Parked Domains</label>
					<div class="controls">
						<select class="span5" multiple="multiple" size="<?php echo count($aAllParkedDomainsList) ?>" name="parkeddomains_to_delete_names[]" id="parkeddomains_to_delete_names">
			<?php
				foreach( $aAllParkedDomainsList as $sParkedDomainName ) {
					echo "<option name=\"$sParkedDomainName\" value=\"$sParkedDomainName\">$sParkedDomainName</option>";
				}
			?>
						</select>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="domain_delete_parkeddomains" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the selected Parked Domain(s)?')">Delete Parked Domain(s)</button>
				</div>
			<?php
			}
			else {
				echo "<p>There doesn't appear to be any Parked Domains on this cPanel account available for deletion.</p>";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'ParkedDomainsDelete' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: AddonDomainsNew
	 */
	ob_start();
	?>
		<legend>Create New Addon Domain</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="addondomain_new_domain">New Addon Domain</label>
				<div class="controls">
					<input type="text" name="addondomain_new_domain" id="addondomain_new_domain" placeholder="Addon Domain Name" class="span3"
					value="<?php echo isset($_POST['addondomain_new_domain'])? $_POST['addondomain_new_domain'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="addondomain_document_root">Document Root</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on"><?php echo $sHomeDir.'/'; ?></span><input type="text" name="addondomain_document_root" id="addondomain_document_root"
						placeholder="Document Root Directory" class="span3"
						value="<?php echo isset($_POST['addondomain_document_root'])? $_POST['addondomain_document_root'] : '' ?>" />
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="addondomain_subdomain_name">Sub Domain/FTP name</label>
				<div class="controls">
					<input type="text" name="addondomain_subdomain_name" id="addondomain_subdomain_name" placeholder="Sub Domain" class="span3"
					value="<?php echo isset($_POST['addondomain_subdomain_name'])? $_POST['addondomain_subdomain_name'] : '' ?>" />
				</div>
			</div>

			<legend>Create Optional FTP User</legend>
			<p>You can create an optional FTP User by supplying a password below. If you leave the field empty, NO FTP user will be created.</p>
			<div class="control-group">
				<label class="control-label" for="addondomain_ftp_password">FTP User Password</label>
				<div class="controls">
					<input type="text" name="addondomain_ftp_password" id="addondomain_ftp_password" placeholder="User Password" class="span3"
					value="<?php echo isset($_POST['addondomain_ftp_password'])? $_POST['addondomain_ftp_password'] : '' ?>" />
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="domain_create_addondomain" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new Addon Domain?')">Create New Addon Domain</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'AddonDomainsNew' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: AddonDomainsDelete
	 */
	ob_start();
	?>
		<legend>Delete Addon Domains</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			
			<?php

			if ( !empty($aAllAddonDomainsList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="addondomains_to_delete_names">Choose Addon Domains</label>
					<div class="controls">
						<select class="span5" multiple="multiple" size="<?php echo count($aAllAddonDomainsList) ?>" name="addondomains_to_delete_names[]" id="addondomains_to_delete_names">
			<?php
				foreach( $aAllAddonDomainsData as $aAddonDomainData ) {
					$sAddonDomain = $aAddonDomainData['domain'];
					$sValue = $sAddonDomain.'_'.$aAddonDomainData['subdomain'].'_'.$aAddonDomainData['rootdomain'];
					echo '<option name="'.$sAddonDomain.'" value="'.$sValue.'">'.$sAddonDomain.'</option>';
				}
			?>
						</select>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="domain_delete_addondomains" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the selected Addon Domain(s)?')">Delete Addon Domain(s)</button>
				</div>
			<?php
			}
			else {
				echo "<p>There doesn't appear to be any Addon Domains on this cPanel account available for deletion.</p>";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'AddonDomainsDelete' ] = ob_get_contents();
	ob_end_clean();
	
	?>
			<div id="TabsFunctionSubdomains" class="tabbable tabs-function">
				<ul class="nav nav-pills">
					<li class="active"><a href="#DomainsInfo" data-toggle="tab"><i class="icon icon-info-sign"></i></a></li>
					<li><a href="#SubDomainsNew" data-toggle="tab"><i class="icon icon-plus-sign"></i> Sub</a></li>
					<li><a href="#SubDomainsDelete" data-toggle="tab"><i class="icon icon-minus-sign"></i> Sub</a></li>
					<li><a href="#ParkedDomainsNew" data-toggle="tab"><i class="icon icon-plus-sign"></i> Parked</a></li>
					<li><a href="#ParkedDomainsDelete" data-toggle="tab"><i class="icon icon-minus-sign"></i> Parked</a></li>
					<li><a href="#AddonDomainsNew" data-toggle="tab"><i class="icon icon-plus-sign"></i> Addon</a></li>
					<li><a href="#AddonDomainsDelete" data-toggle="tab"><i class="icon icon-minus-sign"></i> Addon</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="DomainsInfo"><?php echo $aHtml[ 'DomainsInfo' ]; ?></div>
					<div class="tab-pane" id="SubDomainsNew"><?php echo $aHtml[ 'SubDomainsNew' ]; ?></div>
					<div class="tab-pane" id="SubDomainsDelete"><?php echo $aHtml[ 'SubDomainsDelete' ]; ?></div>
					<div class="tab-pane" id="ParkedDomainsNew"><?php echo $aHtml[ 'ParkedDomainsNew' ]; ?></div>
					<div class="tab-pane" id="ParkedDomainsDelete"><?php echo $aHtml[ 'ParkedDomainsDelete' ]; ?></div>
					<div class="tab-pane" id="AddonDomainsNew"><?php echo $aHtml[ 'AddonDomainsNew' ]; ?></div>
					<div class="tab-pane" id="AddonDomainsDelete"><?php echo $aHtml[ 'AddonDomainsDelete' ]; ?></div>
				</div>
			</div>
			<script>
				jQuery("#subdomain_new_domain").change(function() {
		
					if ( this.value ) {
						jQuery("#subdomain_document_root").val( this.value );
					}
				});
				
				jQuery("#addondomain_new_domain").change(function() {
					
					if ( this.value ) {
						
						var iDotPosition = this.value.search(/\./);
					
						if ( iDotPosition != -1 ) {
							
							var sNewVal = this.value.substring(0, iDotPosition);
		
							jQuery("#addondomain_subdomain_name").val( sNewVal );
							jQuery("#addondomain_document_root").val( sNewVal );
						}
					}
				});
			</script>
	<?php
	
}//getContent_SubDomainsTab
