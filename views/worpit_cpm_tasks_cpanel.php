<?php

include_once( dirname(__FILE__).DS.'worpit_cpm_tasks_cpanel_tab_mysql.php' );
include_once( dirname(__FILE__).DS.'worpit_cpm_tasks_cpanel_tab_ftp.php' );
include_once( dirname(__FILE__).DS.'worpit_cpm_tasks_cpanel_tab_email.php' );
include_once( dirname(__FILE__).DS.'worpit_cpm_tasks_cpanel_tab_domains.php' );

	$aConnectionData = array(
			$worpit_cpanel_server_address,
			$worpit_cpanel_server_port,
			$worpit_cpanel_username,
			$worpit_cpanel_password,
			$worpit_nonce_field,
			$worpit_form_action,
	);
	
	$oCpanelApi = validateConnection( $aConnectionData, $aMessages );
	
	if ( $oCpanelApi === false ) {
		foreach ( $aMessages as $sMessage ) {
			?><div class="alert alert-error"><?php echo $sMessage ?></div><?php
		}
	}
	else {
		?>
		<style>
			ul#TabsTopLevelMenu {
				margin-top: 55px;
				margin-right: 0;
			}
			ul#TabsTopLevelMenu > li {
				margin-bottom: 15px;
			}
			#TabsTopLevelContent > div > .well {
				margin: 44px 20px;
			}
			.tabs-function ul {
				list-style-type: square;
			}
			.tabs-function ul.nav-pills {
				margin-bottom: 4px;
				padding-left: 20px;
			}
			.tabs-function .tab-content {
				margin: 0 0 0 10px;
				padding: 10px 5px;
			}
			.tabs-function .well {
				margin: 0px;
			}
			
			/* MYSQL: #08883B; */
			li#MySqlNav a {
				color: #08883B;
			}
			li#MySqlNav.active a {
				color: #555;
			}
			#TabsFunctionMySql ul.nav-pills a {
				color: #08883B;
			}
			#TabsFunctionMySql ul.nav-pills li.active a {
				background-color: #08883B;
				color: #fffffe;
			}
			
			/* FTP: #400888; */
			li#FtpUsersNav a {
				color: #400888;
			}
			li#FtpUsersNav.active a {
				color: #555;
			}
			#TabsFunctionFtp ul.nav-pills a {
				color: #400888;
			}
			#TabsFunctionFtp ul.nav-pills li.active a {
				background-color: #400888;
				color: #fffffe;
			}
			
			/* SubDomains: #9B440A; */
			li#SubDomainsNav a {
				color: #9B440A;
			}
			li#SubDomainsNav.active a {
				color: #555;
			}
			#TabsFunctionSubdomains ul.nav-pills a {
				color: #9B440A;
			}
			#TabsFunctionSubdomains ul.nav-pills li.active a {
				background-color: #9B440A;
				color: #fffffe;
			}
			
			/* Email: #CC008B; */
			li#EmailNav a {
				color: #CC008B;
			}
			li#EmailNav.active a {
				color: #555;
			}
			#TabsFunctionEmail ul.nav-pills a {
				color: #CC008B;
			}
			#TabsFunctionEmail ul.nav-pills li.active a {
				background-color: #CC008B;
				color: #fffffe;
			}
			
			.user_homedir {
				font-family: "Courier New";
			}
		</style>
		<div id="TabsTopLevelFunction" class="tabbable tabs-left">
			<ul id="TabsTopLevelMenu" class="nav nav-tabs">
				<li id="MySqlNav" class="active"><a href="#MySqlTab" data-toggle="tab">MySQL</a></li>
				<li id="FtpUsersNav"><a href="#FtpUsersTab" data-toggle="tab">FTP Users</a></li>
				<li id="EmailNav"><a href="#EmailTab" data-toggle="tab">Email</a></li>
				<li id="SubDomainsNav"><a href="#SubDomainsTab" data-toggle="tab">Domains</a></li>
				<li id="CronListNav"><a href="#CronListTab" data-toggle="tab">Cron Job List</a></li>
			</ul>
			<div id="TabsTopLevelContent" class="tab-content">
				<div class="tab-pane active" id="MySqlTab"><?php echo getContent_MySqlTab( $aConnectionData, $oCpanelApi ); ?></div>
				<div class="tab-pane fade in" id="FtpUsersTab"><?php echo getContent_FtpTab( $aConnectionData, $oCpanelApi ); ?></div>
				<div class="tab-pane fade in" id="EmailTab"><?php echo getContent_EmailTab( $aConnectionData, $oCpanelApi ); ?></div>
				<div class="tab-pane fade in" id="SubDomainsTab"><?php echo getContent_DomainsTab( $aConnectionData, $oCpanelApi ); ?></div>
				<div class="tab-pane fade in" id="CronListTab">Coming Soon...<?php //echo getCpanelInfoHtml( $aConnectionData, $oCpanelApi, 'Cron', 'listcron', 'command_htmlsafe', 'Cron Jobs' ); ?></div>
			</div>
		</div>
		
		<script LANGUAGE="JavaScript">
			function confirmSubmit( sMessage ) {
				var agree=confirm( sMessage );
				if (agree)
					return true ;
				else
					return false ;
			}
		</script>
		<script type='text/javascript'>
			jQuery( document ).ready(
				function () {
					jQuery( '*[rel=popover]')
						.popover();
					
					jQuery( '*[data-popover=popover]')
						.popover();
				}
			);
		</script>
		<?php
	}
	
function validateConnection( $inaData = array(), &$outaMessage = '' ) {

	list($sServerAddress, $sServerPort, $sUsername, $sPassword) = $inaData;
	$outaMessage = array();
	
	$oCpanelApi = false;
	$fValidConnectionData = true;
	
	if ( empty($sServerAddress) ) {
		$outaMessage[] = 'cPanel Server Address is empty.';
		$fValidConnectionData = false;
	}
	
	if ( empty($sServerPort) ) {
		$outaMessage[] = 'cPanel Server Port is empty.';
		$fValidConnectionData = false;
	}
	if ( empty($sUsername) ) {
		$outaMessage[] = 'cPanel Username is empty.';
		$fValidConnectionData = false;
	}
	if ( empty($sPassword) ) {
		$outaMessage[] = 'cPanel Password is empty.';
		$fValidConnectionData = false;
	}
	
	if ( $fValidConnectionData ) {
		try {
			$oCpanelApi = new CPanel_Api( $sServerAddress, $sUsername, $sPassword );
		} catch (Exception $oE) {
			$outaMessage[] = 'Failed to connect to cPanel with credentials provided. Error returned was... <strong>'.$oE->getMessage().'</strong>';
		}
	}

	return $oCpanelApi;
}

function getCpanelInfoHtml( $inaConnectionData, $inoCpanelApi, $insModule, $insFunction, $insKey, $insTitle ) {

	$sHtml = '<div class="well">';
	$sHtml .= "<h3>$insTitle</h3>";
	
	$aData = Worpit_CPanelTransformer::GetDataFromApi( $inoCpanelApi, 'data', $insModule, $insFunction );
	
	if ( $inoCpanelApi->getLastResult() ) { //Last API call was a success.
		if ( !empty($aData) ) {
			$aKeyData = Worpit_CPanelTransformer::CreateArrayFromOneKey( $aData, $insKey );
			
			if ( $insFunction == 'listdbs' ) {
				$sHtml .= getContent_MysqlDbs( $aKeyData, $inaConnectionData );
			} else {
				$sHtml .= getArrayAsList( Worpit_CPanelTransformer::CreateArrayFromOneKey( $aData, $insKey ) );
			}
			
		} else {
			$sHtml .= "There doesn't appear to be any.";
		}
	} else {
		$sHtml .= 'Failed: Could not get the list.';
	}
	
	$sHtml .= '</div><!-- well -->';
	
	return $sHtml;
}

function getBasicDataListArray( $inoCpanelApi, $insModule, $insFunction, $insKey) {
	$aData = Worpit_CPanelTransformer::GetDataFromApi( $inoCpanelApi, 'data', $insModule, $insFunction );
	
	if ( !empty($aData) ) {
		$aData = Worpit_CPanelTransformer::CreateArrayFromOneKey( $aData, $insKey );
	}
	
	return $aData;
}

function getArrayAsList( $inaData ){
	
	$sHtml = '<ul>';
	foreach( $inaData as $sElement ) {
		$sHtml .= '<li>'.$sElement.'</li>';
	}
	$sHtml .= '</ul>';
	
	return $sHtml;
}
	
function getConfirmBoxHtml() {

	return '
			<legend>Confirmation</legend>
			<div class="control-group">
				<label class="control-label" for="confirm_action">Type \'CONFIRM\'</label>
				<div class="controls">
					<input type="text" id="confirm_action"" name="confirm_action" placeholder="Please confirm your intent" value="" />
				</div>
			</div>
	';
}

		
