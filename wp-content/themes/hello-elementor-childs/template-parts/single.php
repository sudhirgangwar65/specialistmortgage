<?php

/**
 * The template for displaying singular post-types: posts, post-types: articles, pages and user-defined custom post types.
 *
 * @package HelloElementor
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
$id = get_the_ID();
while (have_posts()) :
	the_post();

	// $author = get_the_author_meta( 'display_name', $author_id );
	// echo "<pre>";
	// print_r($author);


	$author_id  = get_post_field('post_author', get_the_ID());

	$author_link = get_field('author_link');

?>

	<main class="single-post-page" id="content" <?php post_class('site-main'); ?>>
		<?php if (apply_filters('hello_elementor_page_title', true)) : ?>
			<header class="page-header">
				<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
			</header>
		<?php endif; ?>
		<div class="page-content">
			<div class="container">
				<div class="single-post-head">
					<!-- <h4><?php //the_excerpt(); 
								?></h4> -->
					<ul>
						<li><?= get_the_date('d M Y'); ?></li>
						<?php if (!empty(get_field('reading_minute', $post_id))) { ?><li><?= (!empty(get_field('reading_minute', $post_id))) ? get_field('reading_minute', $post_id) : ''; ?></li><?php } ?>
						<?php if (!empty($author_link['title'])) { ?><li>By <a href="<?php echo $author_link['url'] ??  ''; ?>"><?php echo $author_link['title'] ??  ''; ?></a></li> <?php } ?>
					</ul>
					<div class="s-post-img">
						<?php
						if (has_post_thumbnail()) {
							$img = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'single-post-thumbnail'); ?>
							<img src="<?= $img[0]; ?>" alt="<?php the_title(); ?>">
						<?php } ?>
					</div>
				</div>

				<div class="single-post-content">
					<?php the_content(); ?>
				</div>

			</div>

		</div>

	</main>


<?php
endwhile;
