<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members image_resize utility class
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_image_resize {	
	// constructor
	function __construct() {
		// php4
		$this->mgm_image_resize();
	}	
	
	// php4 construct
	function mgm_image_resize(){			
		// do init stuff			 
	}
	
	//resize:
	function resize_image( $src, $dst, $type = 'thumb' ) {	
		$settings = mgm_get_class('system')->get_setting();	
		switch($type) {
			case 'thumb':				
				if(isset($settings['thumbnail_image_width']) && !empty($settings['thumbnail_image_width']))
					$width 	= $settings['thumbnail_image_width'];
				else 
					$width 	= get_option('thumbnail_size_w');
				if(isset($settings['thumbnail_image_height']) && !empty($settings['thumbnail_image_height']))
					$height = $settings['thumbnail_image_height']; 
				else		
					$height = get_option('thumbnail_size_h'); 
				//medium image:	
				if(isset($settings['medium_image_width']) && !empty($settings['medium_image_width']))	
					$this->medium_width = $settings['medium_image_width']; 
				else 
					$this->medium_width = get_option('medium_size_w'); 	
				break;		
			case 'medium':				
				if(isset($settings['medium_image_width']) && !empty($settings['medium_image_width']))	
					$width = $settings['medium_image_width']; 
				else 
					$width = get_option('medium_size_w'); 						
				if(isset($settings['medium_image_height']) && !empty($settings['medium_image_height']))	
					$height = $settings['medium_image_height']; 		 
				else
					$height = get_option('medium_size_h'); 
				break;						
		}	
		
		list ($current_width, $current_height) = getimagesize($src);
   		//noneed to resize:
		if ($current_width <= $width && $current_height <= $height){
   			return false;
   		}
		
		if ( $height <= 0 && $width <= 0 ) 
			return false;		
					
		$image = '';
		$final_width = 0;
		$final_height = 0;
		list ($width_old, $height_old, $image_type) = getimagesize($src);		
		
		if     ($width  == 0) $factor = $height / $height_old;
		elseif ($height == 0) $factor = $width / $width_old;
		else                  $factor = min( $width / $width_old, $height / $height_old );
		
		$final_width  = round( $width_old * $factor );
		$final_height = round( $height_old * $factor );
		
		switch ( $image_type ) {
		  case IMAGETYPE_GIF:  
		  		$image = imagecreatefromgif($src);  
		  		break;
		  case IMAGETYPE_JPEG: 
		  		$image = imagecreatefromjpeg($src); 
		  		break;
		  case IMAGETYPE_PNG:  
		  		$image = imagecreatefrompng($src);  
		  		break;
		  default: 
		  		return false;
		}
	
		$image_resized = imagecreatetruecolor( $final_width, $final_height );
		if ( $image_type == IMAGETYPE_GIF || $image_type == IMAGETYPE_PNG ){
		  $transparency = ImageColorTransparent($image);		  
		  if ( $image_type == IMAGETYPE_GIF && $transparency >= 0 ){
		    list($r, $g, $b) = array_values (imagecolorsforindex($image, $transparency));
		    $transparency = imagecolorallocate($image_resized, $r, $g, $b);
		    imagefill($image_resized, 0, 0, $transparency);
		    imagecolortransparent($image_resized, $transparency);
		  }
		  elseif ($image_type == IMAGETYPE_PNG) {
		    imagealphablending($image_resized, false);
		    $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
		    imagefill($image_resized, 0, 0, $color);
		    imagesavealpha($image_resized, true);
		  }
		}
		imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);				
		switch ( $image_type ) {
		  case IMAGETYPE_GIF:  
		  		imagegif($image_resized, $dst);  
		  		break;
		  case IMAGETYPE_JPEG: 
		  		imagejpeg($image_resized, $dst, 85); 
		  		break;
		  case IMAGETYPE_PNG:  
		  		imagepng($image_resized, $dst);  
		  		break;
		  default: 
		  		return false;
		}
		return true;		
	}
}
// core/libs/utilities/mgm_image_resize.php