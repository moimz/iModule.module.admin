<?php
/**
 * 이 파일은 iModule 관리자플러그인의 일부입니다. (https://www.iplugin.kr)
 * 
 * 플러그인의 설정패널을 불러온다. 플러그인의 설정패널은 각 플러그인폴더의 admin/configs.php 파일에 정의되어 있다.
 *
 * @file /plugins/admin/process/@getPluginConfigPanel.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$plugin = Request('target');
$panel = $this->getPlugin()->getConfigPanel($plugin);

if ($panel == null) {
	$results->success = true;
	$results->panel = null;
} else {
	$package = $this->getPlugin()->getPackage($plugin);
	
	$results->success = true;
	$results->panel = $panel;
	$results->language = __IM_DIR__.'/scripts/language.js.php?language='.$this->IM->language.'&languages=plugin@'.$plugin.'@'.$package->language;
	
	if (is_file($this->getPlugin()->getPath($plugin).'/admin/scripts/script.js') == true) {
		$results->script = $this->getPlugin()->getDir($plugin).'/admin/scripts/script.js';
	} else {
		$results->script = null;
	}
}

$results->isInstalled = $this->getPlugin()->isInstalled($plugin);
$results->isLatest = $results->isInstalled == true && $this->getPlugin()->getInstalled($plugin)->hash == $this->getPlugin()->getHash($plugin);
?>