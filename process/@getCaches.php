<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 캐시파일 목록을 가져온다.
 *
 * @file /modules/admin/process/@getCaches.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2020. 4. 29.
 */
if (defined('__IM__') == false) exit;

$lists = array();
$cachePath = @opendir($this->IM->getCachePath());
while ($cacheName = @readdir($cachePath)) {
	if ($cacheName != '.' && $cacheName != '..' && is_file($this->IM->getCachePath().'/'.$cacheName) == true) {
		$temp = explode('.',$cacheName);
		
		$file = new stdClass();
		$file->name = $cacheName;
		$file->type = $temp[0];
		$file->path = $this->IM->getCachePath().'/'.$cacheName;
		$file->size = filesize($this->IM->getCachePath().'/'.$cacheName);
		$file->reg_date = filemtime($this->IM->getCachePath().'/'.$cacheName);
		
		if ($file->type == 'module' || $file->type == 'widget') {
			$file->module = $temp[1];
			$package = $this->IM->getModule()->getPackage($file->module);
			$file->module = $this->IM->getModule()->getTitle($file->module).'('.$file->module.')';
			$file->module_icon = $package != null && isset($package->icon) == true ? substr($package->icon,0,2).' '.$package->icon : 'xi xi-box';
		} else {
			$file->module = 'iModule';
			$file->module_icon = 'mi mi-imodule';
		}
		
		$lists[] = $file;
	}
}
@closedir($cachePath);

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>