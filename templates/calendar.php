<div id="bookaroom_main_container">
    <a href="#top"></a>
    <div id="monthWrapper"><?php
        if( !empty( $age ) or !empty( $category ) ) {
        ?><div class="filterTitle"><?php _e( 'You are currently filtering results. <a href="./">Click here to view the whole calendar</a>', 'book-a-room-events' ); ?></div><?php
        # categories
            if( !empty( $category ) ) {
                $catDisp = explode( ',', $category );
                $catDisp = implode( ', ', $catDisp );
        ?><div class="catFilter"><?php _e( 'Categories', 'book-a-room-events' ); ?>: <?php echo ucwords( $catDisp ); ?></div><?php
            }
            if( !empty( $ages ) ) {
                $ageDisp = explode( ',', $age );
                $ageDisp = implode( ', ', $ageDisp );
        ?><div class="ageFilter"><?php _e( 'Ages', 'book-a-room-events' ); ?>: <?php echo ucwords( $ageDisp ); ?></div><?php
            }
        }

        if( empty( $timestampRaw ) or FALSE == ((string) (int) $timestampRaw === $timestampRaw) && ($timestampRaw <= PHP_INT_MAX) && ($timestampRaw >= ~PHP_INT_MAX) ) {
            $timestampRaw = time();
        }

        $timestampArr = getdate( $timestampRaw );
        $timestamp = mktime( 0, 0, 0, $timestampArr['mon'], $timestampArr['mday'], $timestampArr['year'] );

        $curYearDisp = date( 'Y', $timestamp );
        $curMonthDisp = date( 'F', $timestamp );

        $prevYearRaw = mktime( 0, 0, 0, 1, 1, $timestampArr['year'] - 1 );
        $prevYearDisp = date( 'Y', $prevYearRaw );

        $nextYearRaw = mktime( 0, 0, 0, 1, 1, $timestampArr['year'] + 1 );
        $nextYearDisp = date( 'Y', $nextYearRaw );

        ?>
        <h2><a href="<?php echo CHUH_BaR_permalinkFix(); ?>action=search"><?php _e( 'Perform an advanced search.', 'book-a-room-events' ); ?></a></h2>
      <div class="monthTitle"><?php _e( 'Choose a month', 'book-a-room-events' ); echo ' [' . $curMonthDisp . ' ' . $curYearDisp . ']'; ?></div>
      <div class="monthDisp"><a href="<?php echo CHUH_BaR_permalinkFix() . 'timestamp='.$prevYearRaw.'&amp;category='.$category.'&amp;age='.$age; ?>"><< <?php echo $prevYearDisp; ?></a></div>
      <?php

      for( $m=1; $m<=12; $m++) {
          $realCurMonth = $timestampArr['mon'];
          $curMonthTimestamp = mktime( 0, 0, 0, $m, 1, $timestampArr['year'] );
          $curMonthDisp = date( 'M', $curMonthTimestamp );

          if( $realCurMonth == $m ) {
        ?><div class="monthDisp selected"><?php echo $curMonthDisp; ?></div><?php
          } else {
        ?><div class="monthDisp"><a href="<?php echo CHUH_BaR_permalinkFix() . 'timestamp='.$curMonthTimestamp.'&amp;category='.$category.'&amp;age='.$age; ?>"><?php echo $curMonthDisp; ?></a></div><?php	
          }
      }
        ?>
      <div class="monthDisp"><a href="<?php echo CHUH_BaR_permalinkFix() . 'timestamp='.$nextYearRaw.'&amp;category='.$category.'&amp;age='.$age; ?>"><?php echo $nextYearDisp; ?> >></a></div>
    </div>
    <div id="calEventWrapper">
     <?php
        $daysInMonth = date( 't', $timestamp );
        # add offset
        $dayOffset = date( 'w', $timestamp );

        for( $mainDay=1; $mainDay<=$daysInMonth; $mainDay++ ){
            # make timestamp
            $dateTimestamp = mktime( 0, 0, 0, $timestampArr['mon'], $mainDay, $timestampArr['year'] );
        ?>
        <a name="date_<?php echo $mainDay; ?>" id="date_<?php echo $mainDay; ?>"></a>
      <div class="calSingleDateWrapper">
        <div class="calDateBorder">
          <div class="calDateWrapper">
            <div class="calDateDayName"><?php echo date( 'l', $dateTimestamp ); ?></div>
            <div class="calDateDayDate"><?php echo date( 'F jS', $dateTimestamp ); ?></div>
            <div class="miniCalContainer">
              <div class="calWeek">
                <div class="calDay calHeader"><?php _e( 'Su', 'book-a-room-events' ); ?></div>
                <div class="calDay calHeader"><?php _e( 'Mo', 'book-a-room-events' ); ?></div>
                <div class="calDay calHeader"><?php _e( 'Tu', 'book-a-room-events' ); ?></div>
                <div class="calDay calHeader"><?php _e( 'We', 'book-a-room-events' ); ?></div>
                <div class="calDay calHeader"><?php _e( 'Th', 'book-a-room-events' ); ?></div>
                <div class="calDay calHeader"><?php _e( 'Fr', 'book-a-room-events' ); ?></div>
                <div class="calDay calHeader"><?php _e( 'Sa', 'book-a-room-events' ); ?></div>
              </div>
              <?php
            #$timestampArr = getdate( $timestamp );
            $monthTimestamp = mktime( 0, 0, 0, $timestampArr['mon'], 1, $timestampArr['year'] );

            # days in month
            $daysInMonth = date( 't', $monthTimestamp );

            # add offset
            $dayOffset = date( 'w', $monthTimestamp );
            $weeks = ceil( ( $daysInMonth + $dayOffset ) / 7 );

            $calendarArr = array();
            $count = 1;

            $calendarArr = array();

            $weekFinal = array();
            $weekTemp = NULL;

            for( $w=1; $w<=$weeks; $w++ ) {
                ?><div class="calWeek"><?php

                switch( $w ) {
                    case 1:
                        for( $d=0; $d< $dayOffset; $d++) {
                ?><div class="calDay noDay">&nbsp;</div>
                    <?php
                        }

                        for( $d = $dayOffset; $d <=6; $d++) {
                            if( $timestampArr['mday'] == $count ) {
                                $dayClass = ' class="calDay selDay"';
                            } else {
                                $dayClass = ' class="calDay"';
                            }
                ?><div<?php echo $dayClass; ?>><a href="#date_<?php echo $count; ?>"><?php echo $count; ?></a></div>
                        <?php
                            $count++;
                        }

                        $endDay = 7 - $dayOffset;
                        $nextStart = $endDay+1;		
                        break;

                    default:

                        $startDay = $nextStart;
                        $endDay = $startDay + 6;
                        $nextStart = $endDay+1;

                        for( $d = $startDay; $d <= $startDay+6; $d++) {
                            if( $timestampArr['mday'] == $d ) {
                                $dayClass = '  class="calDay selDay"';
                            } else {
                                $dayClass = ' class="calDay"';
                            }
                ?><div<?php echo $dayClass; ?>><a href="#date_<?php echo $count; ?>"><?php echo $count; ?></a></div>
                        <?php
                            $count++;
                        }	
                        break;

                    case $weeks:

                        $weekLeft = 7;
                        for( $d = $count; $d <=$daysInMonth; $d++) {
                            if( $timestampArr['mday'] == $count ) {
                                $dayClass = ' class="calDay selDay"';
                            } else {
                                $dayClass = ' class="calDay"';
                            }
                ?><div<?php echo $dayClass; ?>><a href="#date_<?php echo $count; ?>"><?php echo $count; ?></a></div>
                        <?php
                            $count++;
                            $weekLeft--;
                        }

                        for( $d=1; $d <= $weekLeft; $d++) {
                ?><div class="calDay noDay">&nbsp;</div><?php
                        }

                        $startDay = $nextStart;
                        $endDay = $daysInMonth;
                        break;
                }
                ?></div>
                <?php
            }
            ?>
              </div>
            <div class="calDateTop"><a href="#top"><?php _e( 'Back to the top', 'book-a-room-events' ); ?></a></div>
          </div>
          <div class="calEventListWrapper"><?php
            if( empty( $monthEvents[$mainDay] ) ) {
              ?><div class="calEventListContents">
              <div class="calEventItemTime"><?php _e( 'There are no events today.', 'book-a-room-events' ); ?></div>
            </div>
            <?php
            } else {
                foreach( $monthEvents[$mainDay] as $eventNow ) {
                    $startTime = date( 'g:i a', strtotime( $eventNow['ti_startTime'] ) );
                    $endTime = date( 'g:i a', strtotime( $eventNow['ti_endTime'] ) );
                    if( $startTime == '12:00 am' ) {
                        $eventTimes = __( 'All Day', 'book-a-room-events' ); 
                    } else {
                        $eventTimes = $startTime.' - '.$endTime;
                    }
                ?>
            <div class="calEventListContents">
              <div class="calEventItemName"><a href="<?php echo CHUH_BaR_permalinkFix(); ?>action=viewEvent&amp;eventID=<?php echo $eventNow['ti_id']; ?>" target="_blank"><?php echo $eventNow['ev_title']; ?></a></div>
              <div class="calEventItemTime"><?php echo $eventTimes; ?></div>
              <div class="calEventItemDesc"><?php
                    # description
                    echo $eventNow['ev_desc'];
                    # extra info
                    if( !empty( $eventNow['ti_extraInfo'] ) ) {
                            echo '<br />'.$eventNow['ti_extraInfo'];
                    }
                    ?></div>
              <div class="calEventItemReg"><?php
                    # registrations
                    if( $eventNow['ev_regType'] !== 'yes' ) {
                            _e( 'No registration required', 'book-a-room-events' );
                    } else {
                        # get reg info
                        $regInfo = self::getRegInfo( $eventNow['ti_id'], $mydb, $settings['bookaroom_events_prefix'] );
                        # since they can register, first see if it's not full, then show the form
                        if( count( $regInfo ) < $eventNow['ev_maxReg'] ) {
                            _e( 'Registration required, slots available.', 'book-a-room-events' );							
                            # else, if number is under total reg + waiting list, show waiting list
                        } elseif( count( $regInfo ) < ( $eventNow['ev_maxReg'] + $eventNow['ev_waitingList'] ) ) {
                            _e( 'Registration required. Program is full but waiting list is open.', 'book-a-room-events' );
                            # else, show waiting list full
                        } else {
                            _e( 'Registration required. Program is full.', 'book-a-room-events' );	
                        }					
                    }				
                    ?></div>
              <div class="calEventItemLink"><a href="<?php echo CHUH_BaR_permalinkFix(); ?>action=viewEvent&amp;eventID=<?php echo $eventNow['ti_id']; ?>" target="_blank"><?php _e( 'View the event\'s details.', 'book-a-room-events' ); ?></a></div>
            </div>
            <?php
                }
            }
            ?>
        </div>
           </div>
      </div>
      <?php
        }
        ?>
    </div>
</div>