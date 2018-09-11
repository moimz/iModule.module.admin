<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 설치된 모듈의 순서를 저장한다.
 * 
 * @file /modules/admin/process/@saveModuleSort.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 7. 10.
 */
if (defined('__IM__') == false) exit;

$updated = json_decode(Request('updated'));
for ($i=0, $loop=count($updated);$i<$loop;$i++) {
	$this->db()->update($this->getModule()->getTable('module'),array('sort'=>$updated[$i]->sort))->where('module',$updated[$i]->module)->execute();
}

$lists = $this->db()->select($this->getModule()->getTable('module'))->orderBy('sort','asc')->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	if ($i != $lists[$i]->sort) {
		$this->db()->update($this->getModule()->getTable('module'),array('sort'=>$i))->where('module',$lists[$i]->module)->execute();
	}
}

$results->success = true;
?>