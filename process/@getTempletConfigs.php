<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 템플릿의 환경설정폼을 가져온다.
 *
 * @file /modules/admin/process/@getTempletConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$name = Request('name');
$type = Request('type');
$target = Request('target');
$templet = Request('templet');
$position = Request('position');

if ($type == 'module') {
	$Templet = $this->IM->getModule($target)->getTemplet($templet);
	
	if ($position == 'module') {
		if ($this->IM->getModule()->isInstalled($target) == true && $this->IM->getModule($target)->getModule()->getConfig($name.'_configs') != null) {
			$Templet->setConfigs($this->IM->getModule($target)->getModule()->getConfig($name.'_configs'));
		}
	}
	
	$configs = $Templet->getConfigs();
}

$results->success = true;
$results->configs = $configs;
?>