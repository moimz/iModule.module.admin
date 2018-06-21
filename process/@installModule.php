<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈을 설치하거나 설정을 업데이트한다.
 *
 * @file /modules/admin/process/@installModule.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$target = Request('target');
$configs = new stdClass();
foreach ($_POST as $key=>$value) {
	if (in_array($key,array('target')) == true) continue;
	if (strpos($key,'@') === 0) {
		$temp = explode('-',substr($key,1));
		$groupKey = $temp[0];
		$childKey = $temp[1];
		
		if (isset($configs->$groupKey) == false) $configs->$groupKey = new stdClass();
		$configs->$groupKey->$childKey = $value;
	} else {
		$configs->$key = $value;
	}
}

$installed = $this->getModule()->install($target,$configs);
if ($installed === true) {
	$results->success = true;
} else {
	$results->success = false;
	$results->message = $installed;
}
?>