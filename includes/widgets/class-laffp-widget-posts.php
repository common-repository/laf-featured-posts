<?php
/**
 * Featured posts widget class.
 *
 * @package Lafrec
 * @since 0.1.0
 */

/**
 * Implementation for featured posts widget
 *
 * @since 0.1.0
 */
class Laffp_Widget_Posts extends WP_Widget {

	/**
	 * Sets up a new widget instance.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$args = array(
			'classname' => 'widget_recent_entries widget_laffp_entries',
			'description' => __( 'Your site&#8217;s featured posts.', LAFFP_TEXT_DOMAIN )
	 	);
		parent::__construct(
			'laffp-posts',
			__( 'Featured Posts', LAFFP_TEXT_DOMAIN ),
			$args
		);
	}

	/**
	 * Outputs the content for the current Recent Posts widget instance.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Recent Posts widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		global $laffp_option;

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Featured Posts', LAFFP_TEXT_DOMAIN );
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : (int) $laffp_option->get_option( 'posts_per_page' );
		if ( ! $number ) {
			$number = 5;
		}
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		$slug = $instance[ 'slug' ];
		$posts_args = array(
			'posts_per_page' => $number
		);
		$featured_posts = laffp_get_posts( $slug, $posts_args );

		if ( count( $featured_posts > 0 ) ) {
			global $post;

			echo $args['before_widget'];
			if ( ! empty( $title ) ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			} ?>
			<ul>
				<?php foreach ( $featured_posts as $post ) : setup_postdata( $post ); ?>
					<li>
						<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
						<?php if ( $show_date ) : ?>
							<span class="post-date"><?php echo get_the_date(); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
			echo $args['after_widget'];
			wp_reset_postdata();
		}
	}

	/**
	 * Outputs the settings form for the widget.
	 *
	 * @since 0.1.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$options = array();

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$slug = isset( $instance['slug'] ) ? $instance['slug'] : '';

		$group_args = array(
			'post_type' => LAFFP_POST_TYPE,
			'posts_per_page' => -1
		);
		$groups = get_posts( $group_args );
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', LAFFP_TEXT_DOMAIN ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', LAFFP_TEXT_DOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>

		<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', LAFFP_TEXT_DOMAIN ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'slug' ); ?>"><?php _e( 'Category:', LAFFP_TEXT_DOMAIN ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'slug' ); ?>" name="<?php echo $this->get_field_name( 'slug' ); ?>">
				<?php foreach ( $groups as $group ) : ?>
					<option value="<?php esc_attr_e( $group->post_name ); ?>" <?php selected( $group->post_name, $slug ); ?>><?php esc_attr_e( $group->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
<?php
	}

	/**
	 * Handles updating the settings for the current widget instance.
	 *
	 * @since 0.1.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['slug'] = sanitize_key( $new_instance['slug'] );
		return $instance;
	}
}
