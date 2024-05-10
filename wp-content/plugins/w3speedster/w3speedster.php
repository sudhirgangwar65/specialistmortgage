<?php 

/*

Plugin Name: W3Speedster Pro

Description: Speedup the site with good scores on google page speed test and Gtmetrix

Version: 7.19

Author: W3speedster

Author URI: https://w3speedster.com

License: GPLv2 or later

Copyright 2019-2023 W3Speedster

*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'W3SPEEDSTER_PLUGIN_VERSION', '7.19' );
define( 'W3SPEEDSTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'W3SPEEDSTER_PLUGIN_FILE', __FILE__ );
define( 'W3SPEEDSTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_init.php');

function w3speedster_mandatory_config_admin_notice() {
    if ( version_compare( PHP_VERSION, '5.6', '<' )){
        echo '<div class="error"><p>' . __( 'W3speedster requires PHP 5.6 (or higher) to function properly.', 'w3speedster' ) . '</p></div>';
    }
    if ( !extension_loaded ('xml')){
        echo '<div class="error"><p>' . __( 'W3speedster requires PHP-XML module to function properly.', 'w3speedster' ) . '</p></div>';
    }
	if(!extension_loaded('gd')){	
		echo '<div class="error"><p>' . __( 'W3speedster image optimization requires GD module to function properly.', 'w3speedster' ) . '</p></div>';	
	}
    if ( isset( $_GET['activate'] ) ) {
        unset( $_GET['activate'] );
    }
}
//register_activation_hook( __FILE__, 'w3speedster_activate'  );
function w3speedster_activate(){
	$w3_speedster_admin = new W3Speedster\w3speedster(); 
	$w3_speedster_admin->w3_remove_cache_files_hourly_event_callback();
}
register_deactivation_hook( __FILE__, 'w3speedster_deactivate' );
function w3speedster_deactivate(){
	if ( wp_next_scheduled( 'w3_cache_size' ) ) {
        wp_clear_scheduled_hook('w3_cache_size');
    }
	if ( wp_next_scheduled( 'w3speedup_preload_css_min' ) ) {
		wp_clear_scheduled_hook('w3speedup_preload_css_min');
	}
	if ( wp_next_scheduled( 'w3speedup_image_optimization' ) ) {
		wp_clear_scheduled_hook('w3speedup_image_optimization');
	}
	if ( wp_next_scheduled( 'w3_check_key' ) ) {
        wp_clear_scheduled_hook('w3_check_key');
    }	
}
function w3speedster_deactivate_unsupported_config() {
    deactivate_plugins( plugin_basename( W3SPEEDSTER_PLUGIN_FILE ) );
}

function w3speedster_action_links( $links ) {

	$links = array_merge( array(
		'<a href="' . esc_url( add_query_arg('page','w3_speedster',admin_url( '/options-general.php' ) ) ) . '">' . __( 'Settings', 'w3speedster' ) . '</a>'
	), $links );

	return $links;

}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'w3speedster_action_links' );

if ( version_compare( PHP_VERSION, '5.6', '<' ) || !extension_loaded ('xml') ) {
    add_action( 'admin_notices', 'w3speedster_mandatory_config_admin_notice' );
    add_action( 'admin_init', 'w3speedster_deactivate_unsupported_config' );
}
add_filter('cron_schedules', 'w3speedster_add_custom_cron_intervals');
function w3speedster_add_custom_cron_intervals($schedules) {
	$schedules['w3speedup_every_minute'] = array('interval' => 60, 'display' => __('Once every minute', 'w3speedster'));
	return $schedules;
}
function w3speedster_optimize_image_on_upload($attach_id){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_image.php');
	$w3_speedster_opt_img = new W3Speedster\w3speedster_optimize_image();
	 if(!empty($w3_speedster_opt_img->settings['opt_upload'])){
		return $w3_speedster_opt_img->w3speedster_optimize_single_image($attach_id);
    }else{
    	return $attach_id;
    }
}
add_action( 'add_attachment','w3speedster_optimize_image_on_upload');
function w3speedster_change_image_name($metadata, $attachment_id, $context){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_image.php');
	$w3_speedster_opt_img = new W3Speedster\w3speedster_optimize_image();
	return $w3_speedster_opt_img->w3speedster_change_image_name($metadata, $attachment_id, $context);
}

add_filter('wp_generate_attachment_metadata','w3speedster_change_image_name',10,3);
add_action( 'w3speedster_image_optimization', 'w3speedster_image_optimization_callback' );
add_action( 'w3speedup_preload_css_min', 'w3speedster_preload_css_callback' );
add_action( 'w3speedster_check_cron_needs_running', 'w3speedster_check_cron_needs_running_callback' );
function w3speedster_check_cron_needs_running_callback(){
	global $wpdb;
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	$result = $w3_speedster_admin->settings;
	if(w3_check_multisite()){
		$img_to_opt = 0;
		$blogs = get_sites();
		foreach( $blogs as $b ){
			$img_to_opt += $wpdb->get_var("SELECT count(ID) FROM {$wpdb->prefix}{$b->blog_id}_posts WHERE post_type='attachment'");
		} 
	}else{
		$img_to_opt = $wpdb->get_var("SELECT count(ID) FROM {$wpdb->prefix}posts WHERE post_type='attachment'");
	}
	$opt_offset = w3_get_option('w3speedup_opt_offset');
	$img_remaining = (int)$img_to_opt-(int)$opt_offset;
	if(!empty($result['enable_background_optimization']) && $img_remaining > 0){
		if ( ! wp_next_scheduled( 'w3speedster_image_optimization' ) ) {
			wp_schedule_event( time(), 'w3speedup_every_minute', 'w3speedster_image_optimization' );
		}
	}else{
		if ( wp_next_scheduled( 'w3speedster_image_optimization' ) ) {
			wp_clear_scheduled_hook('w3speedster_image_optimization');
		}
	}
	$preload_total = (int)w3_get_option('w3speedup_preload_css_total');
	$preload_created = (int)w3_get_option('w3speedup_preload_css_created');
	if($preload_total - $preload_created > 0 ){
		if ( ! wp_next_scheduled( 'w3speedup_preload_css_min' ) ) {
			wp_schedule_event( time(), 'w3speedup_every_minute', 'w3speedup_preload_css_min' );
		}
	}else{
		if ( wp_next_scheduled( 'w3speedup_preload_css_min' ) ) {
			wp_clear_scheduled_hook('w3speedup_preload_css_min') ;
		}
	}
}

function w3speedster_image_optimization_callback(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_image.php');	
    $w3_speedster_opt_img = new W3Speedster\w3speedster_optimize_image();
	if(!empty($_REQUEST['attach_id'])){
		$w3_speedster_opt_img->w3_optimize_attachment_id((int)$_REQUEST['attach_id']);
	}else{
		$w3_speedster_opt_img->w3speedster_optimize_image_callback();
	}
}
add_action('init','w3_get_cache_size_cron');
function w3_get_cache_size_cron(){
	if ( ! wp_next_scheduled( 'w3_cache_size' ) ) {
        wp_schedule_event( time(), 'hourly', 'w3_cache_size' );
    }
	if ( ! wp_next_scheduled( 'w3_check_key' ) ) {
        wp_schedule_event( time(), 'daily', 'w3_check_key' );
    }
	if ( ! wp_next_scheduled( 'w3speedster_check_cron_needs_running' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'w3speedster_check_cron_needs_running' );
	}
}

if(!empty($_REQUEST['w3_preload_css'])){
	add_action('wp_head','w3speedster_preload_css_callback');
}
if(!empty($_REQUEST['w3_put_preload_css'])){
	add_action('wp_head','w3speedster_put_preload_css_callback');
}
function w3speedster_put_preload_css_callback(){
	$w3_speedster = new W3Speedster\w3speedster(); 
	$w3_speedster->w3_put_preload_css();
	exit;
}
add_action( 'wp_ajax_w3speedster_preload_css', 'w3speedster_preload_css_ajax_callback' );
function w3speedster_preload_css_ajax_callback(){
	w3_update_option('w3speedup_critical_css_error','','no');
	w3speedster_preload_css_callback();
	$error = w3_get_option('w3speedup_critical_css_error');
	$total = (int)w3_get_option('w3speedup_preload_css_total');
	$created = (int)w3_get_option('w3speedup_preload_css_created');
	if(!empty($error)){
		echo json_encode(array('error',$error,$total,$created));
	}else{
		echo json_encode(array('success',1,$total,$created));
	}
	exit;
}
function w3_check_multisite(){
	if(function_exists('is_multisite') && is_multisite()){
		return 1;
	}else{
		return 0;
	}
}

function w3_get_option($option){
	if(w3_check_multisite()){
		global $w3_network_option;
		if(empty($w3_network_option)){
			$w3_network_option = get_site_option('w3_speedup_option', true);
		}
	}
	if(w3_check_multisite() && (is_network_admin() || empty($w3_network_option['manage_site_separately']))){
		$settings = get_site_option($option, true);
	}else{
		$settings = get_option( $option, true );
	}
	return $settings;
}
function w3_update_option($option, $value, $autoload = null){
	if(w3_check_multisite()){
		global $w3_network_option;
		if(empty($w3_network_option)){
			$w3_network_option = get_site_option('w3_speedup_option', true);
		}
	}
	if(w3_check_multisite() && (is_network_admin() || empty($w3_network_option['manage_site_separately']))){
		if(update_site_option( $option,$value,$autoload)){
			return 1;
		}else{
			return 0;
		}
	}else{
		if(update_option( $option,$value,$autoload)){
			return 1;
		}else{
			return 0;
		}
	}
}
function w3speedster_preload_css_callback(){
	$w3_speedster = new W3Speedster\w3speedster(); 
	$response = $w3_speedster->w3_generate_preload_css();
	if(!empty($response) && $response == "exists"){
		w3speedster_preload_css_callback();
	}
	if(!empty($_REQUEST['w3_preload_css'])){
		exit;
	}
}
add_action( 'w3_check_key', 'w3_check_key_callback' );
add_action( 'w3_cache_size', 'w3_cache_size_callback' );
function w3_check_key_callback(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin(); 
    $w3_speedster_admin->w3_check_license_key();
}
function w3_cache_size_callback(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin(); 
    $w3_speedster_admin->w3_cache_size_callback();
}
add_action( 'wp_ajax_w3speedster_optimize_image', 'w3speedster_add_image_optimization_schedule' );
function w3speedster_add_image_optimization_schedule(){
	
	w3speedster_image_optimization_callback();
	exit;
}

  
add_action( 'after_setup_theme', 'w3speedster_add_mobile_thumbnail' );
function w3speedster_add_mobile_thumbnail(){
	add_image_size( 'w3speedup-mobile', 595 );
}
function w3_cache_purge_action_js() { 
	if(is_user_logged_in() && current_user_can( 'w3speedster_settings' )){
?>
    <script type="text/javascript" >
		var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        jQuery(".w3-speedster-cache-purge-text, #del_js_css_cache").on( "click", function() {
            jQuery('#del_js_css_cache').attr('disabled',true);
			jQuery('#w3_speedster_cache_purge').show();
            jQuery('.cache_size').addClass('deleting');
			jQuery('.w3-speedster-cache').text('Deleting...');
            var data = {
                        'action': 'w3_speedster_cache_purge',
						'_wpnonce':'<?php echo wp_create_nonce("purge_cache");?>'
                        };
            jQuery.get(ajaxurl, data, function(response) {
                jQuery('#w3_speedster_cache_purge').hide();
                jQuery('.cache_size').removeClass('deleting');
                jQuery('.w3-speedster-cache').text('Cache Deleted!');
                jQuery('.cache_folder_size').text(response+" MB");
				jQuery('#del_js_css_cache').attr('disabled',false);
                setTimeout(() => {
                    jQuery('.w3-speedster-cache').text('W3Speedster cache');
                }, 2000);
            }).fail(function() {
                jQuery('#w3_speedster_cache_purge').hide();
                jQuery('.cache_size').removeClass('deleting');
                jQuery('.w3-speedster-cache').text('try again');
                jQuery('.cache_folder_size').text(response+" MB");
				jQuery('#del_js_css_cache').attr('disabled',false);
                setTimeout(() => {
                    jQuery('.w3-speedster-cache').text('W3Speedster cache');
                }, 2000);
            });

        });
		jQuery("#del_critical_css_cache,.w3-speedster-critical-cache-purge-text,.w3-speedster-critical-cache-purge-single-text").on( "click", function() {
			jQuery('#w3_speedster_cache_purge').show();
            jQuery('.cache_size').addClass('deleting');
            jQuery('#del_critical_css_cache').attr('disabled',true);
			jQuery('.w3-speedster-cache').text('Deleting...');
			var data_id = jQuery(this).attr("data-id");
			var data_type = jQuery(this).attr("data-type");
			var data = {
                        'action': 'w3_speedster_critical_cache_purge',
						'_wpnonce':'<?php echo wp_create_nonce("purge_critical_css");?>',
						'data_id':data_id,
						'data_type':data_type
						};

            jQuery.get(ajaxurl, data, function(response) {
                jQuery('#del_critical_css_cache').attr('disabled',false);
				jQuery('#w3_speedster_cache_purge').hide();
                jQuery('.cache_size').removeClass('deleting');
                jQuery('.w3-speedster-cache').text('Cache Deleted!');
                setTimeout(() => {
                    jQuery('.w3-speedster-cache').text('W3Speedster cache');
                }, 2000);
            }).fail(function() {
				jQuery('#del_critical_css_cache').attr('disabled',false);
				jQuery('#w3_speedster_cache_purge').hide();
                jQuery('.cache_size').removeClass('deleting');
                jQuery('.w3-speedster-cache').text('try again');
                setTimeout(() => {
                    jQuery('.w3-speedster-cache').text('W3Speedster cache');
                }, 2000);
            });

        });
    </script> <?php
	}
}
function w3_toolbar_link_to_delete_cache( $wp_admin_bar ) {

	$filesize = round(get_option('w3_speedup_filesize',false),2);
	$clear_cache_text = '';
	$clear_cache_id = '';
	if(is_page()){
		global $post;
		$clear_cache_text = 'page';
		$clear_cache_id = $post->ID;
	}elseif(is_single()){
		global $post;
		$clear_cache_text = 'post';
		$clear_cache_id = $post->ID;
	}elseif(is_archive() || is_category()){
		$clear_cache_text = 'category';
		$clear_cache_id = get_queried_object_id();
	}
	$args = array(

		'id'    => 'w3_speedster_purge_cache',

		'title' => '<div class="w3-speedster-spinner-container">
		<div id="w3_speedster_cache_purge"></div></div>
	  <style>#w3_speedster_cache_purge {
		width: 20px;
		height: 20px;
		margin: 4px 0px 0px 0px;
		background: transparent;
		border-top: 4px solid #009688;
		border-right: 4px solid transparent;
		border-radius: 50%;
		-webkit-animation: 1s spin linear infinite;
		animation: 1s spin linear infinite;
		display:none;
	  }
	  .w3-speedster-spinner-container{
	  overflow:hidden;
	  display:inline-block;
		}
	  
	  
	  
	  -webkit-@keyframes spin {
		-webkit-from {
		  -webkit-transform: rotate(0deg);
		  -ms-transform: rotate(0deg);
		  transform: rotate(0deg);
		}
		-webkit-to {
		  -webkit-transform: rotate(360deg);
		  -ms-transform: rotate(360deg);
		  transform: rotate(360deg);
		}
	  }
	  
	  @-webkit-keyframes spin {
		from {
		  -webkit-transform: rotate(0deg);
		  transform: rotate(0deg);
		}
		to {
		  -webkit-transform: rotate(360deg);
		  transform: rotate(360deg);
		}
	  }
	  
	  @keyframes spin {
		from {
		  -webkit-transform: rotate(0deg);
		  transform: rotate(0deg);
		}
		to {
		  -webkit-transform: rotate(360deg);
		  transform: rotate(360deg);
		}
	  }
	  }</style>
	 <div class="w3-speedster-cache">W3Speedster cache</div><div class="cache_size">
	 <div class="w3-speedster-cache-purge-text" data-id="0">Delete js/css cache for all pages</div>
	 '.(!empty($clear_cache_text) ? '<div class="w3-speedster-critical-cache-purge-single-text" data-type="'.$clear_cache_text.'" data-id="'.$clear_cache_id.'">Delete critical css cache for this '.$clear_cache_text.' only</div>' : '' ).'
	 <div><span>File Size</span>&nbsp;&nbsp;&nbsp;<span class="cache_folder_size">'.$filesize.'&nbsp;MB</span></div>
	 </div><style>#wp-admin-bar-w3_speedster_purge_cache{min-width:135px;}.wp-speedster-page .cache_size{display:none;}.cache_size.deleting{display:none!important;}.cache_size{position:absolute!important;color:#fff!important;background-color:#000;background: #000;min-width: 250px;}.cache_size div{padding: 2.5px 5px !important;}.cache_size:hover, .w3-speedster-cache:hover + .cache_size{display:block;}.w3-speedster-cache + .cache_size div:hover{background-color:#23282dcf;}.w3-speedster-cache{display: inline-block;
    vertical-align: top;}</style>',

		'href'  => '#',

		'meta'  => array( 'class' => 'wp-speedster-page' )

	);

	$wp_admin_bar->add_node( $args );

}


function w3speedster_role_caps(){
    $role = get_role('administrator');
	$role->add_cap('w3speedster_settings', true);
}
add_action('init', 'w3speedster_role_caps', 11);
function w3_speedster_register_network_options_page() {
	add_submenu_page('settings.php','W3speedster', 'W3speedster', 'w3speedster_settings', 'w3_speedster', 'w3_speedster_options_page');
}
function w3_speedster_register_site_options_page() {
	add_options_page('W3speedster', 'W3speedster', 'w3speedster_settings', 'w3_speedster', 'w3_speedster_options_page' );
}
function w3_speedster_options_page(){
	load_template( W3SPEEDSTER_PLUGIN_DIR . "/admin/admin.php");	
}

function w3_speedster_cache_purge_callback(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	$w3_speedster_admin->w3_speedster_cache_purge_callback();
}

function w3_speedster_critical_cache_purge_callback(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	$w3_speedster_admin->w3_speedster_critical_cache_purge_callback();
}

function add_optimize_image_custom_js(){ ?>
	<style>
	.loader {
			margin: 0px auto;
			border: 5px solid #ccc;
			border-radius: 50%;
			border-top: 5px solid #3498db;
			width: 15px;
			height: 15px;
			-webkit-animation: spin 2s linear infinite;
			animation: spin 2s linear infinite;
	}
	.loader-sec {
		display: none;
		    position: relative;
		width: 15px;
		height: 15px;
		margin: 0 auto;
		left: 35px;
		top: 17px;
	}
	/* Safari */
	@-webkit-keyframes spin {
	  0% { -webkit-transform: rotate(0deg); }
	  100% { -webkit-transform: rotate(360deg); }
	}

	@keyframes spin {
	  0% { transform: rotate(0deg); }
	  100% { transform: rotate(360deg); }
	}
	.dw-operation-sec .p-digital-button {
		display: inline-block;
		padding: 10px;
	}
	.optimize_message {
		color:#2d792d;
		display:block;
		padding-bottom: 10px;
	}
	</style>
		<script>
		var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
		jQuery(document).ready(function(){
			
			//jQuery('.optimize_media_image').click(function(){
			jQuery( "body" ).delegate( ".optimize_media_image", "click", function() {
				var img_id = jQuery( this ).attr('data-id');
				jQuery('.loader-sec').show();	
				jQuery.ajax({
					 type : "POST",
					 dataType : "json",
					 url : ajaxurl,
					 data : {
						 action: "fn_w3_optimize_media_image",
						 id: img_id							
						},
					 success: function(response) {
						   //alert(response);
						   console.log('-- response11 --', response.summary);
						   //response = jQuery.trim(response);
						   
						   
						   jQuery('.loader-sec').hide();							   
						   
						   if(response.summary == true){
							   jQuery('.optimize_message').html('Image optimize successfully.');
						   }else{
							   jQuery('.optimize_message').html('Image not Optimized.');
						   }
							jQuery('.optimize_message').show();							   
						   /* setTimeout(function(){				
								jQuery('.optimize_message').hide();	
							}, 5000); */
						}
				});				
			});	
			
			
		});
	</script>
	<?php	
}

function add_button_to_edit_media_modal_fields_area1($form_fields, $post){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	return $w3_speedster_admin->add_button_to_edit_media_modal_fields_area1($form_fields, $post);
}
function fn_w3_optimize_media_image_callback(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	$w3_speedster_admin->fn_w3_optimize_media_image_callback();
}
function w3speedster_activate_license_key(){
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	$w3_speedster_admin->w3speedster_activate_license_key();
}
add_action( 'upgrader_process_complete', 'w3_upgrade_function',10, 2);
 
function w3_upgrade_function( $upgrader_object, $options ) {
    $current_plugin_path_name = plugin_basename( __FILE__ );
    if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
       foreach($options['plugins'] as $each_plugin) {
          if ($each_plugin==$current_plugin_path_name) {
            $w3_speedster_admin = new W3Speedster\w3speedster(); 
			$w3_speedster_admin->w3_remove_cache_files_hourly_event_callback();
			
          }
       }
    }
}
function w3_load_admin_callback(){
	add_action( 'admin_bar_menu', 'w3_toolbar_link_to_delete_cache' ,999 );
	if(function_exists('is_multisite') && is_multisite()){
		add_action('network_admin_menu', 'w3_speedster_register_network_options_page' );
	}
	$options = get_site_option('w3_speedup_option', true);
	if(!is_multisite() || (function_exists('is_multisite') && is_multisite() && !empty($options['manage_site_separately']))){
		add_action('admin_menu', 'w3_speedster_register_site_options_page' );
	}
	add_action( 'admin_footer', 'w3_cache_purge_action_js' );
	add_action( 'wp_ajax_w3_speedster_cache_purge', 'w3_speedster_cache_purge_callback' );
	add_action( 'wp_ajax_w3_speedster_critical_cache_purge', 'w3_speedster_critical_cache_purge_callback');
	add_action('admin_footer', 'add_optimize_image_custom_js');		
	add_filter( 'attachment_fields_to_edit', 'add_button_to_edit_media_modal_fields_area1' , 99, 2 );
	add_action( 'wp_ajax_fn_w3_optimize_media_image', 'fn_w3_optimize_media_image_callback');
	add_action( 'wp_ajax_w3speedster_activate_license_key', 'w3speedster_activate_license_key' );
	if(!empty($_REQUEST['page']) && $_REQUEST['page'] == 'w3_speedster'){
		require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
		require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_image.php');	
		$w3_speedster_admin = new W3Speedster\w3speedster_admin(); 
		$w3_speedster_admin->launch(); 
	}
}
function w3_load_admin(){
	if(current_user_can('w3speedster_settings')){
		w3_load_admin_callback();
	}
}
if(is_admin()){
	add_action('init','w3_load_admin');
	
	
}else{
    if (defined('DOING_AJAX') && DOING_AJAX) {
	}else{
		require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_minify_css.php');
		require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_js_minify.php');
		require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_html_optimize.php');
		add_action( 'admin_bar_menu', 'w3_toolbar_link_to_delete_cache' ,999 );
		add_action( 'wp_footer', 'w3_cache_purge_action_js' );
		if(!empty($_REQUEST['testing'])){
			$upload_dir = wp_upload_dir();
			$html = file_get_contents($upload_dir['basedir'].'/w3test.html');
			$w3_optimize = new W3Speedster\w3speed_html_optimize();
			echo $w3_optimize->w3_speedster($html); exit;
		}
		
		
			
			add_action('after_setup_theme', 'w3_pre_start',11);
        
		
	}
	
}
function w3_pre_start(){
	if(function_exists('w3_change_start_optimization_hook')	){
    	add_action(w3_change_start_optimization_hook('wp'),'w3_start',11);	
    }else{
    	w3_start();
    }
}
function w3_start(){
	global $current_user;
	if(!empty($current_user) && current_user_can('edit_others_pages')){

	}else{
		$w3_optimize = new W3speedster\w3speed_html_optimize();
		$w3_optimize->w3_start_optimization_callback();
	}
}

if(is_admin()){
	$upload_dir   = wp_upload_dir();
	if(!is_file($upload_dir['basedir'].'/blank-h.png')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank-h.png",$upload_dir['basedir'].'/blank-h.png');
	}
	if(!is_file($upload_dir['basedir'].'/blank-square.png')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank-square.png",$upload_dir['basedir'].'/blank-square.png');
	}
	if(!is_file($upload_dir['basedir'].'/blank-p.png')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank-p.png",$upload_dir['basedir'].'/blank-p.png');
	}
	if(!is_file($upload_dir['basedir'].'/blank-3x4.png')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank-3x4.png",$upload_dir['basedir'].'/blank-3x4.png');
	}
	if(!is_file($upload_dir['basedir'].'/blank-4x3.png')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank-4x3.png",$upload_dir['basedir'].'/blank-4x3.png');
	}
	if(!is_file($upload_dir['basedir'].'/blank.png')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank.png",$upload_dir['basedir'].'/blank.png');
	}
	if(!is_file($upload_dir['basedir'].'/blank.mp4')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank.mp4",$upload_dir['basedir'].'/blank.mp4');
	}
	if(!is_file($upload_dir['basedir'].'/blank.mp3')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank.mp3",$upload_dir['basedir'].'/blank.mp3');
	}
	if(!is_file($upload_dir['basedir'].'/blank.pngw3.webp')){
		copy(W3SPEEDSTER_PLUGIN_DIR."assets/images/blank.pngw3.webp",$upload_dir['basedir'].'/blank.pngw3.webp');
	}
	if(!is_file($upload_dir['basedir'].'/blank.css')){
		$file = fopen($upload_dir['basedir'].'/blank.css','w');
		fwrite($file,'/*blank.css*/');
		fclose($file);
	}
}
add_action('in_plugin_update_message-w3speedster/w3speedster.php','w3speedster_plugin_update_message');
function w3speedster_plugin_update_message(){
	echo __(' License key will be required to update the plugin. To get a key, contact','w3speedster').' <a rel="_blank" href="https://w3speedster.com">'.__('here','w3speedster').'</a>.';
}