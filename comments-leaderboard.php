<?php
/**
 * Plugin Name: Comments Leaderboard
 * Plugin URI: https://kolakube.com/comments-leaderboard/
 * Description: Let the games begin! The Comments Leaderboard ranks your top commentators each month in a way that's sure to spark competition throughout your community.
 * Version: 1.0
 * Author: Alex Mangini, Kolakube
 * Author URI: https://kolakube.com/about/
 * Author email: alex@kolakube.com
 * License: GPL-2.0+
 * Requires at least: 4.2.4
 * Tested up to: 4.0
 * Text Domain: comments-leaderboard
 * Domain Path: /languages/
 */

// Prevent direct access

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'COMMENTS_LEADERBOARD_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );


/**
 * Builds Widget Settings + Frontend output for the Comments Leaderboard.
 *
 * @since 1.0
 */

class Comments_Leaderboard extends WP_Widget {

	/**
	 * Setup Widget.
	 *
	 * @since 1.0
	 */

	public function __construct() {
        parent::__construct( 'comments_leaderboard', __( 'Comments Leaderboard', 'comments-leaderboard' ), array(
        	'description' => __( 'Let the games begin! A cool Widget to rank the top commentators on your blog every month.', 'comments-leaderboard' )
        ) );

		add_action( 'load-widgets.php', array( $this, 'admin_enqueue' ) );

		if ( is_active_widget( false, false, $this->id_base, true ) )
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}


	/**
	 * Enqueue Leaderboard style on frontend.
	 *
	 * @since 1.0
	 */

	public function enqueue() {
		wp_enqueue_style( 'comments-leaderboard', COMMENTS_LEADERBOARD_URL . 'assets/comments-leaderboard.css' );
	}


	/**
	 * Enqueue color picker scripts in admin.
	 *
	 * @since 1.0
	 */

	public function admin_enqueue() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'comments-leaderboard', COMMENTS_LEADERBOARD_URL . 'assets/comments-leaderboard.js', array( 'wp-color-picker' ) );
	}


	/**
	 * Build Widget Frontend Output.
	 *
	 * @since 1.0
	 */

	public function widget( $args, $val ) {
		$title = $val['title'];
		$desc  = $val['desc'];
		$color = $val['color'];
		$none  = $val['none'];

		$exclude_names = $val['exclude_names'];
		$leaders       = $this->authors( $exclude_names );
	?>

		<?php echo $args['before_widget']; ?>

			<?php if ( ! empty( $title ) || ! empty( $desc ) ) : ?>

				<div class="leaderboard-head">

					<!-- Title -->

					<?php if ( ! empty( $title ) ) : ?>
						<p class="leaderboard-title small-title"><?php echo $title; ?></p>
					<?php endif; ?>

					<!-- Description -->

					<?php if ( ! empty( $title ) ) : ?>
						<p class="leaderboard-desc"><?php echo $desc; ?></p>
					<?php endif; ?>

				</div>

			<?php endif; ?>

			<!-- Leaderboard -->

			<ol class="leaderboard-leaders" style="background-color: <?php echo $color; ?>">

				<?php if ( ! empty( $leaders ) ) : ?>

					<?php foreach ( $leaders as $id => $leader ) :
						$html   = ! empty( $leader->comment_author_url ) ? 'a href="' . $leader->comment_author_url . '" target="_blank"' : 'p';
						$html_c = ! empty( $leader->comment_author_url ) ? 'a' : 'p';
					?>

						<li class="leader-tile leader-tile-<?php echo $id; ?>">
							<<?php echo $html; ?> class="leader-tile-inner">
								<span class="leader-avatar">
									<?php echo get_avatar( $leader->comment_author_email, 40 ); ?>
									<span class="leader-count"><?php echo $leader->comments_count; ?></span>
								</span>
								<span class="leader-name"><?php echo $leader->comment_author; ?></span>
							</<?php echo $html_c; ?>>
						</li>

					<?php endforeach; ?>

				<?php else : ?>

					<li class="leader-tile leader-tile-1">
						<p class="leader-tile-inner"><?php echo $none; ?></p>
					</li>

				<?php endif; ?>

			</ol>

		<?php echo $args['after_widget']; ?>

	<?php }


	/**
	 * Build Widget Form Options.
	 *
	 * @since 1.0
	 */

	public function form( $val ) {
		$val = wp_parse_args( (array) $val, array(
			'title'         => '',
			'desc'          => '',
			'exclude_names' => '',
			'color'         => '#ae2525',
			'none'          => __( 'No leaders yet. Leave a comment on any post to begin ranking!', 'comments-leaderboard' )
		) );
	?>

		<!-- Title -->

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'comments-leaderboard' ); ?>:</label>

			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php esc_attr_e( $val['title'] ); ?>" class="widefat" />
		</p>

		<!-- Description -->

		<p>
			<label for="<?php echo $this->get_field_id( 'dec' ); ?>"><?php _e( 'Description', 'comments-leaderboard' ); ?>:</label>

			<textarea id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" rows="4" class="large-text"><?php esc_attr_e( $val['desc'] ); ?></textarea>
		</p>

		<!-- Exclude Names -->

		<p>
			<label for="<?php echo $this->get_field_id( 'exclude_names' ); ?>"><?php _e( 'Exclude Names', 'comments-leaderboard' ); ?>:</label>

			<input type="text" id="<?php echo $this->get_field_id( 'exclude_names' ); ?>" name="<?php echo $this->get_field_name( 'exclude_names' ); ?>" value="<?php esc_attr_e( $val['exclude_names'] ); ?>" class="widefat" placeholder="Name1, Name2, ..." />

			<small class="description" style="display: block; margin-top: 5px; padding-left: 0;"><?php _e( 'Separate each name with a comma (,)', 'comments-leaderboard' ); ?></small>
		</p>

		<!-- Color Picker -->

		<p>
			<label for="<?php echo $this->get_field_id( 'color' ); ?>"><?php _e( 'Leaderboard Color', 'comments-leaderboard' ); ?></label><br />
			<input class="cl-bg-color" type="text" id="<?php echo $this->get_field_id( 'color' ); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" value="<?php echo esc_attr( $val['color'] ); ?>" />
		</p>

		<!-- No Leaders Message -->

		<p>
			<label for="<?php echo $this->get_field_id( 'none' ); ?>"><?php _e( 'No Comments Display Message', 'comments-leaderboard' ); ?>:</label>

			<textarea id="<?php echo $this->get_field_id( 'none' ); ?>" name="<?php echo $this->get_field_name( 'none' ); ?>" rows="4" class="large-text"><?php esc_attr_e( $val['none'] ); ?></textarea>
		</p>

	<?php }


	/**
	 * Safely save the Widget options.
	 *
	 * @since 1.0
	 */

	public function update( $new, $val ) {
		$val['title']         = strip_tags( $new['title'] );
		$val['desc']          = esc_textarea( $new['desc'] );
		$val['exclude_names'] = strip_tags( $new['exclude_names'] );
		$val['color']         = $new['color'];
		$val['none']          = esc_textarea( $new['none'] );

		return $val;
	}


	/**
	 * Returns an array of commenters data ordered by most amount of comments.
	 *
	 * References:
	 * https://gist.github.com/shemul49rmc/7942077
	 * https://wordpress.org/plugins/top-commentators-widget/
	 *
	 * @since 1.0
	 */

	private function authors( $exclude ) {
		global $wpdb;

		// exclude names

		$exclude_names = '';

		if ( ! empty( $exclude ) ) {
			$exclude_list = explode( ',', trim( $exclude ) );
			$names        = '';

			foreach( $exclude_list as $name )
				$names .= " AND comment_author NOT IN ('" . trim( $name ) . "')";

			$exclude_names = $names;
		}

		// get comments

		$results = $wpdb->get_results( "
			SELECT COUNT(comment_author_email) AS comments_count, comment_author_email, comment_author, comment_author_url
			FROM $wpdb->comments
			WHERE comment_author_email != '' AND comment_type != 'pingback' AND comment_approved = 1
			$exclude_names
			AND DATE_FORMAT(comment_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
			GROUP BY comment_author_email
			ORDER BY comments_count DESC, comment_author ASC
			LIMIT 5
		" );

		return $results;
	}

}


/**
 * Register the Comments Leaderboard Widget.
 *
 * @since 1.0
 */

function comments_leaderboard_widget() {
	register_widget( 'Comments_Leaderboard' );
}

add_action( 'widgets_init', 'comments_leaderboard_widget' );