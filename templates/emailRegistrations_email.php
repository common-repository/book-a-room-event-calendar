<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>
		<?php _e( 'Book a Room Registrations', 'book-a-room-events' ); ?>
	</title>
</head>

<body>
	<h2>
		<?php _e( 'Book a Room Event Registrations', 'book-a-room-events' ); ?>
	</h2>
	<?php
	if ( count( $results ) == 0 ) {
		?>
	<p>
		<?php _e( 'You have not registered for any events with this email address.', 'book-a-room-events' ); ?>
	</p>
	<?php
	} else {
		?>
	<p>
		<?php _e( 'You have registered for the following events:', 'book-a-room-events' ); ?>
	</p>
	<?php
		foreach ( $results as $key => $val ) {
		?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="2">
				<h3><?php echo $val[ 'ev_title' ]; ?></h3>
			</td>
		</tr>
		<tr>
			<td colspan="2"><?php echo $val[ 'ev_desc' ]; ?></td>
		</tr>
		<tr>
			<td><strong><?php _e( 'Date', 'book-a-room-events' ); ?></strong>
			</td>
			<td><?php echo date( 'l, F jS, Y', strtotime( $val[ 'ti_startTime' ] ) ); ?></td>
		</tr>
		<tr>
			<td><strong><?php _e( 'Time', 'book-a-room-events' ); ?></strong>
			</td>
			<td><?php
			echo date( 'g:i a', strtotime( $val[ 'ti_startTime' ] ) ) . ' - ' . date( 'g:i a', strtotime( $val[ 'ti_endTime' ] ) ); ?>
			</td>
		</tr>
		<tr>
			<td><strong><?php _e( 'Location', 'book-a-room-events' ); ?></strong>
			</td>
			<td><?php
				echo $val[ 'branchDesc' ] . ' [ ' . $val[ 'roomName' ] . ' ]';
				?></td>
		</tr>
		<?php
			if ( $val[ 'regCount2' ] > $val[ 'ev_maxReg' ] ) {
				if ( strtotime( $val[ 'ti_startTime' ] ) < time() ) {
					$are_were = __( 'You were on the waiting list for this event.', 'book-a-room-events' );
				} else {
					$are_were = __( 'You are on the waiting list for this event.', 'book-a-room-events' );
				}
		?>
		<tr>
			<td colspan="2"><strong style="color: red"><em><?php echo $are_were; ?></em></strong>
			</td>
		</tr>
		<?php
			}
		?>
	</table>
	<br/><br/>
	<?php
		}
	}
	?>
</body>

</html>