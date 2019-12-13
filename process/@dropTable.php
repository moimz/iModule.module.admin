<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 테이블을 삭제한다.
 *
 * @file /modules/admin/process/@dropTable.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 12. 13.
 */
if (defined('__IM__') == false) exit;

$tables = Param('tables') ? explode(',',Param('tables')) : array();
foreach ($tables as $table) {
	if ($this->IM->db()->drop($table,true) === false) {
		$results->success = false;
		$results->message = $table.' 테이블을 삭제하지 못하였습니다.';
		return;
	}
}

$results->success = true;
?>