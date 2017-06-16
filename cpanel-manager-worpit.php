<?php
/*
Plugin Name: cPanel Manager (from iControlWP)
Plugin URI: http://www.icontrolwp.com/
Description: A tool to connect to your Web Hosting cPanel account from within your WordPress.
Version: 1.8.2
Author: iControlWP
Author URI: http://www.icontrolwp.com/
*/

/**
 * Copyright (c) 2017 iControlWP <support@icontrolwp.com>
 * All rights reserved.
 * "cPanel Manager for WordPress, from iControlWP" is
 * distributed under the GNU General Public License, Version 2,
 * June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
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

define( 'ICWP_DS', DIRECTORY_SEPARATOR );

// By requiring this file here, we assume we wont need to require it anywhere else.
require_once( dirname( __FILE__ ) . ICWP_DS . 'src' . ICWP_DS . 'common' . ICWP_DS . 'icwp-foundation.php' );
include_once( dirname( __FILE__ ) . '/src/worpit-plugins-base.php' );
include_once( dirname( __FILE__ ) . '/inc/lib/worpit/Worpit_CPanelTransformer.php' );

class ICWP_CpanelManagerWordPress extends Worpit_Plugins_Base_Cpm {

	const OptionPrefix = 'cpm_';
	const SecurityAccessKeyCookieName = "worpitsakcook";

	protected $m_aPluginOptions_EnableSection;

	protected $m_aPluginOptions_CpmEncryptSection;

	protected $m_aPluginOptions_CpmCredentialsSection;

	protected $m_aSubmitMessages;

	protected $m_aSubmitSuccess;

	protected $m_fSubmitCpmMainAttempt;

	static public $VERSION = '1.8.1'; //SHOULD BE UPDATED UPON EACH NEW RELEASE

	public function __construct() {
		parent::__construct();

		register_activation_hook( __FILE__, array( $this, 'onWpActivatePlugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'onWpDeactivatePlugin' ) );

		self::$PLUGIN_NAME = basename( __FILE__ );
		self::$PLUGIN_PATH = plugin_basename( dirname( __FILE__ ) );
		self::$PLUGIN_DIR = WP_PLUGIN_DIR . ICWP_DS . self::$PLUGIN_PATH . ICWP_DS;
		self::$PLUGIN_URL = WP_PLUGIN_URL . '/' . self::$PLUGIN_PATH . '/';
		self::$OPTION_PREFIX = self::BaseOptionPrefix . self::OptionPrefix;

		$this->m_fSubmitCpmMainAttempt = false;

		$this->m_sParentMenuIdSuffix = 'cpm';
	}

	protected function createPluginSubMenuItems() {
		$this->m_aPluginMenu = array(
			//Menu Page Title => Menu Item name, page ID (slug), callback function for this page - i.e. what to do/load.
			$this->getSubmenuPageTitle( 'Security' )       => array( 'Security', $this->getSubmenuId( 'security' ), 'onDisplayCpmSecurity' ),
			$this->getSubmenuPageTitle( 'cPanel Connect' ) => array( 'cPanel Connect', $this->getSubmenuId( 'main' ), 'onDisplayCpmMain' ),
			$this->getSubmenuPageTitle( 'cPanel Tasks' )   => array( 'cPanel Tasks', $this->getSubmenuId( 'tasks' ), 'onDisplayCpmCpanelTasks' ),
		);
	}

	public function onWpAdminNotices() {

		//Do we have admin priviledges?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$this->adminNoticeMcryptLibUnavailable();
		$this->adminNoticeOptionsUpdated();
		$this->adminNoticeVersionUpgrade();
		$this->adminNoticeSubmitMessages();
	}

	public function onWpDeactivatePlugin() {
		if ( !$this->initPluginOptions() ) {
			return;
		}
		$this->deleteAllPluginDbOptions();
	}

	public function onWpActivatePlugin() {
	}

	protected function handlePluginUpgrade() {
		//Someone clicked the button to acknowledge the update
		if ( isset( $_POST[ self::$OPTION_PREFIX . 'hide_update_notice' ] ) && isset( $_POST[ 'worpit_user_id' ] ) ) {
			update_user_meta( $_POST[ 'worpit_user_id' ], self::$OPTION_PREFIX . 'current_version', self::$VERSION );
			header( "Location: admin.php?page=" . $this->getFullParentMenuId() );
		}
	}

	/**
	 * Override for specify the plugin's options
	 */
	protected function initPluginOptions() {

		$this->m_aPluginOptions_EnableSection = array(
			'section_title'   => 'Enable cPanel Manager for WordPress Feature',
			'section_options' => array(
				array( 'enable_cpanel_manager_wordpress', '', 'N', 'checkbox', 'cPanel Manager', 'Enable cPanel Manager for WordPress Features', "Provides the ability to connect to your cPanel web hosting account." ),
			),
		);

		$this->m_aPluginOptions_CpmEncryptSection = array(
			'section_title'   => 'cPanel Manager Encryption Settings',
			'section_options' => array(
				array( 'cpanel_security_access_key', '', '', 'text', 'Security Access Key:', '', 'Please supply a Security Access Key to protect your cPanel details.' ),
			),
		);

		$this->m_aPluginOptions_CpmCredentialsSection = array(
			'section_title'   => 'cPanel Connection Credentials',
			'section_options' => array(
				array( 'cpanel_server_address', '', '', 'text', 'cPanel Server Address:', '', 'Can either be a valid domain name, or an IP Address.' ),
				array( 'cpanel_server_port', '', '2083', 'text', 'cPanel Server Port:', '', 'Currently locked to 2083 in this version of the plugin.' ),
				array( 'cpanel_username', '', '', 'text', 'cPanel Username:', '', 'This is your cPanel administrator username.' ),
				array( 'cpanel_password', '', '', 'text', 'cPanel Password:', '', 'This is your cPanel administrator password.' ),
			),
		);

		$this->m_aAllPluginOptions = array( &$this->m_aPluginOptions_EnableSection, &$this->m_aPluginOptions_CpmEncryptSection, &$this->m_aPluginOptions_CpmCredentialsSection );

		return true;
	}

	protected function handlePluginFormSubmit() {

		if ( !$this->isWorpitPluginAdminPage() ) {
			return;
		}

		if ( !isset( $_POST[ 'cpm_form_submit' ] ) ) {
			return;
		}

		//Don't need to run isset() because previous function does this
		switch ( $_GET[ 'page' ] ) {
			case $this->getSubmenuId( 'security' ):
				$this->handleSubmit_security();
				return;
			case $this->getSubmenuId( 'main' ):
				if ( isset( $_POST[ self::$OPTION_PREFIX . 'all_options_input' ] ) ) {
					$this->handleSubmit_main();
				}
				return;
			case $this->getSubmenuId( 'tasks' ):

				$this->handleSubmit_tasks();
				return;
		}
	}

	protected function handleSubmit_security() {

		// Ensures we're actually getting this request from WP.
		check_admin_referer( $this->getSubmenuId( 'security' ) );

		if ( isset( $_POST[ 'submit_remove_access' ] ) ) {
			$this->turnSecureAccessOff();
			return;
		}

		if ( isset( $_POST[ 'submit_reset' ] ) ) { //reset all cPanel credentials.

			$this->updateOption( 'cpanel_security_access_key', '' );
			$this->updateOption( 'cpanel_username', '' );
			$this->updateOption( 'cpanel_password', '' );

			$this->turnSecureAccessOff();

			$this->m_aSubmitSuccess = true;
			$this->m_aSubmitMessages[] = "Your Security Access Key, cPanel username, and cPanel password were all reset.";
			return;
		}

		// Check for encryption key password
		if ( !isset( $_POST[ self::$OPTION_PREFIX . 'cpanel_security_access_key' ] ) || empty( $_POST[ self::$OPTION_PREFIX . 'cpanel_security_access_key' ] ) ) {
			$this->m_aSubmitSuccess = false;
			$this->m_aSubmitMessages[] = "Security Access Key was not processed because the field was empty.";

			return;
		}

		$sCurrentSalt_md5 = $this->getOption( 'cpanel_security_access_key' );
		$sEncryptionSalt = trim( $_POST[ self::$OPTION_PREFIX . 'cpanel_security_access_key' ] );
		$sEncryptionSalt_md5 = md5( $sEncryptionSalt );

		if ( empty( $sCurrentSalt_md5 ) ) { // the user has never stored a password before so store it now

			$this->updateOption( 'cpanel_security_access_key', $sEncryptionSalt_md5 );

			$this->turnSecureAccessOn( $sEncryptionSalt );

			$this->m_aSubmitSuccess = true;
			$this->m_aSubmitMessages[] = "You have successfully stored a new Security Access Key that will be used to encrypt your cPanel data: $sEncryptionSalt";
		}
		else {

			//Compare md5 hashes
			if ( $sCurrentSalt_md5 === $sEncryptionSalt_md5 ) { //User supplied correct password

				$this->turnSecureAccessOn( $sEncryptionSalt );

				$this->m_aSubmitSuccess = true;
				$this->m_aSubmitMessages[] = "You entered the correct Security Access Key. Your cPanel Manager is available until your login session expires.";
			}
			else {
				$this->m_aSubmitSuccess = false;
				$this->m_aSubmitMessages[] = "You didn't enter the correct password. Your cPanel Manager wont be available until you do, or you reset your cPanel credentials.";
			}
		}
	}

	protected function handleSubmit_main() {

		//Ensures we're actually getting this request from WP.
		check_admin_referer( $this->getSubmenuId( 'main' ) );

		$sSalt = $this->getSalt();
		if ( empty( $sSalt ) ) {
			$this->m_aSubmitSuccess = false;
			$this->m_aSubmitMessages[] = "Could not save cPanel details because Security Access Key is unavailable.";
			return;
		}

		$this->m_fSubmitCpmMainAttempt = true;

		//At this point, I'm storing all the presented values.
		$sAddress = strtolower( $_POST[ self::$OPTION_PREFIX . 'cpanel_server_address' ] );
		$sAddress = preg_replace( '/(\s|\*|\@|\&|\$)/', '', $sAddress );
		$iPort = intval( trim( $_POST[ self::$OPTION_PREFIX . 'cpanel_server_port' ] ) );
		$sUsername = trim( $_POST[ self::$OPTION_PREFIX . 'cpanel_username' ] );

		if ( !empty( $sAddress ) && !self::IsValidDomainName( $sAddress ) ) {
			$sAddress = '';
		}
		if ( !empty( $iPort ) ) {
			if ( $iPort <= 1023 || $iPort >= 65535 ) {
				$iPort = 2083;
			}
		}
		else {
			$iPort = 2083;
		}
		$_POST[ self::$OPTION_PREFIX . 'cpanel_server_address' ] = $sAddress;
		$_POST[ self::$OPTION_PREFIX . 'cpanel_server_port' ] = $iPort;
		$_POST[ self::$OPTION_PREFIX . 'cpanel_username' ] = $this->encryptOptionValue( $sUsername );
		$_POST[ self::$OPTION_PREFIX . 'cpanel_password' ] = $this->encryptOptionValue( $_POST[ self::$OPTION_PREFIX . 'cpanel_password' ] );

		$this->updatePluginOptionsFromSubmit( $_POST[ self::$OPTION_PREFIX . 'all_options_input' ] );
	}

	protected function handleSubmit_tasks() {

		//Ensures we're actually getting this request from WP.
		check_admin_referer( $this->getSubmenuId( 'tasks' ) );

		if ( isset( $_POST[ 'cpm_submit_action' ] ) ) {

			list( $sActionGroup, $sActionMember ) = explode( '_', $_POST[ 'cpm_submit_action' ], 2 );
			$sActionInclude = dirname( __FILE__ ) . '/src/actions/CPM_ActionDelegate_' . ucfirst( $sActionGroup ) . '.php';

			if ( file_exists( $sActionInclude ) ) {
				$aCpanelCredentials = array(
					self::getOption( 'cpanel_server_address' ),
					self::getOption( 'cpanel_server_port' ),
					self::getOptionDecrypted( 'cpanel_username' ),
					self::getOptionDecrypted( 'cpanel_password' ),
				);

				include_once( $sActionInclude );

				$sClassName = 'CPM_ActionDelegate_' . ucfirst( $sActionGroup );
				/** @var CPM_ActionDelegate_Base $oActionDelegate */
				$oActionDelegate = new $sClassName( $_POST, $aCpanelCredentials );

				if ( $oActionDelegate->getIsValidState() ) {

					$oActionDelegate->reset();
					$this->m_aSubmitSuccess = $oActionDelegate->{$sActionMember}();
					$this->m_aSubmitMessages = $oActionDelegate->getMessages();
				}
			}
			else {
				//not implemented
			}
		}
	}

	public function onDisplayCpmSecurity() {

		//populates plugin options with existing configuration
		$this->readyAllPluginOptions();

		$this->m_aPluginOptions_CpmEncryptSection[ 'section_options' ][ 0 ][ 1 ] = '';

		$sCurrentAccessKey = $this->getOption( 'cpanel_security_access_key' );

		if ( !empty( $sCurrentAccessKey ) ) {
			$this->m_aPluginOptions_CpmEncryptSection[ 'section_options' ][ 0 ][ 6 ] = "You must re-enter your Security Access Key before you can use the plugin.";
		}
		//Specify what set of options are available for this page
		$aAvailableOptions = array( &$this->m_aPluginOptions_CpmEncryptSection );

		$sAllInputOptions = $this->collateAllFormInputsForOptionsSection( $this->m_aPluginOptions_CpmEncryptSection );

		$aData = array(
			'plugin_url'        => self::$PLUGIN_URL,
			'var_prefix'        => self::$OPTION_PREFIX,
			'sak_cookie_name'   => self::SecurityAccessKeyCookieName,
			'aAllOptions'       => $aAvailableOptions,
			'all_options_input' => $sAllInputOptions,
			'nonce_field'       => $this->getSubmenuId( 'security' ),
			'form_action'       => 'admin.php?page=' . $this->getFullParentMenuId() . '-security'
		);

		$this->display( 'worpit_cpm_security', $aData );
	}

	/**
	 * For each display, if you're creating a form, define the form action page and the form_submit_id
	 * that you can then use as a guard to handling the form submit.
	 */
	public function onDisplayCpmMain() {

		//populates plugin options with existing configuration
		$this->readyAllPluginOptions();

		//Decrypt values for display on front end.
		$this->m_aPluginOptions_CpmCredentialsSection[ 'section_options' ][ 2 ][ 1 ] = $this->getOptionDecrypted( 'cpanel_username' );
		$this->m_aPluginOptions_CpmCredentialsSection[ 'section_options' ][ 3 ][ 1 ] = $this->getOptionDecrypted( 'cpanel_password' );

		//Specify what set of options are available for this page
		$aAvailableOptions = array(
			&$this->m_aPluginOptions_EnableSection,
			&$this->m_aPluginOptions_CpmCredentialsSection
		);

		$sAllInputOptions = $this->collateAllFormInputsForOptionsSection( $this->m_aPluginOptions_EnableSection );
		$sAllInputOptions .= ',' . $this->collateAllFormInputsForOptionsSection( $this->m_aPluginOptions_CpmCredentialsSection );

		$aData = array(
			'plugin_url'         => self::$PLUGIN_URL,
			'var_prefix'         => self::$OPTION_PREFIX,
			'sak_cookie_name'    => self::SecurityAccessKeyCookieName,
			'aAllOptions'        => $aAvailableOptions,
			'all_options_input'  => $sAllInputOptions,
			'nonce_field'        => $this->getSubmenuId( 'main' ),
			'page_link_security' => $this->getSubmenuId( 'security' ),
			'form_action'        => 'admin.php?page=' . $this->getFullParentMenuId() . '-main'
		);

		$this->display( 'worpit_cpm_main', $aData );
	}

	/**
	 * For each display, if you're creating a form, define the form action page and the form_submit_id
	 * that you can then use as a guard to handling the form submit.
	 */
	public function onDisplayCpmCpanelTasks() {

		$this->readyAllPluginOptions(); //populates plugin options with existing configuration

		//Specify what set of options are available for this page
		$aAvailableOptions = array( &$this->m_aPluginOptions_EnableSection, &$this->m_aPluginOptions_CpmCredentialsSection );

		$aData = array(
			'plugin_url'            => self::$PLUGIN_URL,
			'var_prefix'            => self::$OPTION_PREFIX,
			'sak_cookie_name'       => self::SecurityAccessKeyCookieName,
			'cpanel_enabled'        => self::getOption( 'enable_cpanel_manager_wordpress' ),
			'cpanel_server_address' => self::getOption( 'cpanel_server_address' ),
			'cpanel_server_port'    => self::getOption( 'cpanel_server_port' ),
			'cpanel_username'       => $this->getOptionDecrypted( 'cpanel_username' ),
			'cpanel_password'       => $this->getOptionDecrypted( 'cpanel_password' ),
			'aAllOptions'           => $aAvailableOptions,
			'page_link_options'     => $this->getSubmenuId( 'main' ),
			'page_link_security'    => $this->getSubmenuId( 'security' ),
			'nonce_field'           => $this->getSubmenuId( 'tasks' ),
			'form_action'           => 'admin.php?page=' . $this->getFullParentMenuId() . '-tasks'
		);
		$this->display( 'worpit_cpm_tasks', $aData );
	}

	/**
	 * Add desired shortcodes to this array.
	 */
	protected function defineShortcodes() {
		$this->m_aShortcodes = array();
	}

	private function adminNoticeMcryptLibUnavailable() {

		if ( $this->isWorpitPluginAdminPage() && !extension_loaded( 'mcrypt' ) ) {
			$sNotice = "<p>Note: because you don't have the necessary encryption libraries loaded
			on your web hosting, your cPanel login credential are <strong>NOT ENCRYPTED</strong>.</p>
			<p>It is recommended that you don't use this plugin until those libraries ('mcrypt') are available.</p>
			<p>Get <a href=\"http://bitly.com/M9SwTn\"><strong>better web hosting</strong></a> that has things like this as standard.</p>";
			$sClass = 'error alert alert-error';
			$this->getAdminNotice( $sNotice, $sClass, true );
		}
	}

	private function adminNoticeOptionsUpdated() {

		//Admin notice for Main Options page submit.
		if ( $this->m_fSubmitCpmMainAttempt ) {

			if ( $this->m_fUpdateSuccessTracker ) {
				$sNotice = '<p>Updating CPM Plugin Options was a <strong>Success</strong>.</p>';
				$sClass = 'updated';
			}
			else {
				$sNotice = '<p>Updating CPM Plugin Options <strong>Failed</strong>.</p>';
				$sClass = 'error';
			}
			$this->getAdminNotice( $sNotice, $sClass, true );
		}
	}

	private function adminNoticeVersionUpgrade() {

		global $current_user;
		$user_id = $current_user->ID;

		$sCurrentVersion = get_user_meta( $user_id, self::$OPTION_PREFIX . 'current_version', true );

		if ( $sCurrentVersion !== self::$VERSION ) {
			$sNotice = '
					<form method="post" action="admin.php?page=' . $this->getFullParentMenuId() . '">
						<p><strong>cPanel Manager for WordPress</strong> plugin has been updated. Worth checking out the latest docs.
						<input type="hidden" value="1" name="' . self::$OPTION_PREFIX . 'hide_update_notice" id="' . self::$OPTION_PREFIX . 'hide_update_notice">
						<input type="hidden" value="' . $user_id . '" name="worpit_user_id" id="worpit_user_id">
						<input type="submit" value="Okay, show me and hide this notice" name="submit" class="button-primary">
						</p>
					</form>
			';

			$this->getAdminNotice( $sNotice, 'updated', true );
		}
	}

	private function adminNoticeSubmitMessages() {

		if ( $this->m_aSubmitSuccess ) {
			$sClasses = 'alert alert-success span12 updated';
		}
		else {
			$sClasses = 'alert alert-error span12 updated';
		}

		if ( !empty( $this->m_aSubmitMessages ) ) {

			foreach ( $this->m_aSubmitMessages as $sMessage ) {
				$this->getAdminNotice( $sMessage, $sClasses, true );
			}
		}
	}

	/**
	 * @param string $sKey
	 * @return string
	 */
	private function getOptionDecrypted( $sKey ) {
		$sEncryptedOption = self::getOption( $sKey );

		$sToDecrypt = '';
		if ( is_array( $sEncryptedOption ) && !empty( $sEncryptedOption[ 0 ] ) ) {
			$sToDecrypt = $sEncryptedOption[ 0 ];
		}
		else if ( is_string( $sEncryptedOption ) ) {
			$sToDecrypt = $sEncryptedOption;
		}

		return self::DecryptString( $sToDecrypt, $this->getSalt() );
	}

	/**
	 * @param string $sValue
	 * @return string
	 */
	private function encryptOptionValue( $sValue ) {
		return self::EncryptString( $sValue, $this->getSalt() );
	}

	/**
	 * @return string
	 */
	private function getSalt() {
		return $this->loadDataProcessor()->FetchCookie( self::SecurityAccessKeyCookieName, '' );
	}

	/**
	 * @param string $sAccessKey
	 * @return $this
	 */
	private function turnSecureAccessOn( $sAccessKey ) {
		$this->loadDataProcessor()->setCookie( self::SecurityAccessKeyCookieName, $sAccessKey );
		return $this;
	}

	/**
	 * @return $this
	 */
	private function turnSecureAccessOff() {
		$this->loadDataProcessor()->setDeleteCookie( self::SecurityAccessKeyCookieName );
		return $this;
	}

	/**
	 * @param string $sText
	 * @param string $sSalt
	 * @return string
	 */
	public static function EncryptString( $sText, $sSalt = '' ) {

		if ( !extension_loaded( 'mcrypt' ) || empty( $sSalt ) || empty( $sText ) ) {
			return $sText;
		}

		return trim(
			base64_encode(
				mcrypt_encrypt(
					MCRYPT_RIJNDAEL_256,
					self::padSalt( $sSalt ),
					$sText,
					MCRYPT_MODE_ECB,
					mcrypt_create_iv(
						mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND )
				)
			)
		);
	}

	/**
	 * @param string $sText
	 * @param string $sSalt
	 * @return string
	 */
	public static function DecryptString( $sText, $sSalt = '' ) {

		if ( !extension_loaded( 'mcrypt' ) || empty( $sSalt ) || empty( $sText ) ) {
			return $sText;
		}

		return trim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_256,
				self::padSalt( $sSalt ),
				base64_decode( $sText ),
				MCRYPT_MODE_ECB,
				mcrypt_create_iv(
					mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND )
			)
		);
	}

	/**
	 * https://stackoverflow.com/questions/27254432/mcrypt-decrypt-error-change-key-size
	 * @param $sSalt
	 * @return bool|string
	 */
	static private function padSalt( $sSalt ) {
		if ( strlen( $sSalt ) > 32 ) {
			return $sSalt;
		}

		// set sizes
		$aSizes = array( 16, 24, 32 );

		// loop through sizes and pad key
		foreach ( $aSizes as $nSize ) {

			if ( strlen( $sSalt ) < $nSize ) {
				while ( strlen( $sSalt ) < $nSize ) {
					$sSalt = $sSalt . "\0";
				}
				break;
			}
		}

		return $sSalt;
	}

	static public function IsValidDomainName( $insUrl ) {

		$aPieces = explode( ".", $insUrl );
		foreach ( $aPieces as $sPiece ) {
			if ( !preg_match( '/^[a-z\d][a-z\d-]{0,62}$/i', $sPiece ) || preg_match( '/-$/', $sPiece ) ) {
				return false;
			}
		}
		return true;
	}
}

new ICWP_CpanelManagerWordPress();