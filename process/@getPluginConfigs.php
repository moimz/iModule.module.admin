<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 플러그인의 설정값을 불러온다. 플러그인의 설정필드는 각 플러그인의 package.json 에 정의되어 있으며 설정값은 DB의 plugin_table 에 저장된다.
 *
 * @file /plugins/admin/process/@getPluginConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$plugin = Request('target');
$package = $this->getPlugin()->getPackage($plugin);
$configs = $this->getPlugin()->isInstalled($plugin) == true ? json_decode($this->getPlugin()->getInstalled($plugin)->configs) : new stdClass();

$data = isset($package->configs) == true ? $package->configs : new stdClass();
foreach ($data as $key=>$type) {
	if (isset($configs->$key) == false) $data->$key = isset($type->default) == true ? $type->default : '';
	else $data->$key = $configs->$key;
}
$results->success = true;
$results->data = $data;
?>