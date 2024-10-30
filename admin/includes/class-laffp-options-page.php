<?php
/**
 * Option page.
 *
 * @since 0.1.0
 */
class Laffp_Options_Page {

	const PAGE_SLUG = 'laffp_options';
	const DISPLAY_SECTION = 'laffp_options_display_section';

	private $option;
	private $option_group;

	public function __construct( Laffp_Option $option ) {
		$this->option = $option;
		$this->option_group = $option->key;
	}

	public function register() {
		$this->register_page();
		$this->register_settings();
	}

	public function register_page() {
		$hook_suffix = add_options_page(
			__( 'Laf Featured Posts Options', LAFFP_TEXT_DOMAIN ),
			__( 'Featured Posts', LAFFP_TEXT_DOMAIN ),
			apply_filters( 'laffp_manage_options_cap', 'laffp_manage_options' ),
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Render manage posts page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render() {
?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Laf Featured Posts Settings', LAFFP_TEXT_DOMAIN ); ?></h2>
		<form method="post" action="options.php">
			<?php
				settings_fields( $this->option_group );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
			?>
		</form>
	</div>
<?php
	}

	public function register_settings() {
		register_setting(
			$this->option_group,
			$this->option->key,
			array( $this, 'sanitize' )
	 	);

		add_settings_section(
			self::DISPLAY_SECTION,
			__( 'Display settings', LAFFP_TEXT_DOMAIN ),
			array( $this, 'on_display_section' ),
			self::PAGE_SLUG
	 	);

		add_settings_field(
			'posts_per_page',
			__( 'Default posts per page', LAFFP_TEXT_DOMAIN ),
			array( $this, 'on_posts_per_page' ),
			self::PAGE_SLUG,
			self::DISPLAY_SECTION,
			array( 'label_for' => 'posts_per_page' )
		);
	}

	public function sanitize( $input = array() ) {
		$option = get_option( $this->option->key );

		$input = (array) $input;

		if ( isset( $input[ 'posts_per_page' ] ) ) {
			$option[ 'posts_per_page' ] = max( 1, absint( $input[ 'posts_per_page' ] ) );
		}

		return $option;
	}

	public function on_display_section( $args ) {
		// Nothing to do.
	}

	public function on_posts_per_page( $args ) {
		$posts_per_page = $this->option->get_option( 'posts_per_page' );
?>
	<input type="number" step="1" min="1" id="posts_per_page" name="<?php esc_attr_e( $this->option->key ); ?>[posts_per_page]" value="<?php esc_attr_e( $posts_per_page ) ?>" size="3" />
<?php
	}
}
