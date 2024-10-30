<?PHP
//set correct content-type-header
$getArr = array(	'startTimeStamp'	=> FILTER_SANITIZE_STRING,
					'endTimeStamp'		=> FILTER_SANITIZE_STRING,
					'eventTitle'		=> FILTER_SANITIZE_STRING,
					'room'				=> FILTER_SANITIZE_STRING,
					'branch'			=> FILTER_SANITIZE_STRING,
				);
# pull in and apply to final
if( $final = filter_input_array( INPUT_GET, $getArr ) )
foreach( $final as $key => $val ):
	if( empty( $val ) ):
		$final[$key] = NULL;
	else:			
		$final[$key] = trim( $val );
	endif;
endforeach;

#print_r( $final );

#echo( $final['startTimeStamp'].'<br />' );
#echo( date( 'm-d-y g:i a', $final['startTimeStamp'] ) ).'<br />';
#echo "------------------------------<br />";


#$final['startTimeStamp'] = strtotime( date( "m-d-y g:i a +5", $final['startTimeStamp'] ) );
#$final['startTimeStamp'] = strtotime( date( "m-d-y g:i a +5", $final['endTimeStamp'] ) );


#echo "<pre>";print_r( $final );echo "</pre>";

$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Bookaroom/EventCal//EN

BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".date('Ymd\THi00', strtotime( $final['startTimeStamp'] ) )."
DTEND:".date('Ymd\THi00', strtotime( $final['endTimeStamp'] ) )."
SUMMARY:{$final['eventTitle']}
LOCATION:{$final['room']} at {$final['branch']}
END:VEVENT
END:VCALENDAR";

header('Content-Disposition: inline; filename=BookaroomCalendarReminder.ics');
header('Content-type: text/calendar; charset=utf-8');
header("HTTP/1.0 200 OK", true, 200);

echo $ical ;
exit;
?>