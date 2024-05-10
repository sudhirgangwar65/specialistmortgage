<?php
namespace W3speedster;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class w3speedster_admin extends w3speedster{
    function launch(){
		
		if(!empty($_REQUEST['import_text'])){
			$import_text = (array)json_decode($_REQUEST['import_text']);
			if($import_text !== null){
				w3_update_option( 'w3_speedup_option',  $import_text, 'no');
				add_action( 'admin_notices', array($this,'w3_admin_notice_import_success') );
			}else{
				add_action( 'admin_notices', array($this,'w3_admin_notice_import_fail') );
			}
		}
		
		if(!empty($_REQUEST['page']) && $_REQUEST['page'] == 'w3_speedster'){
			add_action('admin_enqueue_scripts', array($this,'w3_enqueue_admin_scripts') );
			add_action('admin_head',array($this,'w3_enqueue_admin_head'));
			$this->w3_save_options();
		}
		if(!empty($_REQUEST['w3_reset_preload_css'])){
			w3_update_option('w3speedup_preload_css','','no');
			add_action( 'admin_notices', array($this,'w3_admin_notice_import_success') );
		}
		if(!empty($_REQUEST['restart'])){
			w3_update_option('w3speedup_img_opt_status',0,'no');
		}
		if(!empty($_REQUEST['reset'])){
			w3_update_option('w3speedup_opt_offset',0,'no');
		}
	}
	function w3_admin_notice_import_success() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Data imported successfully!', 'w3speedster' ); ?></p>
		</div>
		<?php
	}
	function w3_admin_notice_import_fail(){
		?>
		<div class="error notice-error is-dismissible">
			<p><?php _e( 'Data import failed', 'w3speedster' ); ?></p>
		</div>
		<?php
	}
	function w3_enqueue_admin_head(){
		if(function_exists('wp_enqueue_code_editor')){
			$cm_settings['codeJs'] = wp_enqueue_code_editor(array('type' => 'text/javascript'));
			$cm_settings['codeCss'] = wp_enqueue_code_editor(array('type' => 'text/css'));
		}else{
			$cm_settings = array();
		}
		?>
		<script>
		var cm_settings = <?php echo json_encode($cm_settings);?>
		</script>
		<?php
	}
	
	function w3_enqueue_admin_scripts(){

		wp_enqueue_script('wp-theme-plugin-editor');
		wp_enqueue_style('wp-codemirror');
	}
	
	function w3_check_license_key(){
		$response = $this->w3speedster_validate_license_key();
		if(!empty($response[0]) && $response[0] == 'fail' && strpos($response[1],'could not verify-1') !== false){
			w3_update_option('w3_key_log',json_encode($response));
			$settings = w3_get_option( 'w3_speedup_option', true );
			$settings['is_activated'] = '';
			w3_update_option( 'w3_speedup_option', $settings,'no' );	
		}
	}
	
	function w3_save_options(){
		if(isset($_POST['ws_action']) && $_POST['ws_action'] == 'cache'){
			unset($_POST['ws_action']);
			foreach($_POST as $key=>$value){
				$array[$key] = $value;
			}
			if(empty($array['license_key'])){
				$array['is_activated'] = '';
			}
			w3_update_option( 'w3_speedup_option', $array,'no' );		
			$this->settings = w3_get_option( 'w3_speedup_option', true );
			$this->w3_modify_htaccess();
			
		}
	}
    function get_curl_url($url){
      return parent::get_curl_url($url);
    }
   
    
    

    

    function w3_speedster_cache_purge_callback() {
		if ( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'],'purge_cache') ) {
			if(!empty($_REQUEST['resource_url'])){
				$url = str_replace(array($this->add_settings['wp_home_url'],$this->add_settings['image_home_url']),'',$_REQUEST['resource_url']);
				if(is_file($this->add_settings['document_root'].'/'.ltrim($url,'/'))){
					echo 'Request not valid'; exit;
				}
			}else{
				echo 'Request not valid'; exit;
			}
		}
        $w3speedster_init = new w3speedster();
        $response =round( (int)$w3speedster_init->w3_remove_cache_files_hourly_event_callback(),2);
        //$response = round( (int)get_option('w3_speedup_filesize') / 1024/1024 , 2);
        echo $response;
        wp_die();
    }
	function w3_speedster_critical_cache_purge_callback() {
		if ( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'],'purge_critical_css') ) {
			return 'Request not valid';
		}
		
        $w3speedster_init = new w3speedster();
		$data_id = !empty($_REQUEST['data_id']) ? $_REQUEST['data_id'] : '';
		$data_type = !empty($_REQUEST['data_type']) ? $_REQUEST['data_type'] : '';
        if(!empty($data_id) && !empty($data_type)){
			if($data_type == 'category'){
				$url = get_term_link($data_id);
			}else{
				$url = get_permalink($data_id);
			}
			$path = $this->w3_preload_css_path($url);
			$this->w3_rmfiles($path);
			echo round( (int)w3_get_option('w3_speedup_filesize') / 1024/1024 , 2);
		}else{
			$response =round( (int)$w3speedster_init->w3_remove_critical_css_cache_files(),2);
			//$response = round( (int)get_option('w3_speedup_filesize') / 1024/1024 , 2);
			echo $response;
		}
        wp_die();
    }
     
	
	function w3_modify_htaccess(){
			$path = $this->add_settings['document_root'].'/';
			if(!file_exists($path.".htaccess")){
				if(isset($_SERVER["SERVER_SOFTWARE"]) && $_SERVER["SERVER_SOFTWARE"] && (preg_match("/iis/i", $_SERVER["SERVER_SOFTWARE"]) || preg_match("/nginx/i", $_SERVER["SERVER_SOFTWARE"]))){
					//
				}else{
					return array("<label>.htaccess was not found</label>", "w3speedster");
				}
			}

			
			if(!WP_CACHE){
				if($wp_config = @file_get_contents(ABSPATH."wp-config.php")){
					$wp_config = str_replace("\$table_prefix", "define('WP_CACHE', true);\n\$table_prefix", $wp_config);

					if(!@file_put_contents(ABSPATH."wp-config.php", $wp_config)){
						return array("define('WP_CACHE', true); is needed to be added into wp-config.php", "w3speedster");
					}
				}else{
					return array("define('WP_CACHE', true); is needed to be added into wp-config.php", "w3speedster");
				}
			}
			$htaccess = @file_get_contents($path.".htaccess");

			// if(defined('DONOTCACHEPAGE')){
			// 	return array("DONOTCACHEPAGE <label>constant is defined as TRUE. It must be FALSE</label>", "error");
			// }else 
			

			if(!get_option('permalink_structure')){
				return array("You have to set <strong><u><a href='".admin_url()."options-permalink.php"."'>permalinks</a></u></strong>", "w3speedster");
			}else if(is_writable($path.".htaccess")){
				$change_in_htaccess = 0;
				if(!empty($this->settings['lbc'])){
					if(strpos($htaccess,'# BEGIN W3LBC') === false || strpos($htaccess,'# END W3LBC') === false){
						$htaccess = $this->w3_insert_LBC_rule($htaccess)."\n";
						$change_in_htaccess = 1;
					}
				}elseif(strpos($htaccess,'# BEGIN W3LBC') !== false || strpos($htaccess,'# END W3LBC') !== false){
					$htaccess = preg_replace("/#\s?BEGIN\s?W3LBC.*?#\s?END\s?W3LBC/s", "", $htaccess);
					$change_in_htaccess = 1;
				}
				if(!empty($this->settings['gzip'])){
					if(strpos($htaccess,'# BEGIN W3Gzip') === false || strpos($htaccess,'# END W3Gzip') === false){
						$htaccess = $this->w3_insert_gzip_rule($htaccess);
						$change_in_htaccess = 1;
					}
				}elseif(strpos($htaccess,'# BEGIN W3Gzip') !== false || strpos($htaccess,'# END W3Gzip') !== false){
					$htaccess = preg_replace("/\s*\#\s?BEGIN\s?W3Gzip.*?#\s?END\s?W3Gzip\s*/s", "", $htaccess);
					$change_in_htaccess = 1;
				}
				$webp_disable_htaccess = function_exists('w3_disable_htaccess_wepb') ? w3_disable_htaccess_wepb() : 0;
				if(empty($webp_disable_htaccess) && $this->add_settings['image_home_url'] == $this->add_settings['wp_site_url']){
					if(!empty($this->settings['webp_png']) || !empty($this->settings['webp_jpg'])){
						if(strpos($htaccess,'# BEGIN W3WEBP') === false || strpos($htaccess,'# END W3WEBP') === false){
							$htaccess = $this->w3_insert_webp($htaccess)."\n";
							$change_in_htaccess = 1;
						}
					}elseif(strpos($htaccess,'# BEGIN W3WEBP') !== false || strpos($htaccess,'# END W3WEBP') !== false){
						$htaccess = preg_replace("/#\s?BEGIN\s?W3WEBP.*?#\s?END\s?W3WEBP/s", "", $htaccess);
						$change_in_htaccess = 1;
					}
				}elseif(strpos($htaccess,'# BEGIN W3WEBP') !== false || strpos($htaccess,'# END W3WEBP') !== false){
					$htaccess = preg_replace("/#\s?BEGIN\s?W3WEBP.*?#\s?END\s?W3WEBP/s", "", $htaccess);
					$change_in_htaccess = 1;
				}
				if(strpos($htaccess,'# BEGIN W3404') === false || strpos($htaccess,'# END W3404') === false){
					$htaccess = $this->w3_insert_404_redirect_to_file($htaccess);
					$change_in_htaccess = 1;
				}
				if($change_in_htaccess){
					file_put_contents($path.".htaccess", $htaccess);
				}
			}else{
				return array(__("Options have been saved", 'w3speedster'), "updated");
			}
			return array(__("Options have been saved", 'w3speedster'), "updated");

		}
		function w3_insert_404_redirect_to_file($htaccess){
			$data = "\n"."# BEGIN W3404"."\n".
					"<IfModule mod_rewrite.c>"."\n".
					"RewriteEngine On"."\n".
					"RewriteBase /"."\n".
					"RewriteCond %{REQUEST_FILENAME} !-f"."\n".
					"RewriteRule (.*)/w3-cache/(css|js)/(\d)*(.*)[mob]*\.(css|js) $4.$5 [L]"."\n".
					"</IfModule>"."\n";
			$data = $data."# END W3404"."\n";
			$htaccess = preg_replace("/\s*\#\s?BEGIN\s?W3404.*?#\s?END\s?W3404\s*/s", "", $htaccess);
			return $data.$htaccess;
		}
		function w3_insert_rewrite_rule($htaccess){
			if(!empty($this->settings['html_cache'])){
				$htaccess = preg_replace("/#\s?BEGIN\s?W3Cache.*?#\s?END\s?W3Cache/s", "", $htaccess);
				$htaccess = $this->w3_get_htaccess().$htaccess;
			}else{
				$htaccess = preg_replace("/#\s?BEGIN\s?W3Cache.*?#\s?END\s?W3Cache/s", "", $htaccess);
				$this->deleteCache();
			}

			return $htaccess;
		}
		function w3_insert_gzip_rule($htaccess){
			$data = "\n"."# BEGIN W3Gzip"."\n".
					"<IfModule mod_deflate.c>"."\n".
					"AddType x-font/woff .woff"."\n".
					"AddType x-font/ttf .ttf"."\n".
					"AddOutputFilterByType DEFLATE image/svg+xml"."\n".
					"AddOutputFilterByType DEFLATE text/plain"."\n".
					"AddOutputFilterByType DEFLATE text/html"."\n".
					"AddOutputFilterByType DEFLATE text/xml"."\n".
					"AddOutputFilterByType DEFLATE text/css"."\n".
					"AddOutputFilterByType DEFLATE text/javascript"."\n".
					"AddOutputFilterByType DEFLATE application/xml"."\n".
					"AddOutputFilterByType DEFLATE application/xhtml+xml"."\n".
					"AddOutputFilterByType DEFLATE application/rss+xml"."\n".
					"AddOutputFilterByType DEFLATE application/javascript"."\n".
					"AddOutputFilterByType DEFLATE application/x-javascript"."\n".
					"AddOutputFilterByType DEFLATE application/x-font-ttf"."\n".
					"AddOutputFilterByType DEFLATE x-font/ttf"."\n".
					"AddOutputFilterByType DEFLATE application/vnd.ms-fontobject"."\n".
					"AddOutputFilterByType DEFLATE font/opentype font/ttf font/eot font/otf"."\n".
					"</IfModule>"."\n";

			$data = $data."# END W3Gzip"."\n";

			$htaccess = preg_replace("/\s*\#\s?BEGIN\s?W3Gzip.*?#\s?END\s?W3Gzip\s*/s", "", $htaccess);
			return $data.$htaccess;
		}
		function w3_insert_LBC_rule($htaccess){
			$data = "\n"."# BEGIN W3LBC"."\n".
				'<FilesMatch "\.(webm|ogg|mp4|ico|pdf|flv|jpg|jpeg|png|gif|webp|js|css|swf|x-html|css|xml|js|woff|woff2|otf|ttf|svg|eot)(\.gz)?$">'."\n".
				'<IfModule mod_expires.c>'."\n".
				'AddType application/font-woff2 .woff2'."\n".
				'AddType application/x-font-opentype .otf'."\n".
				'ExpiresActive On'."\n".
				'ExpiresDefault A0'."\n".
				'ExpiresByType video/webm A10368000'."\n".
				'ExpiresByType video/ogg A10368000'."\n".
				'ExpiresByType video/mp4 A10368000'."\n".
				'ExpiresByType image/webp A10368000'."\n".
				'ExpiresByType image/gif A10368000'."\n".
				'ExpiresByType image/png A10368000'."\n".
				'ExpiresByType image/jpg A10368000'."\n".
				'ExpiresByType image/jpeg A10368000'."\n".
				'ExpiresByType image/ico A10368000'."\n".
				'ExpiresByType image/svg+xml A10368000'."\n".
				'ExpiresByType text/css A10368000'."\n".
				'ExpiresByType text/javascript A10368000'."\n".
				'ExpiresByType application/javascript A10368000'."\n".
				'ExpiresByType application/x-javascript A10368000'."\n".
				'ExpiresByType application/font-woff2 A10368000'."\n".
				'ExpiresByType application/x-font-opentype A10368000'."\n".
				'ExpiresByType application/x-font-truetype A10368000'."\n".
				'</IfModule>'."\n".
				'<IfModule mod_headers.c>'."\n".
				'Header set Expires "max-age=A10368000, public"'."\n".
				'Header unset ETag'."\n".
				'Header set Connection keep-alive'."\n".
				'FileETag None'."\n".
				'</IfModule>'."\n".
				'</FilesMatch>'."\n".
				"# END W3LBC"."\n";

			$htaccess = preg_replace("/#\s?BEGIN\s?W3LBC.*?#\s?END\s?W3LBC/s", "", $htaccess);
			$htaccess = $data.$htaccess;
			return $htaccess;
		}
		function w3_insert_webp($htaccess){
			$wp_content_arr = explode('/',trim($this->add_settings['wp_content_path'],'/'));
			$wp_content = array_pop($wp_content_arr);
			$wp_content_webp = $wp_content."/w3-webp/";
			$basename = $wp_content_webp."$1w3.webp";
			/* 
				This part for sub-directory installation
				WordPress Address (URL): site_url() 
				Site Address (URL): home_url()
			*/
			if(preg_match("/https?\:\/\/[^\/]+\/(.+)/", site_url(), $siteurl_base_name)){
				if(preg_match("/https?\:\/\/[^\/]+\/(.+)/", home_url(), $homeurl_base_name)){
					/*
						site_url() return http://example.com/sub-directory
						home_url() returns http://example.com/sub-directory
					*/

					$homeurl_base_name[1] = trim($homeurl_base_name[1], "/");
					$siteurl_base_name[1] = trim($siteurl_base_name[1], "/");

					if($homeurl_base_name[1] == $siteurl_base_name[1]){
						if(preg_match("/".preg_quote($homeurl_base_name[1], "/")."$/", trim(ABSPATH, "/"))){
							$basename = $homeurl_base_name[1]."/".$basename;
						}
					}
				}else{
					/*
						site_url() return http://example.com/sub-directory
						home_url() returns http://example.com/
					*/
					$siteurl_base_name[1] = trim($siteurl_base_name[1], "/");
					$basename = $siteurl_base_name[1]."/".$basename;
				}
			}

			if(ABSPATH == "//"){
				$RewriteCond = "RewriteCond %{DOCUMENT_ROOT}/".$basename." -f"."\n";
			}else{
				// to escape spaces
				$tmp_ABSPATH = str_replace(" ", "\ ", ABSPATH);

				$RewriteCond = "RewriteCond %{DOCUMENT_ROOT}/".$basename." -f [or]"."\n";
				$RewriteCond = $RewriteCond."RewriteCond ".$tmp_ABSPATH.$wp_content_webp."$1w3.webp -f"."\n";
			}
			
			$data = "\n"."# BEGIN W3WEBP"."\n".
					"<IfModule mod_rewrite.c>"."\n".
					"RewriteEngine On"."\n".
					"RewriteCond %{HTTP_ACCEPT} image/webp"."\n".
					"RewriteCond %{REQUEST_URI} \.(jpe?g|png)"."\n".
					$RewriteCond.
					"RewriteRule ^".$wp_content."/(.*) /".$basename." [L]"."\n".
					"</IfModule>"."\n".
					"<IfModule mod_headers.c>"."\n".
					"Header append Vary Accept env=REDIRECT_accept"."\n".
					"</IfModule>"."\n".
					"AddType image/webp .webp"."\n".
					"# END W3WEBP"."\n";
			$htaccess = preg_replace("/#\s?BEGIN\s?W3WEBP.*?#\s?END\s?W3WEBP/s", "", $htaccess);
			$htaccess = $data.$htaccess;
			return $htaccess;
		}
		
		function createimageinstantly($imges=array()){
		$x=$y=300;
		
		$uploads = wp_upload_dir();
		
	
		//header('Content-Type: image/png');
		//$targetFolder = '/gw/media/uploads/processed/';
		//$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
		$targetPath = $uploads['basedir'];		
		
		if(!empty($imges)){
			$height_array = array();
			$max_width = 0;
			$images_detail = array();
			foreach($imges as $key=>$img){
				$size = getimagesize($img);
				//$size2 = getimagesize($img2);
				//$size3 = getimagesize($img3);			
				//$height_array = array($size1[1], $size2[1] ,$size3[1]);				
				//$max_width = ($size1[0]+$size2[0]+$size3[0])+60 ;	
				$size['src'] = $img ;				
				$height_array[] = $size[1];				
				$max_width = $max_width+$size[0]+20 ;
				$images_detail[$key] = 	$size ;
			}
			$max_height = max($height_array);
			
			
			$outputImage = imagecreatetruecolor( $max_width, $max_height);

			// set background to white
			$white = imagecolorallocate($outputImage, 0, 0, 0);
			//imagefill($outputImage, 0, 0, $white);
			imagecolortransparent($outputImage, $white);
			
			/*
			$first = imagecreatefrompng($img1);
			$second = imagecreatefrompng($img2);
			$third = imagecreatefrompng($img3);

			//imagecopyresized ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
			
			
			imagecopyresized($outputImage,$first,0,0,0,0, $size1[0], $size1[1],$size1[0], $size1[1]);
			
			imagecopyresized($outputImage,$second,($size1[0]+20),0,0,0, $size2[0], $size2[1], $size2[0], $size2[1]);
			
			imagecopyresized($outputImage,$third,($size1[0]+$size2[1]+40),0,0,0, $size3[0], $size3[1],$size3[0], $size3[1]); */
			
						
			$new_coordinates = 0;
			$new_images_detail = array();
			foreach($images_detail as $key=>$img){					
				$new_image = imagecreatefrompng($img['src']);
				imagecopyresized($outputImage,$new_image,$new_coordinates,0,0,0, $img[0], $img[1],$img[0], $img[1]);
				$new_coordinates = $new_coordinates+$img[0]+20;					
			}			
			
			// Add the text
			//imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
			//$white = imagecolorallocate($im, 255, 255, 255);
			$text = 'School Name Here';
			$font = 'OldeEnglish.ttf';
			//imagettftext($outputImage, 32, 0, 150, 150, $white, $font, $text);
			
			$wp_upload_dir = wp_upload_dir();
			
			$image_name = 'combine_image_'.round(microtime(true)).'.png';
			$filename = $wp_upload_dir['path'].'/'.$image_name;
			imagepng($outputImage, $filename);			
			
			// create attachment post			
			$filetype = wp_check_filetype( basename( $image_name ), null );

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_title(preg_replace( '/\.[^.]+$/', '', basename( $filename ) )),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);
			
			
			$attach_id = wp_insert_attachment( $attachment, $filename, 0 );
			// Include image.php
			require_once(ABSPATH . 'wp-admin/includes/image.php');

			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

			// Assign metadata to attachment
			wp_update_attachment_metadata( $attach_id, $attach_data );
			
			w3_update_option( 'w3_speedup_combine_image_id', $attach_id, 'no' );
			
			imagedestroy($outputImage);
		}
	}

  function get_ws_optimize_image($image_url, $image_width){
		$w3_speedster_img = new w3speedster_optimize_image(); 
		$result = $w3_speedster_img->w3_optimize_attachment($image_url, $image_width, false,'',true);
		return $result['img'] == 1 ? 'success' : 'failed' ;
	}
	
	function w3_save_combined_images(){
		if(isset($_POST['ws_action']) && $_POST['ws_action'] == 'combine_image_save'){
			$c_array['combine_images'] = $_POST['combine_images'];		
			w3_update_option( 'w3_speedup_combine_images', $c_array,'no' );
			
			$c_images_src = array();
			if(isset($c_array['combine_images']) && !empty($c_array['combine_images'])){
				foreach($c_array['combine_images'] as $value){ 
					if(!empty($value['src'])){
						$c_images_src[] = $value['src'] ;
					}
				}		
			}
					
			if(!empty($c_images_src)){		
				createimageinstantly($c_images_src);
			}
			
		}
		
		
		$combine_images = get_option( 'w3_speedup_combine_images' );
	}
	function notify($message = array()){
			if(isset($message[0]) && $message[0]){
				if(function_exists("add_settings_error")){
					add_settings_error('wpfc-notice', esc_attr( 'settings_updated' ), $message[0], $message[1]);
				}
			}
		}
	function add_button_to_edit_media_modal_fields_area1( $form_fields, $post ) {
		//print_r($form_fields);	
		
			$image_url = wp_get_attachment_url($post->ID );
			
			$theme_root_array = explode('/',$this->add_settings['theme_base_url']);
			$theme_root = array_pop($theme_root_array);
			$upload_dir = wp_upload_dir();
			$webp_jpg = !empty($this->settings['webp_jpg']) ? 1 : 0;
			$webp_png = !empty($this->settings['webp_png']) ? 1 : 0;
			$optimize_image = !empty($this->settings['opt_jpg_png']) ? 1 : 0;
			$type = explode('.',$image_url);
			$type = array_reverse($type);
			if(strpos($image_url,$theme_root) !== false){
				$img_root_path = rtrim($this->add_settings['theme_base_dir'],'/');
				$img_root_url = rtrim($this->add_settings['theme_base_url'],'/');
			}else{
				$img_root_path = $this->add_settings['upload_base_dir'];
				$img_root_url = $this->add_settings['upload_base_url'];
				
			}
			$image_url_path = str_replace($img_root_url,$img_root_path,$image_url); 
			$webp_path = str_replace($this->add_settings['upload_path'],$this->add_settings['webp_path'],$image_url_path);
			
			$optimize_message = '';
			if(is_file($webp_path.'w3.webp')){
				$optimize_message = 1;
			}
			
		
		
		$form_fields['optimize_image'] = array(
			'label'         =>'',
			'input'         => 'html',
			'html'          => '<div class="loader-sec"><div class="loader"></div></div><a href="#" data-id="' . $post->ID  . '" class="optimize_media_image button-secondary button-large" title="' . esc_attr( __( 'Optimize image', 'w3speedster' ) ) . '">' . __( 'Optimize Image', 'action for a single image', 'w3speedster' ) . '<i class="dashicons dashicons-saved" style="vertical-align: sub;"></i></a>',
			'show_in_modal' => true,
			'show_in_edit'  => false,
		);

		return $form_fields;
	}
	
	

	function fn_w3_optimize_media_image_callback (){ 		
		if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])){
			
			$attach_id = $_REQUEST['id'];		
			require_once(W3SPEEDSTER_PLUGIN_DIR . 'includes/class_image.php');
			$w3speedster_image = new w3speedster_optimize_image();
			$result = $w3speedster_image->w3_optimize_attachment_id($attach_id);
		}
		
		
		echo json_encode(array(
                'summary' => $result,
                'status' => '200'
            ));
		exit;
	}	

}