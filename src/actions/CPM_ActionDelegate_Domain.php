<?php

include_once( dirname(__FILE__).'/../../inc/lib/worpit/Worpit_CPanelTransformer.php' );
include_once( dirname(__FILE__).'/CPM_ActionDelegate_Base.php' );

class CPM_ActionDelegate_Domain extends CPM_ActionDelegate_Base {
		
	/**
	 * Assuming inputs are valid, will create a new database and new database user and assign the user will full
	 * permissions to it.
	 * 
	 * $this->m_aData must contain database_name, database_user, database_user_password
	 */
	public function create_subdomain() {
		
		$aVars = array( 'subdomain_new_domain', 'subdomain_parent_domain', 'subdomain_document_root' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new sub domain' ) ) {
			return false;
		}
		
		$this->m_aData['subdomain_parent_domain'] = trim( $this->m_aData['subdomain_parent_domain'], '/' );
		
		$fValidState = true;
		$fValidState = self::ValidateSubDomain( $this->m_aData['subdomain_new_domain'], $this->m_aMessages ) && $fValidState;
		$fValidState = self::ValidateFullDomain( $this->m_aData['subdomain_parent_domain'], $this->m_aMessages ) && $fValidState;
		$this->m_fGoodToGo = self::ValidateDirectory( $this->m_aData['subdomain_document_root'], $this->m_aMessages ) && $fValidState;
		
		if ( !$this->m_fGoodToGo ) {
			$this->m_aMessages[] = "Your inputs had problems. Please Check.";
			return false;
		}
		
		$fSuccess = false;
		$fSuccess = $this->createNewSubDomain(
							$this->m_aData['subdomain_new_domain'],
							$this->m_aData['subdomain_parent_domain'],
							$this->m_aData['subdomain_document_root']
		);
		
		return $fSuccess;
		
	}//create_subdomain

	public function createNewSubDomain( $insDomain, $insParentDomain, $insRootDir ) {
		
		$aArgs = array(
					'domain'		=> $insDomain,
					'rootdomain'	=> $insParentDomain,
					'dir'			=> $insRootDir,
					'disallowdot'	=> 1
				);
		
		$this->m_oCpanel_Api->doApiFunction( "SubDomain", "addsubdomain", $aArgs );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Creating new Sub Domain on cPanel account succeeded: ".$insDomain.'|'.$insParentDomain.'|'.$insRootDir; 
		}
		else {
			$fSuccess = false;
			$this->m_aMessages[] = "Creating new Sub Domain ( $insDomain | $insParentDomain | $insRootDir ) on cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
		
	}//createNewSubDomain
	
	/**
	 * Will delete all databases from the cPanel account with names that correspond to elements
	 * in the array that is populated in position 'databases_to_delete_names' in the main data array.
	 *
	 */
	public function delete_subdomains() {
	
		$aVars = array();
	
		if ( !$this->preActionBasicValidate( $aVars, 'to delete Sub Domains' ) ) {
			return false;
		}
	
		if ( !isset( $this->m_aData['subdomains_to_delete_names'] ) || !is_array( $this->m_aData['subdomains_to_delete_names'] ) ) {
			$this->m_aMessages[] = "No Sub Domains were selected.";
			return false;
		}
	
		$aSubDomains = $this->m_aData['subdomains_to_delete_names'];
	
		$fSuccess = true;
		foreach( $aSubDomains as $sSubDomain ) {
	
			$aArgs = array ( 'domain' => $sSubDomain );
	
			$this->m_oCpanel_Api->doApiFunction( "SubDomain", "delsubdomain", $aArgs );
			$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
	
			if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
				$fSuccess = true;
				$this->m_aMessages[] = "Deleting Sub Domain ($sSubDomain) from cPanel account succeeded.";
			}
			else {
				$fSuccess = false;
				$this->m_aMessages[] = "Deleting Sub Domain ($sSubDomain) from cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse );
				$this->m_aMessages[] = "Stopping further processing due to previous failure.";
			}
	
			if ( !$fSuccess ) {
				break;
			}
		}
	
		return $fSuccess;
	}
	
	public function create_parkeddomain() {
		
		$aVars = array( 'parkeddomain_new_domain', 'parkeddomain_redirect_http', 'parkeddomain_redirect_url' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new parked domain' ) ) {
			return false;
		}
		
		$fValidState = true;
		$this->m_fGoodToGo = self::ValidateFullDomain( $this->m_aData['parkeddomain_new_domain'], $this->m_aMessages ) && $fValidState;
		
		if ( !$this->m_fGoodToGo ) {
			$this->m_aMessages[] = "Your inputs had problems. Please Check.";
			return false;
		}
		
		$fSuccess = true;
		if ( $this->createNewParkedDomain( $this->m_aData['parkeddomain_new_domain'], $this->m_aData['parkeddomain_top_domain'] ) ) {
			
			//redirect if URL is specified
			if ( !empty( $this->m_aData['parkeddomain_redirect_url'] ) ) {
				
				$sRedirectUrl = $this->m_aData['parkeddomain_redirect_http'] . $this->m_aData['parkeddomain_redirect_url'];
				$fSuccess = $this->redirectParkedDomain( $this->m_aData['parkeddomain_new_domain'], $sRedirectUrl);
			}
			
		} else {
			$fSuccess = false;
			$this->m_aMessages[] = "No attempt was made to perform any other actions due to previous error."; 
		}
		
		return $fSuccess;
	}
	
	public function createNewParkedDomain( $insDomain, $insTopDomain = '' ) {
		
		$aArgs = array(	'domain' => $insDomain );
		if ( !empty($insTopDomain) ) {
			$aArgs['topdomain'] = $insTopDomain;
		}
		
		$this->m_oCpanel_Api->doApiFunction( "Park", "park", $aArgs );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		$fSuccess = false;
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Adding new Parked Domain ($insDomain) succeeded."; 
		}
		else {
			$this->m_aMessages[] = "Adding new Parked Domain ($insDomain) FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
		
	}
	
	public function redirectParkedDomain( $insDomain, $insRedirectUrl ) {
		
		$aArgs = array(	'domain' => $insDomain, 'url' => $insRedirectUrl );
		var_dump($aArgs);
		$this->m_oCpanel_Api->doApiFunction( "Park", "setredirecturl", $aArgs );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		$fSuccess = false;
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Redirecting Parked Domain ($insDomain => $insRedirectUrl) succeeded."; 
		}
		else {
			$this->m_aMessages[] = "Redirecting Parked Domain ($insDomain => $insRedirectUrl) FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
		
	}
	
	public function delete_parkeddomains() {
	
		$aVars = array();
	
		if ( !$this->preActionBasicValidate( $aVars, 'to delete Parked Domains' ) ) {
			return false;
		}
	
		if ( !isset( $this->m_aData['parkeddomains_to_delete_names'] ) || !is_array( $this->m_aData['parkeddomains_to_delete_names'] ) ) {
			$this->m_aMessages[] = "No Parked Domains were selected.";
			return false;
		}
	
		$aParkedDomains = $this->m_aData['parkeddomains_to_delete_names'];
	
		$fSuccess = true;
		foreach( $aParkedDomains as $sDomain ) {

			$aArgs = array ( 'domain' => $sDomain );
	
			$this->m_oCpanel_Api->doApiFunction( "Park", "unpark", $aArgs );
			$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
	
			if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
				$this->m_aMessages[] = "Deleting Parked Domain ($sDomain) from cPanel account succeeded.";
			}
			else {
				$fSuccess = false;
				$this->m_aMessages[] = "Deleting Parked Domain ($sDomain) from cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse );
				$this->m_aMessages[] = "Stopping further processing due to previous failure.";
			}
	
			if ( !$fSuccess ) {
				break;
			}
		}
	
		return $fSuccess;
		
		
	}
	
	public function create_addondomain() {
		
		$aVars = array( 'addondomain_new_domain', 'addondomain_subdomain_name', 'addondomain_ftp_password', 'addondomain_document_root' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new Addon Domain' ) ) {
			return false;
		}
		
		$this->m_aData['addondomain_document_root'] = trim( $this->m_aData['addondomain_document_root'], '/' );
		
		$fValidState = true;
		$fValidState = self::ValidateFullDomain( $this->m_aData['addondomain_new_domain'], $this->m_aMessages ) && $fValidState;
		$fValidState = self::ValidateFtpUser( $this->m_aData['addondomain_subdomain_name'], $this->m_aMessages ) && $fValidState;
		$this->m_fGoodToGo = self::ValidateDirectory( $this->m_aData['addondomain_document_root'], $this->m_aMessages ) && $fValidState;
		
		if ( empty( $this->m_aData['addondomain_ftp_password'] ) ) {
			$fCreateFtpUser = false;
		}
		else {
			$fCreateFtpUser = true;
			$fValidState = self::ValidateUserPassword( $this->m_aData['addondomain_ftp_password'], $this->m_aMessages ) && $fValidState;
		}
		
		if ( !$this->m_fGoodToGo ) {
			$this->m_aMessages[] = "Your inputs had problems. Please Check.";
			return false;
		}
		
		$fSuccess = $this->createNewAddonDomain( $this->m_aData['addondomain_new_domain'], $this->m_aData['addondomain_document_root'], $this->m_aData['addondomain_subdomain_name'] );
		
		if ( $fCreateFtpUser && !$fSuccess ) {
			$this->m_aMessages[] = "No attempt was made to create the new FTP user because of the previous error.";
		}
		else if ( $fCreateFtpUser ) {
			
			//Create new FTP USER.
			$fSuccess = $this->createNewFtpUser( $this->m_aData['addondomain_subdomain_name'], $this->m_aData['addondomain_ftp_password'], 0, $this->m_aData['addondomain_document_root'] );
		}
		
		return $fSuccess;
		
	}//create_addondomain
	
	public function createNewAddonDomain( $insDomain, $insRootDir, $insSubDomain ) {
		
		$aArgs = array(
					'newdomain'	=>	$insDomain,
					'dir'		=>	$insRootDir,
					'subdomain'	=>	$insSubDomain,
		);
		
		$this->m_oCpanel_Api->doApiFunction( "AddonDomain", "addaddondomain", $aArgs );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		$fSuccess = false;
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Adding new Addon Domain ($insDomain) succeeded."; 
		}
		else {
			$this->m_aMessages[] = "Adding new Addon Domain ($insDomain) FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
	}
	
	public function delete_addondomains() {
	
		$aVars = array();
	
		if ( !$this->preActionBasicValidate( $aVars, 'to delete Addon Domains' ) ) {
			return false;
		}
	
		if ( !isset( $this->m_aData['addondomains_to_delete_names'] ) || !is_array( $this->m_aData['addondomains_to_delete_names'] ) ) {
			$this->m_aMessages[] = "No Addon Domains were selected.";
			return false;
		}
	
		$aAddonDomains = $this->m_aData['addondomains_to_delete_names'];
	
		$fSuccess = true;
		foreach( $aAddonDomains as $sDomain ) {
			
			$aAddonDomainParts = explode( '_', $sDomain, 2 );
			
			if ( count( $aAddonDomainParts ) != 2 ) {
				$this->m_aMessages[] = "Unexpected data to do addon domain delete: $sDomain";
				$fSuccess = false;
				break;
			}
			
			list ( $sAddonDomainName, $sSubDomainPart ) = $aAddonDomainParts;
			
			$aArgs = array ( 'domain' => $sAddonDomainName, 'subdomain' => $sSubDomainPart, );

			$this->m_oCpanel_Api->doApiFunction( "AddonDomain", "deladdondomain", $aArgs );
			$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
	
			if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
				$this->m_aMessages[] = "Deleting Addon Domain ($sAddonDomainName) and associated data succeeded.";
			}
			else {
				$fSuccess = false;
				$this->m_aMessages[] = "Deleting Addon Domain ($sAddonDomainName) FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse );
				$this->m_aMessages[] = "Stopping further processing due to previous failure.";
			}
	
			if ( !$fSuccess ) {
				break;
			}
		}
	
		return $fSuccess;
		
	}
	
	public function create_ftpusersbulk() {
		
		$aVars = array( 'ftp_new_user_bulk' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create new FTP users' ) ) {
			return false;
		}
		
		if ( !isset($this->m_aData['ftp_new_user_bulk']) || empty($this->m_aData['ftp_new_user_bulk']) ) {
			$this->m_aMessages[] = "No new FTP User details were provided.";
			return false;
		}
		
		$fValidState = true;
		$aAllNewUsers = array();
		$fValidState = self::ValidateFtpUsersBulk( $this->m_aData['ftp_new_user_bulk'], $aAllNewUsers, $this->m_aMessages ) && $fValidState;

		if ( $fValidState && !empty($aAllNewUsers) ) {
			
			$sBaseHomedir = trim( $this->m_aData['ftp_new_user_bulk_homedir'], '/' );
			if ( !empty($sBaseHomedir) ) {
				$sBaseHomedir .= '/';
			}
			
			foreach ( $aAllNewUsers as $sNewFtpUser ) {
				
				$aNewFtpUserDetails = explode( ',', $sNewFtpUser );
				list( $sUsername, $sPassword, $sQuota ) = $aNewFtpUserDetails;
				
				$this->m_aData['ftp_new_user'] = $sUsername;
				$this->m_aData['ftp_new_user_password'] = $sPassword;
				$this->m_aData['ftp_new_user_quota'] = $sQuota;
				$this->m_aData['ftp_new_user_homedir'] = $sBaseHomedir . $sUsername;
				
				$fValidState = $this->create_ftpuser();
				
				if (!$fValidState) {
					break;
				}
			}
		}
		
		
		return $fValidState;
	}
	
	
	
	public static function ValidateSubDomain( $insTestString, &$aMessages ) {
	
		$fValidState = true;
		if ( !empty( $insTestString ) ) {
	
			if ( !self::IsValidSubDomain($insTestString) ) {
				$aMessages[] = "The Sub Domain provided isn't a valid sub domain name.";
				$fValidState = false;
			}
		}
		else {
			$aMessages[] = "The Sub Domain option is blank.";
			$fValidState = false;
		}
	
		return $fValidState;
	}
	
	public static function ValidateFullDomain( $insTestString, &$aMessages ) {
		
		$fValidState = true;
		if ( !empty( $insTestString ) ) {
	
			if ( !self::IsValidDomainName($insTestString) ) {
				$aMessages[] = "The Domain provided isn't a valid domain name.";
				$fValidState = false;
			}
		}
		else {
			$aMessages[] = "The Domain option is blank.";
			$fValidState = false;
		}
		
		return $fValidState;
	}
	
}//CPM_ActionDelegate_Domain
