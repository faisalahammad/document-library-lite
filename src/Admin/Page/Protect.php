<?php
namespace Barn2\Plugin\Document_Library\Admin\Page;

use Barn2\Plugin\Document_Library\Dependencies\Lib\Registerable;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Service\Standard_Service;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Plugin\Plugin;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Admin\Settings_Util;

/**
 * This class handles our plugin protect page in the admin.
 *
 * @package   Barn2/document-library-pro
 * @author    Barn2 Plugins <info@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Protect implements Standard_Service, Registerable {

	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		add_action( 'admin_menu', [ $this, 'add_protect_page' ] );
	}

	/**
	 * Add the Import sub menu page.
	 */
	public function add_protect_page() {
        if ( Lib_Util::is_plugin_installed( 'password-protected-categories/password-protected-categories.php' ) ) {
			return;
		}

		// Protect
		add_submenu_page(
			'document_library',
			__( 'Access Control', 'document-library-lite' ),
			__( 'Access Control', 'document-library-lite' ),
			'edit_posts',
			'dll_protect',
			[ $this, 'render' ],
			20
		);

	}

	/**
	 * Render the import page.
	 */
	public function render() {
		?>
		<div class='barn2-layout__header'>
			<div class="barn2-layout__header-wrapper">
				<h3 class='barn2-layout__header-heading'>
					<?php esc_html_e( 'Document Library Lite', 'document-library-lite' ); ?>
				</h3>
				<div class="links-area">
					<?php 
						printf(
							'<p>%s</p><p>%s</p>',
							Settings_Util::get_help_links( $this->plugin ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							''
						);
					?>
				</div>
			</div>
		</div>
		<div class="wrap dlw-settings protect-promo">
			<h1><?php esc_html_e( 'Access Control', 'document-library-lite' ); ?></h1>

			<?php
			printf(
				'<div class="promo-wrapper"><p class="promo">' .
				/* translators: %1: Document Library Pro link start, %2: Document Library Pro link end, %3: Update/Upgrade action text */
				esc_html__( 'Need to restrict access to any or all of your documents? %3$s to %1$sDocument Library Pro%2$s for a full range of document access restrictions: ', 'document-library-lite' ) .
				'<ul>
					<li>' . esc_html__( 'Restrict access to documents by user role, to specific users, the person who created the document, or to anyone with the password.', 'document-library-lite' ) . '</li>
					<li>' . esc_html__( 'Control visibility globally, by category or by document.', 'document-library-lite' ) . '</li>
					<li>' . esc_html__( 'Front end document library login form.', 'document-library-lite' ) . '</li>
				</ul>' .
				'<a class="promo-button" href="%4$s" target="_blank"><img class="promo" src="%5$s" /></a></div>',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'<a href="' . Lib_Util::barn2_url( 'wordpress-plugins/document-library-pro/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&utm_content=dlw-settings' ) . '" target="_blank">',
				'</a>',
				__( 'Upgrade', 'document-library-lite' ),
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				Lib_Util::barn2_url( 'wordpress-plugins/document-library-pro/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&utm_content=dlw-settings' ),
				esc_url( $this->plugin->get_dir_url() . '/assets/images/promo-protect.png' )
			);
			?>

		<?php
	}

}
