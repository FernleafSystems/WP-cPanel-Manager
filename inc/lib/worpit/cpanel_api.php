<?php

/**
 * Copyright (c) 2012 Worpit <support@worpit.com>
 * All rights reserved.
 *
 * "cPanel Manager for WordPress, from Worpit" is
 * distributed under the GNU General Public License, Version 2,
 * June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

include_once( dirname(__FILE__).'/../xmlapi-php/xmlapi.php' );

//define( 'XMLAPI_USE_SSL', 0 );

class CPanel_Api {
	
	const PORT		= 2083;
	const DEBUG	 	= 0;
	
	private $m_oXmlApi;
	
	private $m_sIP;
	private $m_sUsername;
	private $m_sPassword;
	
	private $m_aLastResponse;
	
	public static $API1_FUNCTIONS = array(
		'Fileman'	=> array( 'changeperm', 'fullbackup' ),
		'Ftp'		=> array( 'getftpquota', 'get_welcomemsg', 'set_welcomemsg' ),
		'Mysql'		=> array( 'adddb', 'adduser', 'adduserdb', 'deldb', 'deluser'),
		'Park'		=> array( 'setredirecturl', 'getredirecturl', 'disableredirect' )
	);
	
	public static $API2_FUNCTIONS = array(
		'Cron'			=> array( 'listcron', 'add_line', 'remove_line', 'edit_line', 'get_email', 'set_email' ),
		'Email'			=> array( 'addpop', 'delpop', 'listpopssingle' ),
		'Fileman'		=> array( 'fileop' ),
		'Ftp'			=> array( 'addftp', 'delftp', 'listftp', 'listftpwithdisk', 'passwd', 'setquota' ),
		'MysqlFE'		=> array( 'listdbs', 'listusers', 'listusersindb' ),
		'DomainLookup'	=> array( 'getbasedomains', 'getdocroot', 'getdocroots', 'countbasedomains' ),
		'AddonDomain'	=> array( 'deladdondomain', 'addaddondomain', 'listaddondomains' ),
		'SubDomain'		=> array( 'listsubdomains', 'delsubdomain', 'addsubdomain' ),
		'Park'			=> array( 'park', 'unpark', 'listparkeddomains', 'listaddondomains' ),
		'StatsBar'		=> array( 'stat' ),
	);
	
	public function __construct( $insIP, $insUsername, $insPassword ) {
		
		$this->m_sIP = $insIP;
		$this->m_sUsername = $insUsername;
		$this->m_sPassword = $insPassword;
		
		$this->m_oXmlApi = new xmlapi( $insIP );
		$this->m_oXmlApi->password_auth( $insUsername, $insPassword );
		$this->m_oXmlApi->set_http_client( 'curl' );
		$this->m_oXmlApi->set_port( self::PORT );
		$this->m_oXmlApi->set_debug( self::DEBUG ); //this setting will put output into the error log in the directory that you are calling script from
		$this->m_oXmlApi->set_output( 'array' ); //set this for browser output
		
		try {
			$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Serverinfo", "servicestatus" );
		}
		catch ( Exception $oE ) {
			throw new Exception( "Failed to connect to the host. The error was: ".$oE->getMessage() );
		}
		
		if ( isset( $this->m_aLastResponse['data']['result'] ) && $this->m_aLastResponse['data']['result'] === "0" ) {
			//var_dump( $this->m_aLastResponse );
			throw new Exception( 'CPanel: '.$this->m_aLastResponse['data']['reason'] );
		}
	}
	
	public function getLastResponse() {
		return $this->m_aLastResponse;
	}
	
	public function setLastResponse( $inaResponse ) {
		$this->m_aLastResponse = $inaResponse;
	}
	
	public function getLastResult() {
		
		if ( isset( $this->m_aLastResponse['error'] ) ) {
			return false;
		}
		
		if ( isset( $this->m_aLastResponse['event']['result'] ) && $this->m_aLastResponse['event']['result'] === "1" ) {
			return true;
		} else {
			return false;
		}
	}//getLastResult
	
	/**
	 * "Mysql", "adddb", array( "Database Name" )
	 * "Mysql", "adduser", array( "Username", "Password" )
	 * "Mysql", "deluser", array( "Username" )
	 * "Mysql", "deldb", array( "Database Name" )
	 * "Mysql", "adduserdb", array( "Database Name", "Username", 'all' )
	 * "Ftp", "addftp", array( 'user' => , 'pass' => , 'quota' => , 'homedir'	=> ) ;
	 *  
	 * @param String $insModule
	 * @param String $insFunction
	 * @param variable type $inArgs
	 */
	public function doApiFunction( $insModule, $insFunction, $inArgs = null ) {
		
		if ( array_key_exists( $insModule, self::$API2_FUNCTIONS ) && in_array( $insFunction, self::$API2_FUNCTIONS[$insModule] ) ) {
			
			$sApiQuery = 'api2_query';
			
		} else if ( array_key_exists( $insModule, self::$API1_FUNCTIONS ) && in_array( $insFunction, self::$API1_FUNCTIONS[$insModule] ) ) {
			
			$sApiQuery = 'api1_query';
			
		} else {
			return false;
		}
		
		if ( is_null($inArgs) ) {
			$this->m_aLastResponse = $this->m_oXmlApi->{$sApiQuery}( $this->m_sUsername, $insModule, $insFunction );
		} else {
			$this->m_aLastResponse = $this->m_oXmlApi->{$sApiQuery}( $this->m_sUsername, $insModule, $insFunction, $inArgs );
		}
		
		$fSuccess = $this->getLastResult();
		return $fSuccess;
		
	}//doApiFunction
	
	public function getMysqlDatabaseStats() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "StatsBar", "stat", array( 'display' => 'sqldatabases' ) );
		
		if ( !isset( $this->m_aLastResponse['data'] ) ) {
			return array(
				'count'	=> -1,
				'max'	=> -1
			);
		}
		
		$aData = array(
			'count'		=> trim( $this->m_aLastResponse['data']['count'] ),
			'max'		=> trim( $this->m_aLastResponse['data']['max'] )
		);
		
		if ( $aData['max'] == 'unlimited' ) {
			$aData['max'] = 999999;
		}
		return $aData;
	}

	public function listDnsZones(  ) {
		$this->m_aLastResponse = $this->m_oXmlApi->xmlapi_query( "listzones" );
	}
	
	public function getDocumentRoot() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "DomainLookup", "getdocroot" );
		return rtrim( $this->m_aLastResponse['data']['docroot'], '/' ).'/';
	}
	
	public function getMysqlDatabaseList() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "MysqlFE", "listdbs" );
		if ( !isset( $this->m_aLastResponse['data'] ) ) {
			return array();
		}
		$aDatabases = array();
		foreach ( $this->m_aLastResponse['data'] as $nIndex => $aData ) {
			$aDatabases[] = $aData['db'];
		}
		return $aDatabases;
	}
	
	public function getMysqlUsernameList() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "MysqlFE", "listusers" );
		
		if ( !isset( $this->m_aLastResponse['data'] ) ) {
			return array();
		}
		
		if ( isset( $this->m_aLastResponse['data']['dblist'] ) ) {
			$aDbList = $this->m_aLastResponse['data'];
			$this->m_aLastResponse['data'] = array( $aDbList );
		}
		
		$aUsers = array();
		
		foreach ( $this->m_aLastResponse['data'] as $nIndex => $aData ) {
			if ( !isset( $aData['user'] ) ) {
				throw new Exception( 'CPanel_Api::'.__METHOD__.': Propery \'user\' does not exist ('.print_r( $aData, true ).')' );
			}
			$aUsers[] = $aData['user'];
		}
		
		return $aUsers;
	}
	
	public function getNextAvailableDatabaseName( $insDesiredName ) {
		$aDatabases = $this->getMysqlDatabaseList();
		$i = 0;
		do {
			$fNameUsed = false;
			$sSearch = $insDesiredName.($i == 0? '': $i);
			
			for ( $j = 0; $j < count( $aDatabases ); $j++ ) {
				if ( $aDatabases[$j] == $this->m_sUsername.'_'.$sSearch ) {
					$i++;
					$fNameUsed = true;
					break;
				}
			}
		}
		while ( $fNameUsed );
		
		return $sSearch;
	}
	
	public function getNextAvailableDatabaseUsername( $insDesiredName ) {
		$aUsernames = $this->getMysqlUsernameList();
		$i = 0;
		do {
			$fNameUsed = false;
			$sSearch = $insDesiredName.($i == 0? '': $i);
			
			for ( $j = 0; $j < count( $aUsernames ); $j++ ) {
				if ( $aUsernames[$j] == $this->m_sUsername.'_'.$sSearch ) {
					$i++;
					$fNameUsed = true;
					break;
				}
			}
		}
		while ( $fNameUsed );
		
		return $sSearch;
	}
	
	public function createMysqlDatabase( $insName ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Mysql", "adddb", array( $insName ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function createMysqlUser( $insUsername, $insPassword ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Mysql", "adduser", array( $insUsername, $insPassword ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function addMysqlUserToDatabase( $insUsername, $insDatabase ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Mysql", "adduserdb", array( $insDatabase, $insUsername, 'all' ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function deleteMysqlDatabase( $insName ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Mysql", "deldb", array( $insName ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function deleteMysqlUser( $insUsername ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Mysql", "deluser", array( $insUsername ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getPrimaryDomain() {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "print", "", array( '$CPDATA{\'DOMAIN\'}' ) );
		return $this->getLastResult();
	}
	
	public function getHomeDirectory() {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "print", "", array( '$homedir' ) );
		return $this->getLastResult();
	}
	
	public function getStatsBar_stats( $infFull = true ) {
		$aStatOptions = array(
			'ftpaccounts', 'perlversion', 'dedicatedip', 'hostname', 'operatingsystem',
			'sendmailpath', 'autoresponders', 'perlpath', 'emailforwarders', 'bandwidthusage',
			'emailfilters', 'mailinglists', 'diskusage', 'phpversion', 'sqldatabases',
			'apacheversion', 'kernelversion', 'shorthostname', 'parkeddomains',
			'cpanelbuild', 'theme', 'addondomains', 'cpanelrevision', 'machinetype',
			'cpanelversion', 'mysqldiskusage', 'mysqlversion', 'subdomains',
			'sharedip', 'hostingpackage', 'emailaccounts'
		);
		//postgresdiskusage
		
		$aStatOptions = array(
			'bandwidthusage', 'diskusage', 'sqldatabases', 'subdomains', 'phpversion',
			'cpanelversion', 'cpanelrevision', 'sharedip'//, 'dedicatedip'
		);
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "StatsBar", "stat", array( 'display' => implode( '|', $aStatOptions ) ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getFtp_listftp() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "Ftp", "listftp" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getUserHttpUtils_getdirindices() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "UserHttpUtils", "getdirindices" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getPHPINI_getalloptions() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "PHPINI", "getalloptions" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getDomainLookup_getbasedomains() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "DomainLookup", "getbasedomains" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getDomainLookup_getdocroot( $insDomain ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "DomainLookup", "getdocroot",
			array( 'domain' => $insDomain ) );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getDomainLookup_getdocroots() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "DomainLookup", "getdocroots" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getSubDomain_listsubdomains() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "SubDomain", "listsubdomains" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getPark_listparkeddomains() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "Park", "listparkeddomains" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getPark_listaddondomains() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "Park", "listaddondomains" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getCron_listcron() {
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "Cron", "listcron" );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function getFileman_fullbackup( $inaArgs ) {
		$this->m_aLastResponse = $this->m_oXmlApi->api1_query( $this->m_sUsername, "Fileman", "fullbackup", $inaArgs );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function deleteDirectory( $insSourceDir ) {
		$aArgs = array(
			'op'			=> 'unlink',
			'sourcefiles'	=> trim( $insSourceDir ),
			'destfiles'		=> '',
			'doubledecode'	=> 0,
			'metadata'		=> ''
		);
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, "Fileman", "fileop", $aArgs );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
	
	public function addFtpUser( $insUsername, $insPassword, $iniQuota, $insHomedir ) {
		
		$aArgs = array(
			'user'		=> $insUsername,
			'pass'		=> $insPassword,
			'quota'		=> $iniQuota,
			'homedir'	=> $insHomedir
		);
		$this->m_aLastResponse = $this->m_oXmlApi->api2_query( $this->m_sUsername, 'Ftp', 'addftp', $aArgs );
		return ( $this->m_aLastResponse['event']['result'] == 1 );
	}
}