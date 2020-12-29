<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 캐시파일을 삭제한다.
 *
 * @file /modules/admin/process/@deleteCache.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 4. 20.
 */
if (defined('__IM__') == false) exit;

$files = Param('files') ? json_decode(Param('files')) : array();
foreach ($files as $file) {
	@unlink($this->IM->getCachePath().'/'.$file);
}

$results->success = true;
?>