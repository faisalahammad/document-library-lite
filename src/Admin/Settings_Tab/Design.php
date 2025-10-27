<?php

namespace Barn2\Plugin\Document_Library\Admin\Settings_Tab;

use Barn2\Plugin\Document_Library\Dependencies\Lib\Registerable;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Admin\Settings_API_Helper;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Util as Lib_Util;

/**
 * Design Setting Tab
 *
 * @package   Barn2\document-library-lite
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Design implements Registerable {
	const TAB_ID       = 'design';
	const OPTION_GROUP = 'document_library_pro_design';
	const MENU_SLUG    = 'dlp-settings-design';

	private $plugin;
	private $id;
	private $title;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->id     = 'design';
		$this->title  = __( 'Design', 'document-library-lite' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register the Settings with WP Settings API.
	 */
	public function register_settings() {
		Settings_API_Helper::add_settings_section(
			'dlp_design',
			self::MENU_SLUG,
			'',
			[ $this, 'display_design_description' ],
			[]
		);
	}

	/**
	 * Output the Design description.
	 */
	public function display_design_description() {
		printf(
			'<p>' .
			esc_html__( 'Use the following options to customize the design of the document table and grid.', 'document-library-lite' ) .
			'</p>' .
			'<p><span class="pro-version">%s</span></p>',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Lib_Util::barn2_link( 'wordpress-plugins/document-library-pro/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&utm_content=dlw-settings', __( 'Pro version only', 'document-library-lite' ), true )
		);
	}

	/**
	 * Get the tab title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the tab ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}
}
