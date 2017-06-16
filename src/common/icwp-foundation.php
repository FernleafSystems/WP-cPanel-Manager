<?php

if ( class_exists( 'ICWP_CPM_Foundation', false ) ) :
	return;
endif;

class ICWP_CPM_Foundation {

	/**
	 * @var ICWP_CPM_DataProcessor
	 */
	private static $oDp;

	/**
	 * @return ICWP_CPM_DataProcessor
	 */
	static public function loadDataProcessor() {
		if ( !isset( self::$oDp ) ) {
			require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'icwp-data.php' );
			self::$oDp = ICWP_CPM_DataProcessor::GetInstance();
		}
		return self::$oDp;
	}
}