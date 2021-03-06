<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 테이블 구조를 가져온다.
 *
 * @file /modules/admin/process/@getTableDesc.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 11. 23.
 */
if (defined('__IM__') == false) exit;

if ($this->checkIp('database') === false) {
	$results->success = false;
	$results->message = $this->getErrorText('FORBIDDEN');
	return;
}

$table = Param('table');
$lists = $this->db()->desc($table,true);

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>