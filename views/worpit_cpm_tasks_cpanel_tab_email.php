<?php

function getContent_EmailTab( $inaConnectionData, $inoCpanelApi ) {
	
	$aHtml = array();
	$sHtml = '';
	list($sServerAddress, $sServerPort, $sUsername, $sPassword, $sNonce, $sFormAction ) = $inaConnectionData;
	
	//Perform Main cPanel Email API query
	$inoCpanelApi->doApiFunction( 'Email', 'listpopssingle' ); //listpopswithdisk - perhaps is better
	$oLastResponse = $inoCpanelApi->getLastResponse();

	if ( Worpit_CPanelTransformer::GetLastSuccess($oLastResponse) ) { //Last API call was a success.
		
		$aAllEmailUserData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'login' );

		if ( !empty($aAllEmailUserData) ) {

			$sHtml = '<div class="well">
			<h4>Email Users and their full email addresses</h4>';
			foreach( $aAllEmailUserData as $aUser ) {
				$sUserLogin = $aUser[ 'login' ];
				$sUserEmail = $aUser[ 'email' ];
				/*
				$sUserName = $aUser[ 'login' ];
				$sDiskUsed = $aUser[ 'diskused' ];
				$sDiskQuota = $aUser[ 'diskquota' ];
				*/
				$sHtml .= "<h5>$sUserLogin</h5>";
				$sHtml .= "
					<ul>
						<li>Email Address: <span class=\"user_homedir\">$sUserEmail</span></li>
					</ul>
				";
				
			}
			$sHtml .= '</div>';


		} else {
			$sHtml .= "There doesn't appear to be any.";
		}
		
	}
	$aHtml[ 'EmailInfo' ] = $sHtml;
	
	/*
	 * Create HTML for Tab: EmailNewUser
	 */
	ob_start();
	
	$inoCpanelApi->doApiFunction( 'DomainLookup', 'getbasedomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
	$aBaseDomains = Worpit_CPanelTransformer::GetListFromData( $oLastResponse, 'domain' );

	?>
		<legend>Create New Email User</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="email_new_user">New Email User</label>
				<div class="controls">
					<input type="text" name="email_new_user" id="email_new_user" placeholder="Only the part before the @" class="span3"
						value="<?php echo isset($_POST['email_new_user'])? $_POST['email_new_user'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email_new_user_domain">Email User Domain</label>
				<div class="controls">
					<select name="email_new_user_domain" id="email_new_user_domain">
						<?php foreach( $aBaseDomains as $sBaseDomain ) { echo "<option name=\"$sBaseDomain\" value=\"$sBaseDomain\">@$sBaseDomain</option>";	} ?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email_new_user_password">Email User Password</label>
				<div class="controls">
					<input type="text" name="email_new_user_password" id="email_new_user_password" placeholder="User Password" class="span3"
					value="<?php echo isset($_POST['email_new_user_password'])? $_POST['email_new_user_password'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email_new_user_quota">Email Quota (MB)</label>
				<div class="controls">
					<input type="text" name="email_new_user_quota" id="email_new_user_quota" placeholder="Set 0 for no quota" class="span3"
						value="<?php echo isset($_POST['email_new_user_quota'])? $_POST['email_new_user_quota'] : '' ?>" />
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="email_create_emailuser" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new Email User?')">Create New Email User</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'EmailNewUser' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: EmailNewUserBulk
	 */
	ob_start();
	
	?>
	Coming Soon...
	<?php /*
		<legend>Create New Email Users (Bulk)</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="email_new_user_bulk">New Email User Details</label>
				<div class="controls">
					<textarea type="textarea" rows="10" cols="100" name="email_new_user_bulk" id="email_new_user_bulk" placeholder="Username, Password, Quota - Take a new line per new user" class="span5"
					/><?php echo isset($_POST['email_new_user_bulk'])? $_POST['email_new_user_bulk'] : '' ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email_new_user_bulk_homedir">Email User Base Homedir</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on"><?php echo $sHomeDir.'/'; ?></span><input type="text" name="email_new_user_bulk_homedir" id="email_new_user_bulk_homedir" placeholder="User Base Directory"
					value="<?php echo isset($_POST['email_new_user_bulk_homedir'])? $_POST['email_new_user_bulk_homedir'] : '' ?>" class="span2" />
					</div>
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="email_create_emailusersbulk" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new Email Users?')">Create New Email Users</button>
			</div>
		</form>
		*/?>
	<?php
	$aHtml[ 'EmailNewUsersBulk' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: DatabasesDeleteUser
	 */
	ob_start();

	?>
		<legend>Delete Email Users</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>

			<?php 
			if ( !empty($aAllEmailUserData) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="users_to_delete_names">Choose Users</label>
					<div class="controls">
						<select multiple="multiple" size="<?php echo count($aAllEmailUserData) ?>" name="email_users_to_delete_names[]" id="email_users_to_delete_names">
			<?php
				foreach( $aAllEmailUserData as $aUser ) {
					echo '<option value="'.$aUser[ 'email' ].'">'.$aUser[ 'email' ].'</option>';
				}
			?>
						</select>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="email_delete_emailusers" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the selected user(s)?')">Delete Selected User(s)</button>
				</div>
			<?php
			}
			else {
				echo "
				<p>There doesn't appear to be any Email users on this cPanel account available for deletion.</p>
				<p>To protect us from ourselves, we don't allow you to delete Email accounts of type:</p>
				<ul>
					<li>'main' - the core Email user account of the cPanel account.</li>
					<li>'logaccess' and 'anonymous' - cPanel system Email accounts.</li>
				</ul>
				<p>If you don't like this restriction, please write to your local elected official.</p>
				";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'EmailDeleteUser' ] = ob_get_contents();
	ob_end_clean();
	
	?>
			<div id="TabsFunctionEmail" class="tabbable tabs-function">
				<ul class="nav nav-pills">
					<li class="active"><a href="#EmailInfo" data-toggle="tab"><i class="icon icon-info-sign"></i></a></li>
					<li><a href="#EmailNewUser" data-toggle="tab"><i class="icon icon-plus-sign"></i> Add Email User</a></li>
					<li><a href="#EmailNewUserBulk" data-toggle="tab"><i class="icon icon-plus-sign"></i> Add Email Users (Bulk)</a></li>
					<li><a href="#EmailDeleteUser" data-toggle="tab"><i class="icon icon-minus-sign"></i> Delete Email Users</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="EmailInfo"><?php echo $aHtml[ 'EmailInfo' ]; ?></div>
					<div class="tab-pane" id="EmailNewUser"><?php echo $aHtml[ 'EmailNewUser' ]; ?></div>
					<div class="tab-pane" id="EmailNewUserBulk"><?php echo $aHtml[ 'EmailNewUsersBulk' ]; ?></div>
					<div class="tab-pane" id="EmailDeleteUser"><?php echo $aHtml[ 'EmailDeleteUser' ]; ?></div>
				</div>
			</div>
	<?php
	
	return;
	
	
}//getContent_EmailTab

