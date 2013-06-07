<?php
class CMS_base {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* POST variable
	*/
	var $post;
	
	function setSmarty(&$smarty) {
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;
	}
	
	function setPost($post) {
		$this->post		= $post;
	}
	
	function setObjDA(&$objDA) {
		$this->objDA	= $objDA;
	}
	
	/*
	* Class constructor
	*/
	function __construct() {

	}
	
	/*
	* Method to scale an image
	*/
	protected function imageScale($image_path,$image_name,$max_width = 80,$max_height = 60) {
		$src_img = imagecreatefromjpeg($image_path.'/'.$image_name); 
		
		$img_height = imagesy($src_img); 
		$img_width  = imagesx($src_img); 
		
		if ($height <= $max_height && $width <= $max_width) 
			return true; 
		
		//first, we need to figure out which dimension is the largest 
		$width_change = $max_width/$img_width; 
		$height_change = $max_height/$img_height;
		
		if($width_change < $height_change) { 
			$new_w = $max_width; 
			$new_h = $img_height * $width_change; 
		} else {
			$new_h = $max_height; 
			$new_w = $img_width * $height_change; 
		}
		
		
		$dst_img = imagecreatetruecolor($new_w,$new_h); 
		
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_w,$new_h,$img_width,$img_height); 
		
		imagejpeg($dst_img, $image_path/$image_name, 70); 
		return true;
	}
	
	/*
	* Method to get scaled image height based on width
	*/
	protected function imageHeightFromWidth($width,$height,$new_w) {
		if($width == 0 || $height == 0)
			return 0;
		$r	= ($new_w / $width) * $height;
		
		return (int)$r;
	}
	
	/*
	* Method to get scaled image height based on height
	*/
	protected function imageWidthFromHeight($width,$height,$new_h) {
		if($width == 0 || $height == 0)
			return 0;
		
		$r	= ($new_h / $height) * $width;
		
		return (int)$r;
	}
	
	/*
	* Method to build categories tree
	*	@param	bool	root		If true, will display "- ROOT" as the root-level node
	*	@param	bool	flat		If false, will return as nested array
	*/
	protected function categoriesBuildTreeRoot($root=false,$flat=false,$indentChar='-') {
		$q	= "SELECT * FROM cms_products_categories WHERE parent = 0 ORDER BY sort";
		$this->objDA->query($q);
		
		$nodes		= $this->objDA->returnArray();
		
		if($root === true) {
			$nodeTree	= array(0 => array('id' => 0, 'name' => '- ROOT'));
			$iteration	= 2;
		} else {
			$nodeTree	= array();
			$iteration	= 2;
		}
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= '&nbsp;&nbsp;';
		}
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= $indentChar;
		}
		
		$indent		.= '&nbsp;';
				
		$count		= count($nodes);
		
		for($i=0;$i<$count;$i++) {
			
			$nodes[$i]['name']	= $indentChar.'&nbsp;' . $nodes[$i]['name'];
			
			if(($i+1)<$count)
				$nodes[$i]['navDown']	= true;
			else
				$nodes[$i]['navDown']	= false;
			
			array_push($nodeTree,$nodes[$i]);
			
			$this->categoriesBuildTree($nodes[$i]['id'],$nodeTree,$iteration,$indentChar);
		}
		
		if($flat === true) {
			$flatTree	= array();
			
			foreach($nodeTree as $nodes) {
				$flatTree[$nodes['id']]	= $nodes['name'];
			}
			
			return $flatTree;
		} else {
			return $nodeTree;
		}
	}
	
	/*
	* Method to build array of child nodes to $nodeTree (passed as reference)
	*/
	protected function categoriesBuildTree($parent,&$nodeTree,&$iteration=1,$indentChar='-') {
		$q	= "SELECT * FROM cms_products_categories WHERE parent = '".$parent."' ORDER BY sort";
		$this->objDA->query($q);
		
		$nodes		= $this->objDA->returnArray();
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= '&nbsp;&nbsp;';
		}
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= $indentChar;
		}
		
		$indent	.= '&nbsp;';

		$count	= count($nodes);
		
		for($i=0;$i<$count;$i++) {
			$nodes[$i]['name']		= $indent . $nodes[$i]['name'];
			
			if(($i+1)<$count)
				$nodes[$i]['navDown']	= true;
			else
				$nodes[$i]['navDown']	= false;
			
			array_push($nodeTree,$nodes[$i]);
			
			$iteration++;
			$this->categoriesBuildTree($nodes[$i]['id'],$nodeTree,$iteration);
			$iteration--;
		}
		
		return;
	}
	
	/*
	* Method to clean user input
	*/
	protected function sanitize($input) {
		$input	= addslashes($input);
		
		return $input;
	}
}
?>