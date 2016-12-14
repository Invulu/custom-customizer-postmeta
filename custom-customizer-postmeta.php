<?php
/**
 * Plugin Name: Custom Customizer Postmeta
 * Author: Jesse Lee, Organic Themes (based on Weston Ruter's example code)
 * Description: Plugin to add custom postmeta fields to posts and the customizer via the Customize Posts plugin by Weston Ruter.
 *
 * @package CustomCustomizerPostmeta
 */
namespace CustomCustomizerPostmeta ;

// Check for required core version
if ( ! has_required_core_version() ) {
	add_action( 'admin_notices', array( $this, 'show_core_version_dependency_failure' ) );
	return;
}

// Check for customizer
if ( ! isset( $wp_customize->posts ) ) {
	return;
}

// Load text domain
load_plugin_textdomain( 'customize-posts' );


// Require Files
require_once dirname( __FILE__ ) . '/class-custom-customizer-postmeta.php';

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
