<?php
namespace W3speedster;

class w3speedster_css extends w3speedster{
    
    function w3_remove_css_comments( $minify ){
		$minify = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minify );
		return $minify;
	}
	function w3_css_compress( $minify ){
    	$minify = str_replace( array("\r\n", "\r", "\n", "\t",'  ','    ', '    '), ' ', $minify );
		$minify = str_replace( array(": ", ":: "), array(':','::'), $minify );
    	return $minify;
    }
	function w3_relative_to_absolute_path($url, $string){
		
		$url_new = $url;
		$url_arr = $this->w3_parse_url($url);
        $url = $this->add_settings['wp_site_url'].$url_arr['path'];
        
        if(strpos($string,'@import "') !== false || strpos($string,"@import '") !== false){
           $string = preg_replace('/(@import\s*)\"(.*)(\.css)\"/', '$1url("$2$3")', $string);
        }
        $matches = $this->w3_get_tags_data($string,'url(',')');
        return $this->w3_convert_arr_relative_to_absolute($string, $url, $matches);
    
    }
	function w3_convert_arr_relative_to_absolute($string, $url, $matches){
		$webp_enable = $this->add_settings['webp_enable'];
		$replaced = array();
		$replaced_new = array();
		$replace_array = explode('/',str_replace('\'','/',$url));
		array_pop($replace_array);
		$url_parent_path = implode('/',$replace_array);
		$theme_root_array = explode('/',$this->add_settings['theme_base_url']);
		$theme_root = array_pop($theme_root_array);
		foreach($matches as $match){
			
            if(strpos($match,'{{') !== false || strpos($match,'data:') !== false || strpos($match,'chrome-extension:') !== false){
    
                continue;
    
			}
		    $org_match = $match;
    
            $match1 = str_replace(array('url(',')',"url('","')",')',"'",'"','&#039;'), '', html_entity_decode($match));
    
            $match1 = trim($match1);
			
            if(strpos($match1,'//') > 7){
    
                $match1 = substr($match1, 0, 7).str_replace('//','/', substr($match1, 7));
    
            }
    
            if(empty($match1) || strpos(substr($match1, 0, 1),'#') !== false){
				continue;
			}
    
            if(strpos($match,'cdnjs.cloudflare.com') !== false){
				$img_arr = explode('?',$match1 );
				$ext = pathinfo($img_arr[0], PATHINFO_EXTENSION);
                if(strpos($url,'index.php') === false && $ext == 'css'){
					$response = wp_remote_get($match1);
					if(!is_wp_error( $response ) && !empty($response["body"])){
						$string = str_replace('@import '.$match.';',$response["body"], $string);
					}
					continue;
				}
            }
            if(strpos($match,'fonts.googleapis.com') !== false){
                if(strpos($url,'index.php') !== false){
					$string = $this->w3_combine_google_fonts($match1) ? str_replace('@import '.$match.';','', $string) : $string;
				}else{
					$response = wp_remote_get($match1);
					if(!is_wp_error( $response ) && !empty($response["body"])){
						$string = str_replace('@import '.$match.';',$response["body"], $string);
					}
				}
                continue;
			}
			if(strpos($match,'../fonts/fontawesome-webfont.') !== false){
                $font_text = str_replace('../','',$match1);
                $font_text = str_replace('fonts/fontawesome-webfont.','',$font_text);
                $string = str_replace($match,'url(https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/fonts/fontawesome-webfont.'.$font_text.')',$string);
                continue;
			}
			$match1 = str_replace($this->add_settings['image_home_url'],$this->add_settings['wp_site_url'],$match1);
			
			if($this->w3_is_external($match1)){
                continue;
			}
			$match_arr = $this->w3_parse_url($match1);
			if(substr($match1, 0, 1) == '/' || strpos($match1,'http') !== false){
				$match1 = is_file($this->add_settings['document_root'].'/'.trim($match_arr['path'],'/')) ? $this->add_settings['wp_site_url'].'/'.trim($match_arr['path'],'/') : $match1;
				$import_match = $match1;
			}else{
				$match1 = $url_parent_path.'/'.trim($match_arr['path'],'/');
				$import_match = $url_parent_path.'/'.trim($match_arr['path'],'/');
			}
			if(strpos($match1,'.css')!== false && strpos($string,'@import '.$match)!== false && $url != $this->add_settings['wp_home_url'].'/index.php'){
                $string = str_replace('@import '.$match.';',$this->w3_relative_to_absolute_path($this->removeDotPathSegments($import_match),file_get_contents($this->removeDotPathSegments(str_replace($this->add_settings['wp_site_url'],$this->add_settings['wp_document_root'],$import_match)))), $string);
                continue;
			}
			
			$img_arr = explode('?',$match1 );
			$ext = '.'.pathinfo($img_arr[0], PATHINFO_EXTENSION);
			if($ext == '.'){
                continue;
			}
			if(strpos($img_arr[0],$theme_root) !== false){
				$img_root_path = rtrim($this->add_settings['theme_base_dir'],'/');
				$img_root_url = rtrim($this->add_settings['theme_base_url'],'/');
			}else{
				$img_root_path = $this->add_settings['upload_base_dir'];
				$img_root_url = $this->add_settings['upload_base_url'];
			}
			$webp_enable = $this->add_settings['webp_enable'];
			$webp_enable_instance = $this->add_settings['webp_enable_instance'];
			$webp_enable_instance_replace = $this->add_settings['webp_enable_instance_replace'];
			$imgsrc_filepath = strpos($this->add_settings['home_url'].$match_arr['path'],$img_root_url) !== false ? str_replace($img_root_url,'',$this->add_settings['home_url'].$match_arr['path']) : '';
			if($this->add_settings['is_mobile'] && !empty($this->settings['resp_bg_img']) && (strpos($url,'index.php') !== false || !empty($this->settings['separate_cache_for_mobile']) ) && is_file(str_replace($ext,'-595xh'.$ext,$img_root_path.$imgsrc_filepath))){
				$match1 = str_replace($ext,'-595xh'.$ext,$match1);
				$imgsrc_filepath = str_replace($ext,'-595xh'.$ext,$imgsrc_filepath);
			}
			$imgsrc_webpfilepath = !empty($imgsrc_filepath) ? str_replace($this->add_settings['upload_path'],$this->add_settings['webp_path'],$img_root_path).$imgsrc_filepath.'w3.webp' : '';
			if(!empty($this->settings['webp_png']) && in_array($ext, $webp_enable) && !empty($imgsrc_webpfilepath)){
				if(is_file($imgsrc_webpfilepath) && (!empty($this->add_settings['disable_htaccess_webp']) || !is_file($this->add_settings['wp_document_root']."/.htaccess") || $this->add_settings['image_home_url'] != $this->add_settings['wp_site_url'] )){
					//$match1 = str_replace($this->add_settings['upload_path'],$this->add_settings['webp_path'],$img_arr[0]).'w3.webp';
					$match1 = rtrim(str_replace($webp_enable_instance,$webp_enable_instance_replace,$match1.'"'),'"');
				}else{
					if(!empty($this->settings['opt_img_on_the_go'])){
						$response = $w3_speedster_opt_img->w3_optimize_attachment_url(str_replace($this->add_settings['wp_site_url'],$this->add_settings['document_root'],$img_arr[0]));
					}
				}
			}
			if($match1[0] == '/' || strpos($match1,'http') !== false){
				if($this->add_settings['image_home_url'] == $this->add_settings['wp_site_url']){
					$replacement = 'url('.$match1.')';
				}
				else{
					$match_arr = $this->w3_parse_url($match1);
					$replacement = 'url('.$this->add_settings['wp_site_url'].'/'.trim($match_arr['path'],'/').')';
				}
			}else{
				$match_arr = $this->w3_parse_url($match1);
				$replacement = 'url('.$url_parent_path.'/'.trim($match_arr['path'],'/').')';
			}
			if($this->add_settings['image_home_url'] != $this->add_settings['wp_site_url']){
				if(empty($this->add_settings['exclude_cdn']) || !in_array($ext,$this->add_settings['exclude_cdn'])){
					$replacement  = str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$replacement );
				}
			}
			if(strpos($url,'index.php') !== false){
				$this->w3_str_replace_set_img($org_match, $replacement);
			}else{
            	$string = str_replace($org_match, $replacement, $string);
			}
        }
		return $string;
	}
	
	function removeDotPathSegments($path) {
        if (strpos($path, '.') === false) {
            return $path;
        }

        $inputBuffer = $path;
        $outputStack = [];

        while ($inputBuffer != '') {
            if (strpos($inputBuffer, "./") === 0) {
                $inputBuffer = substr($inputBuffer, 2);
                continue;
            }
            if (strpos($inputBuffer, "../") === 0) {
                $inputBuffer = substr($inputBuffer, 3);
                continue;
            }

            if ($inputBuffer === "/.") {
                $outputStack[] = '/';
                break;
            }
            if (substr($inputBuffer, 0, 3) === "/./") {
                $inputBuffer = substr($inputBuffer, 2);
                continue;
            }

            if ($inputBuffer === "/..") {
                array_pop($outputStack);
                $outputStack[] = '/';
                break;
            }
            if (substr($inputBuffer, 0, 4) === "/../") {
                array_pop($outputStack);
                $inputBuffer = substr($inputBuffer, 3);
                continue;
            }

            if ($inputBuffer === '.' || $inputBuffer === '..') {
                break;
            }

            if (($slashPos = stripos($inputBuffer, '/', 1)) === false) {
                $outputStack[] = $inputBuffer;
                break;
            } else {
                $outputStack[] = substr($inputBuffer, 0, $slashPos);
                $inputBuffer = substr($inputBuffer, $slashPos);
            }
        }

        return implode($outputStack);
    }
	
    function w3_create_file_cache_css($path){
		$file_name = get_option('w3_rand_key');
		$new_path = $this->add_settings['css_ext'] != '.css' ? $this->right_replace($path,'.css',$this->add_settings['css_ext']) : $path ;
        $cache_file_path = $this->w3_get_cache_path('css').'/'.$file_name.'/'.ltrim($new_path,'/');
        if( !file_exists($cache_file_path) ){
			$path1 = explode('/',$path);
			array_pop($path1);
			$path1 = implode('/',$path1);
            $this->w3_check_if_folder_exists($this->w3_get_cache_path('css'.'/'.$file_name.'/'.ltrim($path1,'/')));
			$css = file_get_contents($this->add_settings['document_root'].$path);
			$css = str_replace(array('@charset "utf-8";','@charset "UTF-8";'),'',$css);
			if(function_exists('w3speedup_internal_css_customize')){
				$css = w3speedup_internal_css_customize($css,$path);
			}
			$css = $this->w3_remove_css_comments($css);
			$minify = $this->w3_relative_to_absolute_path($this->add_settings['home_url'].$path,$css);
			$css_minify = 1;
			if(function_exists('w3speedup_internal_css_minify')){
				$css_minify = w3speedup_internal_css_minify($path,$css);
			}
			if($css_minify){
				$minify = $this->w3_css_compress($minify);
			}
			$this->w3_create_file($cache_file_path, $minify);
		}
        if(!file_exists($cache_file_path)){
			return $path;
		}else{
			return str_replace($this->add_settings['document_root'],'',$cache_file_path);
		}
    }
    
    function w3_create_file_cache_css_url($url){
        $cache_file_path = $this->w3_get_cache_path('css').'/'.md5($url).$this->add_settings['css_ext'];
        if( !file_exists($cache_file_path) && $this->w3_endswith($url, '.php') ){
            $css = file_get_contents($url);
			if(function_exists('w3speedup_internal_css_customize')){
				$css = w3speedup_internal_css_customize($css,$url);
			}
			$minify = $this->w3_css_compress($this->w3_relative_to_absolute_path($url,$css));
            $this->w3_create_file($cache_file_path, $minify);
        }
        return str_replace($this->add_settings['document_root'],'',$cache_file_path);
    }

    function minify_css($css_links){ 
		if(!empty($this->settings['exclude_page_from_load_combined_css']) && $this->w3_check_if_page_excluded($this->settings['exclude_page_from_load_combined_css'])){
			return $this->html;
		}
		global $fonts_api_links;
        $all_css1 = '';
		$fonts_api_links = array();
   		$i= 1;
		if(!empty($css_links) && !empty($this->settings['css'])){
			$included_css = array();
			$main_included_css = array();
			$final_merge_css = array();
			$final_merge_main_css = array();
			$css_file_name = '';
			$exclude_css_from_minify = !empty($this->settings['exclude_css']) ? explode("\r\n", $this->settings['exclude_css']) : array();
			$preload_css = $this->add_settings['preload_css'];
			$force_lazyload_css	= !empty($this->settings['force_lazyload_css']) ? explode("\r\n", $this->settings['force_lazyload_css']) : array();
			$force_lazyload_css = function_exists('w3_customize_force_lazyload_css') ? w3_customize_force_lazyload_css($force_lazyload_css) : $force_lazyload_css;
			$enable_cdn = 0;
			if($this->w3_check_enable_cdn_ext('.css')){
				$enable_cdn = 1;
			}
			
			$css_links_arr = array();
			$upload_dir   = wp_upload_dir();
			$upload_dir['baseurl'] = $enable_cdn ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$upload_dir['baseurl']) : $upload_dir['baseurl'];
			foreach($css_links as $key => $css){
				$css_obj = $this->w3_parse_link('link',str_replace($this->add_settings['image_home_url'],$this->add_settings['wp_site_url'],$css));
				if( !empty($css_obj['rel']) && strpos($css_obj['rel'],'stylesheet') !== false && !empty($css_obj['href']) ){
					$css_obj['rel'] = 'stylesheet';
					$css_links_arr[] = array('arr'=>$css_obj,'css'=>$css);
				}elseif(empty($css_obj['rel'])){
					$css_links_arr[] = array('arr'=>array(),'css'=>$css);
				}
			}
			foreach($css_links_arr as $key => $link_arr){
				$css = $link_arr['css'];
				$css_obj = $link_arr['arr'];
				$enable_cdn_path = 0;
				if(!empty($css_obj['rel']) && $css_obj['rel'] == 'stylesheet' && !empty($css_obj['href'])){
					if(!empty($css_obj['media']) && strtolower($css_obj['media']) == 'print'){
						continue;
					}
					
					$org_css = '';
					$media = '';
					$exclude_css1 = 0;
					if(!empty($exclude_css_from_minify)){
						foreach($exclude_css_from_minify as $ex_css){
							if(!empty($ex_css) && strpos($css, $ex_css) !== false){
								$exclude_css1 = 1;
							}
						}
					}
					if($this->w3_check_enable_cdn_path($css_obj['href'])){
						$enable_cdn_path = 1;
					}
					if($exclude_css1){
						if($enable_cdn && $enable_cdn_path && $this->w3_endswith($css_obj['href'], '.css')){
							$css_obj['href'] = str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$css_obj['href']);
							$new_css = str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$css);
							$this->w3_str_replace_set_css($css,$new_css);
						}
						$this->add_settings['preload_resources']['all'][] = $css_obj['href'];
						continue;
					}
					$force_lazy_load = 0;
					if(!empty($force_lazyload_css)){
						foreach($force_lazyload_css as $ex_css){
							if(!empty($ex_css) && strpos($css, $ex_css) !== false){
								$force_lazy_load = 1;
							}
						}
					}
					if($force_lazy_load){
						$this->w3_str_replace_set_css($css,str_replace(' href=',' href="'.$upload_dir['baseurl'].'/blank.css'.'" data-href=',$css));
						continue;
					}
					if(!empty($css_obj['media']) && $css_obj['media'] != 'all' && $css_obj['media'] != 'screen'){
						$media = $css_obj['media'];
					}
					$url_array = $this->w3_parse_url(urldecode($css_obj['href']));
					$url_array['path'] = '/'.ltrim($url_array['path'],'/');
					if(strpos($url_array['path'],'./') !== false || strpos($url_array['path'],'../') !== false){
                    	$url_array['path'] = $this->removeDotPathSegments($url_array['path']);
                    }
					if(!$this->w3_is_external($css_obj['href'])){
						if($this->w3_endswith($css_obj['href'], '.php') || strpos($css_obj['href'], '.php?') !== false ){
							$org_css = $url_array['path'];
							$url_array['path'] = $this->w3_create_file_cache_css_url($css_obj['href']);
							$css_obj['href'] = $this->add_settings['home_url'].$url_array['path'];
						}elseif(!is_file($this->add_settings['document_root'].$url_array['path'])){
							if($this->w3_endswith($css_obj['href'], '.css') || strpos($css_obj['href'], '.css?') !== false ){
								$this->w3_str_replace_set_css($css,'');
								continue;
							}
							/*$org_css = $url_array['path'];
							$url_array['path'] = $this->w3_create_file_cache_css_url($css_obj['href']);
							$css_obj['href'] = $this->add_settings['home_url'].$url_array['path'];*/
						}elseif(filesize($this->add_settings['document_root'].$url_array['path']) > 0){
							$org_css = $url_array['path'];
							$url_array['path'] = $this->w3_create_file_cache_css($url_array['path']);
							$css_obj['href'] = $url_array['path'];
						}else{
							if($this->w3_endswith($css_obj['href'], '.php') || strpos($css_obj['href'], '.php?') !== false || filesize($this->add_settings['document_root'].$url_array['path']) < 1 ){
								$this->w3_str_replace_set_css($css,'');
							}
							continue;
						}
					}
					if(!empty($css_obj['href']) && strpos($css_obj['href'],'fonts.googleapis.com') !== false){
						$response = $this->w3_combine_google_fonts($css_obj['href']);
						if($response){
							$this->w3_str_replace_set_css($css,'');
						}
						$create_css_file = 0;
						continue;
					}
					
					$src = $css_obj['href'];
					if(!empty($src) && !$this->w3_is_external($src) && $this->w3_endswith($src, '.css')){
						$filename = $this->add_settings['document_root'].$url_array['path'];
						if(file_exists($filename) && filesize($filename) > 0){
							$combined_css_file = $this->w3_get_css_url($url_array['path'], $enable_cdn && $enable_cdn_path);
							$this->w3_str_replace_set_css($css,'{{'.$combined_css_file.'}}');
							$combined_css_files[$key] = $combined_css_file;
						}
					}elseif($this->w3_endswith($src, '.css') || strpos($src, '.css?')){
						$this->w3_str_replace_set_css($css,'{{'.$css_obj['href'].'}}');
						$combined_css_files[$key] = $css_obj['href'];
						//$this->w3_str_replace_set_css($css,'');
					}
				}
			}
			if(!empty($remove_css_tags)){
				foreach($remove_css_tags as $css){
					$this->w3_str_replace_set_css($css,'');
				}
			}
			$appendonstyle = $this->w3_get_pointer_to_inject_files($this->html);
			$css_defer = '';
			//if(is_array($final_merge_css) && count($final_merge_css) > 0){
			if(!empty($this->settings['load_critical_css'])){
				$ignore_critical_css = 0;
				if((function_exists('is_user_logged_in') && is_user_logged_in()) || is_search() || is_404()){
					$ignore_critical_css = 1;
				}
				if(function_exists('w3_no_critical_css')){
					 $ignore_critical_css = w3_no_critical_css($this->add_settings['full_url']);
				}
				if(!$ignore_critical_css){
					$file_name = 0;
					if(function_exists('w3speedup_customize_critical_css_filename')){
						$final_merge_css = w3speedup_customize_critical_css_filename($final_merge_css);
					}
					foreach($final_merge_css as $css_arr){
						$file_name += count($css_arr);
					}
					$main_css_file_name = md5($file_name).$this->add_settings['css_ext'];
					$this->add_settings['critical_css'] = $main_css_file_name;
					if(is_file($this->w3_preload_css_path().'/'.$this->add_settings['critical_css']) && !empty($this->settings['load_critical_css'])){
						/*if(function_exists('w3speedup_customize_critical_css')){
							$critical_css = file_get_contents($this->w3_preload_css_path().'/'.$this->add_settings['critical_css']);
							$critical_css = w3speedup_customize_critical_css($critical_css);
							$this->w3_create_file($this->w3_preload_css_path().'/'.$this->add_settings['critical_css'], $critical_css);
						}*/
						$file = $this->w3_get_full_url_cache_path().'/critical_css.json';
						$this->w3_create_file($file,$this->add_settings['critical_css']);
					}
				}
			}
			//}
			$all_inline_css = (!empty($this->settings['custom_css']) ? $this->w3_css_compress(stripslashes($this->settings['custom_css'])) : '');
			$this->w3_insert_content_head('<style id="w3_bg_load">div:not(.w3_bg), section:not(.w3_bg), iframelazy:not(.w3_bg){background-image:none !important;}</style><style id="w3speedster-custom-css">'.$all_inline_css.'</style>',4);
			//$this->w3_str_replace_set_css('</head>','<style id="w3speedster-custom-css">'.$all_inline_css.'</style></head>');
			if(!empty($combined_css_files) && is_array($combined_css_files)){
				foreach($combined_css_files as $key=>$css){
					$this->add_settings['preload_resources']['css'][] = $css;
					if(!empty($css_links_arr[$key]['arr']) && count((array)$css_links_arr[$key]['arr']) > 0){
						$css_link = '';
						foreach((array)$css_links_arr[$key]['arr'] as $attr => $attr_value){
							if($attr != 'href' && $attr != 'data-href' && $attr != 'onload' && $attr != 'onerror' && $attr != 'type' && $attr != 'html' ){
								$css_link .= " $attr='$attr_value'";
							}
						}
					}
					
					$this->w3_str_replace_set_css('{{'.$css.'}}','<link rel="stylesheet" data-css="1" href="'.$css.'"'.$css_link.' />');
				}
			}
		}
		
	}
	function w3_get_css_url($css,$enable_cdn){
		if($enable_cdn){
			$css = str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$this->add_settings['wp_site_url'].$css);
		}else{
			$css = $this->add_settings['wp_site_url'].$css;
		}
		return $css;
	}
	
}