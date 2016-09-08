<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈의 설정패널을 불러온다. 모듈의 설정패널은 각 모듈폴더의 admin/config.php 파일에 정의되어 있다.
 *
 * @file /modules/admin/process/@getModuleConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $module 모듈명
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$module = Request('target');
$panel = $this->Module->getConfigPanel($module);

if ($panel == null) {
	$results->success = true;
	$results->panel = null;
} else {
	$package = $this->Module->getPackage($module);
	
	$results->success = true;
	$results->panel = $panel;
	$results->language = __IM_DIR__.'/scripts/language.js.php?languages='.$module.'@'.$this->IM->language.'@'.$package->language;
	
	if (is_file($this->Module->getPath($module).'/admin/scripts/'.$module.'.js') == true) {
		$results->script = $this->Module->getDir($module).'/admin/scripts/'.$module.'.js';
	} else {
		$results->script = null;
	}
}

$results->isInstalled = $this->Module->isInstalled($module);
$results->isLatest = $results->isInstalled == true && $this->Module->getInstalled($module)->hash == $this->Module->getHash($module);
?>