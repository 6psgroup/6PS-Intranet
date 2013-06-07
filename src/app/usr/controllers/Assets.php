<?php
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';

require_once __SITE_FS_ROOT . '/app/usr/models/IBillingSystem.php';
require_once __SITE_FS_ROOT . '/app/usr/models/Billing.php';

require_once __SITE_FS_ROOT . '/app/lib/Barcode/barcode.inc.php';

class Assets extends AdminCP_adminMenu implements IModule {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* Section ID
	*/
	var $sectionID	= 6;
	
	/*
	* POST variable
	*/
	var $post;
	
	function setSmarty($smarty) {
		global $msg;
		
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;
		
		$this->smarty->assign('page_title','6PS Intranet :: Assets');
		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(6);
		$this->smarty->assign('menuMainSel','Assets');
		$this->smarty->assign('headerUsername',$_SESSION['username']);
		
		// Verify access to section
		if($this->permissionCheck($this->sectionID) !== true) {
			echo 'Access denied.';
			die;
		}
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
		parent::__construct();
	}
	
	/*
	* Main
	*/
	function main() {
		$this->assetList();
	}

////////////////////////////////
// ASSETS		              //
////////////////////////////////
	/*
	* Method to display assets dashboard
	*/
	function dashboard() {
		$this->smarty->assign('menuSubSel','Assets');
		$this->smarty->assign('block_title','Assets Dashboard');
		
		$this->smarty->display('assets/dashboard.tpl');
	}







////////////////////////////////
// ASSETS		              //
////////////////////////////////
	/*
	*  Method to list assets
	*/
	function assetList($cat=1) {
		global $msg;
		
		$this->smarty->assign('menuSubSel','Assets');
		$this->smarty->assign('block_title','Assets');
		
		$r			= $this->catBuildChildrenRoot();
		$catTree	= array();
		
		foreach($r as $key=>$value) {
			$catTree[$value['id']]	= $value['name'];
		}
			
		$this->smarty->assign('catTree',$catTree);
		
		if($_POST['cat'] != NULL) {
			$root	= $_POST['cat'];
		} else {
			$root	= $cat;
		}
		
		$this->smarty->assign('selCat',$root);
		
		$q	= "SELECT * FROM assets_assets WHERE cat = '".$root."'";
		$this->objDA->query($q);
		
		$assets	= $this->objDA->returnArray();
		$this->smarty->assign('assets',$assets);
		
		$this->smarty->display('assets/assetList.tpl');
	}
	
	/*
	* Method to add or edit asset
	*/
	function assetEdit($id=0,$catid=0) {
		$this->smarty->assign('menuSubSel','Assets');
		
		// get asset accounts
		$q	= "SELECT * FROM fin_chart_accounts WHERE type = '6' AND name NOT LIKE 'A/D%' ORDER BY num";
		$this->objDA->query($q);
		$r			= $this->objDA->returnArray();
		$accounts	= array(0 => '');
		foreach($r as $key=>$value) {
			$accounts[$value['id']]	= $value['num'].' - '.$value['name'];
		}
		$this->smarty->assign('assetaccounts',$accounts);
		
		// get depreciation accounts
		$q	= "SELECT * FROM fin_chart_accounts WHERE type = '6' AND name LIKE 'A/D%' ORDER BY num";
		$this->objDA->query($q);
		$r			= $this->objDA->returnArray();
		$accounts	= array(0 => '');
		foreach($r as $key=>$value) {
			$accounts[$value['id']]	= $value['num'].' - '.$value['name'];
		}
		$this->smarty->assign('depreciationaccounts',$accounts);
		
		if($this->post)
			$id		= $this->sanitize($this->post['id']);
			
		// Load fields
		$q		= "	SELECT
						cf.id,
						cf.catid,
						cf.name,
						ft.type AS type,
						cf.options,
						cf.required,
						cf.enabled,
						cf.sort
					FROM
						assets_cat_fields cf
						LEFT JOIN assets_field_types ft ON cf.type = ft.id
					WHERE
						cf.catid = '".$catid."'
					ORDER BY
						cf.sort";
		
		$this->objDA->query($q);
		$fields	= $this->objDA->returnArray();
		
		for($i=0;$i<count($fields);$i++) {
			$option	= $fields[$i]['options'];
			
			if($option != '') {
				$option	= substr($option,1);
				$option	= substr($option,0,-1);
				$option	= explode("','",$option);
			}
			
			$fields[$i]['options']	= $option;
			
		}
		
		$this->smarty->assign('fields',$fields);
		
		
		if($id > 0) {
			// edit
			$this->smarty->assign('block_title','Edit Asset');
			
			$r			= $this->catBuildChildrenRoot();
			$catTree	= array();
			foreach($r as $key=>$value) {
				$catTree[$value['id']]	= $value['name'];
			}
			$this->smarty->assign('catTree',$catTree);
			
			$q	= "SELECT * FROM assets_assets WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$asset	= $this->objDA->returnArray();
			$asset	= $asset[0];
			
			if($asset['decommission'] == '' || $asset['decommission'] == '0') {
				$asset['decommission']	= '--';
			}
			
			$this->smarty->assign('asset',$asset);
			$this->smarty->assign('id',$id);
			
			// Load field data
			$q	= "	SELECT
						fd.id,
						fd.asset_id,
						fd.cat_field_id,
						fd.data,
						fd.type,
						cf.enabled,
						cf.sort
					FROM
						assets_field_data fd
						LEFT JOIN assets_cat_fields cf ON fd.cat_field_id = cf.id
					WHERE
						fd.asset_id = '".$id."'
					ORDER BY
						cf.sort";
			$this->objDA->query($q);
			
			$rows		= $this->objDA->returnArray();
			$fieldData	= array();
			
			foreach($rows as $r) {
				switch($r['type']) {
					case 'text':
					case 'select':
					case 'date':
					case 'radio':
					case 'textarea':
						$data	= $r['data'];
						break;
					case 'checkbox':
						$data	= explode(';',$r['data']);
						break;
					default:
						// unknown field
						$data	= '';
						break;
				}
				
				array_push($fieldData,$data);	
			}
					
			$this->smarty->assign('fieldData',$fieldData);
			
			
			
			$this->smarty->display('assets/assetEdit.tpl');
		} else {
			// add
			$this->smarty->assign('block_title','New Asset');
			
			$r			= $this->catBuildChildrenRoot();
			$catTree	= array();
			
			foreach($r as $key=>$value) {
				$catTree[$value['id']]	= $value['name'];
			}
			
			$asset					= array();
			$asset['commission']	= time();
			$asset['decommission']	= '--';
			$asset['cat']			= $catid;
			
			$this->smarty->assign('asset',$asset);

			$this->smarty->assign('catTree',$catTree);
			$this->smarty->assign('id',0);
			
			$this->smarty->assign('enabled','checked');
			
			$this->smarty->display('assets/assetEdit.tpl');
		}
	}
	
	/*
	* Method to process edit asset
	*/
	function assetEditProcess() {
		$id						= $this->sanitize($this->post['id']);
		$cat					= $this->sanitize($this->post['cat']);
		$name					= $this->sanitize($this->post['name']);
		$assetaccount			= $this->sanitize($this->post['assetaccount']);
		$depreciationaccount	= $this->sanitize($this->post['depreciationaccount']);
		$commissionMonth		= $this->sanitize($this->post['commissionMonth']);
		$commissionDay			= $this->sanitize($this->post['commissionDay']);
		$commissionYear			= $this->sanitize($this->post['commissionYear']);
		$decommissionMonth		= $this->sanitize($this->post['decommissionMonth']);
		$decommissionDay		= $this->sanitize($this->post['decommissionDay']);
		$decommissionYear		= $this->sanitize($this->post['decommissionYear']);
		$usefullife				= $this->sanitize($this->post['usefullife']);
		$initialvalue			= (float)$this->sanitize($this->post['initialvalue']);
		$residualvalue			= (float)$this->sanitize($this->post['residualvalue']);
		$enabled				= $this->sanitize($this->post['enabled']);		
		$fields					= $this->sanitize($this->post['fields']);
		
		if($commissionMonth > 0 && $commissionDay > 0 && $commissionYear > 0)
			$commission			= mktime(0,0,0,$commissionMonth,$commissionDay,$commissionYear);
		else
			$commission			= 0;
		
		if($decommissionMonth > 0 && $decommissionDay > 0 && $decommissionYear > 0)
			$decommission		= mktime(0,0,0,$decommissionMonth,$decommissionDay,$decommissionYear);
		else
			$decommission		= 0;
		
		// sanity checks
		if($commission < 1) {
			$this->smarty->assign('msg','Invalid comission date');
			$this->assetEdit($id,$cat);
			return;
		}
		
		if($decommission > 0 && $decommission < $commission) {
			$this->smarty->assign('msg','Decommission date must be after commission date');
			$this->assetEdit($id,$cat);
			return;
		}
		
		if($residualvalue >= $initialvalue) {
			$this->smarty->assign('msg','Residual value must be less than initial value');
			$this->assetEdit($id,$cat);
			return;
		}
		
		if($name == '' || $cat == '' || $assetaccount == '' || $depreciationaccount == '' || $commission == '' || $usefullife == '' || $initialvalue == '' || $residualvalue == '' || $initialvalue < 0.01 || $residualvalue < 0.01) {
			$this->smarty->assign('msg','Missing or invalid required fields');
			$this->assetEdit($id,$cat);
			return;
		}
		
		if($id > 0) {
			// edit
			$q	= "	UPDATE
						assets_assets
					SET	
						cat						= '".$cat."',
						name					= '".$name."',
						asset_account			= '".$assetaccount."',
						asset_depreciation		= '".$depreciationaccount."',
						commission				= '".$commission."',
						decommission			= '".$decommission."',
						usefullife				= '".$usefullife."',
						initialvalue			= '".$initialvalue."',
						residualvalue			= '".$residualvalue."'
					WHERE
						id						= '".$id."'";
			$this->objDA->query($q);
			
			$this->smarty->assign('msg','Asset edited.');
		} else {
			// add
			$q	= "	INSERT INTO
						assets_assets
					VALUES
						(
							'',
							'".$cat."',
							'".$name."',
							'".$assetaccount."',
							'".$depreciationaccount."',
							'".$commission."',
							'".$decommission."',
							'".$usefullife."',
							'".$initialvalue."',
							'".$residualvalue."',
							1
						)";
						
			$this->objDA->query($q);
			
			$id	= $this->objDA->insertID();
			
			$this->smarty->assign('msg','Asset added.');
		}
		
		// Save field data values
		if($id > 0) {
			$q	= "	DELETE FROM assets_field_data WHERE asset_id = '".$id."'";
			$this->objDA->query($q);
		}
		
		foreach($fields as $f) {
			switch($f['type']) {
				case 'text':
					$data	= $f['data'];
					break;
				case 'select':
					$data	= $f['data'];
					break;
				case 'date':
					$data	= mktime(0,0,0,$f['data']['Month'],$f['data']['Day'],$f['data']['Year']);
					break;
				case 'checkbox':
					if(is_array($f['data']))
						$data	= implode(';',$f['data']);
					else
						$data	= $f['data'];
					break;
				case 'radio':
					$data	= $f['data'];
					break;
				case 'textarea':
					$data	= $f['data'];
					break;
				default:
					// unknown type
					continue;
			}
			
			$q	= "INSERT INTO assets_field_data VALUES ('','".$id."','".$f['id']."','".$data."','".$f['type']."')";
			$this->objDA->query($q);
		}
		
		$this->assetList($cat);
	}
	
	/*
	* Methdo to generate barcode view page
	*/
	function assetBarcode($id=0) {
		if($id > 0) {
			$this->smarty->assign('asset_id',$id);
			
			$this->smarty->display('assets/barcodeImage.tpl');
		} else {
			$this->assetList();	
		}
	}
	
	/*
	* Method to generate barcode for an asset
	*/
	function assetBarcodeGenerate($id=0) {
		if($id > 0) {
			$q	= "SELECT * FROM assets_assets WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$asset	= $this->objDA->returnArray();
			$asset	= $asset[0];
			
			$encode		= $_REQUEST['encode'];
			$objBarcode	= new Barcode();
			
			// OR $bar= new BARCODE("I2O5");
			
			$assetcode	= sprintf("[%012s]\n", $asset['id']);
			$assettag	= '[ID: '.$asset['id'].'] '.$asset['name'];
			
			$objBarcode->label	= $assettag;
			//$barnumber="200780";
			//$barnumber="801221905";
			//$barnumber="A40146B";
			//$barnumber="Code 128";
			//$barnumber="TEST8052";
			//$barnumber="TEST93";
		
			$objBarcode->setSymblogy('CODABAR');
			$objBarcode->setHeight(50);
			$objBarcode->setFont("app/lib/Barcode/arial.ttf");
			$objBarcode->setScale(2);
			
			/*
			$label	= $asset['name'];
			
			if(strlen($label) < 23) {
				$pad		= '';
				$padding	= 23 - strlen($label);
				
				while(strlen($pad)<$padding) {
					$pad	.= ' ';
				}
				
				$label		= $pad . $label;
			}
			
			$objBarcode->label	= $label;
			*/
			
			// 23 char max width
			//$objBarcode->label	= 'MMMMMMMMMMMMMMMMMMMMMMM
			//MMMMMMMMMMMMMMMMMMMMMMM';
			
			$objBarcode->setHexColor("#000000","#FFFFFF");
		
			//OR
			//$bar->setColor(255,255,255)   RGB Color
			//$bar->setBGColor(0,0,0)   RGB Color

			$return = $objBarcode->genBarCode($assetcode,'png');

		} else {
			$this->assetList();	
		}
	}
	
	
////////////////////////////////
// CATEGORIES                 //
////////////////////////////////
	
	/*
	* Method to list password categories
	*/
	function catList() {
		global $msg;
		
		$this->smarty->assign('menuSubSel','Categories');
		$this->smarty->assign('block_title','Categories');
		
		$catTree	= $this->catBuildChildrenRoot('-',false);
		$this->smarty->assign('catTree',$catTree);
		
		$this->smarty->display('assets/catList.tpl');
	}
	
	/*
	* Method to sort navigation node up one
	*/
	function catSortUp($id=0) {
		$q		= "SELECT * FROM assets_cats WHERE id = '".$id."'";
		$this->objDA->query($q);
		$node	= $this->objDA->returnArray();
		$node	= $node[0];
		
		if($node['sort'] == 1) {
			// error
			$this->smarty->assign('msg','ERROR: Navigation NOT Moved Up');
			$this->navigation();
			return;
		}
		
		$q			= "SELECT * FROM assets_cats WHERE parent = '".$node['parent']."' AND sort = '".($node['sort'] - 1)."'";
		$this->objDA->query($q);
		$nodeAbove	= $this->objDA->returnArray();
		$nodeAbove	= $nodeAbove[0];
		
		// move node above down one
		$q	= "UPDATE assets_cats SET sort = sort + 1 WHERE id = '".$nodeAbove['id']."' LIMIT 1";
		$this->objDA->query($q);
		
		// move this node up one
		$q	= "UPDATE assets_cats SET sort = sort - 1 WHERE id = '".$id."' LIMIT 1";
		$this->objDA->query($q); 
		
		$this->smarty->assign('msg','Category Moved Up');
		$this->catList();
	}
	
	/*
	* Method to sort navigation node down one
	*/
	function catSortDown($id=0) {
		$q		= "SELECT * FROM assets_cats WHERE id = '".$id."'";
		$this->objDA->query($q);
		$node	= $this->objDA->returnArray();
		$node	= $node[0];
		
		$q			= "SELECT * FROM assets_cats WHERE parent = '".$node['parent']."' AND sort = '".($node['sort'] + 1)."'";
		$this->objDA->query($q);
		$nodeBelow	= $this->objDA->returnArray();
		
		// since there is nothing below us, cannot move down
		if(count($nodeBelow) < 1) {
			$this->smarty->assign('msg','ERROR: Navigation NOT Moved Down');
			$this->navigation();
			return;
		}
		
		$nodeBelow	= $nodeBelow[0];
		

		$q	= "UPDATE assets_cats SET sort = sort - 1 WHERE id = '".$nodeBelow['id']."' LIMIT 1";
		$this->objDA->query($q);
		
		// move this node up one
		$q	= "UPDATE assets_cats SET sort = sort + 1 WHERE id = '".$id."' LIMIT 1";
		$this->objDA->query($q); 
		
		$this->smarty->assign('msg','Category Moved Down');
		$this->catList();
	}
	
	/*
	* Method to add or edit a category
	*/
	function catEdit($id=0,$fields=array()) {
		global $msg;
		
		$this->smarty->assign('menuSubSel','Categories');
		
		$r			= $this->catBuildChildrenRoot();
		$catTree	= array();
		
		foreach($r as $key=>$value) {
			$catTree[$value['id']]	= $value['name'];
		}
		
		$this->smarty->assign('copyTree',$catTree);
		
		// Populate field types
		$q		= "SELECT * FROM assets_field_types ORDER BY id";
		$this->objDA->query($q);
		$rows	= $this->objDA->returnArray();
		$types	= array();
		
		foreach($rows as $r) {
			$types[$r['id']]	= $r['type'];
		}
		
		$this->smarty->assign('types',$types);
		
		// Pre-load blank fields (for display on edit and add)
		$blankField	= array('name' => '', 'type' => '', 'required' => '', 'options' => '');

		for($i=0;$i<8;$i++) {
			array_push($fields,$blankField);
		}
		
		if($id > 0) {
			// edit
			$q	= "SELECT * FROM assets_cats WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r	= $this->objDA->returnArray();
			$r	= $r[0];
			
			// Remove node being edited from parent list (can't make a node it's own parent)
			foreach($catTree as $key=>$value) {
				if($key == $id)
					unset($catTree[$key]);
			}
			
			// Load categories field data
			$q			= "SELECT * FROM assets_cat_fields WHERE catid = '".$id."' ORDER BY sort";
			$this->objDA->query($q);
			$fieldData	= $this->objDA->returnArray();
			
			$fields		= array_merge($fieldData,$fields);
			
			$this->smarty->assign('fields',$fields);
			
			$this->smarty->assign('block_title','Edit Category');
			$this->smarty->assign('selParent',$r['parent']);
			$this->smarty->assign('name',$r['name']);
		} else {
			// add

			// Load field categories
			$this->smarty->assign('fields',$fields);
			
			$this->smarty->assign('block_title','Add Category');
		}
		
		$this->smarty->assign('catTree',$catTree);
		$this->smarty->assign('id',$id);
		
		$this->smarty->display('assets/catEdit.tpl');
	}
	
	/*
	* Method to process a category node
	*/
	function catEditProcess() {
		if($this->post) {
			$catname	= $this->sanitize($this->post['catname']);
			$parent		= $this->sanitize($this->post['parent']);
			$id			= $this->sanitize($this->post['id']);
			
			$fieldid	= $this->sanitize($this->post['fieldid']);
			$name		= $this->sanitize($this->post['name']);
			$delete		= $this->sanitize($this->post['delete']);
			$type		= $this->sanitize($this->post['type']);
			$option 	= $this->sanitize($this->post['options']);
			$required	= $this->sanitize($this->post['required']);
			$sort		= $this->sanitize($this->post['sort']);
			
			$copy		= $this->sanitize($this->post['copy']);
			
			if($copy != '') {
				$copyFields	= $this->sanitize($this->post['copyFields']);
				
				// copy fields from other category, re-edit category
				$q			= "SELECT * FROM assets_cat_fields WHERE catid = '".$copyFields."' ORDER BY sort";
				$this->objDA->query($q);
				$fields	= $this->objDA->returnArray();
			
				for($i=0;$i<count($fields);$i++) {
					$fields[$i]['sort']	= '';
				}
				
				$this->catEdit($id,$fields);
				return;
			}
			
			if($id > 0) {
				// edit
				
				$q	= "	UPDATE
							assets_cats
						SET
							name	= '".$catname."',
							parent	= '".$parent."'
						WHERE
							id		= '".$id."'";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Category edited.');
			} else {
				// add
				// Find highest sort value, then add one for current sort
				$q	= "SELECT sort FROM assets_cats WHERE parent = '".$parent."' ORDER BY sort DESC";
				$this->objDA->query($q);
				
				$r		= $this->objDA->returnArray();
				$r		= $r[0];
				$sort	= $r['sort'] + 1;
				
				$q	= "INSERT INTO assets_cats VALUES ('','".$parent."','".$catname."','".$sort."',1)";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Category added.');
			}
			
			if($id > 0) {
				// Data fields
				$q	= "DELETE FROM assets_cat_fields WHERE catid = '".$id."'";
				$this->objDA->query($q);
			}
			
			for($i=0;$i<count($name);$i++) {
				if($delete[$i] == 'checked' || $name[$i] == '')
					continue; // ignore
				
				if($required[$fieldid[$i]] == 'checked')
					$required[$fieldid[$i]]	= 1;
				else
					$required[$fieldid[$i]]	= 0;
				
				$q	= "INSERT INTO assets_cat_fields VALUES ('','".$id."','".$name[$i]."','".$type[$i]."','".$option[$i]."','".$required[$fieldid[$i]]."','1','".$sort[$i]."')";
				
				$this->objDA->query($q);
			}
		}
		
		$this->catList();
	}
	
	/*
	* Method to delete (disable) a category
	*/
	function catDelete($id=0) {
		if($id > 0) {
			$q	= "UPDATE assets_cats SET enabled = 0 WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$this->smarty->assign('msg','Category deleted.');
		}
		
		$this->catList();
	}


////////////////////////////////
// INTERNAL                   //
////////////////////////////////
	/*
	* Methdo to build array of children nodes from root
	*/
	protected function catBuildChildrenRoot($spacer='-',$includeRoot=true) {
		$q	= "SELECT * FROM assets_cats WHERE parent = '0' ORDER BY sort";
		$this->objDA->query($q);
		
		$nodes		= $this->objDA->returnArray();
		$navTree	= array();
		$iteration	= 0;
		
		$count		= count($nodes);
		
		for($i=0;$i<$count;$i++) {
			if($nodes[$i]['id'] == $id)
				continue;
				
			if($i == 0)
				$nodes[$i]['navUp']	= false;
			else
				$nodes[$i]['navUp']	= true;
			
			if(($i+1)<$count)
				$nodes[$i]['navDown']	= true;
			else
				$nodes[$i]['navDown']	= false;
			
			if($includeRoot === true) {
				$nodes[$i]['name']	= $spacer.'&nbsp;'.$nodes[$i]['name'];
				
				array_push($navTree,$nodes[$i]);
			}
			
			$this->catBuildChilden($nodes[$i]['id'],$navTree,$iteration,$spacer);
		}
		
		
		return $navTree;
	}
	/*
	* Method to build array of children nodes to $navTree (passed as reference)
	*/
	protected function catBuildChilden($parent,&$navTree,&$iteration=1,$spacer='-') {
		$q	= "SELECT * FROM assets_cats WHERE parent = '".$parent."' AND enabled = 1 ORDER BY sort";
		$this->objDA->query($q);
		
		$nodes		= $this->objDA->returnArray();
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= '&nbsp;&nbsp;';
		}
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= $spacer;
		}
		
		$indent	.= '&nbsp;';

		$count	= count($nodes);
		
		for($i=0;$i<$count;$i++) {
			if($i == 0)
				$nodes[$i]['navUp']	= false;
			else
				$nodes[$i]['navUp']	= true;
				
			if(($i+1)<$count)
				$nodes[$i]['navDown']	= true;
			else
				$nodes[$i]['navDown']	= false;
				
			$nodes[$i]['name']		= $indent . $nodes[$i]['name'];
			
			array_push($navTree,$nodes[$i]);
			
			$iteration++;
			$this->catBuildChilden($nodes[$i]['id'],$navTree,$iteration,$spacer);
			$iteration--;
		}
		
		return;
	}
	
	/*
	* Method to delete nested children under the specicifed node
	*/
	protected function catDeleteChildren($parent=0) {
		if($parent == 0)
			return;
			
		$q	= "SELECT * FROM assets_cats WHERE parent = '".$parent."'";
		$this->objDA->query($q);
		$nodes	= $this->objDA->returnArray();
		
		foreach($nodes as $node) {
			$this->catDeleteChildren($node['id']);
			$q	= "DELETE FROM assets_cats WHERE id = '".$node['id']."' LIMIT 1";
			$this->objDA->query($q);
		}
		
		return;
	}
}
?>