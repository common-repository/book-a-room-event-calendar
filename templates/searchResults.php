<div id="bookaroom_main_container">
    <form name="form1" method="get">
      <br>
      <table class="tableSearch allWidth">
        <tr>
          <td colspan="4"><?php _e( 'Search/Filter Settings', 'book-a-room-events' ); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right; cursor:pointer; text-decoration:underline" id="hideToggle"><?php _e( 'Hide/Show', 'book-a-room-events' ); ?></div></td>
        </tr>
        <tr class="searchArea">
          <td><?php _e( 'Search Terms', 'book-a-room-events' ); ?></td>
          <td><input name="searchTerms" type="text" id="searchTerms" value="<?php echo $externals['searchTerms']; ?>"></td>
          <td>Branch</td>
          <td><select name="branchID" id="branchID">
           <?php        
            # branch dropdown
              $selected = ( $externals['branchID'] == NULL ) ? ' selected="selected"' : NULL;
              ?><option value=""<?php echo $selected; ?>><?php _e( 'Do not filter', 'book-a-room-events' ); ?></option><?php		
            foreach( $branchList as $key => $val ) {
                $selected = ( $externals['branchID'] == $val['branchID'] ) ? ' selected="selected"' : NULL;
              ?><option value="<?php echo $val['branchID']; ?>"<?php echo $selected; ?>><?php echo $val['branchDesc']; ?></option><?php		
              }
          ?>
          </select></td>
        </tr>
        <tr class="searchArea">
          <td><?php _e( 'Start Date', 'book-a-room-events' ); ?></td>
          <td><input name="startDate" type="text" id="startDate" value="<?php echo $externals['startDate']; ?>"></td>
          <td><?php _e( 'End Date', 'book-a-room-events' ); ?></td>
          <td><input name="endDate" type="text" id="endDate" value="<?php echo $externals['endDate']; ?>"></td>
        </tr>
        <tr class="searchArea">
          <td><?php _e( 'Age Group', 'book-a-room-events' ); ?><br />
            <span style="font-size:.8em"><?php _e( 'Hold down control to select multiple entries.', 'book-a-room-events' ); ?></span></td>
          <td><select name="ageGroup[]" size="4" multiple="multiple" id="ageGroup">
            <?php
              # display age group
              $ageList = self::getAgeList( $newdb, $bookaroom_events_prefix );
              foreach( $ageList['active'] as $key => $val ) {
                $selected = ( !empty( $externals['ageGroup'] ) and in_array( $val['age_id'], $externals['ageGroup'] ) ) ?  ' selected="selected"' : NULL;
              ?><option value="<?php echo $val['age_id']; ?>"<?php echo $selected; ?>><?php echo $val['age_desc']; ?></option><?php
              }
              ?>
          </select>
          <input type="button" name="resetAge" id="resetAge" value="Clear" /></td>
          <td><?php _e( 'Categories', 'book-a-room-events' ); ?><br />
            <span style="font-size:.8em"><?php _e( 'Hold down control to select multiple entries.', 'book-a-room-events' ); ?></span></td>
          <td><select name="categoryGroup[]" size="4" multiple="multiple" id="categoryGroup">
          <?php
              # display category group
              $categoryList = self::getCatList( $newdb, $bookaroom_events_prefix );
              foreach( $categoryList['active'] as $key => $val ) {
                $selected = ( !empty( $externals['categoryGroup'] ) and in_array( $val['categories_id'], $externals['categoryGroup'] ) ) ?  ' selected="selected"' : NULL;

                $temp = str_replace( '#categoryGroup_desc#', $val['categories_desc'], $temp );
                $temp = str_replace( '#categoryGroup_val#', $val['categories_id'], $temp );
                $temp = str_replace( '#categoryGroup_selected#', $selected, $temp );
              ?><option value="<?php echo $val['categories_id']; ?>"<?php echo $selected; ?>><?php echo $val['categories_desc']; ?></option><?php
              }
              ?>        
          </select>
            <input type="hidden" name="page_id" id="page_id" value="<?php echo CHUH_BaR_permalinkFix( true ); ?>" />
             <input type="button" name="resetCats" id="resetCats" value="Clear" /></td>
        </tr>
        <tr class="searchArea">
          <td colspan="4" align="center"><input name="action" type="hidden" id="action" value="searchReturn">        <input type="submit" name="button" id="button" value="Submit"></td>
        </tr>
      </table>
    </form>
    <?php
    # search results
    if( $searched !== false ) {
        if( empty( $errorMSG ) ) {
    ?><p style="color: #F00;"><?php echo $errorMSG; ?></p><?php

        }
        ##################################################
        # Sort order
        ##################################################		
        #php current url
        $args = explode( '&', $_SERVER['QUERY_STRING'] );
        $nameURL = array_merge( $args, array( 'sortOrder=name' ) );
        $dateURL = array_merge( $args, array( 'sortOrder=date' ) );
        $scoreURL = array_merge( $args, array( 'sortOrder=score' ) );

        # find current
        $nameDisp = '<a href="?' . implode( '&', $nameURL ) .'">'.__( 'Name','book-a-room-events' ).'</a>';
        $dateDisp = '<a href="?' . implode( '&', $dateURL ) .'">'.__( 'Date','book-a-room-events' ).'</a>';
        $scoreDisp = '<a href="?' . implode( '&', $scoreURL ) .'">'.__( 'Relevance','book-a-room-events' ).'</a>';

        switch( $externals['sortOrder'] ):
            case 'name':
                $nameDisp = 'Name';
                break;
            case 'date':
                $dateDisp = 'Date';				
                break;
            case 'score':
            default:
                $scoreDisp = 'Relevance';
                break;		
        endswitch;

        ?>
    <h3><?php printf( __( 'Search Results - Order by %s | %s | %s', 'book-a-room-events' ), $nameDisp, $dateDisp, $scoreDisp ); ?></h3>

    <table width="100%" class="tableSearch allWidth">
      <tr>
        <td><?php _e( 'Date/Time','book-a-room-events' ); ?></td>
        <td><?php _e( 'Title/Desc','book-a-room-events' ); ?></td>
        <td><?php _e( 'Branch/Room','book-a-room-events' ); ?></td>
      </tr>
      <?php
        if( count( $results ) == 0 ) {
            # nothing to return
        ?>
         <tr>
        <td colspan="3"><?php _e( 'Nothing matches your search criteria.', 'book-a-room-events' ); ?></td>
      </tr>
      <?php
        } else {
            # return items
            foreach( $results as $key => $val ) {
                $branchID = self::branch_and_room_id( $val['ti_roomID'], $branchList, $roomContList );

                if( empty( $branchID ) && !empty( $val['ti_noLocation_branch'] ) ) {
                    $room		= __( 'No location required', 'book-a-room-events' );
                    $branch		= $branchList[$val['ti_noLocation_branch']]['branchDesc'];
                } else {
                    $room		= $roomContList['id'][$val['ti_roomID']]['desc'];
                    $branch		= $branchList[$roomContList['id'][$val['ti_roomID']]['branchID']]['branchDesc'];
                }

                # date functions
                $startTime = strtotime( $val['ti_startTime'] );
                $endTime = strtotime( $val['ti_endTime'] );
                $date = date( 'l, F jS, Y', $startTime );
                $startTime = date( 'g:i a', $startTime );
                $endTime = date( 'g:i a', $endTime );
                if( $startTime == '12:00 am' and $endTime == '11:59 pm' ) {
                    $times = 'All Day';
                } else {
                    $times = $startTime . ' to ' . $endTime;
                }
        ?>
       <tr>
        <td valign="top" nowrap="nowrap"><?php echo $date; ?><br />
          <em><?php echo $times; ?></em></td>
        <td valign="top"><p><strong><a href="<?php echo CHUH_BaR_permalinkFix(); ?>action=viewEvent&eventID=<?php echo $val['ti_id']; ?>"><?PHP echo $val['ev_title']; ?></a></strong><br />
        <?php echo $val['ev_desc']; ?><br />
        <?php
                if( !empty( $val['ti_extraInfo'] ) ){
                    echo $val['ti_extraInfo'];
                }
            ?>
        </p></td>
        <td valign="top" nowrap="nowrap"><p><strong><?php echo $branch; ?></strong><br />
        <?php echo $room; ?></p></td>
        </tr>
        <?php
            }
        }
        ?>	
    </table>
        <?php
    }
    ?>
</div>