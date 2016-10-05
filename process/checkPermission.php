<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 현재 로그인이 되어 있는 사용자의 권한을 판단하여 사이트관리자 접속여부를 확인한다.
 *
 * @file /modules/admin/process/checkPermission.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

if ($this->checkPermission() === false) {
	$results->success = false;
	$results->message = $this->getErrorText('FORBIDDEN');
} else {
	$results->success = true;
}
?>