<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 설치되어 있는 모듈의 DB용량과 첨부파일용량을 구한다.
 *
 * @file /modules/admin/process/@copySitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160925
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$modules = $this->db()->select($this->Module->getTable('module'))->get();
for ($i=0, $loop=count($modules);$i<$loop;$i++) {
	$this->Module->updateSize($modules[$i]->module);
}

$results->success = true;
?>