<?php
/**
 * Meta box class.
 *
 * @since 0.1.0
 */
class Laffp_Posts_Order_Meta_Box {

	private $field;
	private $action;
	private $nonce_field;

	public function __construct() {
		$this->field = 'laffp_posts_order';
		$this->action = 'laffp_update_posts_order';
		$this->nonce_field = 'laffp_post_nonce_posts_order';

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
		add_meta_box(
			'laffpdiv-posts-order',
			__( 'Posts Order', LAFFP_TEXT_DOMAIN ),
			array( $this, 'render' ),
			LAFFP_POST_TYPE,
			'advanced',
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
		$args = array(
			'posts_per_page' => -1,
			'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' )
		);
		$featured_posts = laffp_get_posts( $post->post_name, $args );
		$post_ids = array();
?>
<div class="wrap">
	<?php if ( count( $featured_posts ) > 0 ): ?>
		<table class="sortable wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="th_order"><?php esc_html_e( 'Order', LAFFP_TEXT_DOMAIN ); ?></th>
					<th class="th_title"><?php esc_html_e( 'Title', LAFFP_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Post Type', LAFFP_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Operation', LAFFP_TEXT_DOMAIN ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php global $post; $order = 1; foreach ( $featured_posts as $post ) : setup_postdata( $post ); ?>
					<?php $post_ids[] = $post->ID; ?>
					<?php $post_type_object = get_post_type_object( get_post_type() ); ?>
					<tr class="row-<?php esc_attr_e( $post->ID ); ?>" data-post-id="<?php esc_attr_e( $post->ID ); ?>">
						<td class="order">
							<?php esc_html_e( $order ); ?>
						</td>
						<td class="title">
							<a href="<?php the_permalink(); ?>" target="_blank"><?php the_title(); ?></a>
						</td>
						<td class="post_type">
							<?php esc_html_e( $post_type_object->labels->singular_name ); ?>
						</td>
						<td>
							<a href="#" class="delete_featured_post" rel="<?php esc_attr_e( $post->ID ); ?>"><?php esc_html_e( 'Remove', LAFFP_TEXT_DOMAIN ); ?></a>
						</td>
					</tr>
					<?php $order++; ?>
				<?php endforeach; wp_reset_postdata(); ?>
			</tbody>
		</table>
		<?php wp_nonce_field( $this->action, $this->nonce_field ); ?>
		<input type="hidden" id="laffp_order" name="<?php esc_attr_e( $this->field ); ?>" value="<?php esc_attr_e( implode( ',', $post_ids ) ); ?>" />
	<?php else: ?>
		<p><?php esc_html_e( 'Featured Post not exists.', LAFFP_TEXT_DOMAIN ); ?></p>
	<?php endif; ?>
</div>
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

		if ( ! isset( $_POST[ $this->field ] ) ) {
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

		$order = array();
		$csv = trim( $_POST[ $this->field ] );
		if ( $csv !== '' ) {
			if ( ! preg_match( '/^[0-9][0-9,]+$/', $csv ) ) {
				return;
			}
		}

		$group = get_post( $post_id );
		if ( $group ) {
			$order = explode( ',', $csv );
			$order = array_map( 'trim', $order );
			$order = array_filter( $order );
			$order = array_map( 'intval', $order );

			$group_slug = $group->post_name;
			$order_meta_key = 'laffp_order_' . $group_slug;
			$featured_posts = laffp_get_posts( $group_slug,
				array(
					'posts_per_page' => -1,
					'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' )
				)
		 	);
			foreach ( $featured_posts as $post ) {
				delete_post_meta( $post->ID, $order_meta_key );
			}

			if ( count( $order > 0 ) ) {
				foreach ( $order as $index => $_post_id ) {
					update_post_meta( $_post_id, $order_meta_key, $index + 1 );
				}
			}
		}
	}
}
