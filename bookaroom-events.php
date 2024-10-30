<?PHP
/*
Plugin Name: Book a Room Event Calendar
Plugin URI: https://wordpress.org/plugins/book-a-room-event-calendar/
Description: This is the front end for the event calendar system.
Version: 1.9
Author: Colin Tomele
Author URI: http://heightslibrary.org
License: GPLv2 or later
Text-domain: , book-a-room-events
*/
global $bookaroom_events_db_version;
$bookaroom_events_db_version = "1";

define( 'BAR_EVENTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BAR_EVENTS_PLUGIN_URL', plugin_dir_URL( __FILE__ ) );

require_once( BAR_EVENTS_PLUGIN_PATH . 'bookaroom-events-main.php' );


register_activation_hook( __FILE__, array( 'bookaroom_events_init', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'bookaroom_events_init', 'on_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'bookaroom_events_init', 'on_uninstall' ) );

add_action('init', 'bookaroom_events_myStartSession', 1);
add_action('wp_logout', 'bookaroom_events_myEndSession');
add_action('wp_login', 'bookaroom_events_myEndSession');
add_action( 'init', 'bookaroom_init' );

add_action( 'admin_menu', array( 'bookaroom_events_settings', 'add_settingsPage' ) );

add_filter('the_content', 'do_shortcode');


function bookaroom_init()
{
	
	# shortcodes
	wp_enqueue_script( 'bookaroom_calendar_js', plugins_url( 'book-a-room-event-calendar/js/scripts.js' ), array('jquery'), null, true );
	wp_enqueue_script( 'bookaroom_calendar_zebra', plugins_url( 'book-a-room-event-calendar/js/jstree/jquery.jstree.js' ), array('jquery'), null, true );

	wp_enqueue_style( 'myCSS', plugins_url( 'book-a-room-event-calendar/css/calendar.css' ) );

	wp_register_style('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');

	wp_enqueue_style( 'jquery-ui' );   
	
	wp_enqueue_script( 'jquery-ui-datepicker' );
		

	add_shortcode( 'showEvents', array( 'events_main', 'showEvents' ) );
	add_shortcode( 'showCalendar', array( 'events_main', 'calendarMain' ) );
	add_shortcode( 'showReg', array( 'events_main', 'setup_showEmailRegistrations' ) );
}



class bookaroom_events_init
# simple class for activating, deactivating and uninstalling plugin
{
	
    public static function on_activate()
	# this is only run when hooked by activating plugin
    {
		
		global $wpdb;
		global $bookaroom_db_version;
		
		register_setting( 'bookaroom_events_group', 'bookaroom_events_db_database', '' ); 
		register_setting( 'bookaroom_events_group', 'bookaroom_events_db_username', '' ); 
		register_setting( 'bookaroom_events_group', 'bookaroom_events_db_password', '' ); 
		register_setting( 'bookaroom_events_group', 'bookaroom_events_prefix', '' ); 
		register_setting( 'bookaroom_events_group', 'bookaroom_events_db_host', '' );
		register_setting( 'bookaroom_events_group', 'bookaroom_events_regpage_URL', '' );
		
	}
	
	public static function on_deactivate()
	# this is only run when hooked by de-activating plugin
    {
		# TODO fix deactivation and uninstall

    }

    public static function on_uninstall()
	# this is only run when hooked by uninstalling plugin
    {
		unregister_setting( 'bookaroom_events_group', 'bookaroom_events_db_database' ); 
		unregister_setting( 'bookaroom_events_group', 'bookaroom_events_db_username' ); 
		unregister_setting( 'bookaroom_events_group', 'bookaroom_events_db_password' ); 
		unregister_setting( 'bookaroom_events_group', 'bookaroom_events_prefix' ); 
		unregister_setting( 'bookaroom_events_group', 'bookaroom_events_db_host' );
		unregister_setting( 'bookaroom_events_group', 'bookaroom_events_regpage_URL' );
        
    }
}

class bookaroom_events_settings
# main settings functions
{
	public static function add_settingsPage()
	{
		
		require_once( BAR_EVENTS_PLUGIN_PATH . 'bookaroom-events-settings.php' );
		
		add_options_page( __( 'Events', 'book-a-room-events' ), __( 'Events Settings', 'book-a-room-events' ), 'manage_options', 'bookaroom-Events', array( 'settingsPage', 'settings_form' ) );
	}
	
}


if( !function_exists( "preme" ) ):
	function preme( $arr="-----------------+=+-----------------" ) // print_array
	{
		if( $arr === TRUE )	$arr = "**TRUE**";
		if( $arr === FALSE )	$arr = "**FALSE**";
		if( $arr === NULL )	$arr = "**NULL**";
		
		echo "<pre>";
		print_r( $arr );
		echo "</pre>";
	
	}
endif;


if( !function_exists( "bookaroom_events_myStartSession" ) ):
	function bookaroom_events_myStartSession() {
		if(!session_id()) {
			session_start();
		}
	}
endif;

if( !function_exists( "bookaroom_events_myEndSession" ) ):
	function bookaroom_events_myEndSession() {
		session_destroy ();
	}
endif;

# unused functions
function bookaroom_events_checkDependancy() {
	global $wpdb, $bookaroom_db_version;
		
	$bookaroom_db_version = get_option( "bookaroom_db_versions" );
	$errors[] = "Version is: {$bookaroom_db_version}.";
	
	return $errors;
}
function bookaroom_events_adminNotice() {
	$errors = bookaroom_events_checkDependancy();

    if ( empty ( $errors ) )
        return;

    // Suppress "Plugin activated" notice.
    unset( $_GET['activate'] );

    // this plugin's name
    $name = get_file_data( __FILE__, array ( 'Plugin Name' ), 'plugin' );

    printf(
        '<div class="error"><p>%1$s</p>
        <p><i>%2$s</i> has been deactivated.</p></div>',
        join( '</p><p>', $errors ),
        $name[0]
    );
    deactivate_plugins( plugin_basename( __FILE__ ) );	
}

function CHUH_BaR_permalinkFix( $onlyID = false )
{
	$urlInfoRaw = parse_url( get_permalink() );
			
	$urlInfo = (!empty( $urlInfoRaw['query'] ) ) ? $urlInfoRaw['query'] : NULL;
	if( $onlyID ):
		$finalArr = explode( '=', $urlInfo );
		if( !empty( $finalArr[1] ) ):
			return $finalArr[1];
		else:
			return NULL;
		endif;
		
	endif;
	
	if( empty( $urlInfo ) ):
		$permalinkCal = '?';
	else:
		$permalinkCal = '?'.$urlInfo.'&';
	endif;
	
	return $permalinkCal;	
}
?>