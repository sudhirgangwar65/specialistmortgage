 <?php 
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'admin/class_admin.php');
	require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_image.php');
	$w3_speedster_admin = new W3Speedster\w3speedster_admin();
	$result = $w3_speedster_admin->settings;
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/css/admin.css?ver=<?php echo rand(10,1000); ?>">
<script src="//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<style>
.tab-content > .tab-pane.active{
	opacity:1 !important;
	display:block !important;
}
tr.tr-hidden{
	display:none !important;
}
</style>
<main class="admin-speedster">
	<div class="col-md-12 top_panel_container">
		<div class="top_panel">
			<div class="col-md-6 logo_container">
				<img class="logo" src="<?php echo W3SPEEDSTER_PLUGIN_URL; ?>assets/images/w3-logo.png">
			</div>

			<div class="col-md-6 support_section">
				<div class="right_section">
					<div class="doc">
						<?php _e( 'Need help or have question', 'w3speedster' ); ?>?<br />
						<a href="https://w3speedster.com/w3speedster-documentation/" target="_blank"><?php _e( 'Check our documentation', 'w3speedster' ); ?>.</a>
					</div>

				</div>
			</div>
		</div>
	</div>
	<div class="col-md-2">
	<ul class="nav nav-tabs ">
	<li class="w3_general"><a data-toggle="tab" href="#general"><?php _e( 'General', 'w3speedster' ); ?></a></li>
	<?php if (empty($result['manage_site_separately'])){ ?>
		<li class="w3_css"><a data-toggle="tab" href="#css"><?php _e( 'Css', 'w3speedster' ); ?></a></li>
		<li class="w3_js"><a data-toggle="tab" href="#js"><?php _e( 'Javascript', 'w3speedster' ); ?></a></li>
		<li class="w3_cache"><a data-toggle="tab" href="#cache"><?php _e( 'Cache', 'w3speedster' ); ?></a></li>
		<li class="w3_opt_img"><a data-toggle="tab" href="#opt_img"><?php _e( 'Image Optimization', 'w3speedster' ); ?></a></li>
		<li class="w3_import"><a data-toggle="tab" href="#import"><?php _e( 'Import/Export', 'w3speedster' ); ?></a></li>
	<?php } ?>
	</ul>	
	
	<div class="addi-contact-info">
		<div class="need-support">
			<div class="icon-supp"><i class="fa fa-headphones" aria-hidden="true"></i></div>
				<div class="info-supp">
					<h3><?php _e( 'Need Support', 'w3speedster' ); ?></h3>
					<a class="btn-supp" href="https://w3speedster.com/contact-us/"><?php _e( 'Contact Us', 'w3speedster' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	
	
	
	<form method="post">
		<div class="tab-content col-md-10">
			<section id="general" class="tab-pane fade in active">
				<div class="header">
					<div class="heading_container">
					<h4 class="heading"><?php _e( 'General Setting', 'w3speedster' ); ?></h4>
					<h4 class="sub_heading"><?php _e( 'Optimization Level', 'w3speedster' ); ?></h4> <span class="info"><a href="https://w3speedster.com/w3speedster-documentation/"><?php _e( 'More info', 'w3speedster' ); ?>?</a></span>
					</div>
					 <div class="icon_container"><span class="h-icon"><img src="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/images/general-setting-icn.png"></span></div>
				</div>
				<table class="form-table">

				<tbody>
					<tr>
						<th scope="row"><?php _e( 'License Key', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Activate key to get updates and access to all features of the plugin.', 'w3speedster' ); ?></span></th>
						<td>
							<input type="text" name="license_key" value="<?php echo !empty($result['license_key']) ? $result['license_key'] : '';?>" style="width:300px;margin-right:20px;">
							<input type="hidden" name="w3_api_url" value="<?php echo !empty($result['w3_api_url']) ? $result['w3_api_url'] : '';?>">
							<input type="hidden" name="is_activated" value="<?php echo !empty($result['is_activated']) ? $result['is_activated'] : '';?>">
							<input type="hidden" name="ws_action" value="cache">
							<?php if(!empty($result['license_key']) && !empty($result['is_activated'])){
								?>
								<i class="fa fa-check-circle-o" aria-hidden="true"></i>
								<?php
							}else{ ?>
								<button class="activate-key" type="button"><?php _e( 'Activate', 'w3speedster' ); ?></button>
							<?php }
							?>
							
						</td>
					</tr>
					<?php /*<tr class="col-md-6 no-info">
						<th scope="row"><?php _e( 'Enable html cache', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enable to turn on html cache.' ); ?></span></th>
						<td><input type="checkbox" name="html_cache" <?php if (!empty($result['html_cache']) && $result['html_cache'] == "on") echo "checked";?> ></td>
					</tr> */ ?>
					<?php 
					if(function_exists('is_multisite') && is_multisite() && is_network_admin()){ ?>
					<tr>
						<th scope="row"><?php _e( 'Manage each site separately', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enable this option to enter separate settings for each site. Plugin page will then be available in the backend of every site.', 'w3speedster' ); ?></span></th>
						<td>
							<input type="checkbox" name="manage_site_separately" <?php if (!empty($result['manage_site_separately'])) echo "checked";?> >
						</td>
					</tr>
					<?php } ?>
					<?php 
					$hidden_class = '';
					if (!empty($result['manage_site_separately'])){ 
						$hidden_class = 'tr-hidden';
					} ?>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Turn ON optimization', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Site will start to optimize. All optimization settings will be applied.', 'w3speedster' ); ?></span></th>
						<td>
							<input type="checkbox" name="optimization_on" <?php if (!empty($result['optimization_on']) && $result['optimization_on'] == "on") echo "checked";?> >
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Optimize Pages with Query Parameters', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'It will optimize pages with query parameters. Recommended only for servers with high performance', 'w3speedster' ); ?></span></th>
						<td>
							<input type="checkbox" name="optimize_query_parameters" <?php if (!empty($result['optimize_query_parameters'])) echo "checked";?> >
							
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Optimize pages when User Logged In', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'It will optimize pages when users are logged in. Recommended only for servers with high performance', 'w3speedster' ); ?></span></th>
						<td>
							<input type="checkbox" name="optimize_user_logged_in" <?php if (!empty($result['optimize_user_logged_in'])) echo "checked";?> >
							
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Separate javascript and css cache for mobile', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'It will create separate javascript and css cache for mobile', 'w3speedster' ); ?></span></th>
						<td>
							<input type="checkbox" name="separate_cache_for_mobile" <?php if (!empty($result['separate_cache_for_mobile'])) echo "checked";?> >
							
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'CDN url', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter CDN url with http or https' ); ?></span></th>
						<td><input type="text" name="cdn" placeholder="<?php _e( 'Please Enter CDN url here', 'w3speedster' ); ?>" value="<?php if(!empty($result['cdn'])) echo $result['cdn'];?>">
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Exclude file extensions from cdn', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter extension separated by comma which are to excluded from CDN. For eg. (.woff, .eot)' ); ?></span></th>
						<td><input type="text" name="exclude_cdn" placeholder="<?php _e( 'Please Enter extensions separated by comma ie .jpg, .woff', 'w3speedster' ); ?>" value="<?php if(!empty($result['exclude_cdn'])) echo $result['exclude_cdn'];?>">
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Exclude path from cdn', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter path separated by comma which are to excluded from CDN. For eg. (/wp-includes/)' ); ?></span></th>
						<td><input type="text" name="exclude_cdn_path" placeholder="<?php _e( 'Please Enter extensions separated by comma ie .jpg, .woff', 'w3speedster' ); ?>" value="<?php if(!empty($result['exclude_cdn_path'])) echo $result['exclude_cdn_path'];?>">
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Enable leverage browsing cache', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enable to turn on leverage browsing cache.' ); ?></span></th>
						<td><input type="checkbox" name="lbc" <?php if (!empty($result['lbc']) && $result['lbc'] == "on") echo "checked";?> ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Enable Gzip compression', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enable to turn on Gzip compresssion.' ); ?></span></th>
						<td><input type="checkbox" name="gzip" <?php if (!empty($result['gzip']) && $result['gzip'] == "on") echo "checked";?> ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Remove query parameters', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enable to remove query parameters from resources.' ); ?></span></th>
						<td><input type="checkbox" name="remquery" <?php if (!empty($result['remquery']) && $result['remquery'] == "on") echo "checked";?> ></td>
					</tr>
					
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Enable lazy Load', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'This will enable lazy loading of resources.' ); ?></span></th>
						<td><span class="td-span">Image</span><input type="checkbox" name="lazy_load" <?php if (!empty($result['lazy_load']) && $result['lazy_load'] == "on") echo "checked";?> ><span class="td-span">Iframe</span><input type="checkbox" name="lazy_load_iframe" <?php if (!empty($result['lazy_load_iframe']) && $result['lazy_load_iframe'] == "on") echo "checked";?> ><span class="td-span">Video</span><input type="checkbox" name="lazy_load_video" <?php if (!empty($result['lazy_load_video']) && $result['lazy_load_video'] == "on") echo "checked";?> ><span class="td-span">Audio</span><input type="checkbox" name="lazy_load_audio" <?php if (!empty($result['lazy_load_audio']) && $result['lazy_load_audio'] == "on") echo "checked";?> >
						</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">	
						<th scope="row"><?php _e( 'Start Lazy load Images, Videos, Iframes pixels below the screen', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter pixels to start lazy loading of resources which are below the viewable page. For eg. 200' ); ?></span></th>
						<td><input style="width:50px;" type="text" name="lazy_load_px" value="<?php echo !empty($result['lazy_load_px']) ? $result['lazy_load_px'] : 200;?>" > &nbsp;px</td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Enable Webp support', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'This will convert and render images in webp. Need to start image optimization in image optimization tab' ); ?></span></th>
						<td><span class="td-span">Jpg</span><input type="checkbox" name="webp_jpg" <?php if (!empty($result['webp_jpg']) && $result['webp_jpg'] == "on") echo "checked";?> ><span class="td-span">Png</span><input type="checkbox" name="webp_png" <?php if (!empty($result['webp_png']) && $result['webp_png'] == "on") echo "checked";?> >
						</td>
						
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">	
						<th scope="row"><?php _e( 'Webp image quality', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( '90 recommended' ); ?></span></th>
						<td><input style="width:50px;" type="text" name="webp_quality" value="<?php echo !empty($result['webp_quality']) ? $result['webp_quality'] : 90;?>" ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Optimize jpg/png images', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enable to optimize jpg and png images.' ); ?></span></th>
						<td><input type="checkbox" name="opt_jpg_png" <?php if (!empty($result['opt_jpg_png']) && $result['opt_jpg_png'] == "on") echo "checked";?> ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">	
						<th scope="row"><?php _e( 'Jpg png image quality', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( '90 recommended' ); ?></span></th>
						<td><input style="width:50px;" type="text" name="img_quality" value="<?php echo !empty($result['img_quality']) ? $result['img_quality'] : 90;?>" ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Optimize images via wp-cron', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Optimize images via wp-cron.' ); ?></span></th>
						<td><input type="checkbox" name="enable_background_optimization" <?php if (!empty($result['enable_background_optimization']) && $result['enable_background_optimization'] == "on") echo "checked";?> ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Optimize images on the go', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Automatically optimize images when site pages are crawled. Recommended to turn off after initial first crawl of all pages.' ); ?></span></th>
						<td><input type="checkbox" name="opt_img_on_the_go" <?php if (!empty($result['opt_img_on_the_go']) && $result['opt_img_on_the_go'] == "on") echo "checked";?> ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Automatically optimize images on upload', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Automatically optimize new images on upload. Turn off if upload of images is taking more than expected.' ); ?></span></th>
						<td><input type="checkbox" name="opt_upload" <?php if (!empty($result['opt_upload']) && $result['opt_upload'] == "on") echo "checked";?> ></td>
					</tr>
					<tr class="col-md-6 no-info <?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Responsive images', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Load smaller images on mobile to reduce load time.' ); ?></span></th>
						<td><input type="checkbox" name="resp_bg_img" <?php if (!empty($result['resp_bg_img']) && $result['resp_bg_img'] == "on") echo "checked";?> ></td>
					</tr>
					
					<tr class="<?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Preload Resources', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter url of the Resources, which are to be preloaded.' ); ?>.</span></th>
						<td><textarea name="preload_resources" rows="10" cols="16" placeholder="<?php _e( 'Please Enter Resource Url', 'w3speedster' ); ?>" ><?php if (!empty($result['preload_resources'])) echo stripslashes($result['preload_resources']);?></textarea></td>
					</tr>
					
					<tr class="<?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Exclude images from Lazy Loading', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter any matching text of image tag to exclude from lazy loading. For more than one exclusion, enter in a new line. For eg. (class / Id / url / alt). Images will still continue to be optimized and rendered in webp if respective settings are turned on' ); ?>.</span></th>
						<td><textarea name="exclude_lazy_load" rows="10" cols="16" placeholder="<?php _e( 'Please Enter matching text of the image here', 'w3speedster' ); ?>" ><?php if (!empty($result['exclude_lazy_load'])) echo stripslashes($result['exclude_lazy_load']);?></textarea></td>
					</tr>
					
					<tr class="<?php echo $hidden_class; ?>">
						<th scope="row"><?php _e( 'Exclude Pages From Optimization', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter slug of the url to exclude from optimization. For  eg. (/blog/). For home page, enter home url' ); ?>.</span></th>
						<td><textarea name="exclude_pages_from_optimization" rows="10" cols="16" placeholder="<?php _e( 'Please Enter Page Url', 'w3speedster' ); ?>" ><?php if(!empty($result['exclude_pages_from_optimization'])) echo stripslashes($result['exclude_pages_from_optimization']);?></textarea></td>
					</tr>
					<tr class="<?php echo $hidden_class; ?>">
					    <th scope="row"><?php _e( 'Cache Path', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter path where cache can be stored. Leave empty for default path' ); ?>.</span></th>
						<td><input type="text" name="cache_path" placeholder="<?php _e( 'Please Enter full cache path', 'w3speedster' ); ?>" value="<?php echo !empty($result['cache_path']) ? $result['cache_path'] : ''; ?>"><div><?php _e( 'Default cache path', 'w3speedster' ); ?>:&nbsp;&nbsp;<?php echo $w3_speedster_admin->add_settings['wp_content_path'].'/cache'; ?></div></td> 
					</tr>
					<tr>
						<th scope="row"><input type="submit" value="Save Changes"></th>
						<td></td>
					</tr>
				</tbody>
			</table>
			<script>
			jQuery('.activate-key').click(function(){
				var key = jQuery("[name='license_key']");
				if(key.val() == ''){
					alert("Please enter key");
					return false;
				}
				jQuery(this).prop('disabled',true);
				activate_license_key(key);
				
			});
			function activate_license_key(key){
				
				jQuery.ajax({
					url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
					data: {
						'action': 'w3speedster_activate_license_key',
						'key' : key.val()
					},
					success:function(data) {
						// This outputs the result of the ajax request
						data=jQuery.parseJSON( data );
						if(data[1] == 'verified'){
							jQuery('[name="is_activated"]').val(data[2]);
							key.closest('form').submit();
						}else{
							alert("Invalid key");
						}
						jQuery('.activate-key').prop('disabled',false);
						console.log(data[1]);
						console.log(data);
					},
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
			}
		</script>
		</section>
		<section id="css">
		<div class="header">
		<div class="heading_container no_subhead">
		<h4 class="heading"><?php _e( 'CSS Optimization', 'w3speedster' ); ?></h4>
		
		<span class="info"><a href="https://w3speedster.com/w3speedster-documentation/"><?php _e( 'More info', 'w3speedster' ); ?>?</a></span>
		</div>
		<div class="icon_container"> <span class="h-icon"><img src="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/images/css-h.png"></span></div>
		</div><table class="form-table">

				<tbody>
				<tr>
						<th scope="row"><?php _e( 'Enable css minification', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Turn on to optimize css', 'w3speedster' ); ?>.</span></th>
						<td><input type="checkbox" name="css" <?php if (!empty($result['css']) && $result['css'] == "on") echo "checked";?> ></td>
						<td></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Load critical Css', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Preload generated crictical css', 'w3speedster' ); ?>.</span></th>
						<td><input type="checkbox" name="load_critical_css" <?php if (!empty($result['load_critical_css']) && $result['load_critical_css'] == "on") echo "checked";?> ></td>
						<th scope="row"><?php _e( 'Load critical Css in style Tag', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Preload generated crictical css in style tag', 'w3speedster' ); ?>.</span></th>
						<td><input type="checkbox" name="load_critical_css_style_tag" <?php if (!empty($result['load_critical_css_style_tag']) && $result['load_critical_css_style_tag'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<?php $preload_total = (int)w3_get_option('w3speedup_preload_css_total');
							 $preload_created = (int)w3_get_option('w3speedup_preload_css_created');
						?>							 
						<td><button type="button" id="create_critical_css" <?php echo (empty($result['load_critical_css'])) ? 'disabled="true"' : ''; ?>><?php _e( 'Create Critical Css Now', 'w3speedster' ); ?></button>
						<?php 
						if($result['license_key']=="" || $result['is_activated']==""){
							echo '<span class="non_licensed"><b style="color:red;"> * Critical Css of only homepage will be generated <br> <a href="https://w3speedster.com/">*<u> GO PRO </u></a></b></span>';
						} 
						?>
					</td>
						<td><span class="preload_created_css"><?php echo $preload_created;?></span> created of <span class="preload_total_css"><?php echo $preload_total; ?></span> pages crawled</td>
						<td><textarea rows="1" cols="100" class="preload_error_css"><?php echo (empty($result['load_critical_css'])) ? __( '*Please enable load critical css and save to start generating critical css', 'w3speedster' ) : w3_get_option('w3speedup_critical_css_error');?></textarea></td>
						<style>
						#create_critical_css{
							margin-bottom:25px;
						}
						.meter {
							  box-sizing: content-box;
							  height: 10px; 
							  position: relative;
							  margin: -70px 0 20px 0; 
							  background: #ddd;
							  border-radius: 25px;
							  padding: 10px;
							  box-shadow: inset 0 -1px 1px rgba(255, 255, 255, 0.3);
							}
							.meter > span {
							  display: block;
							  height: 100%;
							  border-top-right-radius: 8px;
							  border-bottom-right-radius: 8px;
							  border-top-left-radius: 20px;
							  border-bottom-left-radius: 20px;
							  background-color: rgb(43, 194, 83);
							  background-image: linear-gradient(
								center bottom,
								rgb(43, 194, 83) 37%,
								rgb(84, 240, 84) 69%
							  );
							  box-shadow: inset 0 2px 9px rgba(255, 255, 255, 0.3),
								inset 0 -2px 6px rgba(0, 0, 0, 0.4);
							  position: relative;
							  overflow: hidden;
							}
							.meter > span:after,
							.animate > span > span {
							  content: "";
							  position: absolute;
							  top: 0;
							  left: 0;
							  bottom: 0;
							  right: 0;
							  background-image: linear-gradient(
								-45deg,
								rgba(255, 255, 255, 0.2) 25%,
								transparent 25%,
								transparent 50%,
								rgba(255, 255, 255, 0.2) 50%,
								rgba(255, 255, 255, 0.2) 75%,
								transparent 75%,
								transparent
							  );
							  z-index: 1;
							  background-size: 50px 50px;
							  animation: move 2s linear infinite;
							  border-top-right-radius: 8px;
							  border-bottom-right-radius: 8px;
							  border-top-left-radius: 20px;
							  border-bottom-left-radius: 20px;
							  overflow: hidden;
							}

							.animate > span:after {
							  display: none;
							}

							@keyframes move {
							  0% {
								background-position: 0 0;
							  }
							  100% {
								background-position: 50px 50px;
							  }
							}

							.critical-css-bar{
								display:none;
							}

						</style>
						<script>
						jQuery(document).ready(function(){
								jQuery('#create_critical_css').click(function(){
									jQuery(this).prop('disabled',true);
									jQuery('.critical-css-bar').show();
									create_critical_css();
								});
							});
							function create_critical_css(){
								jQuery('.preload_error_css').html('');
								jQuery.ajax({
									url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
									data: {
										'action': 'w3speedster_preload_css'
									},
									success:function(data) {
										data = jQuery.parseJSON( data );
										console.log(data);
										if(data[0] == 'success' || (data[0] == 'error' && (data[1] == 'process already running' || data[1].indexOf('no stylesheets found') > -1))){
											jQuery('.preload_total_css').html(data[2]);
											jQuery('.preload_created_css').html(data[3]);
											jQuery('.critical-css-bar .meter span').css('width',(parseFloat(data[3])/parseFloat(data[2])*100)+'%');
											if(data[2] > data[3]){
												console.log("next scheduled");
												setTimeout(create_critical_css,30000);
											}else{
												jQuery('.critical-css-bar').hide();
											}
										}else{
											jQuery('.preload_error_css').html(data[1]);
											jQuery('#create_critical_css').prop('disabled',true);
											jQuery('.critical-css-bar').hide();
										}
									},
									error: function(errorThrown){
										console.log(errorThrown);
									}
								});
							}
						</script>
					</tr>
					<tr class="no-line critical-css-bar">
						<td>
							<div class="meter">
								<span style="width: <?php echo !empty($preload_total) ? ($preload_created/$preload_total*100).'%' : 0;?>"></span>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Exclude link tag css from minification', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter matching text of css link url, which are to be excluded from css optimization. Each Exclusion to be entered in a new line', 'w3speedster' ); ?>.</span></th>
						<td><textarea name="exclude_css" rows="10" cols="16" placeholder="<?php _e( 'Please Enter part of link tag css here', 'w3speedster' ); ?>" ><?php if (!empty($result['exclude_css'])) echo $result['exclude_css'];?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Force lazy load link tag css', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter matching text of css link url, which are forced to be lazyloaded. Each Exclusion to be entered in a new line', 'w3speedster' ); ?>.</span></th>
						<td><textarea name="force_lazyload_css" rows="10" cols="16" placeholder="<?php _e( 'Please Enter part of link tag css here', 'w3speedster' ); ?>" ><?php if (!empty($result['force_lazyload_css'])) echo $result['force_lazyload_css'];?></textarea></td>
					</tr>
						
					<tr>
						<th scope="row"><?php _e( 'Combine Google fonts', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Turn on to combine all google fonts', 'w3speedster' ); ?>.</span></th>
						<td><input type="checkbox" name="google_fonts" <?php if (!empty($result['google_fonts']) && $result['google_fonts'] == "on") echo "checked";?> ></td>
					</tr>
					<th scope="row"><?php _e( 'Delay google fonts by', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter in seconds to delay loading of combined google fonts', 'w3speedster' ); ?>.</span></th>
						<td>
						<input type="number" step="any" name="google_fonts_delay_load" value="<?php echo !empty($result['google_fonts_delay_load']) ? $result['google_fonts_delay_load'] : 2 ;?>" >
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Load Style tag in head to avoid CLS', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter matching text of style tag, which are to be loaded in the head. Each style tag to be entered in a new line', 'w3speedster' ); ?></span></th>
						<td><textarea name="load_style_tag_in_head" rows="10" cols="16" placeholder="<?php _e( 'Please Enter style tag text.', 'w3speedster' ); ?>" ><?php if (!empty($result['load_style_tag_in_head'])) echo stripslashes($result['load_style_tag_in_head']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Exclude page from Load Combined Css', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter slug of the page to exclude from css optimization', 'w3speedster' ); ?></span></th>
						<td><textarea name="exclude_page_from_load_combined_css" rows="10" cols="16" placeholder="<?php _e( 'Please Enter css Page Url.', 'w3speedster' ); ?>" ><?php if (!empty($result['exclude_page_from_load_combined_css'])) echo stripslashes($result['exclude_page_from_load_combined_css']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Custom css to load with preload css', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter custom css which works only when css optimization is applied', 'w3speedster' ); ?>.</span></th>
						<td><textarea name="custom_css" rows="10" cols="16" placeholder="<?php _e( 'Please Enter css without the style tag.', 'w3speedster' ); ?>" ><?php if (!empty($result['custom_css'])) echo stripslashes($result['custom_css']);?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><input type="submit" value="<?php _e( 'Save Changes', 'w3speedster' ); ?>"></th>
						<td></td>
					</tr>
				</tbody>
			</table>
		</section>
		<section id="js" class="white-bg-speedster">
			<div class="header">
			<div class="heading_container no_subhead">
				<h4 class="heading"><?php _e( 'Javascript Optimization', 'w3speedster' ); ?></h4>
				
				</span><span class="info-display"><a href="https://w3speedster.com/w3speedster-documentation/"><?php _e( 'More info', 'w3speedster' ); ?>?</a></span></div> 
				<div class="icon_container"><span class="h-icon"><img src="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/images/js-flat-js-dashboard.png"></span></div>
			</div>
			<table class="form-table">

				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Enable js minification', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Turn on to optimize javascript', 'w3speedster' ); ?></span></th>
						<td><input type="checkbox" name="js" <?php if (!empty($result['js']) && $result['js'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Exclude Javascript tags from combine', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter matching text of javascript url, which are to be excluded from javascript optimization. Each exclusion to be entered in new line', 'w3speedster' ); ?>.</span></th>
						<td><textarea name="exclude_javascript" rows="10" cols="16" placeholder="<?php _e( 'Please Enter matching text of the javascript here', 'w3speedster' ); ?>" ><?php if (!empty($result['exclude_javascript'])) echo $result['exclude_javascript'];?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Preload Custom Javascript', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter javascript code which needs to be loaded before page load.', 'w3speedster' ); ?></span></th>
						<td><textarea name="custom_javascript" rows="10" cols="16" placeholder="<?php _e( 'Please javascript without script tag', 'w3speedster' ); ?>" ><?php if (!empty($result['custom_javascript'])) echo stripslashes($result['custom_javascript']);?></textarea></td>
						<td class="top"><?php _e( 'Load as file', 'w3speedster' ); ?>&nbsp;<input type="checkbox" name="custom_javascript_file" <?php if (!empty($result['custom_javascript_file']) && $result['custom_javascript_file'] == "on") echo "checked";?> >&nbsp;&nbsp;<?php _e( 'Defer', 'w3speedster' ); ?>&nbsp;<input type="checkbox" name="custom_javascript_defer" <?php if (!empty($result['custom_javascript_defer']) && $result['custom_javascript_defer'] == "on") echo "checked";?> ></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Exclude Inline Javascript from combine', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter matching text of inline script url, which needs to be excluded from deferring of javascript. Each exclusion to be entered in a new line', 'w3speedster' ); ?>.</span></th>
						<td><textarea name="exclude_inner_javascript" rows="10" cols="16" placeholder="<?php _e( 'Please Enter matching text of the inline javascript here', 'w3speedster' ); ?>" ><?php if (!empty($result['exclude_inner_javascript'])) echo stripslashes($result['exclude_inner_javascript']);?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Force lazy load Javascript', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter matching text of inline javascript which needs to be forced to lazyload. Each lazyload javascript to be entered in a new line ', 'w3speedster' ); ?></span></th>
						<td><textarea name="force_lazy_load_inner_javascript" rows="10" cols="16" placeholder="<?php _e( 'Please Enter matching text of the inline javascript here', 'w3speedster' ); ?>" ><?php if (!empty($result['force_lazy_load_inner_javascript'])) echo stripslashes($result['force_lazy_load_inner_javascript']);?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Load Combined Javascript', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Choose when to load combined javascript', 'w3speedster' ); ?></span></th>
						<td><select name="load_combined_js">
						<option value="on_page_load" <?php echo !empty($result['load_combined_js']) && $result['load_combined_js'] == 'on_page_load' ? 'selected' : '' ;?>><?php _e( 'On Page Load', 'w3speedster' ); ?></option>
						<option value="after_page_load" <?php echo !empty($result['load_combined_js']) && $result['load_combined_js'] == 'after_page_load' ? 'selected' : '' ;?>><?php _e( 'After Page Load', 'w3speedster' ); ?></option>
						</select>
						</td>
						
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Exclude page from Javascript Optimization', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter slug of the page to exclude from javascript optimization', 'w3speedster' ); ?></span></th>
						<td><textarea name="exclude_page_from_load_combined_js" rows="10" cols="16" placeholder="<?php _e( 'Please Enter css Page Url', 'w3speedster' ); ?>" ><?php if (!empty($result['exclude_page_from_load_combined_js'])) echo stripslashes($result['exclude_page_from_load_combined_js']);?></textarea>
						</td>
						
					</tr>
					
					<tr>
						<th scope="row"><?php _e( 'Custom Javascript', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter javascript which needs to be loaded with combined javascript', 'w3speedster' ); ?>.</span></th>
						<td><textarea name="custom_js" rows="10" cols="16" placeholder="<?php _e( 'Please Enter js without the script tag', 'w3speedster' ); ?>" ><?php if (!empty($result['custom_js'])) echo stripslashes($result['custom_js']);?></textarea>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><input type="submit" value="<?php _e( 'Save Changes', 'w3speedster' ); ?>"></th>
						<td></td>
					</tr>
				</tbody>
			</table>
		</section>
		<section id="cache" class="tab-pane fade">
		<div class="header">
		<div class="heading_container no_subhead">
		<h4 class="heading"><?php _e( 'Cache', 'w3speedster' ); ?></h4>
		
		<span class="info"><a href="https://w3speedster.com/w3speedster-documentation/"><?php _e( 'More info', 'w3speedster' ); ?>?</a></span>
		</div>
		<div class="icon_container"> <span class="h-icon"><img src="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/images/cache-icon.png"></span></div>
		</div><table class="form-table">

				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Delete js/css cache', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Delete javascript and css combined and minified files', 'w3speedster' ); ?>.</span></th>
						<td><button type="button" id="del_js_css_cache"><?php _e( 'Delete Now', 'w3speedster' ); ?></button></td>
						<td></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Delete critical css cache', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Delete critical css cache only when you have made any changes to style. This may take considerable amount of time to regenerate depending upon the pages on the site', 'w3speedster' ); ?>.</span></th>
						<td><button type="button" id="del_critical_css_cache"><?php _e( 'Delete Now', 'w3speedster' ); ?></button></td>
						<td></td>
					</tr>
					
				</tbody>
			</table>
			<script>
			</script>
		</section>
	</form>
	<section id="opt_img" class="tab-pane fade">
	<div class="header">
	<div class="heading_container no_subhead">
		<h4 class="heading"><?php _e( 'Image Optimization', 'w3speedster' ); ?></h4>
		
<span class="info"><a href="https://w3speedster.com/w3speedster-documentation/"><?php _e( 'More info', 'w3speedster' ); ?>?</a></span></div>
<div class="icon_container"> <span class="h-icon"><img src="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/images/img-optimzation-icn.png"></span></div>
		</div>
		
		<?php
			$img_to_opt = 1;
			if(w3_check_multisite()){
				
				$blogs = get_sites();
				foreach( $blogs as $b ){
					$img_to_opt += $wpdb->get_var("SELECT count(ID) FROM {$wpdb->base_prefix}{$b->blog_id}_posts WHERE post_type='attachment'");
				} 
			}else{
				$img_to_opt = $wpdb->get_var("SELECT count(ID) FROM {$wpdb->prefix}posts WHERE post_type='attachment'");
			}
			$opt_offset = w3_get_option('w3speedup_opt_offset');
			$img_remaining = (int)$img_to_opt-(int)$opt_offset;
			if(!empty($result['enable_background_optimization']) && $img_remaining > 0){
				if ( ! wp_next_scheduled( 'w3speedster_image_optimization' ) ) {
					wp_schedule_event( time(), 'w3speedster_every_minute', 'w3speedster_image_optimization' );
				}
			}else{
				if ( wp_next_scheduled( 'w3speedster_image_optimization' ) ) {
					wp_clear_scheduled_hook('w3speedster_image_optimization');
				}
			}
		?>
		<h2><?php echo ($img_remaining <= 0) ? __( 'Great Work!, all images are optimized', 'w3speedster' ) : __( 'Images to be optimized', 'w3speedster' ).' - <span class="progress-number">'.($img_remaining).'</span>'; ?></h2>
		<div class="progress-container"><div class="progress" style="<?php echo 'width:'.number_format((100-($img_remaining/$img_to_opt*100)),1).'%'?>"><?php echo '<span class="progress-percent">'.number_format((100-($img_remaining/$img_to_opt*100)),1).'%</span>'; ?></div></div>
		<?php
		if(empty($result['license_key']) || empty($result['is_activated']))	{
			echo '<span class="non_licensed"><b style="color:red;">  * Starting 500 images will be optimized <br><br> <a href="https://w3speedster.com/">*<u> GO PRO </u></a> </b></span><br><br>';
		} 
		?>
			<button class="start_image_optimization <?php echo ($img_remaining <= 0) ? 'restart' : '';?>" type="button"><?php echo ($img_remaining <= 0) ? __( 'Start image optimization again', 'w3speedster' ) : __( 'Start image optimization', 'w3speedster' );?></button>
		<script>
			var start_optimization = 0;
			var offset = 0;
			var img_to_opt = <?php echo $img_to_opt; ?>;
			jQuery('.start_image_optimization').click(function(){
				if(!start_optimization){
					if(jQuery(this).hasClass('restart')){
						start_optimization = 2;
					}else{
						start_optimization = 1;
					}
					jQuery(this).hide();
					do_optimization(start_optimization);
					console.log("optimization_start");
				}					
			});
			function do_optimization(opt){
				jQuery.ajax({
					url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
					data: {
						'action': 'w3speedster_optimize_image',
						'start_type' : opt
					},
					success:function(data) {
						// This outputs the result of the ajax request
						if(data && data != 'optimization running'){
							data=jQuery.parseJSON( data );
							console.log(data,offset);
							if(data.offset == -1){
								setTimeout(function(){
									do_optimization(1);
								},100);
							}else if(offset != data.offset){
								offset = data.offset;
								percent = (offset/img_to_opt*100);
								jQuery('.progress-container .progress').css('width',percent.toFixed(1)+"%");
								jQuery('.progress-container .progress .progress-percent').html(percent.toFixed(1)+"%");
								jQuery('.progress-number').html(img_to_opt - offset);
								setTimeout(function(){
									do_optimization(1);
								},100);
							}
						}else{
							setTimeout(function(){
								do_optimization(1);
							},100);
						}
					},
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
			}
		</script>
	</section> 
	<section id="import" class="tab-pane fade">
	<div class="header">
	<div class="heading_container no_subhead">
		<h4 class="heading"><?php _e( 'Import / Export', 'w3speedster' ); ?></h4>
		
<span class="info"><a href="https://w3speedster.com/w3speedster-documentation/"><?php _e( 'More info', 'w3speedster' ); ?>?</a></span></div>
<div class="icon_container"> <span class="h-icon"><img src="<?php echo W3SPEEDSTER_PLUGIN_URL;?>assets/images/import-export-icon.png"></span></div>
		</div>
		
			<table class="form-table">
				<tbody>
					<form id="import_form" method="post">
					<tr>
						<th scope="row"><?php _e( 'Import Settings', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Enter exported json code from W3speedster plugin import/export page', 'w3speedster' ); ?>.</span></th>
						<td><textarea id="import_text" name="import_text" rows="10" cols="16" placeholder="<?php _e( 'Enter json code', 'w3speedster' ); ?>" ></textarea>
						<button id="import_button" type="button">Import</button>
						</td>
					</tr>
					</form>
					<?php 
					$export_setting = $result;
					$export_setting['license_key'] = '';
					$export_setting['is_activated'] = '';
					?>
					<tr>
						<th scope="row"><?php _e( 'Export Settings', 'w3speedster' ); ?><span class="info"></span><span class="info-display"><?php _e( 'Copy the code and save it in a file for future use', 'w3speedster' ); ?>.</span></th>
						<td><textarea rows="10" cols="16"><?php if (!empty($export_setting)) echo json_encode($export_setting);?></textarea>
						</td>
					</tr>
					
					
				</tbody>
			</table>
		
		
	</section> 
	
	
	
</div>

</main>
		

<script>
var custom_css_cd = 0;
var custom_js_cd = 0;
function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}
jQuery(document).ready(function(){
	jQuery('#import_button').click(function(){
		var text = jQuery("#import_text").val();
		if(!IsJsonString(text)){
			alert("Data is courrpted, please check and enter again.");
		}
		jQuery('#import_form').submit();
	});
	jQuery('.w3_css').click(function(){
		if(!custom_css_cd){
			custom_css_cd = 1;
			setTimeout(function(){wp.codeEditor.initialize(jQuery('[name="custom_css"]'), cm_settings.codeCss);},300);
		}
	});
	jQuery('.w3_js').click(function(){
		console.log("js click");
		if(!custom_js_cd){
			custom_js_cd = 1;
			setTimeout(function(){
				wp.codeEditor.initialize(jQuery('[name="custom_javascript"]'), cm_settings.codeJs);
				wp.codeEditor.initialize(jQuery('[name="custom_js"]'), cm_settings.codeJs);
			},300);
		}
	});
	var hash = window.location.hash;
	if(hash){
		jQuery(hash).prop("checked","checked");
	}
	jQuery('[name="tabs"]').click(function(){
		window.location.hash = jQuery(this).attr("id");
	});
	jQuery('.add_more_image').click(function(){
		var index = jQuery(this).parents('#w3_opt_img_content').find('.image_src_field').length ;
		
		var $html = '<tr class="image_src_field"><td style="width:70%; padding-left:0px;"><input type="text" name="optimiz_images['+index+'][src]" placeholder="Please Enter Img Src" value=""></td><td style="padding-left:0px;"><input type="text" name="optimiz_images['+index+'][width]" placeholder="Please Enter Image Width" value=""></td><td class="remove_image_field" style="width:5%; cursor:pointer;">X</td></tr>';
		
		jQuery(this).parents('.image_add_more_field').before($html);				
	});

	jQuery('.add_more_combine_image').click(function(){
		
		var index =  jQuery(this).parents('#w3_opt_img_combin_content').find('.image_src_field').length ;
		//alert(index);
		
		var $html = '<tr class="image_src_field"><td style="width:70%; padding-left:0px;"><input type="text" name="combine_images['+index+'][src]" placeholder="Please Enter Img Src" value=""></td><td style="padding-left:0px;"><input type="text" name="combine_images['+index+'][position]" placeholder="Please Enter Image Width" value=""></td><td class="remove_image_field" style="width:5%; cursor:pointer;">X</td></tr>';
		
		jQuery(this).parents('.image_add_more_field').before($html);				
	});

	//jQuery('.remove_image_field').click(function(){
	jQuery( "table" ).delegate( ".remove_image_field", "click", function() {
		jQuery(this).parents('.image_src_field').remove();
	});

	var url = document.location.href;
	url=url.split('#')['0'];
	jQuery("ul li a").click(function(e) {
		var url1 = url+jQuery(this).attr('href');
		window.location = url1;
	});
	var hash = window.location.hash;
	if(hash){
		jQuery('.nav-tabs a[href="' + hash + '"]').tab('show');
	}else {
        jQuery('.nav-tabs a[href="#general"]').tab('show');
    }
});
</script>