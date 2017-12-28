<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;?>

<table class="form-table">

	<?php do_action( 'geobl/metaboxes/before_options', $opts );?>

	<tr valign="top">
		<th><label for="geobl_trigger"><?php _e( 'Message', 'geobl' ); ?></label></th>
		<td>
			<textarea class="widefat" name="geobl[block_message]"><?php echo esc_attr($opts['block_message']); ?></textarea>
			<p class="help"><?php _e( 'Display a message to users being blocked', 'geobl' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th><label for="geobl_trigger"><?php _e( 'Exclude Search Engines ?', 'geobl' ); ?></label></th>
		<td>
            <select id="exclude_se" name="geobl[exclude_se]" class="widefat">
                <option value="0" <?php selected($opts['exclude_se'], '0'); ?> > <?php _e( 'No', 'geobl' ); ?></option>
                <option value="1" <?php selected($opts['exclude_se'], '1'); ?> > <?php _e( 'Yes', 'geobl' ); ?></option>
			</select>
            <p class="help"><?php _e( 'Exclude bots and crawlers from being blocked', 'geobl' ); ?></p>
		</td>
	</tr>

    <tr valign="top">
		<th><label for="geobl_trigger"><?php _e( 'IP Whitelist', 'geobl' ); ?></label></th>
		<td>
			<textarea class="widefat" name="geobl[whitelist]"><?php echo esc_attr($opts['whitelist']); ?></textarea>
            <p class="help"><?php _e( 'Exclude the following IPs from being blocked. Enter one per line', 'geobl' ); ?></p>
		</td>
	</tr>
	<?php do_action( 'geobl/metaboxes/after_options', $opts );?>
</table>
<?php wp_nonce_field( 'geobl_options', 'geobl_options_nonce' ); ?>
