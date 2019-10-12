<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 서버에 설치된 플러그인 목록을 가져온다.
 *
 * @file /plugins/admin/process/@getInstalledPlugins.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 7. 11.
 */
if (defined('__IM__') == false) exit;

$lists = $this->db()->select($this->getPlugin()->getTable('plugin'),'plugin,version,sort')->orderBy('sort','asc')->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$package = $this->getPlugin()->getPackage($lists[$i]->plugin);
	
	$lists[$i]->id = $package->id;
	$lists[$i]->title = $this->getPlugin()->getTitle($lists[$i]->plugin);
	$lists[$i]->icon = $package->icon;
	$lists[$i]->description = $this->getPlugin()->getDescription($lists[$i]->plugin);
	
	if ($i != $lists[$i]->sort) {
		$this->db()->update($this->getPlugin()->getTable('plugin'),array('sort'=>$i))->where('plugin',$lists[$i]->plugin)->execute();
		$lists[$i]->sort = $i;
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>