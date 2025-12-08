<?php

namespace Barn2\Plugin\Document_Library\Table;

/**
 * Stores and retrieves shortcode configurations securely using WordPress transients.
 *
 * @package   Barn2\document-library-lite
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Config_Builder {

	/**
	 * Transient prefix for storing configurations.
	 */
	const TRANSIENT_PREFIX = 'dll_table_config_';

	/**
	 * Transient expiration time (1 hour).
	 */
	const EXPIRATION = HOUR_IN_SECONDS;

	/**
	 * Store a table configuration and return its unique ID.
	 *
	 * @param array $config The table configuration arguments
	 * @return string The unique table ID
	 */
	public static function store( $config ) {
		// Sanitize configuration to prevent XSS
		$config = self::sanitize_config( $config );

		// Generate a unique ID for this configuration
		$table_id = self::generate_unique_id( $config );

		// Store the configuration as a transient
		set_transient( self::TRANSIENT_PREFIX . $table_id, $config, self::EXPIRATION );

		return $table_id;
	}

	/**
	 * Retrieve a stored configuration by its ID.
	 *
	 * @param string $table_id The unique table ID
	 * @return array|false The configuration array or false if not found
	 */
	public static function retrieve( $table_id ) {
		// Sanitize the table ID to prevent injection attacks
		$table_id = sanitize_key( $table_id );

		// Retrieve the configuration
		$config = get_transient( self::TRANSIENT_PREFIX . $table_id );

		// Return false if not found or expired
		if ( false === $config ) {
			return false;
		}

		return $config;
	}

	/**
	 * Delete a stored configuration.
	 *
	 * @param string $table_id The unique table ID
	 * @return bool True if deleted, false otherwise
	 */
	public static function delete( $table_id ) {
		$table_id = sanitize_key( $table_id );
		return delete_transient( self::TRANSIENT_PREFIX . $table_id );
	}

	/**
	 * Sanitize configuration values to prevent XSS attacks.
	 *
	 * @param array $config The configuration array
	 * @return array The sanitized configuration
	 */
	private static function sanitize_config( $config ) {
		// List of text fields that need sanitization
		$text_fields = [
			'link_text',
			'content',
			'search',
			'category',
		];

		foreach ( $text_fields as $field ) {
			if ( isset( $config[ $field ] ) ) {
				$config[ $field ] = sanitize_text_field( $config[ $field ] );
			}
		}

		return $config;
	}

	/**
	 * Generate a unique ID based on the configuration.
	 * Uses a combination of timestamp, random bytes, and config hash for uniqueness.
	 *
	 * @param array $config The configuration array
	 * @return string A unique identifier
	 */
	private static function generate_unique_id( $config ) {
		// Create a hash of the configuration
		$config_hash = md5( wp_json_encode( $config ) );

		// Generate random bytes for additional entropy
		$random = bin2hex( random_bytes( 8 ) );

		// Combine with timestamp (remove decimal point to avoid sanitization issues)
		$timestamp = str_replace( '.', '', microtime( true ) );

		// Create unique ID: timestamp (last 10 chars) + random + first 8 chars of config hash
		return substr( $timestamp, -10 ) . $random . substr( $config_hash, 0, 8 );
	}
}
