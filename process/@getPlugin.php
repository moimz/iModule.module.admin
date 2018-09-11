<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 플러그인정보를 가져온다.
 *
 * @file /plugins/admin/process/@getPlugin.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$plugin = Request('target');

/**
 * 모듈 package.json 정보를 가져온다.
 */
$package = $this->getPlugin()->getPackage($plugin);
if ($package == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
} else {
	$data = new stdClass();
	$data->icon = $package->icon;
	$data->title = $this->getPlugin()->getTitle($plugin);
	$data->version = $package->version;
	$data->author = $package->author->name.' (<a href="mailto:'.$package->author->email.'">'.$package->author->email.'</a>)';
	$data->homepage = '<a href="mailto:'.$package->homepage.'" target="_blank">'.$package->homepage.'</a>';
	$data->language = $package->language;
	$data->description = $this->getPlugin()->getDescription($plugin);
	
	$data->admin = $package->admin;
	$data->database = isset($package->databases) == true;
	
	$data->dependencies = array();
	foreach ($package->dependencies as $dependency=>$version) {
		if ($dependency == 'core') {
			$data->dependencies[] = array(
				'name'=>'iModule v'.$version,
				'checked'=>version_compare($version,__IM_VERSION__,'>=') == true
			);
		} else {
			$name = $this->getPlugin()->getTitle($dependency);
			
			$data->dependencies[] = array(
				'name'=>($name !== null ? $name : $dependency).' v'.$version,
				'checked'=>$this->getPlugin()->isInstalled($dependency) === true && version_compare($version,$package->version,'>=') == true
			);
		}
	}
	
	$data->isInstalled = $this->getPlugin()->isInstalled($plugin);
	$data->isLatest = $data->isInstalled == true && $this->getPlugin()->getInstalled($plugin)->hash == $this->getPlugin()->getHash($plugin);
	$data->isConfigPanel = $this->getPlugin()->isConfigPanel($plugin);
	
	$results->success = true;
	$results->data = $data;
}
?>