<?php

include_once( dirname(__FILE__).'/../../inc/lib/worpit/Worpit_CPanelTransformer.php' );
include_once( dirname(__FILE__).'/CPM_ActionDelegate_Base.php' );

class CPM_ActionDelegate_Database extends CPM_ActionDelegate_Base {
	
	public function __construct( $inaData, $inaCpanelCreds ) {
		$this->m_aData = $inaData;
		$this->m_fGoodToGo = $this->connectToCpanel( $inaCpanelCreds );
	}
	
	/**
	 * Assuming inputs are valid, will create a new database and new database user and assign the user will full
	 * permissions to it.
	 * 
	 * $this->m_aData must contain database_name, database_user, database_user_password
	 */
	public function createdb_adduser() {
		
		$aVars = array( 'database_name', 'database_user', 'database_user_password' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new database and add new user' ) ) {
			return false;
		}
		
		$fValidState = true;
		$fValidState = self::ValidateDatabaseName( $this->m_aData['database_name'], $this->m_aMessages ) && $fValidState;
		$fValidState = self::ValidateDatabaseUser( $this->m_aData['database_user'], $this->m_aMessages ) && $fValidState;
		$this->m_fGoodToGo = self::ValidateUserPassword( $this->m_aData['database_user_password'], $this->m_aMessages ) && $fValidState;
		
		if ( !$this->m_fGoodToGo ) {
			$this->m_aMessages[] = "Your inputs had problems. Please Check.";
			return false;
		}
		
		$fSuccess = false;
		if ( $this->createNewMySqlDb( $this->m_aData['database_name'] ) ) { // Successfully created database
			
			// Successfully created new user
			if ( $this->createNewMySqlUser( $this->m_aData['database_user'], $this->m_aData['database_user_password'] ) ) {
				$fSuccess = $this->addMySqlUserToDb( $this->m_aData['database_name'], $this->m_aData['database_user'] );
			}
			else {
				$this->m_aMessages[] = "Did not attempt to add user to DB due to previous error.";
			}
			
		}
		else {
			$this->m_aMessages[] = "Did not attempt to create new user due to previous error.";
		}
		
		return $fSuccess;
		
	}//createdb_adduser
	
	/**
	 * Will create new MySQL user and add it to a list of Databases if specified.
	 * 
	 * 'database_new_user'
	 * 'database_new_user_password'
	 * 'databases_to_add_new_user[]'
	 */
	public function create_mysqluser() {
		
		$aVars = array( 'database_new_user', 'database_new_user_password' );
		
		if ( !$this->preActionBasicValidate( $aVars, 'create new MySQL user' ) ) {
			return false;
		}

		$fValidState = true;
		$fValidState = self::ValidateDatabaseUser( $this->m_aData['database_new_user'], $this->m_aMessages ) && $fValidState;
		$this->m_fGoodToGo = self::ValidateUserPassword( $this->m_aData['database_new_user_password'], $this->m_aMessages ) && $fValidState;
		
		if ( !$this->m_fGoodToGo ) {
			$this->m_aMessages[] = "Your inputs had problems. Please Check.";
			return false;
		}
		
		$fSuccess = false;
		if ( $this->createNewMySqlUser( $this->m_aData['database_new_user'], $this->m_aData['database_new_user_password'] ) ) { // Successfully created user
			
			// Add User to DBs if they're specified
			if ( isset( $this->m_aData['databases_to_add_new_user'] ) && is_array( $this->m_aData['databases_to_add_new_user'] ) ) {
				
				foreach( $this->m_aData['databases_to_add_new_user'] as $sDb ) {
					$fSuccess = $this->addMySqlUserToDb( $sDb, $this->m_aData['database_new_user'] );
				}
			}
		}
		else {
			$this->m_aMessages[] = "Did not attempt to add new user to databases due to previous error.";
		}
		
		return true;
		
	}//create_mysqluser
	
	/**
	 * Will delete all databases from the cPanel account with names that correspond to elements
	 * in the array that is populated in position 'databases_to_delete_names' in the main data array. 
	 * 
	 */
	public function delete_mysqldbs() {
		
		$aVars = array();
		
		if ( !$this->preActionBasicValidate( $aVars, 'delete MySQL databases' ) ) {
			return false;
		}
		
		if ( !isset( $this->m_aData['databases_to_delete_names'] ) || !is_array( $this->m_aData['databases_to_delete_names'] ) ) {
			$this->m_aMessages[] = "No MySQL databases were selected.";
			return false;
		}
		
		$aDatabaseNames = $this->m_aData['databases_to_delete_names'];
		
		$fSuccess = true;
		foreach( $aDatabaseNames as $sDatabaseName ) {
		
			$this->m_oCpanel_Api->doApiFunction( "Mysql", "deldb", array( $sDatabaseName ) );
			$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
			
			if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
				$fSuccess = true;
				$this->m_aMessages[] = "Deleting MySQL database from cPanel account succeeded: ".$sDatabaseName; 
			}
			else {
				$fSuccess = false;
				$this->m_aMessages[] = "Deleting MySQL database from cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse );
				$this->m_aMessages[] = "Stopping further processing due to previous failure.";
			}
			
			if ( !$fSuccess ) {
				break;
			}
		}
		
		return $fSuccess;
	}
	
	
	/**
	 * Will delete all databases from the cPanel account with names that correspond to elements
	 * in the array that is populated in position 'databases_to_delete_names' in the main data array. 
	 * 
	 */
	public function delete_mysqlusers() {
		
		$aVars = array();
		
		if ( !$this->preActionBasicValidate( $aVars, 'delete MySQL users' ) ) {
			return false;
		}
		
		if ( !isset( $this->m_aData['users_to_delete_names'] ) || !is_array( $this->m_aData['users_to_delete_names'] ) ) {
			$this->m_aMessages[] = "No MySQL users were selected.";
			return false;
		}
		
		$aUserNames = $this->m_aData['users_to_delete_names'];
		
		$fSuccess = true;
		foreach( $aUserNames as $sUserName ) {
		
			$this->m_oCpanel_Api->doApiFunction( "Mysql", "deluser", array( $sUserName ) );
			$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
			
			if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
				$fSuccess = true;
				$this->m_aMessages[] = "Deleting MySQL user from cPanel account succeeded: ".$sUserName; 
			}
			else {
				$fSuccess = false;
				$this->m_aMessages[] = "Deleting MySQL user from cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse );
				$this->m_aMessages[] = "Stopping further processing due to previous failure.";
			}
			
			if ( !$fSuccess ) {
				break;
			}
		}
		
		return $fSuccess;
	}
	
	/**
	 * Creates a new database on the current cPanel connection with the given name provided in the data.
	 * 
	 * $inaData must contain the alphanumeric field: database_name
	 * 
	 * There is the option to send new data with $inaData.
	 */
	protected function createNewMySqlDb( $sDbName ) {
		
		$aVars = array();
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new MySQL database' ) ) {
			return false;
		}
		
		$this->m_oCpanel_Api->doApiFunction( "Mysql", "adddb", array( $sDbName ) );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Adding new MySQL database to cPanel account succeeded: ".$this->m_aData['database_name']; 
		}
		else {
			$fSuccess = false;
			$this->m_aMessages[] = "Adding new MySQL database to cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
		
	}
	
	public function createNewMySqlUser( $sUsername, $sPassword ) {

		$aVars = array();
		
		if ( !$this->preActionBasicValidate( $aVars, 'create a new MySQL User' ) ) {
			return false;
		}
		
		$this->m_oCpanel_Api->doApiFunction( "Mysql", "adduser", array( $sUsername, $sPassword ) );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Creating new MySQL User on cPanel account succeeded: ".$sUsername .' / '. $sPassword; 
		}
		else {
			$fSuccess = false;
			$this->m_aMessages[] = "Creating new MySQL User on cPanel account FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
		
	}//createNewMySqlUser
	
	public function addMySqlUserToDb( $sDbName, $sUsername ) {
		
		$aVars = array();
		
		if ( !$this->preActionBasicValidate( $aVars, 'add new MySQL User to the DB' ) ) {
			return false;
		}
		
		$this->m_oCpanel_Api->doApiFunction( "Mysql", "adduserdb", array( $sDbName, $sUsername, 'all'  ) );
		$this->m_oLastApiResponse = $this->m_oCpanel_Api->getLastResponse();
		
		if ( Worpit_CPanelTransformer::GetLastSuccess( $this->m_oLastApiResponse ) ) {
			$fSuccess = true;
			$this->m_aMessages[] = "Adding new MySQL User ('$sUsername') to DB ('$sDbName') succeeded."; 
		}
		else {
			$fSuccess = false;
			$this->m_aMessages[] = "Adding new MySQL User to DB FAILED: ". Worpit_CPanelTransformer::GetLastError( $this->m_oLastApiResponse ); 
		}
		
		return $fSuccess;
		
	}
	
}//class
			