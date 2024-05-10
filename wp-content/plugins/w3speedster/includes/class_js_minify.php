<?php
namespace W3speedster;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class w3speedster_js extends w3speedster_css{

    
	function w3_modify_file_cache_js($string, $path){
		$src_array = explode('/',$path);
		$count = count($src_array);
		unset($src_array[$count-1]);
		if(!empty($this->settings['load_combined_js'])){
			if((stripos($string,'holdready') !== false || stripos($string,'S.holdReady') !== false) && empty($this->add_settings['holdready'])){
				$string .= ';if(typeof($) == "undefined"){$ = jQuery;}else{var $ = jQuery;}';
				$this->add_settings['holdready'] = 1;
			}
			$exclude_from_w3_changes = 0;
			if(function_exists('w3speedup_exclude_internal_js_w3_changes')){
				$exclude_from_w3_changes = w3speedup_exclude_internal_js_w3_changes($path,$string);
			}
			if(stripos($string,'holdready') === false && !$exclude_from_w3_changes){
				
				$string = $this->w3_changes_in_js($string);
				
			}
		}
		if(function_exists('w3speedup_internal_js_customize')){
			$string = w3speedup_internal_js_customize($string,$path);
		}
		return $string;
	}
	function w3_create_file_cache_js_url($path){
		$file_name = get_option('w3_rand_key');
        $cache_file_path = $this->w3_get_cache_path('js').'/'.$file_name.'/'.ltrim($path,'/');
	    //$cache_file_path = $this->w3_get_cache_path('js').'/'.md5($this->add_settings['w3_rand_key'].$path).'.js';
        if( !file_exists($cache_file_path) ){
			$path1 = explode('/',$path);
			array_pop($path1);
			$path1 = implode('/',$path1);
            $this->w3_check_if_folder_exists($this->w3_get_cache_path('js'.'/'.$file_name.'/'.ltrim($path1,'/')));
            //$this->w3_check_if_folder_exists($this->w3_get_cache_path('js'));
			$string = file_get_contents($path);
            $string = $this->w3_modify_file_cache_js($string, $path);
            $this->w3_create_file($cache_file_path, $string );
        }
		if(!file_exists($cache_file_path)){
			return $path;
		}else{
			return str_replace($this->add_settings['document_root'],'',$cache_file_path);
		}
	}
	function w3_changes_in_js($string){
		$string = preg_replace('/"(\s*)use strict(\s*)"(\s*);/', '', $string, 1);
		$string = preg_replace('/([\s\;\:\}\,\)\(\{])([a-zA-Z]+)([.]addEventListener\s*\(\s*[\'|"]\s*readystatechange\s*[\'|"]\s*,)/', "$1$2.addEventListener('w3-DOMContentLoaded',", $string);
		$string = preg_replace('/([\s\;\:\}\,\)\(\{])([a-zA-Z]+)([.]addEventListener\s*\(\s*[\'|"]\s*DOMContentLoaded\s*[\'|"]\s*,)/', "$1$2.addEventListener('w3-DOMContentLoaded',", $string);
		$string = preg_replace('/(jQuery|\$)(\s*\(\s*)(\(*\s*)(\s*function\s*\(\s*[\S|\$]*\s*\))/', 'jQuery(document).ready($3$4', $string);
		$string = preg_replace('/(jQuery|\$)(\s*\(\s*)([\(\s]*\(\s*\(\s*\)=>)/', 'jQuery(document).ready($3', $string);
		$string = preg_replace('/(jQuery|\$)(\s*\(\s*)(document)(\)\s*)\.on(\s*\([\'|\"]\s*ready\s*[\'|\"]\s*),(\s*function\s*\(\s*[.]*\s*\))/', "jQuery(document).ready($6", $string);
		
		return $string;
	}
    function w3_create_file_cache_js($path){
		$file_name = get_option('w3_rand_key');
        $cache_file_path = $this->w3_get_cache_path('js').'/'.$file_name.'/'.ltrim($path,'/');
	    //$cache_file_path = $this->w3_get_cache_path('js').'/'.md5($this->add_settings['w3_rand_key'].$path).'.js';
        if( !file_exists($cache_file_path) ){
			$path1 = explode('/',$path);
			array_pop($path1);
			$path1 = implode('/',$path1);
            $this->w3_check_if_folder_exists($this->w3_get_cache_path('js'.'/'.$file_name.'/'.ltrim($path1,'/')));
            $string = file_get_contents($this->add_settings['document_root'].$path);
            $string = $this->w3_modify_file_cache_js($string, $path);
            $this->w3_create_file($cache_file_path, $string );
        }
	    return str_replace($this->add_settings['document_root'],'',$cache_file_path);
    }
    
    function w3_compress_js($string){
        include_once W3SPEEDSTER_PLUGIN_DIR.'includes/jsmin.php';
        $string = \W3jsMin::minify($string);
        return $string;
    }
    
    function minify($script_links){
		if(!empty($this->settings['exclude_page_from_load_combined_js']) && $this->w3_check_if_page_excluded($this->settings['exclude_page_from_load_combined_js'])){
			return ;
        }
		
		if(!empty($script_links) && !empty($this->settings['js'])){
			$lazy_load_js = !empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' ? 1 : 0;
			$force_innerjs_to_lazy_load  = !empty($this->settings['force_lazy_load_inner_javascript']) ? explode("\r\n", $this->settings['force_lazy_load_inner_javascript']) : array();
            $exclude_js_arr_split  = !empty($this->settings['exclude_javascript']) ? explode("\r\n", $this->settings['exclude_javascript']) : array();
			foreach($exclude_js_arr_split as $key => $value){
				if(strpos($value,' defer') !== false){
					$exclude_js_arr[$key]['string'] = str_replace(' defer','',$value);
					$exclude_js_arr[$key]['defer'] = 1;
				}elseif(strpos($value,' full') !== false){
					$exclude_js_arr[$key]['string'] = str_replace(' full','',$value);
					$exclude_js_arr[$key]['full'] = 1;
				}else{	
					$exclude_js_arr[$key]['string'] = $value;
					$exclude_js_arr[$key]['defer'] = 0;
				}
			}
            $exclude_inner_js= !empty($this->settings['exclude_inner_javascript']) ? explode("\r\n", stripslashes($this->settings['exclude_inner_javascript'])) : array('google-analytics', 'hbspt',base64_decode("LyogPCFbQ0RBVEFbICov"));
            $load_ext_js_before_internal_js = !empty($this->settings['load_external_before_internal']) ? explode("\r\n", $this->settings['load_external_before_internal']) : array();
            $all_js='';
            $included_js = array();
            $final_merge_js = array();
            $js_file_name = '';
            $enable_cdn = 0;
            if($this->add_settings['image_home_url'] != $this->add_settings['wp_site_url']){
				$ext = '.js';
				if(empty($this->add_settings['exclude_cdn']) || !in_array($ext,$this->add_settings['exclude_cdn'])){
					$enable_cdn = 1;
				}
			}
			
			$final_merge_has_js = array();
			
			for($si=0; $si < count($script_links); $si++){
                $script = $script_links[$si];
				$script_obj = !empty($this->add_settings['script_obj'][$si]) ? $this->add_settings['script_obj'][$si] : $this->w3_parse_link('script',str_replace($this->add_settings['image_home_url'],$this->add_settings['wp_site_url'],$script_links[$si]));
				$script_text = '';
				if(!array_key_exists('src',$script_obj)){
                    $script_text = $this->w3_parse_script('<script',$script);
                }
				if(!empty($script_obj['type']) && strtolower($script_obj['type']) != 'application/javascript' && strtolower($script_obj['type']) != 'module' && strtolower($script_obj['type']) != 'text/javascript' && strtolower($script_obj['type']) != 'text/jsx;harmony=true'){
                    continue;
                }
				if(function_exists('w3speedup_customize_script_object')){
					$script_obj = w3speedup_customize_script_object($script_obj, $script);
				}
                if(!empty($script_obj['src'])){
					
					//echo $script_obj['src'];
                    $url_array = $this->w3_parse_url($script_obj['src']);
					$url_array['path'] = '/'.ltrim($url_array['path'],'/');
                    $exclude_js = 0;
					$enable_cdn_path = 0;
                    if(!empty($exclude_js_arr) && is_array($exclude_js_arr)){
						foreach($exclude_js_arr as $ex_js){
							if(!empty($ex_js['string']) && strpos($script,$ex_js['string']) !== false){
								if(!empty($ex_js['defer'])){
									$exclude_js = 2;
								}elseif(!empty($ex_js['full'])){
									$exclude_js = 3;
								}else{
									$exclude_js = 1;
								}
							}
						}
					}
					if(function_exists('w3speedup_exclude_javascript_filter')){
						$exclude_js = w3speedup_exclude_javascript_filter($exclude_js,$script_obj,$script,$this->html);
					}
					if($exclude_js){
						$this->settings['js_is_excluded'] = 1;
					}
					if($this->w3_check_enable_cdn_path($script_obj['src'])){
						$enable_cdn_path = 1;
					}
					if(!$this->w3_is_external($script_obj['src']) && $this->w3_endswith($url_array['path'], '.js') && $exclude_js != 3){
                        $old_path = $url_array['path'];
                        if(file_exists($this->add_settings['document_root'].$url_array['path'])){
							$url_array['path'] = $this->w3_create_file_cache_js($url_array['path']);
						}else{
							$url_array['path'] = $this->w3_create_file_cache_js_url($script_obj['src']);
						}
                        $script_obj['src'] = $this->add_settings['wp_site_url'].$url_array['path'];
                    }
					if($exclude_js){
                        if( $exclude_js == 3){
							$script_obj['src'] = $enable_cdn && $enable_cdn_path ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'] ,$script_obj['src']) : $script_obj['src'];
							$this->add_settings['preload_resources']['all'][] = $script_obj['src'];
							$this->w3_str_replace_set($script,$this->w3_implode_link_array('script',$script_obj));
							continue;
						}
						if( $exclude_js == 2){
                            $script_obj['defer'] = 'defer';
						}
						if(file_exists($this->add_settings['document_root'].$url_array['path']) && strpos(file_get_contents($this->add_settings['document_root'].$url_array['path']),'jQuery requires a window with a document') !== false){
							$this->add_settings['jquery_excluded'] = $this->add_settings['document_root'].$url_array['path'];
						}
						$script_obj['src'] = $enable_cdn && $enable_cdn_path ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'] ,$script_obj['src']) : $script_obj['src'];
						if(!empty($exclude_js) && $exclude_js == 1){
							$this->add_settings['preload_resources']['all'][] = $script_obj['src'];
						}
						$this->w3_str_replace_set($script,$this->w3_implode_link_array('script',$script_obj));
                        continue;
                    }
                    $exclude_js_bool=0;
					if(!empty($force_innerjs_to_lazy_load)){
                        foreach($force_innerjs_to_lazy_load as $js){
                            if( !empty($js) && strpos($script,$js) !== false){
                                $exclude_js_bool=1;
                                break;
                            }
                        }
                    }
					
                    $val = $script_obj['src'];
                    if(!empty($val) && !$this->w3_is_external($val) && strpos($script, '.js') && empty($exclude_js_bool)){
						if(!empty($script_obj['type']) && $script_obj['type'] != 'text/javascript'){
							$script_obj['data-w3-type']= $script_obj['type'];
						}
						$script_obj['type'] = 'lazyload_int';
						$script_obj['data-src'] = $enable_cdn && $enable_cdn_path ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'] ,$this->add_settings['wp_site_url'].$url_array['path']) : $this->add_settings['wp_site_url'].$url_array['path'];
						unset($script_obj['src']);
						$this->w3_str_replace_set($script,$this->w3_implode_link_array('script',$script_obj));
					}elseif($this->w3_is_external($val) && empty($exclude_js_bool) ){
						if(!empty($script_obj['type']) && $script_obj['type'] != 'text/javascript'){
							$script_obj['data-w3-type']= $script_obj['type'];
						}
						$script_obj['type'] = 'lazyload_int';
						$script_obj['data-src'] = $script_obj['src'];
						unset($script_obj['src']);
						$this->w3_str_replace_set($script,$this->w3_implode_link_array('script',$script_obj));
					}elseif($exclude_js_bool){
						if(!empty($script_obj['type']) && $script_obj['type'] != 'text/javascript'){
							$script_obj['data-w3-type']= $script_obj['type'];
						}
						$script_obj['data-src'] = $enable_cdn && $enable_cdn_path ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'] ,$script_obj['src']) : $script_obj['src'];
						unset($script_obj['src']);
						$script_obj['type'] = 'lazyload_ext';
						if(function_exists('w3_external_javascript_customize')){
							$script_obj = w3_external_javascript_customize($script_obj, $script);
						}
						$this->w3_str_replace_set($script,$this->w3_implode_link_array('script',$script_obj));
                    }
                }else{
                    
                    $inner_js = $script_text;
                    $lazy_loadjs = 0;
                    $exclude_js_bool = 0;
					$force_js_bool = 0;
                    $exclude_js_bool = $this->w3_check_js_if_excluded($inner_js, $exclude_inner_js);
					if(function_exists('w3speedup_inner_js_customize')){
						$script_text = w3speedup_inner_js_customize($script_text);
					}
					if(!empty($force_innerjs_to_lazy_load)){
                        foreach($force_innerjs_to_lazy_load as $js){
                            if(strpos($script_text,$js) !== false){
                                $exclude_js_bool=0;
								$force_js_bool = 1;
                                break;
                            }
                        }
                    }
                    if(!empty($exclude_js_bool) && $exclude_js_bool != 2){
						if(function_exists('w3speedup_inner_js_customize')){
							$this->w3_str_replace_set($script,'<script>'.$script_text.'</script>');
						}
					}else{
						if(!empty($script_obj['type']) && $script_obj['type'] != 'text/javascript'){
							$script_obj['data-w3-type']= $script_obj['type'];
						}
						if($exclude_js_bool == 2){
							$script_modified = '<script type="lazyload_int" ';
						}elseif($force_js_bool){
    						$script_modified = '<script type="lazyload_ext" ';
    					}else{
    						$script_modified = '<script type="lazyload_int" ';
    					}
    					foreach($script_obj as $key => $value){
                            if($key != 'type' && $key != 'html'){
                                $script_modified .= $key.'="'.$value.'" ';
                            }
                        }
						if(!empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' && !empty($force_js_bool)){
							$script_text = $this->w3_changes_in_js($script_text);
						}
						
						$script_modified = $script_modified.'>'.$script_text.'</script>';
						
						$this->w3_str_replace_set($script,$script_modified);
						if(!empty($final_merge_js) && count($final_merge_js) > 0){
							$cache_js_url = $this->w3_create_js_combined_cache_file($final_merge_js, $enable_cdn && $enable_cdn_path);
							$this->w3_replace_js_files_with_combined_files($final_merge_has_js,$cache_js_url);
							$final_merge_js = array();
							$final_merge_has_js = array();
						}
						
					}
                }
				if($si == count($script_links)-1 && !empty($final_merge_has_js)){
					if(!empty($final_merge_js) && count($final_merge_js) > 0){
						$cache_js_url = $this->w3_create_js_combined_cache_file($final_merge_js, $enable_cdn && $enable_cdn_path);
						$this->w3_replace_js_files_with_combined_files($final_merge_has_js, $cache_js_url);
						$final_merge_js = array();
					}
				}
            }
			if(!empty($this->settings['custom_javascript'])){
			   if(!empty($this->settings['custom_javascript_file'])){    
					$custom_js_path = $this->w3_get_cache_path('all-js').'/wnw-custom-js.js';
					if(!is_file($custom_js_path)){
						$this->w3_create_file($custom_js_path, stripslashes($this->settings['custom_javascript']));
					}
					$custom_js_url = $this->w3_get_cache_url('all-js').'/wnw-custom-js.js';
					$custom_js_url = $enable_cdn && $enable_cdn_path ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'] ,$custom_js_url) : $custom_js_url;
					$position = strrpos($this->html,'</body>');
					$this->html = substr_replace( $this->html, '<script '.(!empty($this->settings['custom_javascript_defer']) ? 'defer="defer"' : '').' id="wnw-custom-js" src="'.$custom_js_url.'?ver='.rand(10,1000).'"></script>', $position, 0 );
				}else{
					$position = strrpos($this->html,'</body>');
					$this->html = substr_replace( $this->html, '<script>'.stripslashes($this->settings['custom_javascript']).'</script>', $position, 0 ); 
				}
			}
		}
        
        
    }
	function w3_check_js_if_excluded($inner_js, $exclude_inner_js){
		$exclude_js_bool=0;
		if(strpos($inner_js,'moment.') === false && strpos($inner_js,'wp.') === false && strpos($inner_js,'.noConflict') === false && strpos($inner_js,'wp.i18n') === false){
			$exclude_js_bool=2;
		}
		if(strpos($inner_js,'DOMContentLoaded') !== false || strpos($inner_js,'jQuery(') !== false || strpos($inner_js,'$(') !== false || strpos($inner_js,'jQuery.') !== false || strpos($inner_js,'$.') !== false){
			$exclude_js_bool=2;
		}
		
		if(!empty($exclude_inner_js)){
			foreach($exclude_inner_js as $js){
				if(strpos($inner_js,$js) !== false){
					return 1;
					break;
				}
			}
		}
		return $exclude_js_bool;
	}
	function w3_replace_js_files_with_combined_files($final_merge_has_js,$cache_js_url){
		if(!empty($final_merge_has_js)){
			$lazy_load_js = !empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' ? 1 : 0;
			for($ii = 0; $ii < count($final_merge_has_js); $ii++){
				if($ii == count($final_merge_has_js) -1 ){
					$this->w3_str_replace_set($final_merge_has_js[$ii],'<script type="lazyload_int" data-src="'.$cache_js_url.'"></script>');
				}else{
					$this->w3_str_replace_set($final_merge_has_js[$ii],'');
				}
			}
		}
	}
	
	function w3_create_js_combined_cache_file($final_merge_js, $enable_cdn){
		$file_name = is_array($final_merge_js) ? $this->add_settings['w3_rand_key'].'-'.implode('-', $final_merge_js) : '';
		if(!empty($file_name)){
			$js_file_name = md5($file_name).$this->add_settings['js_ext'];
			if(!is_file($this->w3_get_cache_path('all-js').'/'.$js_file_name)){
				$all_js = '';
				foreach($final_merge_js as $key => $script_path){
					$all_js .= file_get_contents($this->add_settings['document_root'].$script_path).";\n";
				}
				$this->w3_create_file($this->w3_get_cache_path('all-js').'/'.$js_file_name, $all_js);
			}
			$main_js_url = $this->w3_get_cache_url('all-js').'/'.$js_file_name;
			$main_js_url = $enable_cdn ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'] ,$main_js_url) : $main_js_url;
			return $main_js_url;
		}
	}
    function w3_lazy_load_javascript(){
		$enable_cdn = 0;
		if($this->add_settings['image_home_url'] != $this->add_settings['wp_site_url'] ){
			$enable_cdn = 1;
		}
		$exclude_cdn_arr = !empty($this->add_settings['exclude_cdn']) ? $this->add_settings['exclude_cdn'] : array();
		$lazy_load_by_px = !empty($this->settings['lazy_load_px']) ? (int)$this->settings['lazy_load_px'] : 200;
        $google_fonts_delay_load = !empty($this->settings['google_fonts_delay_load']) ? $this->settings['google_fonts_delay_load']*1000 : 2000;
        $script = 'var w3_lazy_load_by_px='.$lazy_load_by_px.';var blank_image_webp_url = "'. (($enable_cdn && !in_array('.png',$exclude_cdn_arr)) ? str_replace($this->add_settings['wp_site_url'],$this->add_settings['image_home_url'],$this->add_settings['upload_base_url']): $this->add_settings['upload_base_url']).'/blank.pngw3.webp";var google_fonts_delay_load = '.$google_fonts_delay_load.';var w3_upload_path="'.$this->add_settings['upload_path'].'"; var w3_webp_path="'.$this->add_settings['webp_path'].'";var w3_mousemoveloadimg = false;var w3_page_is_scrolled = false;var w3_lazy_load_js = '.(!empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' ? 1 : 0).';var w3_js_is_excluded = '.(!empty($this->settings['js_is_excluded']) ? 1 : 0).';';
		if(!empty($this->settings['exclude_page_from_load_combined_js']) && $this->w3_check_if_page_excluded($this->settings['exclude_page_from_load_combined_js'])){
			$script.='var w3_excluded_js=1;';
        }else{
			$script.='var w3_excluded_js=0;';
		}
		return $script.file_get_contents(W3SPEEDSTER_PLUGIN_DIR.'assets/js/script-load.min.js');
	}
	function w3_lazy_load_images(){
		
		$inner_script_optimizer = file_get_contents(W3SPEEDSTER_PLUGIN_DIR.'assets/js/img-lazyload.js');
        $custom_js_path = $this->w3_get_cache_path('all-js').'/wnw-custom-inner-js.js';
        if(!is_file($custom_js_path)){
            $this->w3_create_file($custom_js_path,$this->w3_compress_js($inner_script_optimizer));
        }
        return file_get_contents($custom_js_path);
    
    }
}