<style type="text/css">
	.even_row_no {
		background-color: #EEE;
	}
	
	.dataTable tr td {
		border: thin solid #888;
		margin: 0px;
		padding: 4px;
	}
</style>
<div id="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php _e( 'Events Settings', 'book-a-room-events' ); ?>
	</h2>
</div>
<?php
if ( !empty( $errorMSG ) ) {
	?>
	<h3 style="color: red"><strong><?php echo $errorMSG; ?></strong></h3>
	<?php
	}
	?>
	<form id="form1" name="form1" method="post" action="?page=bookaroom-Events">
		<table class="tableMain">
			<tbody>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'Description', 'book-a-room-events' ); ?>
					</td>
					<td>
						<?php _e( 'Setting', 'book-a-room-events' ); ?>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'Security key for password encryption', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_db_key" type="password" id="bookaroom_events_db_key" value="<?php echo $settings['bookaroom_events_db_key']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'DB Hostname', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_db_host" type="text" id="bookaroom_events_db_host" value="<?php echo $settings['bookaroom_events_db_host']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'DB Username', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_db_username" type="text" id="bookaroom_events_db_username" value="<?php echo $settings['bookaroom_events_db_username']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'DB Password', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_db_password" type="password" id="bookaroom_events_db_password" value="<?php echo $settings['bookaroom_events_db_password']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'DB Prefix', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_prefix" type="text" id="bookaroom_events_prefix" value="<?php echo $settings['bookaroom_events_prefix']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'Database name', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_db_database" type="text" id="bookaroom_events_db_database" value="<?php echo $settings['bookaroom_events_db_database']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td nowrap="nowrap">
						<?php _e( 'Registration page URL', 'book-a-room-events' ); ?>
					</td>
					<td><input name="bookaroom_events_regpage_URL" type="text" id="bookaroom_events_regpage_URL" value="<?php echo $settings['bookaroom_events_regpage_URL']; ?>" size="30"/>
					</td>
				</tr>
				<tr>
					<td colspan="2" nowrap="nowrap"><input name="action" type="hidden" value="updateSettings"/><input type="submit" name="button" id="button" value="<?php _e( 'Submit', 'book-a-room-events' ); ?>"/>
					</td>
				</tr>
			</tbody>
		</table>
		<p>
			<?php _e( '<strong>Security key for password encryption</strong>: This is a random string of characters used to encrypt your password when logging in. Just make anything up.', 'book-a-room-events' ); ?>
		</p>
		<p>
			<?php _e( '<strong>DB Hostname/Username/Password</strong>: These are the log in details for the server that the main Book a Room plugin is located.', 'book-a-room-events' ); ?>
		</p>
		<p>
			<?php _e( '<strong>DB Prefix</strong>: The prefix you used when setting up Wordpress on the site where the main Book a Room plugin is located.', 'book-a-room-events' ); ?>
		</p>
		<p>
			<?php _e( '<strong>Database name</strong>: The name of the database where Book a Room was installed.', 'book-a-room-events' ); ?>
		</p>
		<p>
			<?php _e( '<strong>NEW! IMPORTANT! Registraion page URL: </strong>Please create a new page&nbsp;and add the shortcode [showReg] to it. Then, copy the URL and add it here. You can add the entire URL or just the text after the first slash (/) not including the http://.', 'book-a-room-events' ); ?>
		</p>
		<blockquote>
			<p>
				<?php _e( '<strong>Example:</strong><br> If you make a page and the URL is <em>http://heightslibrary.org/event-registraion/, </em>you can user either&nbsp;the entire URL or:', 'book-a-room-events' ); ?>
			</p>
			<p><em>/event-registraion/</em>
			</p>
		</blockquote>
	</form>