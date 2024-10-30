<?php
/**
 * Capability class.
 *
 * @since 0.1.0
 */

class Laffp_Capability {
	/**
	 * Map meta capabilities.
	 *
	 * @since 0.1.0
	 *
	 * @return array Actual capabilities for meta capability.
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		$meta_caps = array(
			'laffp_manage_options' => 'manage_options'
		);
		$meta_caps = apply_filters( 'laffp_meta_caps', $meta_caps );

		$caps = array_diff( $caps, array_keys( $meta_caps ) );
		if ( isset( $meta_caps[ $cap ] ) ) {
			$caps[] = $meta_caps[ $cap ];
		}

		return $caps;
	}
}
