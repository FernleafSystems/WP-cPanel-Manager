<?php

include_once( dirname(__FILE__).'/../../inc/lib/worpit/Worpit_CPanelTransformer.php' );
include_once( dirname(__FILE__).'/CPM_ActionDelegate_Base.php' );

class CPM_ActionDelegate_Email extends CPM_ActionDelegate_Base {
		
	/**
	 * Assuming inputs are valid, will create a new database and new database user and assign the user will full
	 * permissions to it.
	 * 
	 * $this->m_aData must contain database_name, database_user, database_user_password
	 */
	public function create_emailuser() {
		
		$aVars = array( 'email_new_user', 'email_new_user_password', 'email_new_user_domain', 'email_new_user_quota' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new Email user' ) ) {
			return false;
		}
		
		$fValidState = true;
		$fValidState = self::IsValidEmailPart( $this->m_aData['email_new_user'], $this->m_aMessages ) && $fValidState;
		$fValidState = self::ValidateUserPassword( $this->m_aData['email_new_user_password'], $this->m_aMessages ) && $fValidState;
		$this->m_fGoodToGo = self::ValidateQuota( $this->m_aData['email_new_user_quota'], $this->m_aMessages ) && $fValidState;
		
		if ( !$this->m_fGoodToGo ) {
			$this->m_aMessages[] = $sErrorPrefix."Your inputs had problems. Please Check.";
			return false;
		}
		
		$fSuccess = false;
		
		$fSuccess = $this->createNewEmailUser(
						$this->m_aData['email_new_user'],
						$this->m_aData['email_new_user_password'],
						$this->m_aData['email_new_user_domain'],
						$this->m_aData['email_new_user_quota'] );
		
		return $fSuccess;
		
	}//create_emailuser
	
	public function create_emailusersbulk() {
		
		$aVars = array( 'email_new_user_bulk' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create new Email users' ) ) {
			return false;
		}
		
		if ( !isset($this->m_aData['email_new_user_bulk']) || empty($this->m_aData['email_new_user_bulk']) ) {
			$this->m_aMessages[] = "No new Email User details were provided.";
			return false;
		}
		
		$fValidState = true;
		$aAllNewUsers = array();
		$fValidState = self::ValidateEmailUsersBulk( $this->m_aData['email_new_user_bulk'], $aAllNewUsers, $this->m_aMessages ) && $fValidState;

		if ( $fValidState && !empty($aAllNewUsers) ) {
			
			$sBaseHomedir = trim( $this->m_aData['email_new_user_bulk_homedir'], '/' );
			if ( !empty($sBaseHomedir) ) {
				$sBaseHomedir .= '/';
			}
			
			foreach ( $aAllNewUsers as $sNewEmailUser ) {
				
				$aNewEmailUserDetails = explode( ',', $sNewEmailUser );
				list( $sUsername, $sPassword, $sQuota ) = $aNewEmailUserDetails;
				
				$this->m_aData['email_new_user'] = $sUsername;
				$this->m_aData['email_new_user_password'] = $sPassword;
				$this->m_aData['email_new_user_quota'] = $sQuota;
				$this->m_aData['email_new_user_homedir'] = $sBaseHomedir . $sUsername;
				
				$fValidState = $this->create_emailuser();
				
				if (!$fValidState) {
					break;
				}
			}
		}
		
		
		return $fValidState;
	}
	
	/**
	 * Will delete all databases from the cPanel account with names that correspond to elements
	 * in the array that is populated in position 'databases_to_delete_names' in the main data array.
	 */
	public function delete_emailusers() {
		
		$aVars = array();
		
		if ( !$this->preActionBasicValidate( $aVars, 'to delete Email users' ) ) {
			return false;
		}
		
		if ( !isset( $this->m_aData['email_users_to_delete_names'] ) || !is_array( $this->m_aData['email_users_to_delete_names'] ) ) {
			$this->m_aMessages[] = "No Email users were selected.";
			return false;
		}
		
		$aEmails = $this->m_aData['email_users_to_delete_names'];
		
		$fSuccess = true;
		foreach( $aEmails as $sFullEmail ) {
			
			$aArgs = array ();
			list( $aArgs['email'], $aArgs['domain'] ) = explode( '@', $sFullEmail );
			
			$this->m_oCpanel_Api->doApiFunction( "Email", "delpop", $aArgs );
			$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
			
			if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
				$fSuccess = true;
				$this->m_aMessages[] = "Deleting Email user from cPanel account succeeded: ".$sFullEmail; 
			}
			else {
				$fSuccess = false;
				$this->m_aMessages[] = "Deleting Email user from cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse );
				$this->m_aMessages[] = "Stopping further processing due to previous failure.";
			}
			
			if ( !$fSuccess ) {
				break;
			}
		}
		
		return $fSuccess;
	}
	
	public static function ValidateEmailUsersBulk( $insEmailUserDataBulk, &$inaAllNewUsers, &$inaMessages ) {
		
		$fValidState = true;
		if ( !empty( $insEmailUserDataBulk ) ) {
			
			$inaAllNewUsers = explode( "\n", $insEmailUserDataBulk );

			$iCount = -1;
			foreach( $inaAllNewUsers as $sNewUserLine ) {
				
				$iCount++;
				
				//Remove Empty lines from array to process
				$sNewUserLine = self::CleanupEmailUserBulkString( $sNewUserLine );
				if ( empty($sNewUserLine) ) {
					unset( $inaAllNewUsers[$iCount] );
					continue;
				}
				
				$iCommaCount = substr_count( $sNewUserLine, ',' );

				if ( $iCommaCount != 2 ) {
					$inaMessages[] = "One of the new user entries doesn't have the correct number of values. Check that you have 3 values separated by (2) commas.";
					$fValidState = false;
					break;
				}
				
			}
		}
		else {
			$inaMessages[] = "The new Email User data is blank.";
			$fValidState = false;
		}
		
		return $fValidState;
	}
	
	protected static function CleanupEmailUserBulkString( $insUserString ) {
		
		$insUserString = preg_replace( '/\s+/', '', $insUserString);
		return $insUserString;
	}
	
	
}//CPM_ActionDelegate_Email
