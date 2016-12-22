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

include_once( dirname(__FILE__).'/cpanel_api.php' );

class Worpit_CPanelTransformer {
	
	public function __construct( ) { }
	
	public static function GetDataFromApi( $inoCpanel_Api, $insDataExtract, $insModule, $insFunction, $inaArgs = null ) {
		
		$inoCpanel_Api->doApiFunction( $insModule, $insFunction, $inaArgs );
		$aResponseData = $inoCpanel_Api->getLastResponse();
		
		return self::GetDataFromResponse( $aResponseData, $insDataExtract );
		
	}
	
	public static function GetDataFromResponse( $inaApiResponse, $insDataExtract ) {
		
		$aKeys = explode( '_', $insDataExtract);
		
		$aExtractData = $inaApiResponse;
		foreach( $aKeys as $key ) {
			
			if ( isset( $aExtractData[ $key ] ) ) {
				$aExtractData = &$aExtractData[ $key ];
				continue;
			} else {
				$aExtractData = array();
				break;
			}
		}
		return $aExtractData;
	}

	public static function GetLastSuccess( $inaApiResponse ) {

		if ( isset( $inaApiResponse['error'] ) ) {
			return false;
		}
		
		if ( isset( $inaApiResponse['event']['result'] ) && $inaApiResponse['event']['result'] === "1" ) {
			return true;
		}
		else {
			return false;
		}
	
	}
	
	public static function GetLastError( $inaApiResponse ) {
		
		if ( isset( $inaApiResponse['error'] ) ) {
			return $inaApiResponse['error'];
		} else {
			return '';
		}
	}
	
	public static function GetPrimaryDomain( $inaApiResponse ) {
		
		$sPrimaryDomain = '';
		
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $sPrimaryDomain;
		}
	
		$aData = self::GetDataArray( $inaApiResponse );
		
		if ( isset( $aData['result'] ) ) {
			$sPrimaryDomain = $aData['result'];
		}

		return $sPrimaryDomain;
	}
	
	/**
	 * Returns a 1D array of all MySQL databases on a cPanel account.
	 * 
	 * @param unknown_type $inaApiResponse
	 */
	public static function GetList_MySqlDbNames( $inaApiResponse ) {
		
		$aDbNamesList = array();
		
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aDbNamesList;
		}
	
		$aDbData = self::GetDataArray( $inaApiResponse, 'db' );
		
		foreach( $aDbData as $aDb ) {
			$aDbNamesList[] = $aDb['db'];
		}

		return $aDbNamesList;
	}
	
	/**
	 * Assumes last API call was: 'MysqlFE', 'listusers'
	 * 
	 * @param $inaApiResponse
	 */
	public static function GetList_AllMySqlUsers( $inaApiResponse ) {

		$aUsersList = array();
		
		if ( is_null($inaApiResponse) || !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aUsersList;
		}
	
		$aDbUserData = self::GetDataArray( $inaApiResponse, 'user' );
		
		foreach( $aDbUserData as $aDbUser ) {
			$aUsersList[] = $aDbUser['user'];
		}
		
		return $aUsersList;
		
	}//GetList_AllMySqlUsers
	
	/**
	 * Returns a 1D array of all MySQL users on a particular database.
	 * 
	 * @param $inaApiResponse
	 */
	public static function GetList_MySqlUsersOnDb( $inaApiResponse, $sDbName ) {
		
		$aUsersList = array();
		
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aUsersList;
		}
		
		$aDb = self::GetData_MySqlDb( $inaApiResponse, $sDbName );

		if ( !empty($aDb) ) { //The DB even exists.

			$sDbUserCount = $aDb[ 'usercount' ];
			
			if ( $aDb[ 'usercount' ] > 0 ) {
				
				$aDbUserList = $aDb[ 'userlist' ];
				
				if ( $sDbUserCount == 1 ) {
					$aUsersList[] = $aDbUserList['user'];
				}
				else {
					$iCount = 0;
					while( $iCount < $sDbUserCount ) {
						$aUsersList[] = $aDbUserList[$iCount]['user'];
						$iCount++;
					}
				}
			}
		}
		
		if ( !empty($aDbData) ) {
			if ( array_key_exists('db', $aDbData) ) { //there's only 1 Database in this data set
				$aUsersList[] = $aDbData['db'];
			}
			else {
				foreach( $aDbData as $aDb ) {
					$aUsersList[] = $aDb['db'];
				}
			}
		}
	
		return $aUsersList;
	}
	
	public static function GetData_MySqlDb( $inaApiResponse, $sDbName ) {
		
		$aDb = array();
		
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aDb;
		}
		
		$aAllDbData = self::GetDataArray( $inaApiResponse, 'db' );

		if ( !empty($aAllDbData) ) {
			
			foreach( $aAllDbData as $aDbData ) {
				
				if ( $aDbData['db'] == $sDbName ) {
					$aDb = $aDbData;
				}
			}
		}
		
		return $aDb;
		
	}//GetData_MySqlDb
	
	/**
	 * 
	 * @param $inaApiResponse
	 */
	public static function GetData_MainFtpUser( $inaApiResponse ) {
		
		$aAllFtpUsers = self::GetDataArray( $inaApiResponse, 'user' );
		$aMainFtpUser = array();
		
		if ( empty($aAllFtpUsers) ) { //Last API call failed.
			return $aMainFtpUser;
		}
		
		foreach ( $aAllFtpUsers as $aFtpUser  ) {
			
			if ( isset($aFtpUser['type']) && $aFtpUser['type'] == 'main' ) {
				$aMainFtpUser = $aFtpUser;
			}
			
		}
		
		return $aMainFtpUser;
		
	}//GetData_MainFtpUser
	
	/**
	 * 
	 * @param $inaApiResponse
	 */
	public static function GetList_AllFtpUsers( $inaApiResponse ) {
		
		return self::GetListFromData( $inaApiResponse, 'user' );
		
	}//GetList_AllFtpUsers
	
	/**
	 * 
	 * @param $inaApiResponse
	 */
	public static function GetListFromData( $inaApiResponse, $insDataKey, $insListIdKey = '' ) {
		
		$aList = array();
		
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aList;
		}
		
		$aData = self::GetDataArray( $inaApiResponse, $insDataKey );

		if ( empty($insListIdKey) ) {
			$insListIdKey = $insDataKey;
		}
		
		if ( !empty($aData) ) { //Last API call passed.
			
			foreach ( $aData as $aElement  ) {
				if ( isset($aElement[$insListIdKey]) ) {
					$aList[] = $aElement[$insListIdKey];
				}
			}
		}
		
		return $aList;
		
	}//GetListFromData
	
	/**
	 * 
	 * @param $inaApiResponse
	 */
	public static function GetData_MainDomain( $inaApiResponse ) {
		
		$aAllDomains = self::GetDataArray( $inaApiResponse, 'domain' );
		$sMainDomain = '';
		
		if ( !empty($aAllDomains) ) { 
			$sMainDomain = $aAllDomains[0]['domain'];
		}
		
		return $sMainDomain;
		
	}//GetData_MainDomain
	
	/**
	 * Returns an array of arrays of all 'data'
	 * 
	 * You need to supply the TEST KEY in order to determine whether it's 1D array or an array of arrays
	 * 
	 * Databases: 'db'
	 * FTP Users: 'user'
	 * 
	 * @param unknown_type $inaApiResponse
	 */
	public static function GetDataArray( $inaApiResponse, $insDataKey = '' ) {
		
		$aData = array();
		
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aData;
		}
		
		$aResponseData = self::GetDataFromResponse( $inaApiResponse, 'data' );
		
		if ( !empty($aResponseData) ) {
			
			if ( array_key_exists( $insDataKey, $aResponseData ) ) { //there's only 1 Database in this data set
				$aData[] = $aResponseData;
			}
			else {
				$aData = $aResponseData;
			}
			
		}
		
		return $aData;
	}
	
	public static function GetData_AllStatsData( $inaApiResponse ) {
		
		$aStatsData = array();
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aStatsData;
		}
		
		$aResponseData = self::GetDataArray( $inaApiResponse, 'name' );
		return $aResponseData;
	}
	
	public static function GetData_OneStatData( $inaApiResponse, $sStatsDataName ) {
		
		$aStatsData = array();
		if ( !self::GetLastSuccess( $inaApiResponse ) ) {//Last API call failed.
			return $aStatsData;
		}
		
		$aResponseData = self::GetData_AllStatsData( $inaApiResponse );
		
		if ( !empty($aResponseData) ) {
			foreach( $aResponseData as $aResponse ) {
				if ( $aResponse['name'] == $sStatsDataName ) {
					$aStatsData = $aResponse;
				}
			}
		}
		
		return $aStatsData;
		
	}
	
	
	
	/**
	 * Given an array of associative arrays, returns an array of the values of a common key
	 * 
	 * @param $inaArray
	 * @param $insKey
	 */
	public static function CreateArrayFromOneKey( $inaArray, $insKey ) {
		
		$aNewArray = array();
		foreach( $inaArray as $aElement ) {
			if ( isset($aElement[$insKey]) ) {
				$aNewArray[] = $aElement[$insKey];
			}
		}
		return $aNewArray;
	}
	
	/**
	 * Given an array of associative arrays, returns an associative array of the same array based on a common key
	 * 
	 * @param unknown_type $inaArray
	 * @param unknown_type $insKey
	 */
	public static function CreateAssocArrayOnKey( $inaArray, $insKey ) {
		
	}
	
	public static function IsAssocArray( $inaArray ) {
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}