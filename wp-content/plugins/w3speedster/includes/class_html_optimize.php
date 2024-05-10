<?php
namespace W3speedster;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class w3speed_html_optimize extends w3speedster_js{
	function w3_speedster($html){
		$this->html = $html;
		$this->w3_debug_time('start optimization');
		if(function_exists('w3speedup_pre_start_optimization')){
            $this->html = w3speedup_pre_start_optimization($this->html);
        }
        $upload_dir = wp_upload_dir();
		if(!file_exists($upload_dir['basedir'].'/w3test.html') && !empty($this->html)){
			file_put_contents($upload_dir['basedir'].'/w3test.html',$this->html);
		}
        if($this->w3_no_optimization()){
            return $this->html;
        }
		if(function_exists('w3speedup_customize_add_settings')){
			$this->add_settings = w3speedup_customize_add_settings($this->add_settings);
		}
		if(function_exists('w3speedup_customize_main_settings')){
			$this->settings = w3speedup_customize_main_settings($this->settings);
		}
		$this->add_settings['disable_htaccess_webp'] = function_exists('w3_disable_htaccess_wepb') ? w3_disable_htaccess_wepb() : 0;
		if(!empty($this->settings['js'])){
			$this->w3_custom_js_enqueue();
		}
        //$this->html = str_replace(array('<script type="text/javascript"',"<script type='text/javascript'",'<style type="text/css"',"<style type='text/css'"),array('<script','<script','<style','<style'),$this->html);
        if(function_exists('w3speedup_before_start_optimization')){
            $this->html = w3speedup_before_start_optimization($this->html);
        }
        
        $js_json_exists = 0;
        /*if(file_exists($file = $this->w3_get_full_url_cache_path().'/js.json')){
            $rep_js = json_decode(file_get_contents($file));
            if(is_array($rep_js[0]) && is_array($rep_js[1])){
                $js_json_exists = 1;
                if(file_exists($file = $this->w3_get_full_url_cache_path().'/main_js.json')){
                    global $internal_js;
                    $internal_js = json_decode(file_get_contents($file));
                }
            }
        }*/
        $img_json_exists = 0;
        if(file_exists($file = $this->w3_check_full_url_cache_path().'/img.json')){
            $rep_img = json_decode(file_get_contents($file));
            if(is_array($rep_img[0]) && is_array($rep_img[1])){
                $img_json_exists = 1;
            }
        }
        $rep_main_css = array();
        $css_json_exists = 0;
        if(file_exists($file = $this->w3_check_full_url_cache_path().'/main_css.json')){
            $rep_main_css = json_decode(file_get_contents($file));
        }
		if(file_exists($file = $this->w3_check_full_url_cache_path().'/css.json')){
            $rep_css = json_decode(file_get_contents($file));
            if(is_array($rep_css[0]) && is_array($rep_css[1])){
                $css_json_exists = 1;
            }
		}
        if(file_exists($file = $this->w3_check_full_url_cache_path().'/content_head.json') && $css_json_exists){
            $rep_content_head = json_decode(file_get_contents($file));
            if(is_array($rep_content_head) && count($rep_content_head) > 0){
                $content_head_exists = 1;
            }else{
                $content_head_exists = 0;
            }
        }
		if($img_json_exists && $css_json_exists){
			$this->w3_debug_time('before create all links');
            $all_links = $this->w3_setAllLinks($this->html,array('script','link'));
			$this->w3_debug_time('after create all links');
            $this->minify($all_links['script']);
            $this->w3_debug_time('minify script');
            if(is_array($rep_content_head) && count($rep_content_head) > 0){
				for($i = 0; $i < count($rep_content_head); $i++){
					$this->w3_insert_content_head($rep_content_head[$i][0],$rep_content_head[$i][1]);
				}
			}
			$this->w3_debug_time('after replace json data');
            $this->w3_str_replace_bulk();
            $this->w3_str_replace_bulk_json(array_merge($rep_css[0],$rep_img[0]),array_merge($rep_css[1],$rep_img[1]));
        }else{
			$this->w3_debug_time('before create all links');
            $lazyload = array('script','link','img','url');
			if(!empty($this->settings['lazy_load_iframe'])){
				$lazyload[] = 'iframe';
			}
			if(!empty($this->settings['lazy_load_video'])){
				$lazyload[] = 'video';
			}
			if(!empty($this->settings['lazy_load_audio'])){
				$lazyload[] = 'audio';
			}
            $all_links = $this->w3_setAllLinks($this->html,$lazyload);
			$this->w3_debug_time('after create all links');
            if(!empty($all_links['script'])){
				$this->minify($all_links['script']);
			}
			$this->w3_debug_time('minify script');
			$this->lazyload(array('iframe'=>$all_links['iframe'],'video'=>$all_links['video'],'audio'=>$all_links['audio'],'img'=>$all_links['img'],'url'=>$all_links['url'] ) );
			if(!empty($this->settings['load_style_tag_in_head'])){
				$this->load_style_tag_in_head($all_links['style']);
			}
			$this->w3_debug_time('lazyload images');
            $this->minify_css($all_links['link']);
			$this->w3_debug_time('minify css');
            $this->w3_str_replace_bulk();
            //$this->w3_str_replace_bulk_img();
            //$this->w3_str_replace_bulk_css();
            $this->w3_debug_time('replace json');
			$this->w3_insert_content_head('<script>'.$this->w3_lazy_load_javascript().'</script>',3);
			$this->w3_insert_content_head($this->w3_load_google_fonts(),3);
			$this->w3_insert_content_head_in_json();
			$this->w3_debug_time('after javascript insertion');
		}
		$ignore_critical_css = 0;
		if(!empty($this->add_settings['w3_user_logged_in']) || is_search() || is_404()){
			$ignore_critical_css = 1;
		}
		if(function_exists('w3_no_critical_css')){
			$ignore_critical_css = w3_no_critical_css($this->add_settings['full_url']);
		}
		if(!$ignore_critical_css){
			$critical_css_file = $this->w3_get_full_url_cache_path().'/critical_css.json';
			if(file_exists($critical_css_file)){
				$this->add_settings['critical_css'] = file_get_contents($critical_css_file);
			}
			if(!empty($_REQUEST['w3_get_css_post_type'])){
				$this->html .= 'rocket22'.$this->w3_preload_css_path().'--'.$this->add_settings['critical_css'].'--'.file_exists($this->w3_preload_css_path().'/'.$this->add_settings['critical_css']);
			}
			if(!empty($this->settings['load_critical_css'])){
				if(!file_exists($this->w3_preload_css_path().'/'.$this->add_settings['critical_css'])){
					$this->w3_add_page_critical_css();
				}else{
					$critical_css = file_get_contents($this->w3_preload_css_path().'/'.$this->add_settings['critical_css']);
					if(!empty($critical_css)){
						$this->w3_insert_content_head('{{main_w3_critical_css}}',3);
						if(function_exists('w3speedup_customize_critical_css')){
							$critical_css = w3speedup_customize_critical_css($critical_css);
						}
						$enable_cdn = 0;
						if($this->w3_check_enable_cdn_ext('.css')){
							$upload_dir['baseurl'] = str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$upload_dir['baseurl']);
							$enable_cdn = 1;
							$this->add_settings['preload_resources']['all'][] = $upload_dir['baseurl'].'/blank.css';
						}
						if(!empty($this->settings['load_critical_css_style_tag'])){
							$this->html = str_replace(array('data-css="1" ','{{main_w3_critical_css}}'),array('href="'.$upload_dir['baseurl'].'/blank.css" data-','<style id="w3speedster-critical-css">'.$critical_css.'</style>'),$this->html);
							$this->add_settings['preload_resources']['critical_css'] = 1;
						}else{
							$critical_css_url = str_replace($this->add_settings['document_root'],($enable_cdn ? $this->add_settings['image_home_url'] :$this->add_settings['wp_site_url']),$this->w3_preload_css_path().'/'.$this->add_settings['critical_css']);
							$this->html = str_replace(array('data-css="1" ','{{main_w3_critical_css}}'),array('href="'.$upload_dir['baseurl'].'/blank.css" data-','<link rel="stylesheet" href="'.$critical_css_url.'"/>'),$this->html);
							$this->add_settings['preload_resources']['critical_css'] = $critical_css_url;
						}
					}else{
						$this->w3_add_page_critical_css();
					}
				}
			}
		}
		$preload_html = $this->w3_preload_resources();
		$this->w3_insert_content_head($preload_html,3);
        $position = strrpos($this->html,'</body>');
		$this->html = substr_replace( $this->html, '<script>'.$this->w3_lazy_load_images().'</script>', $position, 0 );
		$this->w3_debug_time('w3 script');
		
        if(function_exists('w3speedup_after_optimization')){
            $this->html = w3speedup_after_optimization($this->html);
        }
		$this->w3_debug_time('before final output');
        return $this->html;
    } 
	
	function w3_add_page_critical_css(){
		if(!empty($this->settings['optimization_on'])){
			$preload_css = w3_get_option('w3speedup_preload_css');
			$preload_css = (empty($preload_css) || !is_array($preload_css)) ? array() : $preload_css;
			if(is_array($preload_css) && count($preload_css) > 20){
				return;
			}
			if(!is_array($preload_css) || (is_array($preload_css) && !array_key_exists(base64_encode($this->add_settings['full_url_without_param']),$preload_css)) || (!empty($preload_css[$this->add_settings['full_url_without_param']]) && $preload_css[$this->add_settings['full_url_without_param']][0] != $this->add_settings['critical_css']) ){
				$preload_css[base64_encode($this->add_settings['full_url_without_param'])] = array($this->add_settings['critical_css'],2,$this->w3_preload_css_path());
				w3_update_option('w3speedup_preload_css',$preload_css,'no');
				w3_update_option('w3speedup_preload_css_total',(int)w3_get_option('w3speedup_preload_css_total')+1,'no');
				return serialize(w3_get_option('w3speedup_preload_css'));
			}
		}
	}
	public function w3_header_check() {
        return is_admin()
			|| $this->isSpecialContentType()
	    	|| $this->isSpecialRoute()
	    	|| $_SERVER['REQUEST_METHOD'] === 'POST'
	    	|| $_SERVER['REQUEST_METHOD'] === 'PUT'
			|| $_SERVER['REQUEST_METHOD'] === 'DELETE';
	}

   private function isSpecialContentType() {
		if($this->w3_endswith($this->add_settings['full_url'],'.xml') || $this->w3_endswith($this->add_settings['full_url'],'.xsl')){
        	return true;
        }

		return false;
    }

    private function isSpecialRoute() {
		$current_url = $this->add_settings['full_url'];

		if( preg_match('/(.*\/wp\/v2\/.*)/', $current_url) ) {
			return true;
		}

		if( preg_match('/(.*wp-login.*)/', $current_url) ) {
			return true;
		}

		if( preg_match('/(.*wp-admin.*)/', $current_url) ) {
			return true;
		}

		return false;
    }
	
	function w3_custom_js_enqueue(){
		if(!empty($this->settings['custom_js'])){
			$custom_js = stripslashes($this->settings['custom_js']);
		}else{
			$custom_js = 'console.log("js loaded");';
		}
		$js_file_name1 = 'custom_js_after_load.js';
		if(!file_exists($this->w3_get_cache_path('js').'/'.$js_file_name1)){
			$this->w3_create_file($this->w3_get_cache_path('js').'/'.$js_file_name1, $custom_js);
		}
		$this->html = $this->w3_str_replace_last('</body>','<script src="'.$this->add_settings['cache_url'].'/js/'.$js_file_name1.'"></script></body>',$this->html);
	}
    function w3_no_optimization(){
        if(!empty($_REQUEST['orgurl']) || strpos($this->html,'<body') === false){
            return true;
        }
        if (function_exists( 'is_amp_endpoint' ) && is_amp_endpoint()) {
            return true;
        }
		if($this->w3_header_check()){
			return true;
		}
        if(empty($this->settings['optimization_on']) && empty($_REQUEST['tester']) && empty($_REQUEST['testing'])){
             return true;
        }
		if(function_exists('w3speedup_exclude_page_optimization')){
            if(w3speedup_exclude_page_optimization($this->html)){
				return true;
			}
        }
		if(empty($this->settings['optimize_user_logged_in']) && function_exists('is_user_logged_in') && is_user_logged_in()){
			return true;
		}
		if(empty($this->settings['optimize_query_parameters']) && $this->add_settings['full_url'] != $this->add_settings['full_url_without_param'] && empty($_REQUEST['tester'])){
			return true;
		}
        if($this->w3_check_if_page_excluded($this->settings['exclude_pages_from_optimization'])){
            return true;
        }
        global $current_user;
        if((empty($_REQUEST['testing']) && is_404()) || (!empty($current_user) && current_user_can('edit_others_pages')) ){
            return true;
        }
        return false;
    }
    
    function w3_start_optimization_callback(){
        ob_start(array($this,"w3_speedster") );
		//add_action( 'shutdown', array($this,'w3_ob_end_flush'));
        //register_shutdown_function(array($this,'w3_ob_end_flush') );
    }
    
    function w3_ob_end_flush() {
    
        if (ob_get_level() != 0) {
    
            ob_end_flush();
    
         }
    
    }
	function load_style_tag_in_head($style_tags){
		$load_style_tag_in_head	= !empty($this->settings['load_style_tag_in_head']) ? explode("\r\n", $this->settings['load_style_tag_in_head']) : array();
		foreach($style_tags as $style_tag){
			$load_in_head = 0;
			foreach($load_style_tag_in_head as $ex_css){
				if(!empty($ex_css) && strpos($style_tag, $ex_css) !== false){
					$load_in_head = 1;
				}
			}
			if($load_in_head){
				//$this->w3_insert_content_head('/<style(.*)'.$ex_css.'(.*)<\/style>/',5);
				$this->w3_insert_content_head($style_tag,4);
			}
		}
	}
	
	function createBlankDataImage($width, $height) {

		$image = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n";
		$image .= '<svg width="'.$width.'" height="'.$height.'" xmlns="http://www.w3.org/2000/svg">' . "\n";
		$image .= '	<rect width="100%" height="100%" opacity="0"/>' . "\n";
		$image .= '</svg>';
		$base64SVG = base64_encode($image);
		$dataURI = 'data:image/svg+xml;base64,' . $base64SVG;
		$this->w3_create_file($this->add_settings['upload_base_dir'].'/blank-'.$width.'x'.$height.'.txt',$dataURI);
	}
	
	function w3_load_google_fonts(){
		$google_font = array();
        if(!empty($this->add_settings['fonts_api_links'])){
            $all_links = '';
            foreach($this->add_settings['fonts_api_links'] as $key => $links){
                $all_links .= !empty($links) && is_array($links) ? $key.':'.implode(',',$links).'|' : $key.'|';
            }
            $google_font[] = $this->add_settings['secure']."fonts.googleapis.com/css?display=swap&family=".urlencode(trim($all_links,'|'));
        }
		if(!empty($this->add_settings['fonts_api_links_css2'])){
			$all_links = 'https://fonts.googleapis.com/css2?';
			foreach($this->add_settings['fonts_api_links_css2'] as $font){
				$all_links .= $font.'&';
			}
			$all_links .= 'display=swap';
			$google_font[] = $all_links;
		}
		return '<script>var w3_googlefont='.json_encode($google_font).';</script>';
	}
    function lazyload($all_links){
		$upload_dir   = wp_upload_dir();
        $excluded_img = !empty($this->settings['exclude_lazy_load']) ? explode("\r\n",stripslashes($this->settings['exclude_lazy_load'])) : array();
		$excluded_img = array_merge($excluded_img,array('about:blank','gform_ajax'));
	    if(!empty($this->settings['lazy_load_iframe'])){
            $iframe_links = $all_links['iframe'];
            foreach($iframe_links as $img){
				if(strpos($img,'\\') !== false){
					continue;
				}
                $exclude_image = 0;
                foreach( $excluded_img as $ex_img ){
                    if(!empty($ex_img) && strpos($img,$ex_img)!==false){
                        $exclude_image = 1;
                    }
                }
                if($exclude_image){
                    continue;
                }
                $img_obj = $this->w3_parse_link('iframe',$img);
				$iframe_html = '';
                if(strpos($img_obj['src'],'youtu') !== false){
                    preg_match("#([\/|\?|&]vi?[\/|=]|youtu\.be\/|embed\/)([a-zA-Z0-9_-]+)#", $img_obj['src'], $matches);
                    if(empty($img_obj['style'])){
                        $img_obj['style'] = '';
                    }
                    $img_obj['style'] .= 'background-image:url(https://i.ytimg.com/vi/'.trim(end($matches)).'/sddefault.jpg)';
					//$iframe_html = '<img width="68" height="48" class="iframe-img" src="/wp-content/uploads/yt-png2.png"/>';
                }
                $img_obj['data-src'] = $img_obj['src'];
                $img_obj['src'] = 'about:blank';
                $img_obj['data-class'] = 'LazyLoad';
				
                $this->w3_str_replace_set_img($img,$this->w3_implode_link_array('iframelazy',$img_obj).$iframe_html);
            }
	    }
        if(!empty($this->settings['lazy_load_video'])){
            $iframe_links = $all_links['video'];
			if(strpos($this->add_settings['upload_base_url'],$this->add_settings['wp_site_url']) !== false){
				$v_src = $this->add_settings['image_home_url'].str_replace($this->add_settings['wp_site_url'],'',$this->add_settings['upload_base_url']).'/blank.mp4';
			}else{
				$v_src = $this->add_settings['upload_base_url'].'/blank.mp4';
			}
            foreach($iframe_links as $img){
				if(strpos($img,'\\') !== false){
					continue;
				}
                $exclude_image = 0;
                foreach( $excluded_img as $ex_img ){
                    if(!empty($ex_img) && strpos($img,$ex_img)!==false){
                        $exclude_image = 1;
                    }
                }
                if($exclude_image){
                    continue;
                }
				$img_new = $img;
				if(strpos($img,'poster=') !== false){
					$img_new = str_replace('poster=','data-poster=',$img_new);
				}
                $img_new = str_replace('src=','data-class="LazyLoad" data-src=',$img_new);
				if(function_exists('w3_change_video_to_videolazy') && w3_change_video_to_videolazy()){
					$img_new= str_replace(array('<video','</video>'),array('<videolazy','</videolazy>'),$img_new);
				}
                $this->w3_str_replace_set_img($img,$img_new);
            }
        }
		if(!empty($this->settings['lazy_load_audio'])){
            $iframe_links = $all_links['audio'];
			if(strpos($this->add_settings['upload_base_url'],$this->add_settings['wp_site_url']) !== false){
				$v_src = $this->add_settings['image_home_url'].str_replace($this->add_settings['wp_site_url'],'',$this->add_settings['upload_base_url']).'/blank.mp3';
			}else{
				$v_src = $this->add_settings['upload_base_url'].'/blank.mp3';
			}
            foreach($iframe_links as $img){
				if(strpos($img,'\\') !== false){
					continue;
				}
                $exclude_image = 0;
                foreach( $excluded_img as $ex_img ){
                    if(!empty($ex_img) && strpos($img,$ex_img)!==false){
                        $exclude_image = 1;
                    }
                }
                if($exclude_image){
                    continue;
                }
				
                $img_new = str_replace('src=','data-class="LazyLoad" src="'.$v_src.'" data-src=',$img);
                $this->w3_str_replace_set_img($img,$img_new);
            }
        }
        $img_links = $all_links['img'];
        if(!empty($all_links['img'])){
			$lazy_load_img = !empty($this->settings['lazy_load']) ? 1 : 0;
            $enable_cdn = 0;
            if($this->add_settings['image_home_url'] != $this->add_settings['wp_site_url'] ){
                $enable_cdn = 1;
            }
            $exclude_cdn_arr = !empty($this->add_settings['exclude_cdn']) ? $this->add_settings['exclude_cdn'] : array();
			
            $webp_enable = $this->add_settings['webp_enable'];
			$webp_enable_instance = $this->add_settings['webp_enable_instance'];
			$webp_enable_instance_replace = $this->add_settings['webp_enable_instance_replace'];
			$theme_root_array = explode('/',$this->add_settings['theme_base_url']);
			$theme_root = array_pop($theme_root_array);
			foreach($img_links as $img){
				$blank_image_url = ($enable_cdn && !in_array('.png',$exclude_cdn_arr)) ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$this->add_settings['upload_base_url']) : $this->add_settings['upload_base_url'];
                $exclude_image = 0;
                $imgnn = $img;
				$imgnn_arr = $this->w3_parse_link('img',str_replace($this->add_settings['image_home_url'],$this->add_settings['wp_site_url'],$imgnn));
				if(empty($imgnn_arr['src'])){
					continue;
				}
				if(strpos($imgnn_arr['src'],'\\') !== false){
					continue;
				}
				if(!$this->w3_is_external($imgnn_arr['src'])){
					if(strpos($imgnn_arr['src'],$theme_root) !== false){
						$img_root_path = rtrim($this->add_settings['theme_base_dir'],'/');
						$img_root_url = rtrim($this->add_settings['theme_base_url'],'/');
					}else{
						$img_root_path = $this->add_settings['upload_base_dir'];
						$img_root_url = $this->add_settings['upload_base_url'];
					}
					if(strpos($imgnn_arr['src'],'?') !== false){
						$temp_src = explode('?',$imgnn_arr['src']);
						$imgnn_arr['src'] = $temp_src[0];
					}
					$img_url_arr = parse_url($imgnn_arr['src']);
					$w3_img_ext = '.'.pathinfo($imgnn_arr['src'], PATHINFO_EXTENSION);
					$imgsrc_filepath = str_replace($img_root_url,'',$this->add_settings['home_url'].$img_url_arr['path']);
					$imgsrc_webpfilepath = str_replace($this->add_settings['upload_path'],$this->add_settings['webp_path'],$img_root_path).$imgsrc_filepath.'w3.webp';
					if($enable_cdn){
						$image_home_url = $this->add_settings['image_home_url'];
						foreach($exclude_cdn_arr as $cdn){
							if(strpos($img,$cdn) !== false){
								$image_home_url = $this->add_settings['wp_site_url'];
								break;
							}
						}
						$imgnn = str_replace($this->add_settings['wp_site_url'],$image_home_url,$imgnn);
					}else{
						$image_home_url = $this->add_settings['wp_site_url'];
					}
					
					$img_size = file_exists($img_root_path.$imgsrc_filepath) ? @getimagesize($img_root_path.$imgsrc_filepath) : array();
					if(!empty($img_size[0]) && !empty($img_size[1])){
						if(empty($imgnn_arr['width']) || $imgnn_arr['width'] == 'auto' || $imgnn_arr['width'] == '100%'){
							$imgnn = str_replace(array(' width="auto"',' src='),array('',' width="'.$img_size[0].'" src='),$imgnn);
						}
						if(empty($imgnn_arr['height']) || $imgnn_arr['height'] == 'auto' || $imgnn_arr['height'] == '100%'){
							$imgnn = str_replace(array(' height="auto"',' src='),array('',' height="'.$img_size[1].'" src='),$imgnn);
						}
						$imgnn = str_replace(' src=',' style="aspect-ratio:'.$img_size[0].'/'.$img_size[1].'" src=',$imgnn);
						$blank_image = '/blank-'.(int)$img_size[0].'x'.(int)$img_size[1].'.txt';
						if(!file_exists($this->add_settings['upload_base_dir'].$blank_image)){
							 $this->createBlankDataImage((int)$img_size[0],(int)$img_size[1]);
						}
						$blank_image_url = file_get_contents($this->add_settings['upload_base_dir'].$blank_image);		
					}else{
						$blank_image_url .= '/blank.png';
					}
					if(strpos($img, ' srcset=') === false && !empty($this->settings['resp_bg_img'])){
						if(!empty($img_size[0]) && $img_size[0] > 600){
							$w3_thumbnail = rtrim(str_replace($w3_img_ext.'$','-595xh'.$w3_img_ext.'$',$imgsrc_filepath.'$'),'$');
							if(in_array($w3_img_ext, $webp_enable) && !file_exists($this->add_settings['document_root'].$w3_thumbnail) && !empty($this->settings['opt_img_on_the_go'])){
								$response = $this->w3_optimize_attachment_url($img_root_path.$imgsrc_filepath);
							}
							if(file_exists($img_root_path.$w3_thumbnail)){
								$w3_thumbnail = str_replace(' ','%20',$w3_thumbnail);
								$imgnn_arr['src'] = str_replace(' ','%20',$imgnn_arr['src']);
								$imgnn = str_replace(' src=',' data-mob-src="'.$img_root_url.$w3_thumbnail.'" src=',$imgnn);
							}
						}
					}
					if(count($webp_enable) > 0 && in_array($w3_img_ext, $webp_enable)){
						if(!empty($this->settings['opt_img_on_the_go']) && !file_exists($imgsrc_webpfilepath) && file_exists($img_root_path.$imgsrc_filepath)){
							$this->w3_optimize_attachment_url($img_root_path.$imgsrc_filepath);
						}
						if(file_exists($imgsrc_webpfilepath) && (!empty($this->add_settings['disable_htaccess_webp']) || !file_exists($this->add_settings['wp_document_root']."/.htaccess") || $this->add_settings['image_home_url'] != $this->add_settings['wp_site_url'] ) ){
							$imgnn = str_replace($webp_enable_instance,$webp_enable_instance_replace,$imgnn);
						}
					}
				}
				if($lazy_load_img){
					foreach( $excluded_img as $ex_img ){
						if(!empty($ex_img) && strpos($img,$ex_img)!==false){
							$exclude_image = 1;
						}
					}
					if(!empty($imgnn_arr['data-class']) && strpos($imgnn_arr['data-class'],'LazyLoad') !== false){
						$exclude_image = 1;
					}
				}else{
					$exclude_image = 1;
				}
				if(function_exists('w3speedup_image_exclude_lazyload')){
					$exclude_image = w3speedup_image_exclude_lazyload($exclude_image,$img, $imgnn_arr);
				}
				if($exclude_image){
					if(function_exists('w3speedup_customize_image')){
						$imgnn = w3speedup_customize_image($imgnn,$img,$imgnn_arr);
					}
					if($img != $imgnn){
						$this->w3_str_replace_set_img($img,$imgnn);
					}
					continue;
				}
				if(strpos($blank_image_url,'/blank') === false && strpos($blank_image_url,'data:image') === false){
					$blank_image_url .= '/blank.png';
				}
                $imgnn = str_replace(' src=',' data-class="LazyLoad" src="'. $blank_image_url .'" data-src=',$imgnn);
				if(strpos($imgnn, ' srcset=') !== false){
					$imgnn = str_replace(' srcset=',' data-srcset=',$imgnn);
				}
				if(function_exists('w3speedup_customize_image')){
					$imgnn = w3speedup_customize_image($imgnn,$img,$imgnn_arr);
				}
                $this->w3_str_replace_set_img($img,$imgnn);
            }
		}
       
        $this->html = $this->w3_convert_arr_relative_to_absolute($this->html, $this->add_settings['wp_home_url'].'/index.php',$all_links['url']);
    }
	function calculateHCF($num1, $num2) {
		while ($num2 != 0) {
			$temp = $num2;
			$num2 = $num1 % $num2;
			$num1 = $temp;
		}
		return $num1;
	}
	function w3_increment_prioritized_img($attach_id=''){
		$opt_priority = w3_get_option('w3speedup_opt_priortize');
		if(empty($opt_priority) || !is_array($opt_priority)){
			$opt_priority = array();
		}
		if(is_array($opt_priority) && count($opt_priority) > 50){
			return true;
		}
		if(empty($opt_priority) || !in_array($attach_id,$opt_priority)){
			$opt_priority[] = $attach_id;
		}
		w3_update_option('w3speedup_opt_priortize',$opt_priority,'no');
		return true;
	}
	function w3_optimize_attachment_url($path){
		global $wpdb;
		if(strpos($path,'/themes/') !== false){
			return $this->w3_increment_prioritized_img($path);
		}
		$query = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND guid like '%".$path."' limit 0,1";
		$attach_id = $wpdb->get_var($query);
		if(!empty($attach_id)){
			return $this->w3_increment_prioritized_img($attach_id);
		}else{
			$path_arr = explode('/',$path);
			$img = array_pop($path_arr);
			$attach_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='_wp_attachment_metadata' AND meta_value LIKE '%".$img."%'");
			if(!empty($attach_id)){
				return $this->w3_increment_prioritized_img($attach_id);
			}else{
				return $this->w3_increment_prioritized_img($path);
			}
		}
	}
    
}