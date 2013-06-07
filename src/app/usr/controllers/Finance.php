<?php
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';

class Finance extends AdminCP_adminMenu implements IModule {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* POST variable
	*/
	var $post;
	
	/*
	* Section ID
	*/
	var $sectionID	= 8;
	
	function setSmarty($smarty) {
		global $msg;
		
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;
		
		$this->smarty->assign('page_title','6PS Intranet :: Finance');
		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(5);
		$this->smarty->assign('menuMainSel','');
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
		$this->dashboard();
	}
	
	/*
	* Financial Dashboard
	*/
	function dashboard() {
		$this->smarty->assign('menuSubSel','Dashboard');
		$this->smarty->assign('block_title','Dashboard');
		
		// FOREX: Brazil Reais
		$h	= fopen('http://finance.yahoo.com/q?s=USDBRL=X','r');
		$p	= stream_get_contents($h);
		fclose($h);
		
		$start			= strpos($p,'Last Trade:</td><td class="yfnc_tabledata1"><big><b>');
		$BRLLastTrade	= substr($p,($start+52),6);
		
		$start			= strpos($p,'Bid:</td><td class="yfnc_tabledata1">');
		$BRLBid			= substr($p,($start+37),6);
		
		$start			= strpos($p,'Ask:</td><td class="yfnc_tabledata1">');
		$BRLAsk			= substr($p,($start+37),6);
		
		$this->smarty->assign('BRLLastTrade',$BRLLastTrade);
		$this->smarty->assign('BRLBid',$BRLBid);
		$this->smarty->assign('BRLAsk',$BRLAsk);
		
		
		
		// FOREX: Japan Yen
		$h	= fopen('http://finance.yahoo.com/q?s=JPYUSD=X','r');
		$p	= stream_get_contents($h);
		fclose($h);
		
		$start			= strpos($p,'Last Trade:</td><td class="yfnc_tabledata1"><big><b>');
		$JPYLastTrade	= substr($p,($start+52),6);
		if(!is_numeric(substr($JPYLastTrade,-1,1)))
			$JPYLastTrade	= substr($p,($start+52),7);
			
		$start			= strpos($p,'Bid:</td><td class="yfnc_tabledata1">');
		$JPYBid			= substr($p,($start+37),6);
		if(!is_numeric(substr($JPYBid,-1,1)))
			$JPYBid	= substr($p,($start+37),7);
		
		$start			= strpos($p,'Ask:</td><td class="yfnc_tabledata1">');
		$JPYAsk			= substr($p,($start+37),6);
		if(!is_numeric(substr($JPYAsk,-1,1)))
			$JPYAsk	= substr($p,($start+37),7);
		
		$this->smarty->assign('JPYLastTrade',$JPYLastTrade);
		$this->smarty->assign('JPYBid',$JPYBid);
		$this->smarty->assign('JPYAsk',$JPYAsk);
		
		
		
		// FOREX: Brazil Reais
		$h	= fopen('http://finance.yahoo.com/q?s=EURUSD=X','r');
		$p	= stream_get_contents($h);
		fclose($h);
		
		$start			= strpos($p,'Last Trade:</td><td class="yfnc_tabledata1"><big><b>');
		$EURLastTrade	= substr($p,($start+52),6);
		
		$start			= strpos($p,'Bid:</td><td class="yfnc_tabledata1">');
		$EURBid			= substr($p,($start+37),6);
		
		$start			= strpos($p,'Ask:</td><td class="yfnc_tabledata1">');
		$EURAsk			= substr($p,($start+37),6);
		
		$this->smarty->assign('EURLastTrade',$EURLastTrade);
		$this->smarty->assign('EURBid',$EURBid);
		$this->smarty->assign('EURAsk',$EURAsk);
		
		$this->smarty->display('finance/dashboard.tpl');
	}
	
////////////////////////////////
// CHART OF ACCOUNTS		  //
////////////////////////////////
	/*
	* Method to list chart of accounts
	*/
	function chartList($showDisabled=false) {
		$this->smarty->assign('menuSubSel','Chart of Accounts');
		$this->smarty->assign('block_title','Chart of Accounts');
		
		if($showDisabled == '1')
			$showDisabled	= true;
		else
			$showDisabled	= false;
		
		$chart	= $this->getChartList($showDisabled);
		$this->smarty->assign('chart',$chart);
		
		$this->smarty->assign('showDisabled',$showDisabled);
		
		$this->smarty->display('finance/chartList.tpl');
	}
	
	/*
	* Method to edit a chart of accounts account
	*/
	function chartEdit($id=0) {
		$this->smarty->assign('menuSubSel','Chart of Accounts');
		
		// account types
		$q	= "SELECT * FROM fin_chart_types ORDER BY sort";
		$this->objDA->query($q);
		$type	= $this->objDA->returnArray();
		$types	= array();
		foreach($type as $r) {
			$types[$r['id']]	= $r['name'];
		}
		$this->smarty->assign('types',$types);
		
		// accounts (parents)
		$chart		= $this->getChartList(false);
		$accounts	= array(0 => '-- ROOT --');
		foreach($chart as $r) {
			$accounts[$r['id']]	= $r['num'].' - '.$r['name'];
		}
		$this->smarty->assign('accounts',$accounts);
		
		if($id > 0) {
			// edit
			$this->smarty->assign('block_title','Edit Account');
			
			$q	= "SELECT * FROM fin_chart_accounts a WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r	= $this->objDA->returnArray();
			$r	= $r[0];
			
			$this->smarty->assign('id',$r['id']);
			$this->smarty->assign('selType',$r['type']);
			$this->smarty->assign('name',$r['name']);
			$this->smarty->assign('num',$r['num']);
			$this->smarty->assign('description',$r['description']);
			$this->smarty->assign('banknum',$r['banknum']);
			$this->smarty->assign('disabled',$r['disabled']);
			$this->smarty->assign('selParent',$r['parent']);
			$this->smarty->assign('taxline',$r['taxline']);
		} else {
			// add
			$this->smarty->assign('id',0);
			$this->smarty->assign('block_title','Add Account');
		}
		
		$this->smarty->display('finance/chartEdit.tpl');
	}
	
	/*
	* Method to process editing a chart of accounts account
	*/
	function chartEditProcess() {
		if($this->post) {
			$id				= $this->post['id'];
			$type			= $this->post['type'];
			$name			= $this->post['name'];
			$num			= $this->post['num'];
			$description	= $this->post['description'];
			$banknum		= $this->post['banknum'];
			$disabled		= $this->post['disabled'];
			$parent			= $this->post['parent'];
			$taxline		= $this->post['taxline'];
			
			if($parent == $id) {
				$this->smarty->assign('msg','Invalid sub-account');
				$this->chartList();
				return;
			}
			
			if($disabled == 'checked')
				$disabled	= true;
			else
				$disabled	= false;
				
			if($id > 0) {
				// edit
				$q	= "	UPDATE
							fin_chart_accounts
						SET
							type		= '".$type."',
							name		= '".$name."',
							num			= '".$num."',
							description	= '".$description."',
							banknum		= '".$banknum."',
							disabled	= '".$disabled."',
							parent		= '".$parent."',
							taxline		= '".$taxline."'
						WHERE
							id			= '".$id."'";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Account Edited');
			} else {
				// add
				$q	= "	INSERT INTO
							fin_chart_accounts
						VALUES
							(
								'',
								'".$type."',
								'".$num."',
								'".$name."',
								'".$description."',
								'".$banknum."',
								'".$disabled."',
								'".$parent."',
								'".$taxline."'
							)";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Account Added');
			}
		}
		
		$this->chartList();
	}
	
////////////////////////////////
// INTERNAL                   //
////////////////////////////////
	/*
	* Method to build a array of chart of accounts
	*/
	protected function getChartList($showDisabled,$parent=0,$indent=0,&$chart=array()) {
		if($showDisabled !== true) {
			$where	= 'AND disabled = 0';
		} else {
			$where	= '';
		}
		
		$pad	= '';
		
		for($i=0;$i<$indent;$i++)
			$pad	.= '&nbsp;&nbsp;&nbsp;';
		
		$q	= "	SELECT
					a.id,
					a.type,
					a.num,
					a.name,
					a.description,
					a.banknum,
					a.disabled,
					a.parent,
					a.taxline,
					t.name AS type_name
				FROM
					fin_chart_accounts a
					LEFT JOIN fin_chart_types t ON a.type = t.id
				WHERE
					parent	= '".$parent."'
					".$where."
				ORDER BY
					t.sort,
					a.num";
		$this->objDA->query($q);

		$accounts	= $this->objDA->returnArray();
		
		foreach($accounts as $r) {
			$account				= array();
			
			$account['id']			= $r['id'];
			$account['type']		= $r['type_name'];
			$account['name']		= $pad.$r['name'];
			$account['num']			= $pad.$r['num'];
			$account['description']	= $r['description'];
			$account['banknum']		= $r['banknum'];
			$account['disabled']	= $r['disabled'];
			$account['parent']		= $r['parent'];
			$account['taxline']		= $r['taxline'];
			
			array_push($chart,$account);
			
			$this->getChartList($showDisabled,$r['id'],($indent+1),$chart);
		}
		
		return $chart;
	}
}
?>