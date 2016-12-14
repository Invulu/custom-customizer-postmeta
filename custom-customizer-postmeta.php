<?php
/**
 * Plugin Name: Custom Customizer Postmeta
 * Author: Jesse Lee, Organic Themes (based on Weston Ruter's example code)
 * Description: Plugin to add custom postmeta fields to posts and the customizer via the Customize Posts plugin by Weston Ruter.
 *
 * @package CustomCustomizerPostmeta
 */

// Check for required core version
if ( ! has_required_core_version() ) {
	add_action( 'admin_notices', array( $this, 'show_core_version_dependency_failure' ) );
	return;
}

// Load text domain
load_plugin_textdomain( 'customize-posts' );

// Require Files
require_once dirname( __FILE__ ) . '/class-custom-customizer-postmeta.php';

//Initial meta values
$meta_keys = array(
	array(
		'meta_key' => 'gpp_test',
		'plural_meta_key' => 'gpp_tests',
		'meta_name' => 'GPP Test',
		'post_types' => array('post'),
		'field_type' => 'text'
	),
	array(
		'meta_key' => 'gpp_test2',
		'plural_meta_key' => 'gpp_test2s',
		'meta_name' => 'GPP Test 2',
		'post_types' => array('post'),
		'field_type' => 'text'
	),
	array(
		'meta_key' => 'gpp_test3',
		'plural_meta_key' => 'gpp_test3s',
		'meta_name' => 'GPP Test 3',
		'post_types' => array('team'),
		'field_type' => 'text'
	)
);
// Create meta
foreach( $meta_keys as $args ) {
	$accp = new ACCP_Custom_Customizer_Postmeta( $args );
}

/**
 * Determine whether the dependencies are satisfied for the plugin.
 *
 * @return bool
 */
function has_required_core_version() {
	$has_required_wp_version = version_compare( str_replace( array( '-src' ), '', $GLOBALS['wp_version'] ), '4.7', '>=' );
	return $has_required_wp_version;
}

/**
 * Show error dependency failure notice.
 */
function show_core_version_dependency_failure() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Customize Posts requires WordPress 4.7 and should have the Customize Setting Validation plugin active.', 'customize-posts' ); ?></p>
	</div>
	<?php
}
