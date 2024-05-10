<?php

/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */
if (function_exists('acf_add_options_page')) {

	acf_add_options_page(array(
		'page_title'    => 'Our Team',
		'menu_title'    => 'Meet The Team',
		'menu_slug'     => 'meet-the-team',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
	acf_add_options_page(array(
		'page_title'    => 'Testimonials',
		'menu_title'    => 'Testimonial',
		'menu_slug'     => 'testimonial',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
	acf_add_options_page(array(
		'page_title'    => 'Lenders',
		'menu_title'    => 'Lenders',
		'menu_slug'     => 'lenders',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
	acf_add_options_page(array(
		'page_title'    => 'Awards',
		'menu_title'    => 'Awards',
		'menu_slug'     => 'awards',
		'capability'    => 'edit_posts',
		'redirect'      => false
	));
}

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('HELLO_ELEMENTOR_VERSION', '2.7.1');

if (!isset($content_width)) {
	$content_width = 800; // Pixels.
}

if (!function_exists('hello_elementor_setup')) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup()
	{
		if (is_admin()) {
			//hello_maybe_update_theme_version_in_db();
		}

		if (apply_filters('hello_elementor_register_menus', true)) {
			register_nav_menus(['menu-1' => esc_html__('Header', 'hello-elementor')]);
			register_nav_menus(['menu-2' => esc_html__('Footer', 'hello-elementor')]);
		}

		if (apply_filters('hello_elementor_post_type_support', true)) {
			add_post_type_support('page', 'excerpt');
		}

		if (apply_filters('hello_elementor_add_theme_support', true)) {
			add_theme_support('post-thumbnails');
			add_theme_support('automatic-feed-links');
			add_theme_support('title-tag');
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style('classic-editor.css');

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support('align-wide');

			/*
			 * WooCommerce.
			 */
			if (apply_filters('hello_elementor_add_woocommerce_support', true)) {
				// WooCommerce in general.
				add_theme_support('woocommerce');
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support('wc-product-gallery-zoom');
				// lightbox.
				add_theme_support('wc-product-gallery-lightbox');
				// swipe.
				add_theme_support('wc-product-gallery-slider');
			}
		}
	}
}
add_action('after_setup_theme', 'hello_elementor_setup');

// function hello_maybe_update_theme_version_in_db() {
// 	$theme_version_option_name = 'hello_theme_version';
// 	// The theme version saved in the database.
// 	$hello_theme_db_version = get_option( $theme_version_option_name );

// 	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
// 	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
// 		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
// 	}
// }

if (!function_exists('hello_elementor_scripts_styles')) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles()
	{
		$min_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		if (apply_filters('hello_elementor_enqueue_style', true)) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if (apply_filters('hello_elementor_enqueue_theme_style', true)) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action('wp_enqueue_scripts', 'hello_elementor_scripts_styles');

if (!function_exists('hello_elementor_register_elementor_locations')) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations($elementor_theme_manager)
	{
		if (apply_filters('hello_elementor_register_elementor_locations', true)) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action('elementor/theme/register_locations', 'hello_elementor_register_elementor_locations');

if (!function_exists('hello_elementor_content_width')) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width()
	{
		$GLOBALS['content_width'] = apply_filters('hello_elementor_content_width', 800);
	}
}
add_action('after_setup_theme', 'hello_elementor_content_width', 0);

if (is_admin()) {
	require get_template_directory() . '/includes/admin-functions.php';
}

/**
 * If Elementor is installed and active, we can load the Elementor-specific Settings & Features
 */

// Allow active/inactive via the Experiments
require get_template_directory() . '/includes/elementor-functions.php';

/**
 * Include customizer registration functions
 */
// function hello_register_customizer_functions() {
// 	if ( is_customize_preview() ) {
// 		require get_template_directory() . '/includes/customizer-functions.php';
// 	}
// }
// add_action( 'init', 'hello_register_customizer_functions' );

if (!function_exists('hello_elementor_check_hide_title')) {
	/**
	 * Check hide title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title($val)
	{
		if (defined('ELEMENTOR_VERSION')) {
			$current_doc = Elementor\Plugin::instance()->documents->get(get_the_ID());
			if ($current_doc && 'yes' === $current_doc->get_settings('hide_title')) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter('hello_elementor_page_title', 'hello_elementor_check_hide_title');

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if (!function_exists('hello_elementor_body_open')) {
	function hello_elementor_body_open()
	{
		wp_body_open();
	}
}

function custom_shortcode()
{
	ob_start();
	$team = get_field('my_team', 'option');
?>
	<div class="hteam-sec">
		<div class="hteam-nav">
			<div class="hteam-nav-bxoes">
			</div>
		</div>
		<div class="hteam-slider">
			<?php
			if (!empty($team)) {
				foreach ($team as $teamRow) { ?>
					<div class="team-slide">
						<div class="team-slide-box">
							<div class="team-slide-text">
								<h3><?= $teamRow['Team_name']; ?></h3>
								<h5><?= $teamRow['team_position']; ?></h5>
								<div class="s-arrow-btn">
									<a href="<?= $teamRow['profile_link'] ?>">Find Out More <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
											<path d="M24.2933 19.2925C24.1057 19.4801 24 19.7348 24 20C24 20.2652 24.1054 20.5196 24.2929 20.7071L24.3015 20.7156C24.4883 20.8976 24.7393 21 25 21L25.0055 21C25.2688 20.9985 25.5209 20.8933 25.7071 20.7071L29.7071 16.7071C29.8946 16.5196 30 16.2652 30 16C30 15.7348 29.8946 15.4804 29.7071 15.2929L25.7071 11.2929C25.5196 11.1054 25.2652 11 25 11C24.9827 11 24.9655 11.0004 24.9483 11.0013C24.7013 11.0141 24.4678 11.118 24.2929 11.2929C24.1054 11.4804 24 11.7348 24 12C24 12.2652 24.1054 12.5196 24.2929 12.7071L27.5858 16L24.2933 19.2925Z" fill="#2F655B"></path>
											<path d="M29 15L3 15C2.44772 15 2 15.4477 2 16C2 16.5523 2.44772 17 3 17L29 17C29.5523 17 30 16.5523 30 16C30 15.4477 29.5523 15 29 15Z" fill="#2F655B"></path>
										</svg></a>
								</div>
							</div>
							<div class="team-slide-img">
								<img src="<?= $teamRow['profile_image']; ?>" alt="<?= $teamRow['Team_name']; ?>">
							</div>
						</div>
					</div>
			<?php }
			} ?>
		</div>
	</div>

<?php
	return ob_get_clean();
}
add_shortcode('team_module', 'custom_shortcode');

function custom_testimonial()
{
	ob_start();
	$testimonial = get_field('testimonial', 'option');
?>
	<div class="htestimonials-slider">
		<?php
		if (!empty($testimonial)) {
			foreach ($testimonial as $testiRow) { ?>
				<div class="h-test-slide">
					<div class="h-test-slidein">
						<h3><?= $testiRow['testimonial_name']; ?></h3>
						<img src="https://www.specialistmortgage.com/wp-content/uploads/2023/05/Frame-42.png">
						<p><?= $testiRow['about_testimonial']; ?></p>
						<div class="author"><?= $testiRow['publish_date']; ?> <span></span> <?= $testiRow['author_city']; ?></div>
					</div>
				</div>
		<?php }
		} ?>
	</div>
<?php
	return ob_get_clean();
}
add_shortcode('testimonial', 'custom_testimonial');

// function codeflies_our_article_post()
// {
// 	$labels = array(
// 		'name' => __('Articles'),
// 		'singular_name' => __('Our Article'),
// 		'menu_name'   =>  __('Our Articles'),
// 		'all_items' => __('All Articles'),
// 		'view_item' => __('View Article'),
// 		'add_new_item' => __('Add New Article'),
// 		'add_new' =>   __('Add New Article'),
// 		'edit_item' => __('Edit Article'),
// 		'update_item' => __('Update Article'),
// 		'search_items' =>  __('Search Article'),
// 		'not_found' => __('Not Found'),
// 		'not_found_in_trash'  =>  __('Not found in Trash')

// 	);
// 	$args = array(
// 		'label' => __('Our Articles'),
// 		'description' => __('Best Our Articles'),
// 		'labels'   => 	$labels,
// 		'menu_position' => 4,
// 		'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields', 'comments'),
// 		'hierarchical'        => false,

// 		'public'              => true,

// 		'show_ui'             => true,

// 		'show_in_menu'        => true,

// 		'show_in_nav_menus'   => true,

// 		'show_in_admin_bar'   => true,

// 		'menu_position'       => 5,

// 		'can_export'          => true,

// 		'has_archive'         => false,

// 		'rewrite' => array('slug' => 'articles'),

// 		'exclude_from_search' => false,

// 		'publicly_queryable'  => true,

// 		'capability_type'     => 'post',

// 		'show_in_rest' => true,

// 	);
// 	register_post_type('articles', $args);
// 	register_taxonomy(
// 		'type',
// 		'articles',
// 		array(
// 			'labels' => array(
// 				'name' => 'Types',
// 				'add_new_item' => 'Add Type',
// 				'new_item_name' => "New Type"
// 			),
// 			'show_ui' => true,
// 			'show_tagcloud' => false,
// 			'hierarchical' => true,
// 			'hasArchive' => true,
// 			'show_admin_column' => true,

// 		)
// 	);
// }
// add_action('init', 'codeflies_our_article_post');

add_shortcode('article_list', 'new_post_shortcode');

function new_post_shortcode($atts)
{
	ob_start();
	extract(shortcode_atts(array(
		'per_page'  => -1,
		'columns'   => '',
		'orderby' => 'date',
		'order' => 'desc',
		'taxonomy' => '',
		'terms'    => '',
	), $atts));

	$args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order,
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $terms
			)
		)
	);
	$article = new WP_Query($args);
?>
	<div class="news-listing">
		<?php
		if ($article->have_posts()) {
			while ($article->have_posts()) {
				$article->the_post();
				$id = get_the_ID();
		?>
				<div class="news-box">
					<div class="news-box-in">
						<div class="news-img">
							<?php if (has_post_thumbnail($id)) {
								$img = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail'); ?>
								<img src="<?= $img[0] ?? ''; ?>" alt="<?php the_title(); ?>">
							<?php } ?>
						</div>
						<div class="news-content">
							<h3><?php the_title(); ?></h3>
							<p><?php get_custom_excerpt(20); ?></p>
							<div class="s-arrow-btn"><a href="<?php the_permalink(); ?>" tabindex="0">Read more <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
										<path d="M24.2933 19.2925C24.1057 19.4801 24 19.7348 24 20C24 20.2652 24.1054 20.5196 24.2929 20.7071L24.3015 20.7156C24.4883 20.8976 24.7393 21 25 21L25.0055 21C25.2688 20.9985 25.5209 20.8933 25.7071 20.7071L29.7071 16.7071C29.8946 16.5196 30 16.2652 30 16C30 15.7348 29.8946 15.4804 29.7071 15.2929L25.7071 11.2929C25.5196 11.1054 25.2652 11 25 11C24.9827 11 24.9655 11.0004 24.9483 11.0013C24.7013 11.0141 24.4678 11.118 24.2929 11.2929C24.1054 11.4804 24 11.7348 24 12C24 12.2652 24.1054 12.5196 24.2929 12.7071L27.5858 16L24.2933 19.2925Z" fill="#2F655B"></path>
										<path d="M29 15L3 15C2.44772 15 2 15.4477 2 16C2 16.5523 2.44772 17 3 17L29 17C29.5523 17 30 16.5523 30 16C30 15.4477 29.5523 15 29 15Z" fill="#2F655B"></path>
									</svg></a></div>
						</div>
					</div>
				</div>
		<?php
			}
		}
		wp_reset_postdata();
		?>
	</div>
<?php
	return ob_get_clean();
}
add_shortcode('author_archive_list', 'new_author_archive_post_shortcode');

function get_custom_excerpt($num)
{
	$limit = $num + 1;
	$excerpt = explode(' ', get_the_excerpt(), $limit);
	array_pop($excerpt);
	$excerpt = implode(" ", $excerpt);
	echo $excerpt . " [...]";
}

function new_author_archive_post_shortcode($atts)
{

	extract(shortcode_atts(array(
		'per_page'  => -1,
		'columns'   => '',
		'orderby' => 'title',
		'order' => 'desc',
	), $atts));
	ob_start();
	$get_author_id = get_the_author_ID();
	$get_author_gravatar = get_avatar_url($get_author_id, array('size' => 450));
	echo "<div class='news-list'>";
	echo '<img src="' . $get_author_gravatar . '" class="grav_tar_image" alt="' . get_the_title() . '" />';
	echo "</div>";

?>
	<h2 class="entry-title">Author: <?= get_the_author(); ?><strong>.</strong> </h2>
	<div class="news-listing">
		<?php
		$args = array(
			'post_type' => 'articles',
			'post_status' => 'publish',
			//'ignore_sticky_posts'   => 1,
			'posts_per_page' => $per_page,
			'orderby' => $orderby,
			'order' => $order,
			'author' => get_the_author_ID()
		);
		$article = new WP_Query($args);
		if ($article->have_posts()) {
			while ($article->have_posts()) {
				$article->the_post();
				$id = get_the_ID();
		?>
				<div class="news-box">
					<div class="news-box-in">
						<div class="news-img">
							<?php if (has_post_thumbnail($id)) {
								$img = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail'); ?>
								<img src="<?= $img[0]; ?>" alt="<?php the_title(); ?>">
							<?php } ?>
						</div>
						<div class="news-content">
							<h3><?php the_title(); ?></h3>
							<p><?php the_excerpt(); ?></p>
							<div class="s-arrow-btn"><a href="" tabindex="0">Find Out More <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
										<path d="M24.2933 19.2925C24.1057 19.4801 24 19.7348 24 20C24 20.2652 24.1054 20.5196 24.2929 20.7071L24.3015 20.7156C24.4883 20.8976 24.7393 21 25 21L25.0055 21C25.2688 20.9985 25.5209 20.8933 25.7071 20.7071L29.7071 16.7071C29.8946 16.5196 30 16.2652 30 16C30 15.7348 29.8946 15.4804 29.7071 15.2929L25.7071 11.2929C25.5196 11.1054 25.2652 11 25 11C24.9827 11 24.9655 11.0004 24.9483 11.0013C24.7013 11.0141 24.4678 11.118 24.2929 11.2929C24.1054 11.4804 24 11.7348 24 12C24 12.2652 24.1054 12.5196 24.2929 12.7071L27.5858 16L24.2933 19.2925Z" fill="#2F655B"></path>
										<path d="M29 15L3 15C2.44772 15 2 15.4477 2 16C2 16.5523 2.44772 17 3 17L29 17C29.5523 17 30 16.5523 30 16C30 15.4477 29.5523 15 29 15Z" fill="#2F655B"></path>
									</svg></a></div>
						</div>
					</div>
				</div>
		<?php
			}
			wp_reset_postdata();
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}

function custom_awards_section()
{
	ob_start();
	$awards = get_field('awards', 'option');
	if (!empty($awards)) {
		foreach ($awards as $awards_row) {
			$awardsimage = $awards_row['awards_first_row'];
			$awardsimagesecond = $awards_row['awards_second_row']
	?>
			<div class="awards-slider-sec">
				<div class="awards-slider1">
					<?php
					if ($awardsimage) {
						foreach ($awardsimage as $awardsimageitem) {
							$award = $awardsimageitem['awards_logo_image'];
					?>
							<div class="awards-box">
								<img src="<?= esc_url($award['url']); ?>" alt="<?= esc_attr($award['alt']); ?>">
							</div>
					<?php }
					}
					?>
				</div>
				<div class="awards-slider2">
					<?php
					if ($awardsimagesecond) {
						foreach ($awardsimagesecond as $awardsimageseconditem) {
							$award1 = $awardsimageseconditem['award_second_logo_image'];
					?>
							<div class="awards-box">
								<img src="<?= esc_url($award1['url']); ?>" alt="<?= esc_attr($award1['alt']); ?>">
							</div>
					<?php }
					}
					?>
				</div>
			</div>
		<?php
		}
	}
	return ob_get_clean();
}
add_shortcode('awards_slider', 'custom_awards_section');

function custom_Lenders_section()
{
	ob_start();
	$lender = get_field('lenders', 'option');
	if ($lender) {
		foreach ($lender as $lender_row) {
			$lenderfirstrow = $lender_row['lenders_first_row'];
			$lendersecondrow = $lender_row['lenders_second_row'];
		?>
			<div class="lenders-slider-box">
				<div class="lenders-slider1">
					<?php
					if ($lenderfirstrow) {
						foreach ($lenderfirstrow as $lenderfirstrow_item) {
							$imagedata = $lenderfirstrow_item['lenders_first_image'];
					?>
							<div class="lender-box">
								<img src="<?= esc_url($imagedata['url']); ?>" alt="<?= esc_attr($imagedata['alt']); ?>">
							</div>
					<?php }
					}
					?>
				</div>
				<div class="lenders-slider2">
					<?php
					if ($lendersecondrow) {
						foreach ($lendersecondrow as $lendersecondtrow_item) {
							$imagedata1 = $lendersecondtrow_item['lenders_second_image'];
					?>
							<div class="lender-box">
								<img src="<?= esc_url($imagedata1['url']); ?>" alt="<?= esc_attr($imagedata1['alt']); ?>">
							</div>
					<?php }
					}
					?>
				</div>
			</div>
	<?php
		}
	}
	return ob_get_clean();
}
add_shortcode('Lenders_slider', 'custom_Lenders_section');


// Create a post type for News

// function news_custom_post_type()
// {



// 	// Set UI labels for Custom Post Type 

// 	$labels = array(

// 		'name'                => _x('News', 'Post Type General Name', 'twentytwentyone'),

// 		'singular_name'       => _x('News', 'Post Type Singular Name', 'twentytwentyone'),

// 		'menu_name'           => __('News', 'twentytwentyone'),

// 		'parent_item_colon'   => __('Parent News', 'twentytwentyone'),

// 		'all_items'           => __('All News', 'twentytwentyone'),

// 		'view_item'           => __('View News', 'twentytwentyone'),

// 		'add_new_item'        => __('Add New News', 'twentytwentyone'),

// 		'add_new'             => __('Add New', 'twentytwentyone'),

// 		'edit_item'           => __('Edit News', 'twentytwentyone'),

// 		'update_item'         => __('Update News', 'twentytwentyone'),

// 		'search_items'        => __('Search News', 'twentytwentyone'),

// 		'not_found'           => __('Not Found', 'twentytwentyone'),

// 		'not_found_in_trash'  => __('Not found in Trash', 'twentytwentyone'),

// 	);



// 	// Set other options for Custom Post Type 



// 	$args = array(

// 		'label'               => __('News', 'twentytwentyone'),

// 		'description'         => __('News', 'twentytwentyone'),

// 		'labels'              => $labels,

// 		'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),

// 		// 'taxonomies'          => array( 'genres' ), 

// 		'hierarchical'        => false,

// 		'public'              => true,

// 		'show_ui'             => true,

// 		'show_in_menu'        => true,

// 		'show_in_nav_menus'   => true,

// 		'show_in_admin_bar'   => true,

// 		'menu_position'       => 5,

// 		'can_export'          => true,

// 		'has_archive'         => false,

// 		'rewrite' => array('slug' => 'news'),

// 		'exclude_from_search' => false,

// 		'publicly_queryable'  => true,

// 		'capability_type'     => 'post',

// 		'show_in_rest' => true,



// 	);
// 	register_post_type('news', $args);
// 	register_taxonomy(
// 		'news-type',
// 		'news',
// 		array(
// 			'labels' => array(
// 				'name' => 'Type',
// 				'add_new_item' => 'Add New',
// 				'new_item_name' => "New Type"
// 			),
// 			'show_ui' => true,
// 			'show_tagcloud' => false,
// 			'hierarchical' => true,
// 			'hasArchive' => true
// 		)
// 	);
// }
// add_action('init', 'news_custom_post_type', 0);

// function insights_custom_post_type()
// {



// 	// Set UI labels for Custom Post Type 

// 	$labels = array(

// 		'name'                => _x('Insights', 'Post Type General Name', 'twentytwentyone'),

// 		'singular_name'       => _x('Insights', 'Post Type Singular Name', 'twentytwentyone'),

// 		'menu_name'           => __('Insights', 'twentytwentyone'),

// 		'parent_item_colon'   => __('Parent Insights', 'twentytwentyone'),

// 		'all_items'           => __('All Insights', 'twentytwentyone'),

// 		'view_item'           => __('View Insights', 'twentytwentyone'),

// 		'add_new_item'        => __('Add New Insights', 'twentytwentyone'),

// 		'add_new'             => __('Add New', 'twentytwentyone'),

// 		'edit_item'           => __('Edit Insights', 'twentytwentyone'),

// 		'update_item'         => __('Update Insights', 'twentytwentyone'),

// 		'search_items'        => __('Search Insights', 'twentytwentyone'),

// 		'not_found'           => __('Not Found', 'twentytwentyone'),

// 		'not_found_in_trash'  => __('Not found in Trash', 'twentytwentyone'),

// 	);



// 	// Set other options for Custom Post Type 



// 	$args = array(

// 		'label'               => __('Insights', 'twentytwentyone'),

// 		'description'         => __('Insights', 'twentytwentyone'),

// 		'labels'              => $labels,

// 		'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),

// 		// 'taxonomies'          => array( 'genres' ), 

// 		'hierarchical'        => false,

// 		'public'              => true,

// 		'show_ui'             => true,

// 		'show_in_menu'        => true,

// 		'show_in_nav_menus'   => true,

// 		'show_in_admin_bar'   => true,

// 		'menu_position'       => 5,

// 		'can_export'          => true,

// 		'has_archive'         => false,

// 		'rewrite' => array('slug' => 'insights'),

// 		'exclude_from_search' => false,

// 		'publicly_queryable'  => true,

// 		'capability_type'     => 'post',

// 		'show_in_rest' => true,



// 	);
// 	register_post_type('insights', $args);
// 	register_taxonomy(
// 		'insights-type',
// 		'insights',
// 		array(
// 			'labels' => array(
// 				'name' => 'Type',
// 				'add_new_item' => 'Add New',
// 				'new_item_name' => "New Type"
// 			),
// 			'show_ui' => true,
// 			'show_tagcloud' => false,
// 			'hierarchical' => true,
// 			'hasArchive' => true
// 		)
// 	);
// }
// add_action('init', 'insights_custom_post_type', 0);


// function featured_custom_post_type()
// {



// 	// Set UI labels for Custom Post Type 

// 	$labels = array(

// 		'name'                => _x('Featured', 'Post Type General Name', 'twentytwentyone'),

// 		'singular_name'       => _x('Featured', 'Post Type Singular Name', 'twentytwentyone'),

// 		'menu_name'           => __('Featured', 'twentytwentyone'),

// 		'parent_item_colon'   => __('Parent Featured', 'twentytwentyone'),

// 		'all_items'           => __('All Featured', 'twentytwentyone'),

// 		'view_item'           => __('View Featured', 'twentytwentyone'),

// 		'add_new_item'        => __('Add New Featured', 'twentytwentyone'),

// 		'add_new'             => __('Add New', 'twentytwentyone'),

// 		'edit_item'           => __('Edit Featured', 'twentytwentyone'),

// 		'update_item'         => __('Update Featured', 'twentytwentyone'),

// 		'search_items'        => __('Search Featured', 'twentytwentyone'),

// 		'not_found'           => __('Not Found', 'twentytwentyone'),

// 		'not_found_in_trash'  => __('Not found in Trash', 'twentytwentyone'),

// 	);



// 	// Set other options for Custom Post Type 



// 	$args = array(

// 		'label'               => __('Featured', 'twentytwentyone'),

// 		'description'         => __('Featured', 'twentytwentyone'),

// 		'labels'              => $labels,

// 		'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),

// 		// 'taxonomies'          => array( 'genres' ), 

// 		'hierarchical'        => false,

// 		'public'              => true,

// 		'show_ui'             => true,

// 		'show_in_menu'        => true,

// 		'show_in_nav_menus'   => true,

// 		'show_in_admin_bar'   => true,

// 		'menu_position'       => 5,

// 		'can_export'          => true,

// 		'has_archive'         => false,

// 		'rewrite' => array('slug' => 'featured'),

// 		'exclude_from_search' => false,

// 		'publicly_queryable'  => true,

// 		'capability_type'     => 'post',

// 		'show_in_rest' => true,



// 	);
// 	register_post_type('featured', $args);
// 	register_taxonomy(
// 		'featured-type',
// 		'featured',
// 		array(
// 			'labels' => array(
// 				'name' => 'Type',
// 				'add_new_item' => 'Add New',
// 				'new_item_name' => "New Type"
// 			),
// 			'show_ui' => true,
// 			'show_tagcloud' => false,
// 			'hierarchical' => true,
// 			'hasArchive' => true
// 		)
// 	);
// }
// add_action('init', 'featured_custom_post_type', 0);



// function articles_custom_post_type()
// {



// 	// Set UI labels for Custom Post Type 

// 	$labels = array(

// 		'name'                => _x('Articles', 'Post Type General Name', 'twentytwentyone'),

// 		'singular_name'       => _x('Articles', 'Post Type Singular Name', 'twentytwentyone'),

// 		'menu_name'           => __('Articles', 'twentytwentyone'),

// 		'parent_item_colon'   => __('Parent Articles', 'twentytwentyone'),

// 		'all_items'           => __('All Articles', 'twentytwentyone'),

// 		'view_item'           => __('View Articles', 'twentytwentyone'),

// 		'add_new_item'        => __('Add New Articles', 'twentytwentyone'),

// 		'add_new'             => __('Add New', 'twentytwentyone'),

// 		'edit_item'           => __('Edit Articles', 'twentytwentyone'),

// 		'update_item'         => __('Update Articles', 'twentytwentyone'),

// 		'search_items'        => __('Search Articles', 'twentytwentyone'),

// 		'not_found'           => __('Not Found', 'twentytwentyone'),

// 		'not_found_in_trash'  => __('Not found in Trash', 'twentytwentyone'),

// 	);



// 	// Set other options for Custom Post Type 



// 	$args = array(

// 		'label'               => __('Articles', 'twentytwentyone'),

// 		'description'         => __('Articles', 'twentytwentyone'),

// 		'labels'              => $labels,

// 		'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),

// 		// 'taxonomies'          => array( 'genres' ), 

// 		'hierarchical'        => false,

// 		'public'              => true,

// 		'show_ui'             => true,

// 		'show_in_menu'        => true,

// 		'show_in_nav_menus'   => true,

// 		'show_in_admin_bar'   => true,

// 		'menu_position'       => 5,

// 		'can_export'          => true,

// 		'has_archive'         => false,

// 		'rewrite' => array('slug' => 'articles'),

// 		'exclude_from_search' => false,

// 		'publicly_queryable'  => true,

// 		'capability_type'     => 'post',

// 		'show_in_rest' => true,



// 	);
// 	register_post_type('articles', $args);
// 	register_taxonomy(
// 		'articles-type',
// 		'articles',
// 		array(
// 			'labels' => array(
// 				'name' => 'Type',
// 				'add_new_item' => 'Add New',
// 				'new_item_name' => "New Type"
// 			),
// 			'show_ui' => true,
// 			'show_tagcloud' => false,
// 			'hierarchical' => true,
// 			'hasArchive' => true
// 		)
// 	);
// }
// add_action('init', 'articles_custom_post_type', 0);



// Short code for News


add_shortcode('short_code_for_news', 'short_code_for_news_fn');

function short_code_for_news_fn($atts)
{
	ob_start();
	extract(shortcode_atts(array(
		'per_page'  => -1,
		'columns'   => '',
		'orderby' => 'title',
		'order' => 'desc',
	), $atts));

	$args = array(
		'post_type' => 'news',
		'post_status' => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order
	);
	$article = new WP_Query($args);
	?>
	<div class="news-listing">
		<?php
		if ($article->have_posts()) {
			while ($article->have_posts()) {
				$article->the_post();
				$id = get_the_ID();
		?>
				<div class="news-box">
					<div class="news-box-in">
						<div class="news-img">
							<?php if (has_post_thumbnail($id)) {
								$img = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail'); ?>
								<img src="<?= $img[0] ?? ''; ?>" alt="<?php the_title(); ?>">
							<?php } ?>
						</div>
						<div class="news-content">
							<h3><?php the_title(); ?></h3>
							<p><?php the_excerpt(); ?></p>
							<div class="s-arrow-btn"><a href="<?php the_permalink(); ?>" tabindex="0">Find Out More <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
										<path d="M24.2933 19.2925C24.1057 19.4801 24 19.7348 24 20C24 20.2652 24.1054 20.5196 24.2929 20.7071L24.3015 20.7156C24.4883 20.8976 24.7393 21 25 21L25.0055 21C25.2688 20.9985 25.5209 20.8933 25.7071 20.7071L29.7071 16.7071C29.8946 16.5196 30 16.2652 30 16C30 15.7348 29.8946 15.4804 29.7071 15.2929L25.7071 11.2929C25.5196 11.1054 25.2652 11 25 11C24.9827 11 24.9655 11.0004 24.9483 11.0013C24.7013 11.0141 24.4678 11.118 24.2929 11.2929C24.1054 11.4804 24 11.7348 24 12C24 12.2652 24.1054 12.5196 24.2929 12.7071L27.5858 16L24.2933 19.2925Z" fill="#2F655B"></path>
										<path d="M29 15L3 15C2.44772 15 2 15.4477 2 16C2 16.5523 2.44772 17 3 17L29 17C29.5523 17 30 16.5523 30 16C30 15.4477 29.5523 15 29 15Z" fill="#2F655B"></path>
									</svg></a></div>
						</div>
					</div>
				</div>
		<?php
			}
		}
		wp_reset_postdata();
		?>
	</div>
<?php
	return ob_get_clean();
}



add_shortcode('short_code_for_insights', 'short_code_for_insights');

function short_code_for_insights($atts)
{
	ob_start();
	extract(shortcode_atts(array(
		'per_page'  => -1,
		'columns'   => '',
		'orderby' => 'title',
		'order' => 'desc',
	), $atts));

	$args = array(
		'post_type' => 'insights',
		'post_status' => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order
	);
	$article = new WP_Query($args);
?>
	<div class="news-listing">
		<?php
		if ($article->have_posts()) {
			while ($article->have_posts()) {
				$article->the_post();
				$id = get_the_ID();
		?>
				<div class="news-box">
					<div class="news-box-in">
						<div class="news-img">
							<?php if (has_post_thumbnail($id)) {
								$img = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail'); ?>
								<img src="<?= $img[0] ?? ''; ?>" alt="<?php the_title(); ?>">
							<?php } ?>
						</div>
						<div class="news-content">
							<h3><?php the_title(); ?></h3>
							<p><?php the_excerpt(); ?></p>
							<div class="s-arrow-btn"><a href="<?php the_permalink(); ?>" tabindex="0">Find Out More <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
										<path d="M24.2933 19.2925C24.1057 19.4801 24 19.7348 24 20C24 20.2652 24.1054 20.5196 24.2929 20.7071L24.3015 20.7156C24.4883 20.8976 24.7393 21 25 21L25.0055 21C25.2688 20.9985 25.5209 20.8933 25.7071 20.7071L29.7071 16.7071C29.8946 16.5196 30 16.2652 30 16C30 15.7348 29.8946 15.4804 29.7071 15.2929L25.7071 11.2929C25.5196 11.1054 25.2652 11 25 11C24.9827 11 24.9655 11.0004 24.9483 11.0013C24.7013 11.0141 24.4678 11.118 24.2929 11.2929C24.1054 11.4804 24 11.7348 24 12C24 12.2652 24.1054 12.5196 24.2929 12.7071L27.5858 16L24.2933 19.2925Z" fill="#2F655B"></path>
										<path d="M29 15L3 15C2.44772 15 2 15.4477 2 16C2 16.5523 2.44772 17 3 17L29 17C29.5523 17 30 16.5523 30 16C30 15.4477 29.5523 15 29 15Z" fill="#2F655B"></path>
									</svg></a></div>
						</div>
					</div>
				</div>
		<?php
			}
		}
		wp_reset_postdata();
		?>
	</div>
<?php
	return ob_get_clean();
}



add_shortcode('short_code_for_featured', 'short_code_for_featured');

function short_code_for_featured($atts)
{
	ob_start();
	extract(shortcode_atts(array(
		'per_page'  => -1,
		'columns'   => '',
		'orderby' => 'title',
		'order' => 'desc',
	), $atts));

	$args = array(
		'post_type' => 'featured',
		'post_status' => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order
	);
	$article = new WP_Query($args);
?>
	<div class="news-listing">
		<?php
		if ($article->have_posts()) {
			while ($article->have_posts()) {
				$article->the_post();
				$id = get_the_ID();
		?>
				<div class="news-box">
					<div class="news-box-in">
						<div class="news-img">
							<?php if (has_post_thumbnail($id)) {
								$img = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail'); ?>
								<img src="<?= $img[0] ?? ''; ?>" alt="<?php the_title(); ?>">
							<?php } ?>
						</div>
						<div class="news-content">
							<h3><?php the_title(); ?></h3>
							<p><?php the_excerpt(); ?></p>
							<div class="s-arrow-btn"><a href="<?php the_permalink(); ?>" tabindex="0">Find Out More <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
										<path d="M24.2933 19.2925C24.1057 19.4801 24 19.7348 24 20C24 20.2652 24.1054 20.5196 24.2929 20.7071L24.3015 20.7156C24.4883 20.8976 24.7393 21 25 21L25.0055 21C25.2688 20.9985 25.5209 20.8933 25.7071 20.7071L29.7071 16.7071C29.8946 16.5196 30 16.2652 30 16C30 15.7348 29.8946 15.4804 29.7071 15.2929L25.7071 11.2929C25.5196 11.1054 25.2652 11 25 11C24.9827 11 24.9655 11.0004 24.9483 11.0013C24.7013 11.0141 24.4678 11.118 24.2929 11.2929C24.1054 11.4804 24 11.7348 24 12C24 12.2652 24.1054 12.5196 24.2929 12.7071L27.5858 16L24.2933 19.2925Z" fill="#2F655B"></path>
										<path d="M29 15L3 15C2.44772 15 2 15.4477 2 16C2 16.5523 2.44772 17 3 17L29 17C29.5523 17 30 16.5523 30 16C30 15.4477 29.5523 15 29 15Z" fill="#2F655B"></path>
									</svg></a></div>
						</div>
					</div>
				</div>
		<?php
			}
		}
		wp_reset_postdata();
		?>
	</div>
<?php
	return ob_get_clean();
}



// function members_skip_trash($post_id)
// {
// 	if (get_post_type($post_id) == 'articles') { // <-- members type posts
// 		// Force delete
// 		remove_all_actions('wp_trash_post');
// 		wp_delete_post($post_id, true);
// 	}
// }
// add_action('trashed_post', 'members_skip_trash', 1);


// add_filter('register_post_type_args', 'movies_to_films', 10, 2);
// function movies_to_films($args, $post_type)
// {

// 	if ($post_type == 'post') {
// 		$args['rewrite']['slug'] = 'articles';
// 	}

// 	return $args;
// }
