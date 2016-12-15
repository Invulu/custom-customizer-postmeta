<?php
/**
 * Plugin Name: Custom Customizer Postmeta
 * Plugin URI: https://github.com/Invulu/custom-customizer-postmeta
 * Author: Jesse Lee, Organic Themes - Based on Weston Ruter's example code
 * Description: Plugin to add custom postmeta fields to posts and the customizer via the Customize Posts plugin by Weston Ruter.
 *
 * @package CustomCustomizerPostmeta
 */

/**
 * Run the plugin
 *
 */
function accp_run_plugin(){

	// Check for required core version
	if ( ! has_required_core_version() ) {
		add_action( 'admin_notices', 'show_core_version_dependency_failure' );
		return;
	}

	// Check for customize posts plugin
	if ( ! is_plugin_active( 'customize-posts/customize-posts.php' ) ) {
		add_action( 'admin_notices', 'show_customize_posts_dependency_failure' );
		return;
	}

	// Load text domain
	load_plugin_textdomain( 'custom-customizer-postmeta' );

	// Require Files
	require_once dirname( __FILE__ ) . '/class-custom-customizer-postmeta.php';

	$meta_keys = get_post_meta_information();

	// Create meta
	foreach( $meta_keys as $meta_key ) {
		$accp = new ACCP_Custom_Customizer_Postmeta( $meta_key );
	}

}
add_action( 'admin_init', 'accp_run_plugin' );

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
 * Show error dependency failure notice for WordPress Core version.
 */
function show_core_version_dependency_failure() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Custom Customizer Postmeta requires WordPress 4.7.', 'custom-customizer-postmeta' ); ?></p>
	</div>
	<?php
}

/**
 * Show error dependency failure notice for Customize Posts plugin.
 */
function show_customize_posts_dependency_failure() {
	?>
	<div class="error">
		<p>
		<?php esc_html_e( 'Custom Customizer Postmeta requires the ', 'custom-customizer-postmeta');
		echo '<a href="https://github.com/xwp/wp-customize-posts">Customize Posts</a>';
		esc_html_e( ' plugin to be active.', 'custom-customizer-postmeta' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Get post meta information
 * (Temporary solution for now)
 *
 * @return array
 */
function get_post_meta_information() {

	$json_path = plugin_dir_path( __FILE__ ) . 'meta-info/meta-info.json';
	$meta_string = file_get_contents( $json_path );
	$meta_array = json_decode( $meta_string, TRUE );

	return $meta_array;

}
