<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈의 설정패널을 불러온다. 모듈의 설정패널은 각 모듈폴더의 admin/configs.php 파일에 정의되어 있다.
 *
 * @file /modules/admin/process/@getModuleConfigPanel.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$module = Request('target');
$panel = $this->getModule()->getConfigPanel($module);

if ($panel == null) {
	$results->success = true;
	$results->panel = null;
} else {
	$package = $this->getModule()->getPackage($module);
	
	$results->success = true;
	$results->panel = $panel;
	$results->language = __IM_DIR__.'/scripts/language.js.php?language='.$this->IM->language.'&languages=module@'.$module.'@'.$package->language;
	
	if (is_file($this->getModule()->getPath($module).'/admin/scripts/script.js') == true) {
		$results->script = $this->getModule()->getDir($module).'/admin/scripts/script.js';
	} else {
		$results->script = null;
	}
}

$results->isInstalled = $this->getModule()->isInstalled($module);
$results->isLatest = $results->isInstalled == true && $this->getModule()->getInstalled($module)->hash == $this->getModule()->getHash($module);
?>