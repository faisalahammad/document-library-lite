<?php

namespace Barn2\Plugin\Document_Library\Admin\Settings_Tab;

use Barn2\Plugin\Document_Library\Dependencies\Lib\Registerable;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Admin\Settings_API_Helper;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\Document_Library\Util\Options;

/**
 * Advanced Setting Tab
 *
 * @package   Barn2\document-library-lite
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Advanced implements Registerable {
	const TAB_ID       = 'advanced';
	const OPTION_GROUP = 'document_library_pro_advanced';
	const MENU_SLUG    = 'dlp-settings-advanced';

	private $plugin;
	private $id;
	private $title;
	private $default_settings;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin           = $plugin;
		$this->id               = 'advanced';
		$this->title            = __( 'Advanced', 'document-library-lite' );
		$this->default_settings = Options::get_default_settings();
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
		// Advanced section
		Settings_API_Helper::add_settings_section( 'dlp_advanced', self::MENU_SLUG, '', [ $this, 'display_advanced_description' ], $this->get_advanced_settings() );

		// Table options section
		Settings_API_Helper::add_settings_section( 'dlp_table_options', self::MENU_SLUG, __( 'Table options', 'document-library-lite' ), '__return_false', $this->get_table_options_settings() );

		// Pagination section
		Settings_API_Helper::add_settings_section( 'dlp_pagination', self::MENU_SLUG, __( 'Pagination', 'document-library-lite' ), '__return_false', $this->get_pagination_settings() );
	}

	/**
	 * Output the Advanced description.
	 */
	public function display_advanced_description() {
		printf(
			'<p>' .
			esc_html__( 'The following options allow you to configure advanced settings for your document libraries.', 'document-library-lite' ) .
			'</p>'
		);
	}

	/**
	 * Get the Advanced settings.
	 *
	 * @return array
	 */
	private function get_advanced_settings() {
		return Options::mark_readonly_settings(
			[
				[
					'id'                => Options::SHORTCODE_OPTION_KEY . '[content_length]',
					'title'             => __( 'Content length', 'document-library-lite' ),
					'type'              => 'number',
					'class'             => 'small-text',
					'suffix'            => __( 'words', 'document-library-lite' ),
					'desc'              => __( 'Enter -1 to show the full content.', 'document-library-lite' ),
					'default'           => 15,
					'custom_attributes' => [
						'min' => -1,
					],
				],
				[
					'title'   => __( 'Shortcodes and media', 'document-library-lite' ),
					'type'    => 'checkbox',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[shortcodes]',
					'label'   => __( 'Allow shortcodes and media files in the document library content', 'document-library-lite' ),
					'default' => false,
				],
				[
					'id'      => Options::SHORTCODE_OPTION_KEY . '[lightbox]',
					'title'   => __( 'Image lightbox', 'document-library-lite' ),
					'type'    => 'checkbox',
					'label'   => __( 'Display images in a lightbox when opened', 'document-library-lite' ),
					'default' => $this->default_settings['lightbox'],
				],
				[
					'title'   => __( 'Text links', 'document-library-lite' ),
					'type'    => 'checkbox',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[text_links_new_tab]',
					'label'   => __( 'Open text links in a new tab', 'document-library-lite' ),
					'default' => false,
				],
			]
		);
	}

	/**
	 * Get the Table Options settings.
	 *
	 * @return array
	 */
	private function get_table_options_settings() {
		return Options::mark_readonly_settings(
			[
				[
					'id'      => Options::SHORTCODE_OPTION_KEY . '[image_size]',
					'title'   => __( 'Image size', 'document-library-lite' ),
					'type'    => 'text',
					'desc'    => __( 'Enter WxH in pixels (e.g. 80x80).', 'document-library-lite' ) . ' ' . Lib_Util::barn2_link( 'kb/document-library-image-options/#image-size', '', true ),
					'default' => '70x70',
				],
				[
					'title'             => __( 'Lazy load', 'document-library-lite' ),
					'type'              => 'checkbox',
					'id'                => Options::SHORTCODE_OPTION_KEY . '[lazy_load]',
					'label'             => __( 'Load the document table one page at a time', 'document-library-lite' ),
					'desc'              => __( 'Enable this if you have many documents or experience slow page load times.', 'document-library-lite' ) . '<br/>' .
					__( 'Warning: Lazy load limits the searching and sorting features in the document library. Only use it if you definitely need it.', 'document-library-lite' ) .
					' ' . Lib_Util::barn2_link( 'kb/document-library-lazy-load/', '', true ),
					'default'           => $this->default_settings['lazy_load'],
					'class'             => 'dlp-toggle-parent',
					'custom_attributes' => [
						'data-child-class' => 'post-limit',
						'data-toggle-val'  => 0,
					],
				],
				[
					'title'             => __( 'Caching', 'document-library-lite' ),
					'type'              => 'checkbox',
					'id'                => Options::SHORTCODE_OPTION_KEY . '[cache]',
					'label'             => __( 'Cache document libraries to improve load time', 'document-library-lite' ),
					'default'           => false,
					'class'             => 'toggle-parent',
					'custom_attributes' => [
						'data-child-class' => 'expires-after',
					],
				],
				[
					'title'             => __( 'Cache expires after', 'document-library-lite' ),
					'type'              => 'number',
					'id'                => Options::MISC_OPTION_KEY . '[cache_expiry]',
					'suffix'            => __( 'hours', 'document-library-lite' ),
					'desc'              => __( 'Your table data will be refreshed after this length of time.', 'document-library-lite' ),
					'default'           => 6,
					'class'             => 'expires-after',
					'custom_attributes' => [
						'min' => 1,
						'max' => 9999,
					],
				],
				[
					'title'             => __( 'Document limit', 'document-library-lite' ),
					'type'              => 'number',
					'id'                => Options::SHORTCODE_OPTION_KEY . '[post_limit]',
					'desc'              => __( 'The maximum number of documents to display in each table. Enter -1 to show all documents.', 'document-library-lite' ),
					'default'           => $this->default_settings['post_limit'],
					'class'             => 'small-text post-limit',
					'custom_attributes' => [
						'min' => -1,
					],
				],
				[
					'title'   => __( 'Accent-insensitive search', 'document-library-lite' ),
					'type'    => 'checkbox',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[accent_insensitive]',
					'label'   => __( 'Make searches match accented and non-accented characters when lazy load is disabled', 'document-library-lite' ),
					'default' => false,
				],
				[
					'title'   => __( 'Diacritics sorting', 'document-library-lite' ),
					'type'    => 'checkbox',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[diacritics_sort]',
					'label'   => __( 'Improve sorting of accented characters when lazy load is disabled', 'document-library-lite' ),
					'default' => false,
				],
				[
					'title'   => __( 'Responsive display', 'document-library-lite' ),
					'type'    => 'select',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[responsive_display]',
					'options' => [
						'child_row' => __( 'Click a plus icon to display a hidden child row', 'document-library-lite' ),
					],
					'default' => 'child_row',
				],
			]
		);
	}

	/**
	 * Get the Pagination settings.
	 *
	 * @return array
	 */
	private function get_pagination_settings() {
		return Options::mark_readonly_settings(
			[
				[
					'title'             => __( 'Documents per page', 'document-library-lite' ),
					'type'              => 'number',
					'id'                => Options::SHORTCODE_OPTION_KEY . '[rows_per_page]',
					'desc'              => __( 'The number of documents per page of the document library. Enter -1 to display all documents on one page.', 'document-library-lite' ),
					'default'           => $this->default_settings['rows_per_page'],
					'custom_attributes' => [
						'min' => -1,
					],
				],
				[
					'title'   => __( 'Document total', 'document-library-lite' ),
					'type'    => 'select',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[totals]',
					'options' => [
						'top'    => __( 'Above library', 'document-library-lite' ),
						'bottom' => __( 'Below library', 'document-library-lite' ),
						'both'   => __( 'Above and below library', 'document-library-lite' ),
						'false'  => __( 'Hidden', 'document-library-lite' ),
					],
					'desc'    => __( "The position of the document total, e.g. '25 documents'.", 'document-library-lite' ),
					'default' => 'bottom',
				],
				[
					'title'   => __( 'Pagination style', 'document-library-lite' ),
					'type'    => 'select',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[paging_type]',
					'options' => [
						'numbers'        => __( 'Numbers only', 'document-library-lite' ),
						'simple'         => __( 'Prev|Next', 'document-library-lite' ),
						'simple_numbers' => __( 'Prev|Next + Numbers', 'document-library-lite' ),
						'full'           => __( 'Prev|Next|First|Last', 'document-library-lite' ),
						'full_numbers'   => __( 'Prev|Next|First|Last + Numbers', 'document-library-lite' ),
					],
					'default' => 'simple_numbers',
				],
				[
					'title'   => __( 'Pagination position', 'document-library-lite' ),
					'type'    => 'select',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[pagination]',
					'options' => [
						'top'    => __( 'Above library', 'document-library-lite' ),
						'bottom' => __( 'Below library', 'document-library-lite' ),
						'both'   => __( 'Above and below library', 'document-library-lite' ),
						'false'  => __( 'Hidden', 'document-library-lite' ),
					],
					'desc'    => __( 'The position of the paging buttons which scroll between results.', 'document-library-lite' ),
					'default' => 'bottom',
				],
				[
					'title'   => __( 'Page length', 'document-library-lite' ),
					'type'    => 'select',
					'id'      => Options::SHORTCODE_OPTION_KEY . '[page_length]',
					'options' => [
						'top'    => __( 'Above library', 'document-library-lite' ),
						'bottom' => __( 'Below library', 'document-library-lite' ),
						'both'   => __( 'Above and below library', 'document-library-lite' ),
						'false'  => __( 'Hidden', 'document-library-lite' ),
					],
					'desc'    => __( "The position of the 'Show [x] entries' dropdown list.", 'document-library-lite' ),
					'default' => 'above',
				],
			]
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
