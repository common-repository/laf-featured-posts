<?php
/**
 * Meta box class.
 *
 * @since 0.1.0
 */
class Laffp_Feature_Meta_Box {

	private $group;
	private $slug;
	private $meta_key;
	private $post_type;
	private $field;
	private $action;
	private $nonce_field;

	public function __construct( WP_Post $group ) {
		$this->group = $group;
		$this->slug = $group->post_name;
		$this->meta_key = 'laffp_order_' . $this->slug;
		$this->post_type = (array) get_post_meta( $group->ID, 'laffp_post_types' );
		$this->field = 'laffp_post_order_' . $this->slug;
		$this->action = 'laffp_update_post_order_' . $this->slug;
		$this->nonce_field = 'laffp_post_nonce_post_order_' . $this->slug;

		// Register hooks
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}

	/**
	 * Register meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register() {
		$meta_box_id = 'laffpdiv-feature-' . $this->slug;
		$meta_box_title = apply_filters( 'laffp_meta_box_title', get_the_title( $this->group ), $this->group );
		add_meta_box(
			$meta_box_id,
			$meta_box_title,
			array( $this, 'render' ),
			$this->post_type,
			'side',
			'high',
			array()
		);
	}

	/**
	 * Render meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render( $post, $metabox ) {
		$order = get_post_meta( $post->ID, $this->meta_key, true );
		$featured = ( $order !== '' );
		$id = 'laffp_feature_' . $this->slug;
		$label_for = $id;
		$label = apply_filters( 'laffp_meta_box_label', __( 'Feature this post', LAFFP_TEXT_DOMAIN ), $this->group, $post );
?>
	<?php wp_nonce_field( $this->action, $this->nonce_field ); ?>
	<input id="<?php esc_attr_e( $id ); ?>" type="checkbox" name="<?php esc_attr_e( $this->field ); ?>" value="1" <?php checked( $featured, true ); ?> />
	<label for="<?php esc_attr_e( $label_for ); ?>"><?php esc_html_e( $label ); ?></label>
<?php
	}

	/**
	 * Save post meta data that whether feature post or not.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function save_meta( $post_id ) {
		// Prevent action if new post or trash
		if ( empty( $_POST ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Nonce verification
		if ( ! isset( $_POST[ $this->nonce_field ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ $this->nonce_field ], $this->action ) ) {
			return;
		}

		// User capability verification
		$cap = apply_filters( 'laffp_save_meta_cap', 'edit_post', $post_id );
		if ( ! current_user_can( $cap, $post_id ) ) {
			return;
		}

		$order = get_post_meta( $post_id, $this->meta_key, true );
		$featured = ( $order !== '' );
		$checked = isset( $_POST[ $this->field ] ) && $_POST[ $this->field ];
		if ( $checked && ! $featured ) {
			update_post_meta( $post_id, $this->meta_key, 0 );
		} else if ( ! $checked && $featured ) {
			delete_post_meta( $post_id, $this->meta_key );
		}
	}
}
