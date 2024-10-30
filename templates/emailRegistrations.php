<form name="form1" method="post" action="">
	<table class="tableMain">
		<tr>
			<td colspan="2">
				<?php _e( 'Registration Information', 'book-a-room-events' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php _e( 'If you registered for an event using your email address, please enter it here and we will email a list of events for which you have registered.', 'book-a-room-events' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php _e( 'For privacy and to protect your data, this list will be emailed directly to the email address that you used when you registered.', 'book-a-room-events' ); ?>
			</td>
		</tr>
		<?php
		if( !empty( $errorMSG ) ) {
		?>
		<tr>
			<td colspan="2"><span class="error"><?php echo $errorMSG; ?></span>
			</td>
		</tr>
		<?php
		}		
		?>
		<tr>
			<td>
				<?php _e( 'Email address', 'book-a-room-events' ); ?>
			</td>
			<td><input name="email" type="text" id="email" value="<?php echo $externals[ 'email' ]; ?>" style="width: 300px;">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input name="action" type="hidden" id="action" value="checkEmailRegistraions">
				<input type="submit" name="button" id="button" value="Submit">
			</td>
		</tr>
	</table>
</form>