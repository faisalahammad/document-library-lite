<?php

namespace Barn2\Plugin\Document_Library\Table;

use Barn2\Plugin\Document_Library\Dependencies\Lib\Util;
use Barn2\Plugin\Document_Library\Dependencies\Lib\Service\Standard_Service;
use Barn2\Plugin\Document_Library\Frontend_Scripts;
use Barn2\Plugin\Document_Library\Simple_Document_Library;
use Barn2\Plugin\Document_Library\Util\Options;

/**
 * The main plugin class.
 *
 * @package   Barn2\document-library-lite
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Ajax_Handler implements Standard_Service {

    const SHORTCODE = 'doc_library';

    public function __construct() {
        add_action( 'wp_ajax_dll_load_posts', [ $this, 'load_posts' ] );
        add_action( 'wp_ajax_nopriv_dll_load_posts', [ $this, 'load_posts' ] );
    }
    
    public function register() {
    }

    public function load_posts() {
        // Parse and sanitize incoming args
        $args = isset( $_POST['args'] ) ? Options::handle_shortcode_attribute_aliases( wp_unslash( $_POST['args'] ) ) : [];
        $args = shortcode_atts( Options::get_defaults(), $args, self::SHORTCODE );

        $requested_status = isset( $args['status'] ) ? $args['status'] : 'publish';
        $is_logged_in = is_user_logged_in();

        // Unauthenticated users can ONLY see published content
        if ( ! $is_logged_in ) {
            $args['status'] = 'publish';
        }
        // Authenticated users requesting non-published content must provide nonce and capability
        elseif ( $requested_status !== 'publish' ) {
            $nonce = '';
            if ( isset( $_POST['_ajax_nonce'] ) ) {
                $nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
            } elseif ( isset( $_POST['ajax_nonce'] ) ) {
                $nonce = sanitize_text_field( wp_unslash( $_POST['ajax_nonce'] ) );
            }

            if ( ! $nonce || ! wp_verify_nonce( $nonce, 'dll_load_posts' ) ) {
                wp_send_json_error( [ 'message' => 'Security check failed' ], 403 );
            }

            if ( ! current_user_can( 'edit_posts' ) ) {
                wp_send_json_error( [ 'message' => 'Insufficient permissions' ], 403 );
            }
        }

        $table = new simple_Document_Library( $args );
        $response = $table->get_table( 'array' );

        // Return the response as JSON
        wp_send_json( $response );
    
    }

}
