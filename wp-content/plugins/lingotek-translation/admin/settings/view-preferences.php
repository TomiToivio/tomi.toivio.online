<?php
global $wp_post_statuses;
$setting_details = array(
	'download_post_status' => array(
	'type' => 'dropdown',
	'label' => __( 'Download translation status', 'lingotek-translation' ),
	'description' => __( 'The post status for newly downloaded translations', 'lingotek-translation' ),
	'values' => array(
	  Lingotek_Group_Post::SAME_AS_SOURCE => __( 'Same as source post', 'lingotek-translation' )
	),
),
	'auto_upload_post_statuses' => array( // blacklist.
	'type' => 'checkboxes',
	'label' => __( 'Auto upload statuses', 'lingotek-translation' ),
	'description' => __( 'The post statuses checked above are enabled for automatic upload (when using automatic uploading translation profiles).', 'lingotek-translation' ),
	'values' => array(),
	),
	'trash_linked_content' => array(
	'type' => 'checkboxes',
	'label' => __( 'Trashed Content', 'wp-lingotek' ),
	'description' => __( 'When enabled, sending source or target content to the trash will also send all linked content to the trash. ', 'wp-lingotek' ),
	'values' => array(
	  'enabled' => __( 'Sync trashed content', 'wp-lingotek' ),
	),
),
	'import_enabled' => array(
	'type' => 'checkboxes',
	'label' => __( 'Import', 'wp-lingotek' ),
	'description' => __( 'When checked, an "Import" submenu will appear.', 'wp-lingotek' ),
	'values' => array(
	  'enabled' => __( 'Enable importing from Lingotek Content Cloud. (beta)', 'wp-lingotek' ),
	),
),
'auto_update_status' => array(
	'type' => 'dropdown',
	'label'       => __( 'Automatic Status Update Interval', 'wp-lingotek' ),
	'description' => __( 'Changes the rate at which content statuses update automatically.', 'wp-lingotek' ),
	'values' => array(
		'10' => '10 seconds', '30' => '30 seconds', '60' => '60 seconds', '-1' => 'Do not update automatically'
	),
),
);

function map_wp_post_status($status){
	return __( $status->label, 'lingotek-translation' );
}

function filter_statuses($statuses){
	$statuses_to_filter = array('auto-draft', 'trash', 'inactive', 'inherit');
	$ret = array();
	foreach ($statuses as $status => $value) {
		if (!in_array($status,$statuses_to_filter)) {
			$ret[$status] = $value;
		}
	}
	return $ret;
}

$post_statuses = filter_statuses(array_map("map_wp_post_status", $wp_post_statuses));
$setting_details["auto_upload_post_statuses"]["values"] = array_merge($post_statuses, $setting_details["auto_upload_post_statuses"]["values"]);
$setting_details["download_post_status"]["values"] = array_merge($post_statuses, $setting_details["download_post_status"]["values"]);

$page_key = $this->plugin_slug . '_settings&sm=preferences';

if ( ! empty( $_POST ) ) {
	check_admin_referer( $page_key, '_wpnonce_' . $page_key );
	$options = array();
	foreach ( $setting_details as $key => $setting ) {
		$key_input = filter_input( INPUT_POST, $key );
		if ( ! empty( $key_input ) ) {
			$options[ $key ] = $key_input;
		} else {
			$key_input = filter_input_array( INPUT_POST );
			if (!empty($key_input[$key])) {
				$options[ $key ] = $key_input[$key];
			}
			else {
				$options[ $key ] = null;
			}
		}
	}
	update_option( 'lingotek_prefs', $options );

	add_settings_error( 'lingotek_prefs', 'prefs', __( 'Your preferences were successfully updated.', 'lingotek-translation' ), 'updated' );
	settings_errors();
}
$selected_options = Lingotek_Model::get_prefs();

?>

<h3><?php esc_html_e( 'Preferences', 'lingotek-translation' ); ?></h3>
<p class="description"><?php esc_html_e( 'These are your preferred settings.', 'lingotek-translation' ); ?></p>


<form id="lingotek-settings" method="post" action="admin.php?page=<?php echo esc_html( $page_key ); ?>" class="validate">
<?php wp_nonce_field( $page_key, '_wpnonce_' . $page_key ); ?>

	<table class="form-table"><?php foreach ( $setting_details as $key => $setting ) { ?>

	  <tr>
		<th scope="row"><label for="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $setting['label'] ) ?></label></th>
		<td>
			<?php if ( 'dropdown' === $setting['type'] ) { ?>
		  <select name="<?php echo esc_html( $key ); ?>" id="<?php echo esc_html( $key ); ?>">
			<?php
			foreach ( $setting['values'] as $id => $title ) {
				echo "\n\t" . '<option value="' . esc_attr( $id ) . '" ' . selected( $selected_options[ $key ], $id ) . '>' . esc_html( $title ) . '</option>';
			}
			?>
			</select>
			<?php } elseif ( 'checkboxes' === $setting['type'] ) {
			echo '<ul class="pref-statuses">';
	foreach ( $setting['values'] as $id => $title ) {
		$cb_name = $key . '[' . esc_attr( $id ) . ']';
		$checked = checked( '1', (isset( $selected_options[ $key ][ $id ] ) && $selected_options[ $key ][ $id ]), false );
		echo '<li><input type="checkbox" id="' . esc_attr( $cb_name ) . '" name="' . esc_attr( $cb_name ) . '" value="1" ' . esc_attr( $checked ) . '><label for="' . esc_attr( $cb_name ) . '">' . esc_html( $title ) . '</label></li>';
	}
			echo '</ul>';
} ?>
		  <p class="description">
			<?php echo esc_html( $setting['description'] ); ?>
		  </p>
	  </tr><?php } ?>
	</table>

<?php submit_button( __( 'Save Changes', 'lingotek-translation' ), 'primary', 'submit', false ); ?>
</form>
