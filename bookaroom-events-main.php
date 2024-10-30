<?php
class events_main {
	public static function setup_showEmailRegistrations() {
		$goodArr = array( 'emailRegistrations', 'checkEmailRegistraions' );
		if( in_array( $_POST['action'], $goodArr ) ) {
			self::calendarMain( $_POST['action'] );
		} else {
			self::calendarMain( 'emailRegistrations' );
		}		
	}
	# main calendar view
	public static function calendarMain( $actionOverride = null ) {
		$externals = self::getExternals();
		
		if( $actionOverride ) {
			$externals['action'] = $actionOverride;
		}
		
		switch ( $externals[ 'action' ] ) {
			case 'checkEmailRegistraions':
				# check for errors
				if ( true == ( $errorMSG = self::checkEmailErrors( $externals[ 'email' ] ) ) ) {
					self::showEmailRegistrations( $externals, $errorMSG );
					break;
				} else {
					$results = self::getRegEmailList( $externals[ 'email' ] );
					self::sendEmailRegAlert( $externals[ 'email' ], $results );
					self::showEmailRegistrations_success( $externals[ 'email' ] );
				}
				break;

			case 'emailRegistrations':
				self::showEmailRegistrations( $externals );
				break;

			case 'searchReturn':
				$_SESSION[ 'bookaroom_temp_search_settings' ] = $externals;
				$results = self::getEventList( $externals );

				self::showSearchedEvents( $externals, $results, true );
				break;

			case 'search':
				$_SESSION[ 'bookaroom_temp_search_settings' ] = $externals;
				$curTime = getdate( time() );
				$externals[ 'startDate' ] = date( 'm/d/Y', mktime( 0, 0, 0, $curTime[ 'mon' ], $curTime[ 'mday' ], $curTime[ 'year' ] ) );
				$externals[ 'endDate' ] = date( 'm/d/Y', mktime( 0, 0, 0, $curTime[ 'mon' ], $curTime[ 'mday' ] + 31, $curTime[ 'year' ] ) - 1 );
				self::showSearchedEvents( $externals, array(), false );
				break;

			case 'checkReg':
				if ( empty( $externals[ 'eventID' ] )or( $eventInfo = self::checkID( $externals[ 'eventID' ] ) ) == false ) {
					preme( 'error' );
					break;
				}

				# bad hash?
				if ( empty( $_SESSION[ 'bookaroomRegFormSub' ] )or $externals[ 'bookaroomRegFormSub' ] !== $_SESSION[ 'bookaroomRegFormSub' ] ) {
					$errorMSG = __( 'Either there was a problem processing your form, or you are trying to refresh an already completed form. Please fill out the form again.', 'book-a-room-events' );
					# check event ID
					$_SESSION[ 'bookaroomRegFormSub' ] = md5( rand( 1, 500000000000 ) );
					unset( $_POST );
					$externals = array( 'fullName' => NULL, 'phone' => NULL, 'email' => NULL, 'notes' => NULL );
					$externals[ 'bookaroomRegFormSub' ] = $_SESSION[ 'bookaroomRegFormSub' ];
					self::viewEvent( $eventInfo, $externals, $errorMSG );
					break;
				}

				if ( ( $errorMSG = self::checkReg( $externals ) ) !== false ) {
					self::viewEvent( $eventInfo, $externals, $errorMSG );
					break;
				}

				self::addRegistration( $externals );
				self::notify( $externals, $eventInfo );
				self::viewEvent( $eventInfo, $externals, $errorMSG, true );
				unset( $_SESSION[ 'bookaroomRegFormSub' ] );

				break;

			case 'viewEvent':
				# check event ID
				if ( empty( $externals[ 'eventID' ] )or( $eventInfo = self::checkID( $externals[ 'eventID' ] ) ) == false ) {
					preme( 'error' );
					break;
				}
				$_SESSION[ 'bookaroomRegFormSub' ] = md5( rand( 1, 500000000000 ) );
				$externals[ 'bookaroomRegFormSub' ] = $_SESSION[ 'bookaroomRegFormSub' ];
				
				self::viewEvent( $eventInfo, $externals );
				break;

			default:
				self::showCalendar( $externals[ 'timestamp' ], $externals[ 'searchTerms' ] );
				break;
		}
	}

	protected static function notify( $externals, $eventInfo ) {
		if ( empty( $externals[ 'email' ] ) ) {
			return false;
		}

		$bookaroom_events_db_key = get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database = get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username = get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password = get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix = get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host = get_option( 'bookaroom_events_db_host' );
		$bookaroom_events_regpage_URL = get_option( 'bookaroom_events_regpage_URL' );

		$newdb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		$branchList = self::getBranches( $newdb, $bookaroom_events_prefix );
		$roomContList = self::getRoomContList( $newdb, $bookaroom_events_prefix );

		# branch and room
		if ( !empty( $eventInfo[ 'ti_noLocation_branch' ] ) ) {
			$branch = $branchList[ $eventInfo[ 'ti_noLocation_branch' ] ][ 'branchDesc' ];
			$room = 'No location specified';
		} else {
			$branch = $branchList[ $roomContList[ 'id' ][ $eventInfo[ 'ti_roomID' ] ][ 'branchID' ] ][ 'branchDesc' ];
			$room = $roomContList[ 'id' ][ $eventInfo[ 'ti_roomID' ] ][ 'desc' ];
		}

		$displayDate = date( 'l, F jS, Y', strtotime( $eventInfo[ 'ti_startTime' ] ) ) . ", " . date( 'g:i a', strtotime( $eventInfo[ 'ti_startTime' ] ) );

		$mydb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );
		$query = "SELECT option_value FROM {$bookaroom_events_db_database}.wp_options WHERE option_name = 'bookaroom_alertEmailFromEmail' LIMIT 1";
		$emailFromRaw = $mydb->get_row( $query, ARRAY_A );
		$emailFrom = $emailFromRaw[ 'option_value' ];

		$query = "SELECT option_value FROM {$bookaroom_events_db_database}.wp_options WHERE option_name = 'bookaroom_alertEmailFromName' LIMIT 1";
		$emailNameRaw = $mydb->get_row( $query, ARRAY_A );
		$emailName = $emailNameRaw[ 'option_value' ];

		$contents = sprintf( __( 'You have successfully been registered for the <em>%s</em> event.', 'book-a-roiom-events' ), $eventInfo[ 'ev_title' ] ) . '<br /><br />';
		$contents .= sprintf( __( 'Location: %s', 'book-a-room-events' ), $room ) . '<br />';
		$contents .= sprintf( __( 'Branch: %s', 'book-a-room-events' ), $branch ) . '<br /><br />';
		$contents .= sprintf( __( 'Event Date: %s', 'book-a-room-events' ), $displayDate ) . '<br /><br />';
		$contents .= __( 'If you are on the waiting list, you will be contacted to let you know if there has been a cancellation.', 'book-a-room-events' );

		$subject = sprintf( __( 'Book a Room: Your registration for the "%s" event.', 'book-a-room-events' ), $eventInfo[ 'ev_title' ] );
		$headers = 'MIME-Version: 1.0' . "\r\n";

		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
		"From: {$emailName} <{$emailFrom}>\r\n" .
		'X-Mailer: PHP/' . phpversion();

		mail( $externals[ 'email' ], $subject, $contents, $headers );
	}


	public static function getEventList( $externals ) {
		$bookaroom_events_db_key = get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database = get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username = get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password = get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix = get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host = get_option( 'bookaroom_events_db_host' );

		$newdb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		$branchList = self::getBranches( $newdb, $bookaroom_events_prefix );
		$roomList = self::getRoomList( $newdb, $bookaroom_events_prefix );
		$roomContList = self::getRoomContList( $newdb, $bookaroom_events_prefix );
		$amenityList = self::getAmenityList( $newdb, $bookaroom_events_prefix );

		$categoryList = self::getCatList( $newdb, $bookaroom_events_prefix );

		$where = array();
		$where[] = 'ti.ti_type = "event"';

		# categories
		if ( !empty( $externals[ 'category' ] ) ) {
			$categories = explode( ',', $externals[ 'category' ] );

			$categoryArr = array();

			foreach ( $categories as $val ) {
				if ( $newID = array_search( trim( $val ), $categoryList[ 'all' ] ) ) {
					if ( array_key_exists( $newID, $categoryList[ 'active' ] ) ) {
						$categoryArr[] = $newID;
					}
				}
			}

			$externals[ 'categoryGroup' ] = $categoryArr;
		}

		# age group
		if ( !empty( $externals[ 'title' ] ) ) {
			$where[] = "res.ev_title LIKE '%{$externals['title']}%'";
		}

		# age group
		if ( !empty( $externals[ 'ageGroup' ] ) ) {
			$where[] = "ages.ea_ageID IN (" . implode( ',', $externals[ 'ageGroup' ] ) . ")";
		}

		# category group
		if ( !empty( $externals[ 'categoryGroup' ] ) ) {
			$where[] = "cats.ec_catID IN (" . implode( ',', $externals[ 'categoryGroup' ] ) . ")";
		}

		# check for branch
		if ( !empty( $externals[ 'branchID' ] )and array_key_exists( $externals[ 'branchID' ], $branchList ) ) {
			# find all rooms in branch
			$branchArr = implode( ',', $roomContList[ 'branch' ][ $externals[ 'branchID' ] ] );
			$where[] = "(ti.ti_roomID IN ( {$branchArr} ) or ti.ti_noLocation_branch = '{$externals['branchID']}')";
		}

		# start time
		if ( !empty( $externals[ 'startDate' ] )and( $startTimestamp = date( 'Y-m-d H:i:s', strtotime( $externals[ 'startDate' ] ) ) ) !== false ) {
			$where[] = "ti.ti_startTime >= '{$startTimestamp}'";
		}

		# end time
		if ( !empty( $externals[ 'endDate' ] )and( $endTimestamp = date( 'Y-m-d H:i:s', strtotime( $externals[ 'endDate' ] . " + 1 days" ) ) ) !== false ) {
			$where[] = "ti.ti_endTime <= '$endTimestamp'";
		}

		# search term
		if ( !empty( $externals[ 'searchTerms' ] ) ) {
			$where[] = " MATCH ( res.ev_desc, res.ev_presenter, res.ev_privateNotes, res.ev_publicEmail, res.ev_publicName, res.ev_submitter, res.ev_title, res.ev_website, res.ev_webText ) AGAINST ('{$externals['searchTerms']}' IN NATURAL LANGUAGE MODE )";
		}

		# check for only published
		$where[] = "res.ev_noPublish = 0";

		#  check for and build WHERE statment
		if ( count( $where ) > 0 ) {
			$whereFinal = 'WHERE ' . implode( ' AND ', $where );
		}

		switch ( $externals[ 'sortOrder' ] ) {
			case 'name':
				$sortOrder = "res.ev_title";
				break;
			case 'date':
				$sortOrder = "ti.ti_startTime";
				break;
			case 'score':
			default:
				$sortOrder = "score DESC, res.ev_title, ti.ti_startTime";
				break;
		}

		$sql = "SELECT MATCH ( res.ev_desc, res.ev_presenter, res.ev_privateNotes, res.ev_publicEmail, res.ev_publicName, res.ev_submitter, res.ev_title, res.ev_website, res.ev_webText ) AGAINST ('{$externals['searchTerms']}' IN NATURAL LANGUAGE MODE ) as score, 
		ti.ti_id, ti.ti_startTime, ti.ti_endTime, ti.ti_type, res.ev_title, res.ev_desc, ti.ti_roomID, ti.ti_noLocation_branch, ti.ti_extraInfo, res.ev_maxReg, res.ev_noPublish, COUNT( DISTINCT tiCount.ti_id ) as eventCount, res.res_id
					FROM {$bookaroom_events_prefix}bookaroom_times AS ti
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_reservations AS res ON res.res_id = ti.ti_extID
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_times AS tiCount ON tiCount.ti_extID = res.res_id 
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_eventAges AS ages ON ages.ea_eventID = res.res_id
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_eventCats AS cats ON cats.ec_eventID = res.res_id

					{$whereFinal}
					GROUP BY ti.ti_id
					ORDER BY {$sortOrder}";
		$cooked = $newdb->get_results( $sql, ARRAY_A );
		return $cooked;

	}

	protected static function addRegistration( $externals ) {
		$settings[ 'bookaroom_events_db_key' ] = get_option( 'bookaroom_events_db_key' );
		$settings[ 'bookaroom_events_db_database' ] = get_option( 'bookaroom_events_db_database' );
		$settings[ 'bookaroom_events_db_username' ] = get_option( 'bookaroom_events_db_username' );
		$settings[ 'bookaroom_events_db_password' ] = get_option( 'bookaroom_events_db_password' );
		$settings[ 'bookaroom_events_prefix' ] = get_option( 'bookaroom_events_prefix' );
		$settings[ 'bookaroom_events_db_host' ] = get_option( 'bookaroom_events_db_host' );


		$mydb = new wpdb( $settings[ 'bookaroom_events_db_username' ], $settings[ 'bookaroom_events_db_password' ], $settings[ 'bookaroom_events_db_database' ], $settings[ 'bookaroom_events_db_host' ] );

		if ( empty( $externals[ 'phone' ] ) ) {
			$cleanPhone = NULL;
		} else {
			$cleanPhone = preg_replace( "/[^0-9]/", '', $externals[ 'phone' ] );
			if ( strlen( $cleanPhone ) == 11 ) {
				$cleanPhone = preg_replace( "/^1/", '', $cleanPhone );
			}

			$cleanPhone = "(" . substr( $cleanPhone, 0, 3 ) . ") " . substr( $cleanPhone, 3, 3 ) . "-" . substr( $cleanPhone, 6 );
		}

		$table_name = $settings[ 'bookaroom_events_prefix' ] . "bookaroom_registrations";

		$final = $mydb->insert( $table_name,
			array( 'reg_eventID' => $externals[ 'eventID' ],
				'reg_fullname' => $externals[ 'fullName' ],
				'reg_phone' => $cleanPhone,
				'reg_email' => $externals[ 'email' ],
				'reg_notes' => $externals[ 'notes' ],
			) );

	}

	public static function checkEmailErrors( $email ) {
		if ( empty( $email ) ) {
			return ( __( 'You must enter an email address to search.', 'book-a-room-events' ) );
		} elseif ( false == filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return ( __( 'The email address you entered is invalid. Please try again.', 'book-a-room-events' ) );
		}
		return false;
	}

	protected static function checkReg( $externals ) {
		$error = array();

		# check for empty values
		if ( empty( $externals[ 'fullName' ] ) ) {
			$error[] = __( 'You must enter the full name of the person who is registering.', 'book-a-rooom-events' );
		}

		if ( empty( $externals[ 'phone' ] )and empty( $externals[ 'email' ] ) ) {
			$error[] = __( 'You must enter contact information; either a phone number or email address where you can be reached.', 'book-a-room-events' );
		} else {
			if ( !empty( $externals[ 'phone' ] ) ) {
				$cleanPhone = preg_replace( "/[^0-9]/", '', $externals[ 'phone' ] );
				if ( strlen( $cleanPhone ) == 11 ) {
					$cleanPhone = preg_replace( "/^1/", '', $cleanPhone );
				}
				if ( !is_numeric( $cleanPhone ) || strlen( $cleanPhone ) !== 10 ) {
					$error[] = __( 'You must enter a valid phone number.', 'book-a-room-events' );
				}
			}

			if ( !empty( $externals[ 'email' ] )and!filter_var( $externals[ 'email' ], FILTER_VALIDATE_EMAIL ) ) {
				$error[] = __( 'Please enter a valid email address.', 'book-a-room-events' );
			};
		}

		if ( count( $error ) !== 0 ) {
			return implode( "<br />", $error );
		} else {
			return false;
		}


	}
	protected static function checkID( $eventID ) {
		$settings[ 'bookaroom_events_db_key' ] = get_option( 'bookaroom_events_db_key' );
		$settings[ 'bookaroom_events_db_database' ] = get_option( 'bookaroom_events_db_database' );
		$settings[ 'bookaroom_events_db_username' ] = get_option( 'bookaroom_events_db_username' );
		$settings[ 'bookaroom_events_db_password' ] = get_option( 'bookaroom_events_db_password' );
		$settings[ 'bookaroom_events_prefix' ] = get_option( 'bookaroom_events_prefix' );
		$settings[ 'bookaroom_events_db_host' ] = get_option( 'bookaroom_events_db_host' );


		$mydb = new wpdb( $settings[ 'bookaroom_events_db_username' ], $settings[ 'bookaroom_events_db_password' ], $settings[ 'bookaroom_events_db_database' ], $settings[ 'bookaroom_events_db_host' ] );

		$prefix = $settings[ 'bookaroom_events_prefix' ];

		$sql = "SELECT ti.ti_id, ti.ti_type, ti.ti_extID, ti.ti_created, ti.ti_startTime, ti.ti_endTime, ti.ti_roomID, 
res.res_id, res.res_created, res.ev_desc, res.ev_maxReg, res.ev_presenter, res.ev_privateNotes, res.ev_publicEmail, res.ev_publicName, res.ev_publicPhone, res.ev_noPublish, res.ev_regStartDate, res.ev_regType, res.ev_submitter, res.ev_title, res.ev_website, res.ev_webText, res.ev_waitingList, 
		ti.ti_noLocation_branch, ti.ti_extraInfo, 
		group_concat(DISTINCT ea.ea_ageID separator ', ') as ageID, group_concat(DISTINCT ages.age_desc separator ', ') as ages, 
		group_concat(DISTINCT ec.ec_catID separator ', ') as catID, group_concat(DISTINCT cats.categories_desc separator ', ') as cats
		FROM {$prefix}bookaroom_times AS ti 
		LEFT JOIN {$prefix}bookaroom_reservations as res ON ti.ti_extID = res.res_id 
		LEFT JOIN {$prefix}bookaroom_eventAges as ea on ea.ea_eventID = ti.ti_extID
		LEFT JOIN {$prefix}bookaroom_event_ages as ages on ea.ea_ageID = ages.age_id
		
		LEFT JOIN {$prefix}bookaroom_eventCats as ec on ec.ec_eventID = ti.ti_extID
		LEFT JOIN {$prefix}bookaroom_event_categories as cats on ec.ec_catID = cats.categories_id		
		WHERE ti.ti_type = 'event' AND ti.ti_id = '{$eventID}'
		
		GROUP BY ti.ti_id";


		$eventInfo = $mydb->get_results( $sql, ARRAY_A );

		if ( $mydb->num_rows == 0 ) {
			return FALSE;
		}

		return $eventInfo[ 0 ];

	}

	protected static function getExternals()
	# Pull in POST and GET values
	{
		$final = array();

		# setup GET variables
		$getArr = array( 'action' => FILTER_SANITIZE_STRING,
						'branchID' => FILTER_SANITIZE_STRING,
						'ageGroup' => array( 'filter' => FILTER_SANITIZE_STRING,
											'flags' => FILTER_REQUIRE_ARRAY ),
						'categoryGroup' => array( 'filter' => FILTER_SANITIZE_STRING,
												 'flags' => FILTER_REQUIRE_ARRAY ),
						'eventID' => FILTER_SANITIZE_STRING,
						'timestamp' => FILTER_SANITIZE_STRING,
						'filter' => FILTER_SANITIZE_STRING,
						'age' => FILTER_SANITIZE_STRING,
						'category' => FILTER_SANITIZE_STRING,
						'searchTerms' => FILTER_SANITIZE_STRING,
						'sortOrder' => FILTER_SANITIZE_STRING,
						'startDate' => FILTER_SANITIZE_STRING,
						'endDate' => FILTER_SANITIZE_STRING,
						'title' => FILTER_SANITIZE_STRING,
		);

		# pull in and apply to final
		if ( $getTemp = filter_input_array( INPUT_GET, $getArr ) )
			$final = $getTemp;

		# setup POST variables
		$postArr = array( 'published' => FILTER_SANITIZE_STRING,
						 'searchTerms' => FILTER_SANITIZE_STRING,
						 'ageGroup' => array( 'filter' => FILTER_SANITIZE_STRING,
											 'flags' => FILTER_REQUIRE_ARRAY ),
						 'categoryGroup' => array( 'filter' => FILTER_SANITIZE_STRING,
												  'flags' => FILTER_REQUIRE_ARRAY ),
						 'branchID' => FILTER_SANITIZE_STRING,
						'startDate' => FILTER_SANITIZE_STRING,
						'endDate' => FILTER_SANITIZE_STRING,
						'action' => FILTER_SANITIZE_STRING,
						'formAction' => FILTER_SANITIZE_STRING,
						'bookaroomRegFormSub' => FILTER_SANITIZE_STRING,
						'eventID' => FILTER_SANITIZE_STRING,
						'timestamp' => FILTER_SANITIZE_STRING,
						'fullName' => FILTER_SANITIZE_STRING,
						'phone' => FILTER_SANITIZE_STRING,
						'email' => FILTER_SANITIZE_STRING,
						'notes' => FILTER_SANITIZE_STRING,
						 'roomChecked' => array( 'filter' => FILTER_SANITIZE_STRING,
												'flags' => FILTER_REQUIRE_ARRAY ) );


		# pull in and apply to final
		if ( $postTemp = filter_input_array( INPUT_POST, $postArr ) ) {
			$final = array_merge( $final, $postTemp );
		}

		$arrayCheck = array_unique( array_merge( array_keys( $getArr ), array_keys( $postArr ) ) );

		foreach ( $arrayCheck as $key ) {
			if ( empty( $final[ $key ] ) ) {
				$final[ $key ] = NULL;
			} elseif ( is_array( $final[ $key ] ) ) {
				$final[ $key ] = $final[ $key ];
			} else {
				$final[ $key ] = trim( $final[ $key ] );
			}
		}

		if ( !empty( $final[ 'formAction' ] ) ) {
			$final[ 'action' ] = $final[ 'formAction' ];
			$final[ 'formAction' ] = NULL;
		}

		return $final;
	}

	protected static function getMonthEvents( $timestamp, $filter = NULL, $age = NULL, $category = NULL ) {
		global $wpdb;

		$settings[ 'bookaroom_events_db_key' ] = get_option( 'bookaroom_events_db_key' );
		$settings[ 'bookaroom_events_db_database' ] = get_option( 'bookaroom_events_db_database' );
		$settings[ 'bookaroom_events_db_username' ] = get_option( 'bookaroom_events_db_username' );
		$settings[ 'bookaroom_events_db_password' ] = get_option( 'bookaroom_events_db_password' );
		$settings[ 'bookaroom_events_prefix' ] = get_option( 'bookaroom_events_prefix' );
		$settings[ 'bookaroom_events_db_host' ] = get_option( 'bookaroom_events_db_host' );

		#$settings['bookaroom_events_db_password']	= bookaroom_events_decrypt( $settings['bookaroom_events_db_password'], $settings['bookaroom_events_db_key'] );

		$mydb = new wpdb( $settings[ 'bookaroom_events_db_username' ], $settings[ 'bookaroom_events_db_password' ], $settings[ 'bookaroom_events_db_database' ], $settings[ 'bookaroom_events_db_host' ] );


		if ( empty( $timestamp ) ) {
			$timestamp = time();
		}

		$catList = self::getCatList( $mydb, $settings[ 'bookaroom_events_prefix' ] );
		$ageList = self::getAgeList( $mydb, $settings[ 'bookaroom_events_prefix' ] );

		# find first of the month
		$monthFirst = date( 'Y-m-01', $timestamp ) . ' 00:00:00';
		$monthLast = date( 'Y-m-t', $timestamp ) . ' 23:59:59';

		$where = array();
		$prefix = $settings[ 'bookaroom_events_prefix' ];

		# is there an age filter?
		if ( !empty( $age ) ) {
			$curAgeList = explode( ',', $age );

			if ( !is_null( $curAgeList ) ) {
				array_walk( $curAgeList, function ( & $value, $index )use( $ageList ) {
					$value = trim( $value );

					if ( ( $foundIt = array_search( strtolower( $value ), $ageList[ 'all' ] ) ) ) {
						$value = "'{$foundIt}'";
					} else {
						$value = NULL;
					}
				} );

				$curAgeList = array_filter( $curAgeList );

				if ( count( $curAgeList ) > 0 ) {
					$where[] = "ti.ti_extID IN ( SELECT ea.ea_eventID FROM {$prefix}bookaroom_eventAges as ea WHERE ea.ea_ageID IN (" . implode( ',', $curAgeList ) . ") )";
				}
			}
		}

		# is there an category filter?
		if ( !empty( $category ) ) {
			$curCatList = explode( ',', $category );

			array_walk( $curCatList, function ( & $value, $index )use( $catList ) {
				$value = trim( $value );

				if ( ( $foundIt = array_search( strtolower( $value ), $catList[ 'all' ] ) ) ) {
					$value = "'{$foundIt}'";
				} else {
					$value = NULL;
				}
			} );

			$curCatList = array_filter( $curCatList );

			if ( count( $curCatList ) > 0 ) {
				$where[] = "ti.ti_extID IN ( SELECT ec.ec_eventID FROM {$prefix}bookaroom_eventCats as ec WHERE ec.ec_catID IN (" . implode( ',', $curCatList ) . ") )";
			}
		}


		# search term
		if ( !empty( $filter ) ) {
			$where[] = " MATCH ( ev.ev_desc, ev.ev_presenter, ev.ev_privateNotes, ev.ev_publicEmail, ev.ev_publicName, ev.ev_submitter, ev.ev_title, ev.ev_website, ev.ev_webText ) AGAINST ('{$filter}' IN NATURAL LANGUAGE MODE )";
			$scoreWhere = "score DESC, ";
		} else {
			$scoreWhere = NULL;
		}

		if ( count( $where ) > 0 ) {
			$whereFinal = ' AND ' . implode( ' AND ', $where );
		} else {
			$whereFinal = NULL;
		}

		$sql = "SELECT MATCH ( ev.ev_desc, ev.ev_presenter, ev.ev_privateNotes, ev.ev_publicEmail, ev.ev_publicName, ev.ev_submitter, ev.ev_title, ev.ev_website, ev.ev_webText ) AGAINST ('{$filter}' IN NATURAL LANGUAGE MODE ) as score, 
		ti.ti_id, ti.ti_extID, ti.ti_startTime, ti.ti_endTime, ti.ti_roomID, ev.ev_desc, ev.ev_maxReg, ev.ev_waitingList, ev.ev_presenter, ev.ev_privateNotes, ev.ev_publicEmail, ev.ev_publicName, ev.ev_publicPhone, ev.ev_noPublish, ev.ev_regStartDate, ev.ev_regType, ev.ev_submitter, ev.ev_title, ev.ev_website, ev.ev_webText, ti.ti_extraInfo, 
				group_concat(DISTINCT ea.ea_ageID separator ', ') as ageID, group_concat(DISTINCT ages.age_desc separator ', ') as ages, 
				group_concat(DISTINCT ec.ec_catID separator ', ') as catID, group_concat(DISTINCT cats.categories_desc separator ', ') as cats
				FROM  {$prefix}bookaroom_times as ti
				LEFT JOIN {$prefix}bookaroom_reservations as ev ON ti.ti_extID = ev.res_id
				LEFT JOIN {$prefix}bookaroom_eventAges as ea on ea.ea_eventID = ti.ti_extID
				LEFT JOIN {$prefix}bookaroom_event_ages as ages on ea.ea_ageID = ages.age_id
				
				LEFT JOIN {$prefix}bookaroom_eventCats as ec on ec.ec_eventID = ti.ti_extID
				LEFT JOIN {$prefix}bookaroom_event_categories as cats on ec.ec_catID = cats.categories_id
				
				WHERE ti.ti_type =  'event'
				AND ti.ti_startTime >=  '{$monthFirst}'
				AND ti.ti_endTime <=  '{$monthLast}' 
				AND ev.ev_noPublish = '0'{$whereFinal}
				GROUP BY ti.ti_id 
				ORDER BY {$scoreWhere}ti.ti_startTime";

		$cooked = $mydb->get_results( $sql, ARRAY_A );
		$final = array();

		foreach ( $cooked as $val ) {
			$dateInfo = getdate( strtotime( $val[ 'ti_startTime' ] ) );
			$final[ $dateInfo[ 'mday' ] ][] = $val;
		}

		return $final;
	}

	public static function showEvents( $atts ) {
		$externals = self::getExternals();
		
		if ( $externals[ 'action' ] == 'viewEvent' ) {
			# check event ID
			if ( empty( $externals[ 'eventID' ] )or( $eventInfo = self::checkID( $externals[ 'eventID' ] ) ) == false ) {
				wp_die( __( "ERROR! Bad ID in ShowEvents!", 'book-a-room-events' ) );
				return false;
			}
			$_SESSION[ 'bookaroomRegFormSub' ] = md5( rand( 1, 500000000000 ) );

			$externals[ 'bookaroomRegFormSub' ] = $_SESSION[ 'bookaroomRegFormSub' ];
			ob_start();
			self::viewEvent( $eventInfo, $externals );			
			$contents = ob_get_contents();	
			ob_end_clean();
			return $contents;
		}

		extract( shortcode_atts( array(
			'start_offset' => '0',
			'end_offset' => '7',
			'num_offset' => 0,
			'basepage'	=> ''
		), $atts ) );

		$bookaroom_events_db_key		= get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database	= get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username	= get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password	= get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix		= get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host		= get_option( 'bookaroom_events_db_host' );

		if ( empty( $bookaroom_events_db_username ) OR empty( $bookaroom_events_db_password ) OR empty( $bookaroom_events_db_database ) OR empty( $bookaroom_events_db_host ) ) {
			return FALSE;
		}

		$newdb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		if ( !empty( $start_offset ) ) {
			$timeStart = time() + ( $start_offset * 60 * 60 * 24 );
		} else {
			$timeStart = time();
		}

		$timeStartInfo = getdate( $timeStart );
		$startTime = date( 'Y-m-d H:i:s', mktime( 0, 0, 0, $timeStartInfo[ 'mon' ], $timeStartInfo[ 'mday' ], $timeStartInfo[ 'year' ] ) );

		if ( !empty( $end_offset ) ) {
			$timeEnd = time() + ( $end_offset * 60 * 60 * 24 );
		} else {
			$timeEnd = time();
		}
		$timeEndInfo = getdate( $timeEnd );
		$endTime = date( 'Y-m-d H:i:s', mktime( 0, 0, 0, $timeEndInfo[ 'mon' ], $timeEndInfo[ 'mday' ], $timeEndInfo[ 'year' ] ) );

		$catList = self::getCatList( $newdb, $bookaroom_events_prefix );
		$ageList = self::getAgeList( $newdb, $bookaroom_events_prefix );

		$where = array();
		# is there an age filter?
		if ( !empty( $atts[ 'age' ] ) ) {
			$curAgeList = explode( ',', $atts[ 'age' ] );
			if ( !is_null( $curAgeList ) ) {
				array_walk( $curAgeList, function ( & $value, $index )use( $ageList ) {
					$value = trim( $value );
					if ( ( $foundIt = array_search( strtolower( $value ), $ageList[ 'all' ] ) ) ) {
						$value = "'{$foundIt}'";
					} else {
						$value = NULL;
					}
				} );

				$curAgeList = array_filter( $curAgeList );

				if ( count( $curAgeList ) > 0 ) {
					$curAgeList = implode( ',', $curAgeList );
					$where[] = "ti.ti_extID IN ( SELECT ea.ea_eventID FROM {$bookaroom_events_prefix}bookaroom_eventAges as ea WHERE ea.ea_ageID IN ({$curAgeList}) )";
				}
			}
		}

		# is there an category filter?
		if ( !empty( $atts[ 'category' ] ) ) {
			$curCatList = explode( ',', $atts[ 'category' ] );

			array_walk( $curCatList, function ( & $value, $index )use( $catList ) {
				$value = trim( $value );

				if ( ( $foundIt = array_search( strtolower( $value ), $catList[ 'all' ] ) ) ) {
					$value = "'{$foundIt}'";
				} else {
					$value = NULL;
				}
			} );

			$curCatList = array_filter( $curCatList );

			if ( count( $curCatList ) > 0 ) {
				$where[] = "ti.ti_extID IN ( SELECT ec.ec_eventID FROM {$bookaroom_events_prefix}bookaroom_eventCats as ec WHERE ec.ec_catID IN (" . implode( ',', $curCatList ) . ") )";
			}
		}

		if ( count( $where ) > 0 ) {
			$whereFinal = ' AND ' . implode( ' AND ', $where );
		} else {
			$whereFinal = NULL;
		}

		if ( !empty( $num_offset )and is_numeric( $num_offset ) ) {
			$limit = " LIMIT {$num_offset}";
		} else {
			$limit = NULL;
		}

		$sql = "SELECT ti.ti_id, ti.ti_type, ti.ti_extID, ti.ti_created, ti.ti_startTime, ti.ti_endTime, ti.ti_roomID, 
res.res_id, res.res_created, res.ev_desc, res.ev_maxReg, res.ev_presenter, res.ev_privateNotes, res.ev_publicEmail, res.ev_publicName, res.ev_publicPhone, res.ev_noPublish, res.ev_regStartDate, res.ev_regType, res.ev_submitter, res.ev_title, res.ev_website, res.ev_webText, ti.ti_noLocation_branch, ti.ti_extraInfo,  
		group_concat(DISTINCT ea.ea_ageID separator ', ') as ageID, group_concat(DISTINCT ages.age_desc separator ', ') as ages, 
		group_concat(DISTINCT ec.ec_catID separator ', ') as catID, group_concat(DISTINCT cats.categories_desc separator ', ') as cats
		FROM {$bookaroom_events_prefix}bookaroom_times AS ti 
		LEFT JOIN {$bookaroom_events_prefix}bookaroom_reservations as res ON ti.ti_extID = res.res_id 
		LEFT JOIN {$bookaroom_events_prefix}bookaroom_eventAges as ea on ea.ea_eventID = ti.ti_extID
		LEFT JOIN {$bookaroom_events_prefix}bookaroom_event_ages as ages on ea.ea_ageID = ages.age_id
		LEFT JOIN {$bookaroom_events_prefix}bookaroom_eventCats as ec on ec.ec_eventID = ti.ti_extID
		LEFT JOIN {$bookaroom_events_prefix}bookaroom_event_categories as cats on ec.ec_catID = cats.categories_id
		WHERE ti.ti_type = 'event' AND 
			(	ti.ti_startTime <= '{$endTime}' AND
				ti.ti_endTime >= '{$startTime}' )
				AND res.ev_noPublish = '0'{$whereFinal}
				GROUP BY ti.ti_id 
				ORDER BY ti.ti_startTime ASC {$limit}";

		$eventList = $newdb->get_results( $sql, ARRAY_A );

		if ( count( $eventList ) == 0 ) {
			_e( 'No upcoming events', 'book-a-room-events' );
		}
		
		$roomContList = self::getRoomContList( $newdb, $bookaroom_events_prefix );
			
	
		#require( BAR_EVENTS_PLUGIN_PATH . 'templates/events_list.php' );

		
		ob_start();
		require( BAR_EVENTS_PLUGIN_PATH . 'templates/events_list.php' );
		$contents = ob_get_contents();	
		ob_end_clean();
		return $contents;
		
	}

	public static function getAmenityList( $wpdb, $bookaroom_events_prefix, $onlyReservable = false, $returnReservableBit = false )
	# get a list of all of the available amenities. 
	# Return NULL on no amenities
	# otherwise, return an array with the unique ID of each amenity
	# as the key and the description as the val
	{

		if ( $onlyReservable ) {
			$sql = "SELECT amenityID, amenityDesc, amenity_isReservable FROM {$bookaroom_events_prefix}bookaroom_amenities WHERE amenity_isReservable IS TRUE ORDER BY amenityDesc";
		} else {
			$sql = "SELECT amenityID, amenityDesc,amenity_isReservable FROM {$bookaroom_events_prefix}bookaroom_amenities ORDER BY amenityDesc";
		}

		$count = 0;

		$cooked = $wpdb->get_results( $sql, ARRAY_A );
		if ( count( $cooked ) == 0 ) {
			return NULL;
		}

		foreach ( $cooked as $key => $val ) {
			if ( $returnReservableBit ) {
				$final[ 'id' ][ $val[ 'amenityID' ] ] = array( 'amenityID' => $val[ 'amenityID' ], 'amenityDesc' => $val[ 'amenityDesc' ], 'amenity_isReservable' => $val[ 'amenity_isReservable' ] );
				$final[ 'desc' ][ $val[ 'amenityID' ] ] = $val[ 'amenityDesc' ];
			} else {
				$final[ $val[ 'amenityID' ] ] = $val[ 'amenityDesc' ];
			}
		}

		return $final;
	}

	public static function getRoomContList( $wpdb, $bookaroom_events_prefix )
	# get a list of room containers
	{
		$roomContList = array();
		$table_name = $bookaroom_events_prefix . "bookaroom_roomConts";
		$table_name_members = $bookaroom_events_prefix . "bookaroom_roomConts_members";

		$sql = "SELECT roomCont.roomCont_ID, roomCont.roomCont_desc, roomCont.roomCont_branch, roomCont.roomCont_occ, 
				GROUP_CONCAT( members.rcm_roomID ) as roomCont_roomArr 
				FROM $table_name as roomCont 
				LEFT JOIN $table_name_members as members ON roomCont.roomCont_ID = members.rcm_roomContID 
				GROUP BY roomCont.roomCont_ID 
				ORDER BY roomCont.roomCont_branch, roomCont.roomCont_desc";

		# 				roomCont.roomCont_roomArr,

		$count = 0;

		$cooked = $wpdb->get_results( $sql, ARRAY_A );

		if ( count( $cooked ) == 0 ) {
			return array();
		}

		foreach ( $cooked as $key => $val ) {
			# check for rooms
			$roomsGood = ( empty( $val[ 'roomCont_roomArr' ] ) ) ? NULL : explode( ',', $val[ 'roomCont_roomArr' ] );
			$roomContList[ 'id' ][ $val[ 'roomCont_ID' ] ] = array( 'branchID' => $val[ 'roomCont_branch' ], 'rooms' => $roomsGood, 'desc' => $val[ 'roomCont_desc' ], 'occupancy' => $val[ 'roomCont_occ' ] );
			$roomContList[ 'names' ][ $val[ 'roomCont_branch' ] ][ $val[ 'roomCont_ID' ] ] = $val[ 'roomCont_desc' ];
			$roomContList[ 'branch' ][ $val[ 'roomCont_branch' ] ][] = $val[ 'roomCont_ID' ];

		}

		return $roomContList;
	}

	protected static function getRoomList( $wpdb, $bookaroom_events_prefix ) {
		$roomList = array();

		$table_name = $bookaroom_events_prefix . "bookaroom_rooms";
		$sql = "SELECT roomID, room_desc, room_amenityArr, room_branchID FROM $table_name ORDER BY room_branchID, room_desc";

		$count = 0;

		$cooked = $wpdb->get_results( $sql, ARRAY_A );

		if ( count( $cooked ) == 0 ) {
			return array();
		}

		foreach ( $cooked as $key => $val ) {
			# check for amenities
			$amenityGood = ( empty( $val[ 'room_amenityArr' ] ) ) ? NULL : unserialize( $val[ 'room_amenityArr' ] );
			$roomList[ 'room' ][ $val[ 'room_branchID' ] ][ $val[ 'roomID' ] ] = $val[ 'room_desc' ];
			#$roomList['amenity'][$val['roomID']] = $amenityGood;
			$roomList[ 'id' ][ $val[ 'roomID' ] ] = array( 'branch' => $val[ 'room_branchID' ], 'amenity' => $amenityGood, 'desc' => $val[ 'room_desc' ] );
		}

		return $roomList;
	}

	protected static function getBranches( $wpdb, $bookaroom_events_prefix )
	# get a list of all of the branches. Return NULL on no branches
	# otherwise, return an array with the unique ID of each branch
	# as the key and the description as the val
	{

		$table_name = $bookaroom_events_prefix . "bookaroom_branches";
		$sql = "SELECT branchID, branchDesc, branchAddress, branchMapLink, branchImageURL, branchOpen_0, branchOpen_1, branchOpen_2, branchOpen_3, branchOpen_4, branchOpen_5, branchOpen_6, branchClose_0, branchClose_1, branchClose_2, branchClose_3, branchClose_4, branchClose_5, branchClose_6 FROM $table_name ORDER BY branchDesc";

		$count = 0;

		$cooked = $wpdb->get_results( $sql, ARRAY_A );
		if ( count( $cooked ) == 0 ) {
			return NULL;
		}

		foreach ( $cooked as $key => $val ) {
			$final[ $val[ 'branchID' ] ] = $val;
		}

		return $final;
	}

	protected static function viewEvent( $eventInfo, $externals, $errorMSG = NULL, $isSuccess = false ) {
		# setup
		$bookaroom_events_db_key = get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database = get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username = get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password = get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix = get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host = get_option( 'bookaroom_events_db_host' );

		$mydb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		$roomContList = self::getRoomContList( $mydb, $bookaroom_events_prefix );
		$branchList = self::getBranches( $mydb, $bookaroom_events_prefix );
		$ageList = self::getAgeList( $mydb, $bookaroom_events_prefix );
		$catList = self::getCatList( $mydb, $bookaroom_events_prefix );

		$todayArr = getdate( time() );
		$today = mktime( 0, 0, 0, $todayArr[ 'mon' ], $todayArr[ 'mday' ], $todayArr[ 'year' ] );

		# load template
		require( BAR_EVENTS_PLUGIN_PATH . 'templates/viewEvent.php' );

		return true;
	}

	protected static function getAgeList( $mydb, $bookaroom_events_prefix ) {
		$table_name = $bookaroom_events_prefix . "bookaroom_event_ages";
		$sql = "SELECT age_id, age_desc, age_order, age_active FROM $table_name ";

		$cooked = $mydb->get_results( $sql, ARRAY_A );

		$final = array( 'active' => array(), 'inactive' => array(), 'all' => array(), 'status' => array(), 'order' => array() );

		foreach ( $cooked as $key => $val ) {
			$active = ( empty( $val[ 'age_active' ] ) ) ? 'inactive' : 'active';
			$final[ $active ][ $val[ 'age_id' ] ] = $val;
			$final[ 'all' ][ $val[ 'age_id' ] ] = strtolower( $val[ 'age_desc' ] );
			$final[ 'status' ][ $val[ 'age_id' ] ] = $val[ 'age_active' ];
			$final[ 'order' ][ $val[ 'age_order' ] ] = $val[ 'age_id' ];
		}

		ksort( $final[ 'order' ] );

		return $final;
	}

	public static function getCatList( $mydb, $bookaroom_events_prefix ) {

		$table_name = $bookaroom_events_prefix . "bookaroom_event_categories";
		$sql = "SELECT categories_id, categories_desc, categories_order, categories_active FROM $table_name ";

		$cooked = $mydb->get_results( $sql, ARRAY_A );

		$final = array( 'active' => array(), 'inactive' => array(), 'all' => array(), 'status' => array(), 'order' => array() );

		foreach ( $cooked as $key => $val ) {
			$active = ( empty( $val[ 'categories_active' ] ) ) ? 'inactive' : 'active';
			$final[ $active ][ $val[ 'categories_id' ] ] = $val;
			$final[ 'all' ][ $val[ 'categories_id' ] ] = strtolower( $val[ 'categories_desc' ] );
			$final[ 'status' ][ $val[ 'categories_id' ] ] = $val[ 'categories_active' ];
			$final[ 'order' ][ $val[ 'categories_order' ] ] = $val[ 'categories_id' ];
		}

		ksort( $final[ 'order' ] );

		return $final;
	}

	protected static function getRegEmailList( $email ) {
		$bookaroom_events_db_key = get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database = get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username = get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password = get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix = get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host = get_option( 'bookaroom_events_db_host' );

		$newdb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		$branchList = self::getBranches( $newdb, $bookaroom_events_prefix );
		$roomContList = self::getRoomContList( $newdb, $bookaroom_events_prefix );

		$sql = "	SELECT reg.reg_id, 
					reg.reg_dateReg, 
					reg.reg_fullname, 
					ti.ti_startTime, 
					ti.ti_endTime,
					res.ev_title, 
					res.ev_desc,
					ti.ti_roomID, 
					res.ev_maxReg,  
					IF( ti.ti_noLocation_branch > 0, 'No location needed', conts.roomCont_desc ) as roomName, 
					branch.branchDesc, 
					
					COUNT( DISTINCT regCount.reg_id ) as regCount, 
					group_concat( DISTINCT regCount.reg_id ), 
					COUNT( DISTINCT regCount2.reg_id ) + 1 as regCount2, 
					group_concat( DISTINCT regCount2.reg_id )
					
					
					FROM {$bookaroom_events_prefix}bookaroom_registrations as reg 
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_times as ti ON reg.reg_eventID = ti.ti_id
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_reservations as res ON ti.ti_extID = res.res_id
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_roomConts as conts ON conts.roomCont_ID = ti.ti_roomID
					
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_branches as branch ON IF( ti.ti_noLocation_branch > 0, ti.ti_noLocation_branch, conts.roomCont_branch ) = branch.branchID
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_registrations as regCount ON regCount.reg_eventID = reg.reg_eventID
					LEFT JOIN {$bookaroom_events_prefix}bookaroom_registrations as regCount2 ON ( reg.reg_eventID = regCount2.reg_eventID AND   regCount.reg_dateReg < regCount2.reg_dateReg  )
					
					WHERE reg.reg_email = '{$email}' and ti.ti_type = 'event'
					
					GROUP BY reg.reg_id
					
					ORDER BY reg.reg_dateReg DESC";


		$cooked = $newdb->get_results( $sql, ARRAY_A );
		#bookaroom_events_preme( $cooked );
		return $cooked;

	}

	protected static function getRegInfo( $eventID, $mydb, $bookaroom_events_prefix ) {
		$table_name = $bookaroom_events_prefix . "bookaroom_registrations";

		$sql = "	SELECT reg_id, reg_fullName, reg_phone, reg_email, reg_notes, reg_dateReg 
					FROM $table_name 
					WHERE reg_eventID = '{$eventID}' 
					ORDER BY reg_dateReg";

		$cooked = $mydb->get_results( $sql, ARRAY_A );

		return $cooked;

	}


	protected static function sendEmailRegAlert( $email, $results ) {
		global $mydb;
		
		ob_start();
		
		require( BAR_EVENTS_PLUGIN_PATH .  'templates/emailRegistrations_email.php' );
		$contents = ob_get_contents();
		ob_end_clean();


		# TODO change subject and emails
		$bookaroom_events_db_key = get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database = get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username = get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password = get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix = get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host = get_option( 'bookaroom_events_db_host' );

		$newdb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		$query = "SELECT option_value FROM {$bookaroom_events_db_database}.wp_options WHERE option_name = 'bookaroom_alertEmailFromEmail' LIMIT 1";
		$emailFromRaw = $newdb->get_row( $query, ARRAY_A );
		$emailFrom = $emailFromRaw[ 'option_value' ];

		$query = "SELECT option_value FROM {$bookaroom_events_db_database}.wp_options WHERE option_name = 'bookaroom_alertEmailFromName' LIMIT 1";
		$emailNameRaw = $newdb->get_row( $query, ARRAY_A );
		$emailName = $emailNameRaw[ 'option_value' ];

		$subject = __( 'Your registration list from Book a Room.', 'book-a-room-events' );
		$headers = 'MIME-Version: 1.0' . "\r\n";

		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
		"From: {$emailName} <{$emailFrom}>\r\n" .
		'X-Mailer: PHP/' . phpversion();

		mail( $email, $subject, $contents, $headers );

		$contents .= "Email: " . $email . "<br />IP:" . $_SERVER[ 'REMOTE_ADDR' ];
	}

	protected static function showEmailRegistrations( $externals, $errorMSG = NULL ) {
		require( BAR_EVENTS_PLUGIN_PATH .  'templates/emailRegistrations.php' );

	}

	protected static function showSearchedEvents( $externals, $results = array(), $searched = false, $errorMSG = NULL ) {
		$bookaroom_events_db_key = get_option( 'bookaroom_events_db_key' );
		$bookaroom_events_db_database = get_option( 'bookaroom_events_db_database' );
		$bookaroom_events_db_username = get_option( 'bookaroom_events_db_username' );
		$bookaroom_events_db_password = get_option( 'bookaroom_events_db_password' );
		$bookaroom_events_prefix = get_option( 'bookaroom_events_prefix' );
		$bookaroom_events_db_host = get_option( 'bookaroom_events_db_host' );

		$newdb = new wpdb( $bookaroom_events_db_username, $bookaroom_events_db_password, $bookaroom_events_db_database, $bookaroom_events_db_host );

		$branchList = self::getBranches( $newdb, $bookaroom_events_prefix );
		$roomList = self::getRoomList( $newdb, $bookaroom_events_prefix );
		$roomContList = self::getRoomContList( $newdb, $bookaroom_events_prefix );
		$amenityList = self::getAmenityList( $newdb, $bookaroom_events_prefix );

		# get template
		require( BAR_EVENTS_PLUGIN_PATH . 'templates/searchResults.php' );

		return true;
	}

	public static function branch_and_room_id( & $roomID, $branchList, $roomContList ) {

		if ( !empty( $roomID )and array_key_exists( $roomID, $roomContList[ 'id' ] ) ) {
			$branchID = $roomContList[ 'id' ][ $roomID ][ 'branchID' ];
			# else check branchID
		} else {
			$branchID = NULL;
		}

		if ( !array_key_exists( $roomID, $roomContList[ 'id' ] ) ) {
			$roomID = NULL;
		}

		return $branchID;
	}

	protected static function showEmailRegistrations_success( $email ) {
		require( BAR_EVENTS_PLUGIN_PATH . 'templates/emailRegistrations_success.php' );
	}

	public static function makeGoogleLink( $contents, $startTimeStamp, $endTimeStamp, $eventTitle, $room, $branch ) {
		$googleLink = "//www.google.com/calendar/event?action=TEMPLATE&text={$eventTitle}&dates=" . date( 'Ymd\THi00', $startTimeStamp ) . "Z/" . date( 'Ymd\THi00', $endTimeStamp ) . "Z&details=&location={$room} at {$branch}&trp=false&sprop=&sprop=name:\"";

		return $googleLink;
	}

	public static function makeOutlookLink( $contents, $startTimeStamp, $endTimeStamp, $eventTitle, $room, $branch ) {
		$final[ 'startTimeStamp' ] = date( "y-m-d H:i", $startTimeStamp );
		$final[ 'endTimeStamp' ] = date( "y-m-d H:i", $endTimeStamp );

		$final[ 'eventTitle' ] = $eventTitle;
		$final[ 'room' ] = $room;
		$final[ 'branch' ] = $branch;

		return plugins_url() . '/book-a-room-event-calendar/bookaroom-create_iCal.php?' . http_build_query( $final );
	}

	public static function makeiCalLink( $contents, $startTimeStamp, $endTimeStamp, $eventTitle, $room, $branch ) {
		$offset = get_option( 'gmt_offset' );
		$final[ 'startTimeStamp' ] = date( "y-m-d H:i", $startTimeStamp );
		$final[ 'endTimeStamp' ] = date( "y-m-d H:i", $endTimeStamp );

		$final[ 'eventTitle' ] = $eventTitle;
		$final[ 'room' ] = $room;
		$final[ 'branch' ] = $branch;

		$pluginURL = str_replace( 'http://', 'webcal://', plugins_url() );
		$pluginURL = str_replace( 'https://', 'webcal://', plugins_url() );

		return $pluginURL . '/book-a-room-event-calendar/bookaroom-create_iCal.php?' . http_build_query( $final );
	}


	protected static function showCalendar( $timestampRaw = NULL, $filter = NULL, $age = NULL, $category = NULL ) {
		$settings[ 'bookaroom_events_db_key' ] = get_option( 'bookaroom_events_db_key' );
		$settings[ 'bookaroom_events_db_database' ] = get_option( 'bookaroom_events_db_database' );
		$settings[ 'bookaroom_events_db_username' ] = get_option( 'bookaroom_events_db_username' );
		$settings[ 'bookaroom_events_db_password' ] = get_option( 'bookaroom_events_db_password' );
		$settings[ 'bookaroom_events_prefix' ] = get_option( 'bookaroom_events_prefix' );
		$settings[ 'bookaroom_events_db_host' ] = get_option( 'bookaroom_events_db_host' );


		$mydb = new wpdb( $settings[ 'bookaroom_events_db_username' ], $settings[ 'bookaroom_events_db_password' ], $settings[ 'bookaroom_events_db_database' ], $settings[ 'bookaroom_events_db_host' ] );

		$monthEvents = self::getMonthEvents( $timestampRaw, $filter, $age, $category );

		require( BAR_EVENTS_PLUGIN_PATH . 'templates/calendar.php' );
	}
}

?>