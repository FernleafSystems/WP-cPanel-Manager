<?php

function getContent_MySqlTab( $inaConnectionData, $inoCpanelApi ) {
	
	$aHtml = array();
	$sHtml = '';
	
/*
	//Perform Main cPanel Stats query for Databases
	$aMysqlStatsFields = array( 'sqldatabases', 'mysqldiskusage', 'mysqlversion' );
	$inoCpanelApi->doApiFunction( 'StatsBar', 'stat', array( 'display' => implode( '|', $aMysqlStatsFields ) ) );
	$oLastResponse = $inoCpanelApi->getLastResponse();
	$aMysqlStats = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'name' );

	//Perform Main cPanel Databases API query
	$inoCpanelApi->doApiFunction( 'MysqlFE', 'listdbs' );
	$oLastResponse = $inoCpanelApi->getLastResponse();

	$sCpanelJumpUrlStem = "http://$sServerAddress:$sServerPort/login/?user=$sUsername&pass=$sPassword&goto_uri=";

	if ( Worpit_CPanelTransformer::GetLastSuccess($oLastResponse) ) { //Last API call was a success.
		
		$aDbData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'db' );

		if ( !empty($aDbData) ) {
			
			$sMySqlVersion = Worpit_CPanelTransformer::GetData_OneStatData( 'mysqlversion' );
			$sMySqlVersion = $sMySqlVersion[ 'value' ];

			$sHtml = '<div class="well">
			<h4>MySQL Databases and Attached MySQL Users</h4>
			<ul>
				<li>MySQL Version: '.$sMySqlVersion.'</li>
				<li>MySQL Quote Usage: </li>
				<li>MySQL Disk Usage: </li>
			</ul>			
*/

	list($sServerAddress, $sServerPort, $sUsername, $sPassword, $sNonce, $sFormAction ) = $inaConnectionData;
	$sServerPort = 2082; //override for now
	
	//Perform Main cPanel Databases API query
	$inoCpanelApi->doApiFunction( 'MysqlFE', 'listdbs' );
	$oLastResponse = $inoCpanelApi->getLastResponse();

	$sCpanelJumpUrlStem = "http://$sServerAddress:$sServerPort/login/?user=$sUsername&pass=$sPassword&goto_uri=";

	if ( Worpit_CPanelTransformer::GetLastSuccess($oLastResponse) ) { //Last API call was a success.
		
		$aDbData = Worpit_CPanelTransformer::GetDataArray( $oLastResponse, 'db' );

		if ( !empty($aDbData) ) {

			$sHtml = '<div class="well">
			<h4>MySQL Databases and Attached MySQL Users</h4>
			<ul>';
			foreach( $aDbData as $aDb ) {
				$sDbName = $aDb[ 'db' ];
				$sDbSize = $aDb[ 'size' ];
				$sDbSizeMb = $aDb[ 'sizemeg' ];
				$sDbUserCount = $aDb[ 'usercount' ];
				$aDbUserList = Worpit_CPanelTransformer::GetList_MySqlUsersOnDb( $oLastResponse, $sDbName );

				$sDownloadUrl = "$sCpanelJumpUrlStem/getsqlbackup/$sDbName.sql.gz";
				$sHtml .= '<li><a href="'.$sDownloadUrl.'" target="_blank" title="Download Backup" onClick="return confirm(\'Do want to download this database?\')"><i class="icon icon-download"></i></a> '.$sDbName.' ('.$sDbSizeMb.'MB)';

				if ( !empty($aDbUserList) ) {

					$sHtml .= '<ul>';
					
					foreach( $aDbUserList as $sUser ) {
						$sHtml .= "<li>$sUser</li>";
					}
					$sHtml .= '</ul>';
				}
				
				$sHtml .= '</li>';
			}
			$sHtml .= '</ul></div>';


		} else {
			$sHtml .= "There doesn't appear to be any databases with connected users on this account.";
		}
	} else {
		$sHtml .= 'Failed: Could not get the list.';
	}
	ob_start();
	?>
	<style>
		#CpanelJumpLinksMySql {
			margin-top: 20px;
		}
		.cpanel_icon {
			border: 1px solid #bbb;
			border-radius: 4px;
			text-align: center;
			vertical-align: middle;
			height: 50px;
		}
		.cpanel_icon a {
			background-repeat: no-repeat;
			display: inline-block;
			margin-top: 5px;
		}
	
	</style>
	<div id="CpanelJumpLinksMySql" class="row">
		<div class="span1 offset1 cpanel_icon"><a class="spriteicon_img" id="icon-mysql" href="<?php echo $sCpanelJumpUrlStem; ?>" target="_blank"></a></div>
		<div class="span1 cpanel_icon"><a class="spriteicon_img" id="icon-mysql-wizard3" href="<?php echo $sCpanelJumpUrlStem; ?>" target="_blank"></a></div>
		<div class="span1 cpanel_icon"><a class="spriteicon_img" id="icon-phpMyAdmin" href="<?php echo $sCpanelJumpUrlStem; ?>/3rdparty/phpMyAdmin/index.php" target="_blank"></a></div>
		<div class="span1 cpanel_icon"><a class="spriteicon_img" id="icon-mysql-remoteaccess" href="<?php echo $sCpanelJumpUrlStem; ?>" target="_blank"></a></div>
	</div>
	
	<?php
	$sHtml .= ob_get_contents();
	ob_end_clean();
	
	
	$aHtml[ 'DatabasesInfo' ] = $sHtml;
	
	/*
	 * Create HTML for Tab: New Database
	 */
	ob_start();
	?>
		<legend>Create New MySQL Database and User</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="database_name">Database Name</label>
				<div class="controls">
					<input type="text" id="database_name" name="database_name" placeholder="Database Name"
					value="<?php echo isset($_POST['database_name'])? $_POST['database_name'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="database_user">New MySQL User</label>
				<div class="controls">
					<input type="text" id="database_user" name="database_user" placeholder="Enter a new username for this DB"
					value="<?php echo isset($_POST['database_user'])? $_POST['database_user'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="database_user_password">MySQL User Password</label>
				<div class="controls" data-popover="popover" data-placement="right" data-content="Must be at least 5 characters long" data-original-title="Password Rules" data-trigger="click">
					<input type="text" id="database_user_password" name="database_user_password" placeholder="Enter a password for the user"
					value="<?php echo isset($_POST['database_user_password'])? $_POST['database_user_password'] : '' ?>" />
				</div>
			</div>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="database_createdb_adduser" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new database?')">Create New MySQL Database</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'DatabasesNewDb' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: DatabasesNewUser
	 */
	ob_start();

	?>
		<legend>Create New MySQL User</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			<div class="control-group">
				<label class="control-label" for="database_new_user">New MySQL User</label>
				<div class="controls">
					<input type="text" name="database_new_user" placeholder="New MySQL Username"
					value="<?php echo isset($_POST['database_new_user'])? $_POST['database_new_user'] : '' ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="database_new_user_password">MySQL User Password</label>
				<div class="controls">
					<input type="text" name="database_new_user_password" placeholder="Enter a password for the user"
					value="<?php echo isset($_POST['database_new_user_password'])? $_POST['database_new_user_password'] : '' ?>" />
				</div>
			</div>
			<?php
			$aDbNamesList = Worpit_CPanelTransformer::GetList_MySqlDbNames( $oLastResponse );
			if ( !empty($aDbNamesList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="databases_to_add_new_user">Grant New User Access to DB?</label>
					<div class="controls">
							<select multiple="multiple" size="<?php echo count($aDbNamesList) ?>" name="databases_to_add_new_user[]" id="databases_to_add_new_user">
								<?php foreach( $aDbNamesList as $sDbName ) { echo "<option name=\"$sDbName\" value=\"$sDbName\">$sDbName</option>";	} ?>
							</select>
					</div>
				</div>
			<?php 
			}
			?>
			<?php echo getConfirmBoxHtml(); ?>
			<div class="form-actions">
				<input type="hidden" name="cpm_submit_action" value="database_create_mysqluser" />
				<input type="hidden" name="cpm_form_submit" value="1" />
			 	<button type="submit" class="btn btn-primary" onClick="return confirmSubmit('Are you sure you want to create the new MySQL User?')">Create New MySQL User</button>
			</div>
		</form>
		
	<?php
	$aHtml[ 'DatabasesNewUser' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: Delete Databases
	 */
	ob_start();

	?>
		<legend>Delete MySQL Databases</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			
			<?php
			$aDbNamesList = Worpit_CPanelTransformer::GetList_MySqlDbNames( $oLastResponse );
			if ( !empty($aDbNamesList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="databases_to_delete_names">Choose Databases</label>
					<div class="controls">
						<select multiple="multiple" size="<?php echo count($aDbNamesList) ?>" name="databases_to_delete_names[]" id="databases_to_delete_names">
			<?php
				foreach( $aDbNamesList as $sDbName ) {
					echo "<option name=\"$sDbName\" value=\"$sDbName\">$sDbName</option>";
				}
			?>
						</select>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="database_delete_mysqldbs" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the databases?')">Delete Selected MySQL Database(s)</button>
				</div>
			<?php
			}
			else {
				echo "There doesn't appear to be any databases on this account.";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'DatabasesDeleteDb' ] = ob_get_contents();
	ob_end_clean();
	
	/*
	 * Create HTML for Tab: DatabasesDeleteUser
	 */
	ob_start();
	
	$inoCpanelApi->doApiFunction( 'MysqlFE', 'listusers' );
	$oLastResponse = $inoCpanelApi->getLastResponse();
	
	?>
		<legend>Delete MySQL Users</legend>
		<form class="form-horizontal" action="<?php echo $sFormAction; ?>" method="post" >
			<?php wp_nonce_field( $sNonce ); ?>
			
			<?php
			$aMySqlUserNamesList = Worpit_CPanelTransformer::GetList_AllMySqlUsers( $oLastResponse );

			if ( !empty($aMySqlUserNamesList) ) {
			?>
				<div class="control-group">
					<label class="control-label" for="users_to_delete_names">Choose Users</label>
					<div class="controls">
						<select multiple="multiple" size="<?php echo count($aMySqlUserNamesList) ?>" name="users_to_delete_names[]" id="users_to_delete_names">
			<?php
				foreach( $aMySqlUserNamesList as $sUserName ) {
					echo "<option name=\"$sUserName\" value=\"$sUserName\">$sUserName</option>";
				}
			?>
						</select>
					</div>
				</div>
				<?php echo getConfirmBoxHtml(); ?>
				<div class="form-actions">
					<input type="hidden" name="cpm_submit_action" value="database_delete_mysqlusers" />
					<input type="hidden" name="cpm_form_submit" value="1" />
				 	<button type="submit" class="btn btn-primary btn-danger" onClick="return confirmSubmit('Are you sure you want to delete the selected user(s)?')">Delete Selected User(s)</button>
				</div>
			<?php
			}
			else {
				echo "There doesn't appear to be any MySQL users on this cPanel account.";
			}
			?>
		</form>
		
	<?php
	$aHtml[ 'DatabasesDeleteUser' ] = ob_get_contents();
	ob_end_clean();
	
	?>
			<div id="TabsFunctionMySql" class="tabbable tabs-function">
				<ul class="nav nav-pills">
					<li class="active"><a href="#DatabasesInfo" data-toggle="tab"><i class="icon icon-info-sign"></i></a></li>
					<li><a href="#DatabasesNewDb" data-toggle="tab"><i class="icon icon-plus-sign"></i> Add DB</a></li>
					<li><a href="#DatabasesNewUser" data-toggle="tab"><i class="icon icon-plus-sign"></i> Add DB User</a></li>
					<li><a href="#DatabasesDeleteDb" data-toggle="tab"><i class="icon icon-minus-sign"></i> Del DBs</a></li>
					<li><a href="#DatabasesDeleteUser" data-toggle="tab"><i class="icon icon-minus-sign"></i> Del DB Users</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="DatabasesInfo"><?php echo $aHtml[ 'DatabasesInfo' ]; ?></div>
					<div class="tab-pane" id="DatabasesNewDb"><?php echo $aHtml[ 'DatabasesNewDb' ]; ?></div>
					<div class="tab-pane" id="DatabasesNewUser"><?php echo $aHtml[ 'DatabasesNewUser' ]; ?></div>
					<div class="tab-pane" id="DatabasesDeleteDb"><?php echo $aHtml[ 'DatabasesDeleteDb' ]; ?></div>
					<div class="tab-pane" id="DatabasesDeleteUser"><?php echo $aHtml[ 'DatabasesDeleteUser' ]; ?></div>
				</div>
			</div>
	<?php
	
	
}

