<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 서버에 설치된 모듈목록을 가져온다.
 *
 * @file /modules/admin/process/@getInstalledModules.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 7. 10.
 */
if (defined('__IM__') == false) exit;

$lists = $this->db()->select($this->getModule()->getTable('module'),'module,version,sort')->orderBy('sort','asc')->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$package = $this->getModule()->getPackage($lists[$i]->module);
	
	$lists[$i]->id = $package->id;
	$lists[$i]->title = $this->getModule()->getTitle($lists[$i]->module);
	$lists[$i]->icon = $package->icon;
	$lists[$i]->description = $this->getModule()->getDescription($lists[$i]->module);
	
	if ($i != $lists[$i]->sort) {
		$this->db()->update($this->getModule()->getTable('module'),array('sort'=>$i))->where('module',$lists[$i]->module)->execute();
		$lists[$i]->sort = $i;
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>