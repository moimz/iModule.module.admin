<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 모듈의 설정값을 불러온다. 모듈의 설정필드는 각 모듈의 package.json 에 정의되어 있으며 설정값은 DB의 module_table 에 저장된다.
 *
 * @file /modules/admin/process/@getModuleConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$module = Request('target');
$package = $this->getModule()->getPackage($module);
$configs = $this->getModule()->isInstalled($module) == true ? json_decode($this->getModule()->getInstalled($module)->configs) : new stdClass();

$data = isset($package->configs) == true ? $package->configs : new stdClass();
foreach ($data as $key=>$type) {
	if (isset($configs->$key) == false) $data->$key = isset($type->default) == true ? $type->default : '';
	else $data->$key = $configs->$key;
}
$results->success = true;
$results->data = $data;
?>