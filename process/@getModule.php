<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈정보를 가져온다.
 *
 * @file /modules/admin/process/@getModule.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$module = Request('target');

/**
 * 모듈 package.json 정보를 가져온다.
 */
$package = $this->Module->getPackage($module);
if ($package == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
} else {
	$data = new stdClass();
	$data->icon = $package->icon;
	$data->title = $this->Module->getTitle($module);
	$data->version = $package->version;
	$data->author = $package->author->name.' (<a href="mailto:'.$package->author->email.'">'.$package->author->email.'</a>)';
	$data->homepage = '<a href="mailto:'.$package->homepage.'" target="_blank">'.$package->homepage.'</a>';
	$data->language = $package->language;
	$data->description = $this->Module->getDescription($module);
	
	$data->context = $package->context;
	$data->global = $package->global;
	$data->article = $package->article;
	$data->admin = $package->admin;
	
	$data->dependencies = array();
	foreach ($package->dependencies as $dependency=>$version) {
		if ($dependency == 'core') {
			$data->dependencies[] = array(
				'name'=>'iModule v'.$version,
				'checked'=>version_compare($version,__IM_VERSION__,'>=') == true
			);
		} else {
			$name = $this->Module->getTitle($dependency);
			
			$data->dependencies[] = array(
				'name'=>($name !== null ? $name : $dependency).' v'.$version,
				'checked'=>$this->Module->isInstalled($dependency) === true && version_compare($version,$package->version,'>=') == true
			);
		}
	}
	
	$data->isInstalled = $this->Module->isInstalled($module);
	$data->isLatest = $data->isInstalled == true && $this->Module->getInstalled($module)->hash == $this->Module->getHash($module);
	$data->isConfigPanel = $this->Module->isConfigPanel($module);
	
	$results->success = true;
	$results->data = $data;
}
?>