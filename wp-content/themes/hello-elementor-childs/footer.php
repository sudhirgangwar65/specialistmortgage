<?php
/**
 * The template for displaying the footer.
 *
 * Contains the body & html closing tags.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
		get_template_part( 'template-parts/dynamic-footer' );
	} else {
		get_template_part( 'template-parts/footer' );
	}
}
?>

<?php wp_footer(); ?>


<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/assets/js/slick.min.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/assets/js/local.js"></script>

<script type="text/javascript">
//home-team-slider
jQuery('.hteam-slider').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  dots: true
});

jQuery(".slick-dots").insertAfter(".hteam-nav-bxoes");
<?php 
$i = 0;
$team = get_field('my_team', 'option'); 
foreach ($team as $teamRow) {
	$i++;
?>
jQuery('.slick-dots li:nth-child(<?= $i; ?>)').html('<div class="nva-dot"><img src="<?= $teamRow['profile_image']; ?>" alt="<?= $teamRow['Team_name']; ?>"></div>');
<?php } ?>

 	
</script>

</body>
</html>
