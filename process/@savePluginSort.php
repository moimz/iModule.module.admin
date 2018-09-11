<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 설치된 플러그인의 순서를 저장한다.
 * 
 * @file /plugins/admin/process/@savePluginSort.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 7. 10.
 */
if (defined('__IM__') == false) exit;

$updated = json_decode(Request('updated'));
for ($i=0, $loop=count($updated);$i<$loop;$i++) {
	$this->db()->update($this->getPlugin()->getTable('plugin'),array('sort'=>$updated[$i]->sort))->where('plugin',$updated[$i]->plugin)->execute();
}

$lists = $this->db()->select($this->getPlugin()->getTable('plugin'))->orderBy('sort','asc')->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	if ($i != $lists[$i]->sort) {
		$this->db()->update($this->getPlugin()->getTable('plugin'),array('sort'=>$i))->where('plugin',$lists[$i]->plugin)->execute();
	}
}

$results->success = true;
?>