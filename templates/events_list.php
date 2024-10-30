<style type="text/css">
	#eventContainer {
		width: 100%;
		position: relative;
		padding: 0px;
		margin: 0px;
		clear: both;
	}
	
	#eventContainer .eventWrapper {
		position: relative;
		margin: 0px 0px 20px 0px;
		clear: both;
		width: 100%;
		border: 1px solid #CCC;
		border-radius: 1em
	}
	
	#eventContainer .eventWrapper .eventBuffer {
		padding: 10px;
		clear: both;
		position: relative;
	}
	
	#eventContainer .eventWrapper .eventName {
		position: relative;
		font-size: 1.2em;
		font-weight: bold;
		clear: both;
		width: 100%;
		padding: 5px 10px 5px 10px;
	}
	
	#eventContainer .eventWrapper .eventText {
		clear: both;
		position: relative;
	}
	
	#eventContainer .eventWrapper .eventHeader {
		background-color: #DDD;
		border-radius: 1em 1em 0em 0em;
		clear: both;
		position: relative;
	}
	
	#eventContainer .eventWrapper .eventDate {
		position: relative;
		float: left;
		padding-right: 30px;
	}
	
	#eventContainer .eventWrapper .eventBranch {
		clear: both;
		position: relative;
	}
	
	#eventContainer .eventWrapper .eventTime {
		clear: right;
		position: relative;
	}
</style>
<div id="bookaroom_main_container">

    <div id="eventContainer">
        <?php
        foreach ( $eventList as $key => $val ) {
            if ( date( 'g:i a', strtotime( $val[ 'ti_startTime' ] ) ) == "12:00 am" ) {
                $times = __( 'All Day', 'book-a-room-events' );
            } else {
                $times = date( 'g:i a', strtotime( $val[ 'ti_startTime' ] ) );
            }

            $roomContList = self::getRoomContList( $newdb, $bookaroom_events_prefix );
            $branchList = self::getBranches( $newdb, $bookaroom_events_prefix );
            #$ageList = self::getAgeList( $mydb, $bookaroom_events_prefix );
            #$catList = self::getCatList( $mydb, $bookaroom_events_prefix );

            if ( !empty( $val[ 'ti_noLocation_branch' ] ) ) {
                $branch		= $branchList[ $val[ 'ti_noLocation_branch' ] ][ 'branchDesc' ];
                $branchDesc		= __( 'No location specified', 'book-a-room-events' );
            } else {
                $branch		= $branchList[ $roomContList[ 'id' ][ $val[ 'ti_roomID' ] ][ 'branchID' ] ][ 'branchDesc' ];
                $branchDesc	= $roomContList['id'][ $val[ 'ti_roomID' ] ]['desc'];
            }
			
			if( !isset( $atts['basepage'] ) or empty( $atts['basepage'] ) ) {
				$atts['basepage'] = '';
			}
        ?>
        <div class="eventWrapper">
            <div class="eventHeader">
                <div class="eventName"><a href="<?php echo $atts['basepage']; ?>?action=viewEvent&amp;eventID=<?php echo $val[ 'ti_id' ]; ?>" target="_blank"><?php echo $val[ 'ev_title' ]; ?></a>
                </div>
            </div>

            <div class="eventBuffer">
                <div class="eventBranch"><strong><?php _e( 'Branch', 'book-a-room-events' ); ?>: </strong><?php echo $branch; ?>&nbsp;&nbsp;[ <?php echo $branchDesc; ?> ]</div>
                <div class="eventDate"><strong><?php _e( 'Date', 'book-a-room-events' ); ?>: </strong><?php echo date( 'l, F jS, Y', strtotime( $val[ 'ti_startTime' ] ) ); ?></div>
                <div class="eventTime"><strong><?php _e( 'Start time', 'book-a-room-events' ); ?>: </strong><?php echo $times; ?></div>
                <div class="eventAge"><strong><?php _e( 'Age group', 'book-a-room-events' ); ?>: </strong><?php echo $val[ 'ages' ]; ?></div>
                <div class="eventType"><strong><?php _e( 'Event type', 'book-a-room-events' ); ?>: </strong><?php echo $val[ 'cats' ]; ?></div>
                <div class="eventText"><strong><?php _e( 'Description', 'book-a-room-events' ); ?>: </strong><?php echo $val[ 'ev_desc' ]; ?></div>
                <div class="eventText"><a href="<?php echo $atts['basepage']; ?>?action=viewEvent&eventID=<?php echo $val[ 'ti_id' ]; ?>" target="_blank"><?php _e( 'View the event\'s details.', 'book-a-room-events' ); ?></a>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>
</div>