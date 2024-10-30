<div id="bookaroom_main_container">
    <table class="tableMain">
        <tr>
            <td colspan="2">
                <?php _e( 'Event Information', 'book-a-room-events' ); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Title', 'book-a-room-events' ); ?>:</td>
            <td>
                <?php echo $eventInfo['ev_title']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php _e( 'Date', 'book-a-room-events' ); ?>:</td>
            <td>
                <span class="eventVal">
                    <?php echo date( 'l, M. jS', strtotime( $eventInfo['ti_startTime'] ) ); ?>
                </span>
            </td>
        </tr>
        <?php
        # time - check for all day
        $startTime = date( 'g:i a', strtotime( $eventInfo[ 'ti_startTime' ] ) );
        $endTime = date( 'g:i a', strtotime( $eventInfo[ 'ti_endTime' ] ) );
        if ( $startTime == '12:00 am' ) {
            $time = __( 'All Day', 'book-a-room-events' );
        } else {
            $time = $startTime . ' - ' . $endTime;
        }
        ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Time', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $time; ?>
                </span>
            </td>
        </tr>
        <?php
        # branch and room
        if ( !empty( $eventInfo[ 'ti_noLocation_branch' ] ) ) {
            $branch = $branchList[ $eventInfo[ 'ti_noLocation_branch' ] ][ 'branchDesc' ];
            $room = __( 'No location specified', 'book-a-room-events' );
        } else {
            $branch = $branchList[ $roomContList[ 'id' ][ $eventInfo[ 'ti_roomID' ] ][ 'branchID' ] ][ 'branchDesc' ];
            $room = $roomContList[ 'id' ][ $eventInfo[ 'ti_roomID' ] ][ 'desc' ];
        }

        ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Branch', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $branch; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Location', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $room; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Description', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php 
            echo $eventInfo['ev_desc'];
            if( !empty( $eventInfo['ti_extraInfo'] ) ) {
                echo '<br />'.$eventInfo['ti_extraInfo'];
            }
            ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Categories', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $eventInfo['cats']; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="eventHeader botBop">
                    <?php _e( 'Age groups', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal botBop">
                    <?php echo $eventInfo['ages']; ?>
                </span>
            </td>
        </tr>
    </table>
    <br/>
    <?php
    # presenter box
    if ( !empty( $eventInfo[ 'ev_presenter' ] )or!empty( $eventInfo[ 'ev_publicName' ] )or!empty( $eventInfo[ 'ev_publicEmail' ] )or!empty( $eventInfo[ 'ev_publicPhone' ] )or!empty( $eventInfo[ 'ev_website' ] ) ) {
        ?>
    <table class="tableMain">
        <tr>
            <td colspan="2">
                <?php _e( 'Presenter Information', 'book-a-room-events' ); ?>
            </td>
        </tr>
        <?php
        # presenter
        if ( !empty( $eventInfo[ 'ev_presenter' ] ) ) {
            ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Presenter', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $eventInfo['ev_presenter']; ?>
                </span>
            </td>
        </tr>
        <?php
        }
        if ( !empty( $eventInfo[ 'ev_website' ] ) ) {
            # website
            if ( empty( $eventInfo[ 'ev_webText' ] ) ) {
                $eventInfo[ 'ev_webText' ] = $eventInfo[ 'ev_website' ];
            }
            ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Website', 'book-a-room-events' ); ?>:</span>
            </td>
            <td><span class="eventVal"><a href="<?php echo $eventInfo['ev_website']; ?>" target="_blank"><?php echo $eventInfo['ev_webText']; ?></a></span>
            </td>
        </tr>
        <?php
        }
        # public Name
        if ( !empty( $eventInfo[ 'ev_publicName' ] ) ) {
            ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Contact Name', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $eventInfo['ev_publicName']; ?>
                </span>
            </td>
        </tr>
        <?php
        }
        if ( !empty( $eventInfo[ 'ev_publicEmail' ] ) ) {
            # public Email
            ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Contact Email', 'book-a-room-events' ); ?>:</span>
            </td>
            <td><span class="eventVal"><a href="mailto:<?php echo $eventInfo['ev_publicEmail']; ?>"><?php echo $eventInfo['ev_publicEmail']; ?></a></span>
            </td>
        </tr>
        <?php
        }
        if ( !empty( $eventInfo[ 'ev_publicPhone' ] ) ) {
            # phone
            ?>
        <tr>
            <td>
                <span class="eventHeader">
                    <?php _e( 'Contact Phone', 'book-a-room-events' ); ?>:</span>
            </td>
            <td>
                <span class="eventVal">
                    <?php echo $eventInfo['ev_publicPhone']; ?>
                </span>
            </td>
        </tr>
        <?php
        }
        ?>
    </table>
    <br/>
    <?php
    }
    $offset = get_option( 'gmt_offset' );

    $linkGoogle		= self::makeGoogleLink( $contents, strtotime( $eventInfo[ 'ti_startTime' ] . $offset ), strtotime( $eventInfo[ 'ti_endTime' ] . $offset ), $eventInfo[ 'ev_title' ], $room, $branch );

    $linkOutlook	= self::makeOutlookLink( $contents, strtotime( $eventInfo[ 'ti_startTime' ] ), strtotime( $eventInfo[ 'ti_endTime' ] ), $eventInfo[ 'ev_title' ], $room, $branch );

    $linkiCal		= self::makeiCalLink( $contents, strtotime( $eventInfo[ 'ti_startTime' ] ), strtotime( $eventInfo[ 'ti_endTime' ] ), $eventInfo[ 'ev_title' ], $room, $branch );
    ?>
    <table class="tableMain">
        <tr>
            <td>
                <?php _e( 'Calendar Links', 'book-a-room-events' ); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <a href="<?php echo $linkGoogle; ?>" target="new">
                    <?php _e( 'Add to Google Calendar', 'book-a-room-events' ); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <a href="<?php echo $linkOutlook; ?>" target="new">
                    <?php _e( 'Add to Outlook Calendar', 'book-a-room-events' ); ?>
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <a href="<?php echo $linkiCal; ?>" target="new">
                    <?php _e( 'Add to iCal', 'book-a-room-events' ); ?>
                </a>
            </td>
        </tr>
    </table>
    <br>
    <?php
    # errors
    if ( !empty( $errorMSG ) ) {
        ?>
    <div style="color: red">
        <p>
            <strong>
                <?php _e( 'Error!', 'book-a-room-events' ); ?>
            </strong>
        </p>
        <p>
            <strong>
                <?php echo $errorMSG; ?>
            </strong>
        </p>
    </div>
    <?php
    }
    ?>
    <form id="form1" name="form1" method="post" action="">
        <?php
        # EVENT OVER
        #############################################
        if ( current_time( 'timestamp' ) >= strtotime( $eventInfo[ 'ti_startTime' ] ) ) {
            ?>
        <table class="tableMain">
            <tr>
                <td colspan="2">
                    <?php _e( 'Registration', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>
                        <?php _e( 'This event is already over. You cannot register for this event.', 'book-a-room-events' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
        # TOO EARLY
        #############################################
        } elseif ( $today < strtotime( $eventInfo[ 'ev_regStartDate' ] ) ) {
                ?>
        <table class="tableMain">
            <tr>
                <td colspan="2">
                    <?php _e( 'Registration', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>
                        <?php _e( 'This event is not accepting registrations yet.', 'book-a-room-events' ); ?>
                    </p>
                    <p>
                        <?php printf( __( 'Registration opens on %s.', 'book-a-room-events' ), date( 'l, F jS, Y', strtotime( $eventInfo['ev_regStartDate'] ) ) ); ?>
                    </p>
                    <p>
                        <?php _e( 'Please stop back later.', 'book-a-room-events' ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
        } elseif ( $isSuccess == true ) {
                # SUCCESS
                #############################################
                ?>
        <table class="tableMain">
            <tr>
                <td colspan="2">
                    <?php _e( 'Registration was successful', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p>
                        <?php _e( 'Your registration was entered successfully.', 'book-a-room-events' ); ?>
                    </p>
                    <p>
                        <?php _e( 'Please print this for your records.', 'book-a-room-events' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Full Name', 'book-a-room-events' ); ?>:</span>
                </td>
                <td>
                    <span class="eventVal">
                        <?php echo $externals['fullName']; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Phone number', 'book-a-room-events' ); ?>:</span>
                </td>
                <td>
                    <span class="eventVal">
                        <?php echo $externals['phone']; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Email address', 'book-a-room-events' ); ?>:</span>
                </td>
                <td>
                    <span class="eventVal">
                        <?php echo $externals['email']; ?>
                    </span>
                </td>
            </tr>
            <!--
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php #_e( 'Notes', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal"><?php #echo $externals['notes']; ?></span>
                </td>
            </tr>-->
        </table>
        <?php
        # NO REGISTRATION NEEDED
        #############################################
        } elseif ( $eventInfo[ 'ev_regType' ] !== 'yes' ) {
                ?>
        <table class="tableMain">
            <tr>
                <td>
                    <?php _e( 'Registration', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e( 'This event doesn\'t require registration.', 'book-a-room-events' ); ?>
                </td>
            </tr>
        </table>
        <?php
        } else {
            $settings[ 'bookaroom_events_db_key' ] = get_option( 'bookaroom_events_db_key' );
            $settings[ 'bookaroom_events_db_database' ] = get_option( 'bookaroom_events_db_database' );
            $settings[ 'bookaroom_events_db_username' ] = get_option( 'bookaroom_events_db_username' );
            $settings[ 'bookaroom_events_db_password' ] = get_option( 'bookaroom_events_db_password' );
            $settings[ 'bookaroom_events_prefix' ] = get_option( 'bookaroom_events_prefix' );
            $settings[ 'bookaroom_events_db_host' ] = get_option( 'bookaroom_events_db_host' );

            #$settings['bookaroom_events_db_password']	= bookaroom_events_decrypt( $settings['bookaroom_events_db_password'], $settings['bookaroom_events_db_key'] );

            $mydb = new wpdb( $settings[ 'bookaroom_events_db_username' ], $settings[ 'bookaroom_events_db_password' ], $settings[ 'bookaroom_events_db_database' ], $settings[ 'bookaroom_events_db_host' ] );
            # get reg info
            $regInfo = self::getRegInfo( $eventInfo[ 'ti_id' ], $mydb, $settings[ 'bookaroom_events_prefix' ] );

            # SHOW REGISTRATION FORM
            #############################################
            if ( count( $regInfo ) < $eventInfo[ 'ev_maxReg' ] ) {
                ?>
        <table class="tableMain">
            <tr>
                <td colspan="2">
                    <?php _e( 'Registration', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h2>
                        <?php _e( 'Please only add ONE NAME.', 'book-a-room-events' ); ?>
                    </h2>
                    <p>
                        <?php _e( 'If you add more than one name, only the first name will be registered. Please have everyone attending fill out a registration.', 'book-a-room-events' ); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Full Name', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
            <input name="fullName" type="text" id="fullName" value="<?php echo $externals['fullName']; ?>" />
          </span>



                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Phone number', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
            <input name="phone" type="text" id="phone" value="<?php echo $externals['phone']; ?>" size="15" maxlength="15" />
          </span>



                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Email address', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
            <input name="email" type="text" id="email" value="<?php echo $externals['email']; ?>" />
          </span>



                </td>
            </tr>
            <!--
            <tr>
                <td>
                    <span class="eventHeader">
                        < ? php _e( 'Notes', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
            <textarea name="notes" id="notes" cols="45" rows="5">< ? php #echo $externals['notes']; ?></textarea>
          </span>
                </td>
            </tr>
            -->
            <tr>
                <td>&nbsp;</td>
                <td><span class="eventVal">
            <input name="formAction" type="hidden" id="formAction" value="checkReg" />
            <input name="action" type="hidden" id="action" value="viewEvent" />
            <input name="eventID" type="hidden" id="eventID" value="<?php echo $eventInfo['ti_id']; ?>" />
            <input name="bookaroomRegFormSub" type="hidden" id="bookaroomRegFormSub" value="<?php echo $externals['bookaroomRegFormSub']; ?>" />
            <input type="submit" name="button" id="button" value="<?php _e( 'Submit', 'book-a-room-events' ); ?>" />
          </span>



                </td>
            </tr>
        </table>
        <?php
        } elseif ( count( $regInfo ) < ( $eventInfo[ 'ev_maxReg' ] + $eventInfo[ 'ev_waitingList' ] ) ) {
                ?>
        <table class="tableMain">
            <tr>
                <td colspan="2">
                    <?php _e( 'Waiting List', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php _e( 'This event is full. Please enter your information below if you would like to be added to the waiting list. You will be notified if anyone cancels and a spot becomes available.', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h2>
                        <?php _e( 'Please only add ONE NAME.', 'book-a-room-events' ); ?>
                    </h2>
                    <p>
                        <?php _e( 'If you add more than one name, only the first name will be registered. Please have everyone attending fill out a registration.', 'book-a-room-events' ); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Full Name', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
          <input name="fullName" type="text" id="fullName" value="<?php echo $externals['fullName']; ?>" />
        </span>



                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Phone number', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
          <input name="phone" type="text" id="phone" value="<?php echo $externals['phone']; ?>" size="15" maxlength="15" />
        </span>



                </td>
            </tr>
            <tr>
                <td>
                    <span class="eventHeader">
                        <?php _e( 'Email address', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
          <input name="email" type="text" id="email" value="<?php echo $externals['email']; ?>" />
        </span>



                </td>
            </tr>
            <!--
            <tr>
                <td>
                    <span class="eventHeader">
                        < ? php #_e( 'Notes', 'book-a-room-events' ); ?>:</span>
                </td>
                <td><span class="eventVal">
          <textarea name="notes" id="notes" cols="45" rows="5">< ? php #echo $externals['notes']; ?></textarea>
        </span>
                </td>
            </tr>
            -->
            <tr>
                <td>&nbsp;</td>
                <td><span class="eventVal">
          <input name="action" type="hidden" id="action" value="viewEvent" />
          <input name="formAction" type="hidden" id="formAction" value="checkReg" />
          <input name="eventID" type="hidden" id="eventID" value="<?php echo $eventInfo['ti_id']; ?>" />
          <input name="bookaroomRegFormSub" type="hidden" id="bookaroomRegFormSub" value="<?php echo $externals['bookaroomRegFormSub']; ?>" />
          <input type="submit" name="button2" id="button2" value="<?php _e( 'Submit', 'book-a-room-events' ); ?>" />
                    </span>
                </td>
            </tr>
        </table>
        <?php
                $doReg = true;
            } else {			
            ?>
        <table class="tableMain">
            <tr>
                <td colspan="2">
                    <?php _e( 'Registration', 'book-a-room-events' ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php _e( 'This event is currently full.', 'book-a-room-events' ); ?>
                </td>
            </tr>
        </table>
            <?php
            }
        }
        ?>
    </form>
</div>