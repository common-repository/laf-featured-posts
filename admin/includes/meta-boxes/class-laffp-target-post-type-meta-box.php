<?php
/**
 * Meta box class.
 *
 * @since 0.1.0
 */
class Laffp_Target_Post_Type_Meta_Box {

	private $meta_key;
	private $field;
	private $action;
	private $nonce_field;

	public function __construct() {
		$this->meta_key = 'laffp_post_types';
		$this->field = 'laffp_post_types';
		$this->action = 'laffp_update_post_types';
		$this->nonce_field = 'laffp_post_nonce_post_types';

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
		$args = array();
		$args[ 'post_types' ] = get_post_types( array( 'public' => true ), 'objects' );

		add_meta_box(
			'laffpdiv-target-post-type',
			__( 'Target Post Type', LAFFP_TEXT_DOMAIN ),
			array( $this, 'render' ),
			LAFFP_POST_TYPE,
			'side',
			'high',
			$args
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
		$post_types = $metabox[ 'args' ][ 'post_types' ];
		$current_post_types = (array) get_post_meta( $post->ID, $this->meta_key, true );
?>
	<?php wp_nonce_field( $this->action, $this->nonce_field ); ?>
	<ul>
		<?php foreach ( $post_types as $post_type ) : ?>
			<li>
				<label><input type="checkbox" name="<?php esc_attr_e( $this->field ); ?>[]" value="<?php esc_attr_e( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $current_post_types ), true ); ?>><?php esc_html_e( $post_type->label ); ?></label>
			</li>
		<?php endforeach; ?>
	</ul>

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

		$post_types = isset( $_POST[ $this->field ] ) ? $_POST[ $this->field ] : array();
		update_post_meta( $post_id, $this->meta_key, $post_types );

	}
}
