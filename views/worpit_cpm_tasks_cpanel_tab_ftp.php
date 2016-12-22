<?php

function getContent_FtpTab( $inaConnectionData, $inoCpanelApi ) {
	
	$aHtml = array();
	$sHtml = '';
	list($sServerAddress, $sServerPort, $sUsername, $sPassword, $sNonce, $sFormAction ) = $inaConnectionData;
	
	//Perform Main cPanel FTP API query
	$inoCpanelApi->doApiFunction( 'Ftp', 'listftpwithdisk' );
	$oLastResponse = $inoCpanelApi->getLastResponse();

	if ( Worpit_CPanelTransformer::GetLastSuccess($oLastResponse) ) { //Last API call was a success.
		
		$aAllFtpUserData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'login' );

		if ( !empty($aAllFtpUserData) ) {

			$sHtml = '<div class="well">
			<h4>FTP Users and their Home Directories</h4>';
			foreach( $aAllFtpUserData as $aFtpUser ) {
				$sHomeDir = $aFtpUser[ 'dir' ];
				$sUserType = $aFtpUser[ 'accttype' ];
				$sUserName = $aFtpUser[ 'login' ];
				$sDiskUsed = $aFtpUser[ 'diskused' ];
				$sDiskQuota = $aFtpUser[ 'diskquota' ];
				
				$sHtml .= "<h5>$sUserName</h5>";
				$sHtml .= "
					<ul>
						<li>Home Directory: <span class=\"user_homedir\">$sHomeDir</span></li>
						<li>Type: $sUserType</li>
						<li>Disk Quota Used: $sDiskUsed MB / $sDiskQuota</li>
					</ul>
				";
				
			}
			$sHtml .= '</div>';


		} else {
			$sHtml .= "There doesn't appear to be any.";
		}
		
	}
	$aHtml[ 'FtpInfo' ] = $sHtml;
	
	/*
	 * Create HTML for Tab: FtpNewUser
	 */
	ob_start();
	
	$inoCpanelApi->getHomeDirectory();
	$sData = Worpit_CPanelTransformer::GetDataArray( $inoCpanelApi->getLastResponse() );
	$sHomeDir = $sData['result'];
	
	$inoCpanelApi->getPrimaryDomain();
	$sMainDomain = Worpit_CPanelTransformer::GetPrimaryDomain( $inoCpanelApi->getLastResponse() );
	
	$inoCpanelApi->doApiFunction( 'DomainLookup', 'getbasedomains' );
	$oLastResponse = $inoCpanelApi->getLastResponse();

	?>
		<legend>Create New FTP User</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="ftp_new_user">New FTP User</label>
				<div class="controls">
					<div class="input-append">
						<input type="text" name="ftp_new_user" id="ftp_new_user" placeholder="FTP Username" class="span2"
					value="<?php echo isset($_POST['ftp_new_user'])? $_POST['ftp_new_user'] : '' ?>" /><span class="add-on">@<?php echo $sMainDomain; ?></span>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ftp_new_user_password">FTP User Password</label>
				<div class="controls">
					<input type="text" name="ftp_new_user_password" id="ftp_new_user_password" placeholder="User Password" class="span2"
					value="<?php echo isset($_POST['ftp_new_user_password'])? $_POST['ftp_new_user_password'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ftp_new_user_quota">FTP Quota (MB)</label>
				<div class="controls">
					<input type="text" name="ftp_new_user_quota" id="ftp_new_user_quota" placeholder="Disk space" class="span2"
						value="<?php echo isset($_POST['ftp_new_user_quota'])? $_POST['ftp_new_user_quota'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ftp_new_user_homedir">FTP User Homedir</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on"><?php echo $sHomeDir.'/'; ?></span><input type="text" name="ftp_new_user_homedir" id="ftp_new_user_homedir" placeholder="Home Directory"
					value="<?php echo isset($_POST['ftp_new_user_homedir'])? $_POST['ftp_new_user_homedir'] : '' ?>" class="span2" />
					</div>
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="ftp_create_ftpuser" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new FTP User?')">Create New FTP User</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'FtpNewUser' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: FtpNewUserBulk
	 */
	ob_start();
	
	?>
		<legend>Create New FTP Users (Bulk)</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="ftp_new_user_bulk">New FTP User Details</label>
				<div class="controls">
					<textarea type="textarea" rows="10" cols="100" name="ftp_new_user_bulk" id="ftp_new_user_bulk" placeholder="Username, Password, Quota - Take a new line per new user" class="span5"
					/><?php echo isset($_POST['ftp_new_user_bulk'])? $_POST['ftp_new_user_bulk'] : '' ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="ftp_new_user_bulk_homedir">FTP User Base Homedir</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on"><?php echo $sHomeDir.'/'; ?></span><input type="text" name="ftp_new_user_bulk_homedir" id="ftp_new_user_bulk_homedir" placeholder="User Base Directory"
					value="<?php echo isset($_POST['ftp_new_user_bulk_homedir'])? $_POST['ftp_new_user_bulk_homedir'] : '' ?>" class="span2" />
					</div>
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="ftp_create_ftpusersbulk" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new FTP Users?')">Create New FTP Users</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'FtpNewUsersBulk' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: DatabasesDeleteUser
	 */
	ob_start();
	
	$aSkipAccountTypes = array( 'skip_acct_types' => 'main|anonymous|logaccess' );
	$inoCpanelApi->doApiFunction( 'Ftp', 'listftp', $aSkipAccountTypes );
	$oLastResponse = $inoCpanelApi->getLastResponse();

	?>
		<legend>Delete FTP Users</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			
			<?php
			$aFtpUserNamesList = Worpit_CPanelTransformer::GetList_AllFtpUsers( $oLastResponse );

			if ( !empty($aFtpUserNamesList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="users_to_delete_names">Choose Users</label>
					<div class="controls">
						<select multiple="multiple" size="<?php echo count($aFtpUserNamesList) ?>" name="users_to_delete_names[]" id="users_to_delete_names">
			<?php
				foreach( $aFtpUserNamesList as $sUserName ) {
					echo "<option name=\"$sUserName\" value=\"$sUserName\">$sUserName</option>";
				}
			?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<label class="checkbox" for="ftp_delete_users_disk">
							<input type="checkbox" name="ftp_delete_users_disk" id="ftp_delete_users_disk"
							<?php echo isset($_POST['ftp_delete_users_disk'])? 'checked="checked"' : '' ?>" /> Delete all files for these users.
						</label>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="ftp_delete_ftpusers" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the selected user(s)?')">Delete Selected User(s)</button>
				</div>
			<?php
			}
			else {
				echo "
				<p>There doesn't appear to be any FTP users on this cPanel account available for deletion.</p>
				<p>To protect us from ourselves, we don't allow you to delete FTP accounts of type:</p>
				<ul>
					<li>'main' - the core FTP user account of the cPanel account.</li>
					<li>'logaccess' and 'anonymous' - cPanel system FTP accounts.</li>
				</ul>
				<p>If you don't like this restriction, please write to your local elected official.</p>
				";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'FtpDeleteUser' ] = ob_get_contents();
	ob_end_clean();
	
	?>
			<div id="TabsFunctionFtp" class="tabbable tabs-function">
				<ul class="nav nav-pills">
					<li class="active"><a href="#FtpInfo" data-toggle="tab"><i class="icon icon-info-sign"></i></a></li>
					<li><a href="#FtpNewUser" data-toggle="tab"><i class="icon icon-plus-sign"></i> Add FTP User</a></li>
					<li><a href="#FtpNewUserBulk" data-toggle="tab"><i class="icon icon-plus-sign"></i> Add FTP Users (Bulk)</a></li>
					<li><a href="#FtpDeleteUser" data-toggle="tab"><i class="icon icon-minus-sign"></i> Del FTP Users</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="FtpInfo"><?php echo $aHtml[ 'FtpInfo' ]; ?></div>
					<div class="tab-pane" id="FtpNewUser"><?php echo $aHtml[ 'FtpNewUser' ]; ?></div>
					<div class="tab-pane" id="FtpNewUserBulk"><?php echo $aHtml[ 'FtpNewUsersBulk' ]; ?></div>
					<div class="tab-pane" id="FtpDeleteUser"><?php echo $aHtml[ 'FtpDeleteUser' ]; ?></div>
				</div>
			</div>
		
		<script>
			jQuery("#ftp_new_user").change(function() {
				if ( this.value ) {
					jQuery("#ftp_new_user_homedir").val( this.value );
				}
			});
		</script>
	<?php
	
}//getContent_FtpTab

