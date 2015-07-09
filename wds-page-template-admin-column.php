<?php
/**
* Plugin Name: WDS Page Template Admin Column
* Plugin URI:  http://webdevstudios.com
* Description: Adds admin column to pages listings to display the pages Template name
* Version:     0.1.0
* Author:      WebDevStudios
* Author URI:  http://webdevstudios.com
* Donate link: http://webdevstudios.com
* License:     GPLv2
*/

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */

/**
 * Main initiation class
 *
 * @since  0.1.0
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class WDS_Page_Template_Admin_Column {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var WDS_Page_Template_Admin_Column
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return WDS_Page_Template_Admin_Column A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->hooks();
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function hooks() {
		add_action( 'admin_head', array( $this, 'hook_in_columns_and_css' ) );
	}

	/**
	 * If we're on the pages listing admin page, then add our column
	 *
	 * @since  0.1.0
	 *
	 * @return null
	 */
	public function hook_in_columns_and_css() {
		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'edit-page' == $screen->id && 'page' == $screen->post_type ) {

			// Hook in our column
			add_filter( 'manage_pages_columns', array( $this, 'add_template_column' ) );
			add_action( 'manage_pages_custom_column', array( $this, 'display_template_column' ), 10, 2 );

			// In case you want to modify the width of the column. Default is 12%
			$col_width = apply_filters( 'wds_page_template_admin_column_width', '12%' );

			?>
			<style type="text/css" media="screen">
				#page-template { width: <?php echo esc_html( $col_width ); ?>; }
			</style>
			<?php
		}
	}

	/**
	 * Adds page-template column to list of registered columns for pages
	 *
	 * @since 0.1.0
	 *
	 * @param  array  $columns Array of columns
	 * @return array           Modified array of columns
	 */
	public function add_template_column( $columns ) {
		// Add a new column
		$columns['page-template'] = __( 'Template' );

		return $columns;
	}

	/**
	 * Display handler for the page-template column
	 *
	 * @since  0.1.0
	 *
	 * @param  string $column_name Column name
	 * @param  int    $post_id     Post ID
	 *
	 * @return null
	 */
	public function display_template_column( $column_name, $post_id ) {
		if ( 'page-template' != $column_name ) {
			return;
		}

		$template = self::get_template( $post_id );
		$name     = $template['name'] ? $template['name'] : '&mdash;';
		$html     = sprintf( '<strong title="%s">%s</strong>', esc_attr( $template['slug'] ), $name );
		// in case you want to modify the markup
		echo apply_filters( 'wds_page_template_admin_column_markup', $html, $template );
	}

	/**
	 * Get a template name for a page
	 *
	 * @since  0.1.0
	 *
	 * @param  int    $post_id Post ID
	 *
	 * @return string          Template name or slug, or empty
	 */
	public static function get_template( $post_id = 0 ) {
		$post_id = $post_id ? $post_id : get_the_ID();

		$template      = get_page_template_slug( $post_id );
		$templates     = get_page_templates( $post_id );
		$templates     = is_array( $templates ) ? array_flip( $templates ) : array();
		$template_name = array_key_exists( $template, $templates ) ? $templates[ $template ] : '';

		return array( 'slug' => $template, 'name' => $template_name );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}
}

/**
 * Grab the WDS_Page_Template_Admin_Column object and return it.
 * Wrapper for WDS_Page_Template_Admin_Column::get_instance()
 *
 * @since  0.1.0
 * @return WDS_Page_Template_Admin_Column  Singleton instance of plugin class.
 */
function wds_page_template_admin_column() {
	return WDS_Page_Template_Admin_Column::get_instance();
}

// Kick it off
wds_page_template_admin_column();
