<?PHP
class settingsPage
{
	public static function settings_form()
	{
		$externals = self::getExternals();
		
		switch( $externals['action'] ):
			case 'updateSettings':
				# check for errors
				if( TRUE == ( $errors = self::check_errors( $externals ) ) ):
					self::show_form( $externals, $errors );
					break;
				else:
					self::update_settings( $externals );
					
					$errorMSG = self::check_settings_errors();

					if( !empty( $errorMSG ) ):
						self::show_form( $externals, $errorMSG );
						break;						
					endif;
					self::show_success();
					break;
				endif;
				
			default:
				$settings['bookaroom_events_db_key']			= get_option( 'bookaroom_events_db_key' );
				$settings['bookaroom_events_db_database']		= get_option( 'bookaroom_events_db_database' );
				$settings['bookaroom_events_db_password']		= get_option( 'bookaroom_events_db_password' );
				$settings['bookaroom_events_db_username']		= get_option( 'bookaroom_events_db_username' );
				$settings['bookaroom_events_prefix']			= get_option( 'bookaroom_events_prefix' );
				$settings['bookaroom_events_db_host']			= get_option( 'bookaroom_events_db_host' );
				$settings['bookaroom_events_regpage_URL']		= get_option( 'bookaroom_events_regpage_URL' );
				#$settings['bookaroom_events_db_password']		= bookaroom_events_decrypt( $settings['bookaroom_events_db_passwordRaw'], $settings['bookaroom_events_db_key'] );
				
				self::show_form( $settings );
				break;
		endswitch;
	}

		
	protected static function check_errors( $externals )
	{
		$goodArr = array( 'Security key for password encryption' => 'bookaroom_events_db_key', 'Database name' => 'bookaroom_events_db_database', 'DB Username' => 'bookaroom_events_db_username', '
DB Password' => 'bookaroom_events_db_password', 'DB Prefix' => 'bookaroom_events_prefix', 'DB Hostname' => 'bookaroom_events_db_host', 'Registration page URL' => 'bookaroom_events_regpage_URL' );
		
		$errors = array();
		$errorMsg = NULL;
		
		foreach( $goodArr as $key => $val ):
			if( empty( $externals[$val] ) && $val !== 'bookaroom_events_db_password' ):
				$errors[] = "You must ernter a value for <em>{$key}</em>.";
			endif;
		endforeach;
				
		if( count( $errors ) !== 0 ):
			array_walk( $errors, function( &$value, $key ){ $value = "<p>{$value}</p>"; });
		endif;

		$errorMsg = implode( "\r\n", $errors );

		return $errorMsg;	
	}
	
	protected static function check_settings_errors()
	{
			$settings['bookaroom_events_db_key']			= get_option( 'bookaroom_events_db_key' );
			$settings['bookaroom_events_db_database']		= get_option( 'bookaroom_events_db_database' );
			$settings['bookaroom_events_db_username']		= get_option( 'bookaroom_events_db_username' );
			$settings['bookaroom_events_db_password']		= get_option( 'bookaroom_events_db_password' );
			$settings['bookaroom_events_prefix']			= get_option( 'bookaroom_events_prefix' );
			$settings['bookaroom_events_db_host']			= get_option( 'bookaroom_events_db_host' );
			$settings['bookaroom_events_regpage_URL']		= get_option( 'bookaroom_events_regpage_URL' );			
			
			#$settings['bookaroom_events_db_password']	= bookaroom_events_decrypt( $settings['bookaroom_events_db_passwordRaw'], $settings['bookaroom_events_db_key'] );
			
			$error = NULL;
			# check for connection
			$mysqli = @new mysqli( $settings['bookaroom_events_db_host'], $settings['bookaroom_events_db_username'], $settings['bookaroom_events_db_password'], $settings['bookaroom_events_db_database'] ) or $error;
		
			if ( mysqli_connect_error() ) {
				return sprintf( __( 'Connect Error: %s', 'book-a-room-events' ), mysqli_connect_error() );
			}
			# check that the table exists
			$query = "SHOW TABLES LIKE '{$settings['bookaroom_events_prefix']}bookaroom_branches'";
			
			$raw = $mysqli->query( $query );
			
			if( $raw->num_rows < 1 ):
				return __( 'Connect Error: We were able to connect but could not find the Book a Room tables. <br>Either your prefix is wrong or you haven\'t set up the main Book a Room plugin on the server you entered.', 'book-a-room-events' );
			endif;
		
			return false;
	}
	
	
	protected static function update_settings( $externals )
	{
		$goodArr = array( 'bookaroom_events_db_key', 'bookaroom_events_db_database', 'bookaroom_events_db_username', 'bookaroom_events_prefix', 'bookaroom_events_db_host', 'bookaroom_events_db_password', 'bookaroom_events_regpage_URL' );
		
		foreach( $goodArr as $key => $val ):
			update_option( $val, $externals[$val] );
		endforeach;
		
		#update_option( 'bookaroom_events_db_password', bookaroom_events_encrypt( $externals['bookaroom_events_db_password'], $externals['bookaroom_events_db_key'] ) );
			
	}
	
	protected static function show_success()
	{
		require( BAR_EVENTS_PLUGIN_PATH . 'templates/events_settings_success.php' );
		return true;
		$filename = BAR_EVENTS_PLUGIN_PATH . 'templates/events_settings_success.html';	
		$handle = fopen( $filename, "r" );
		$contents = fread( $handle, filesize( $filename ) );
		fclose( $handle );
		
		echo $contents;
	}
	
	protected static function show_form( $settings, $errorMSG = NULL )
	{
		require( BAR_EVENTS_PLUGIN_PATH . 'templates/events_settings.php' );
	}
	
	public static function getExternals()
	# Pull in POST and GET values
	{
		$final = array();
		
		# setup GET variables
		$getArr = array( );

		# pull in and apply to final
		if( $getTemp = filter_input_array( INPUT_GET, $getArr ) ):
			$final = array_merge( $final, $getTemp );
		endif;
		
		$bookaroom_events_db_key		= get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database	= get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username	= get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password	= get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix		= get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host		= get_option( 'bookaroom_events_db_host' );
		$bookaroom_events_regpage_URL	= get_option( 'bookaroom_events_regpage_URL' );
		
		# setup POST variables
		$postArr = array(	'action'					=> FILTER_SANITIZE_STRING,
							'bookaroom_events_db_key'		=> FILTER_SANITIZE_STRING,
							'bookaroom_events_db_database'	=> FILTER_SANITIZE_STRING,
							'bookaroom_events_db_username'	=> FILTER_SANITIZE_STRING,
							'bookaroom_events_db_password'	=> FILTER_SANITIZE_STRING,
							'bookaroom_events_prefix'		=> FILTER_SANITIZE_STRING,
							'bookaroom_events_db_host'		=> FILTER_SANITIZE_STRING, 
							'bookaroom_events_regpage_URL'	=> FILTER_SANITIZE_STRING );
	
	

		# pull in and apply to final
		if( $postTemp = filter_input_array( INPUT_POST, $postArr ) )
			$final = array_merge( $final, $postTemp );

		$arrayCheck = array_unique( array_merge( array_keys( $getArr ), array_keys( $postArr ) ) );
		
		foreach( $arrayCheck as $key ):
			if( empty( $final[$key] ) ):
				$final[$key] = NULL;
			else:			
				$final[$key] = trim( $final[$key] );
			endif;
		endforeach;
		
		
		return $final;
	}
}
?>