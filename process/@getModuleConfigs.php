<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈의 설정값을 불러온다. 모듈의 설정필드는 각 모듈의 package.json 에 정의되어 있으며 설정값은 DB의 module_table 에 저장된다.
 *
 * @file /modules/admin/process/@getModuleConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $target 대상 모듈명
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$module = Request('target');
$package = $this->Module->getPackage($module);
$configs = $this->Module->isInstalled($module) == true ? json_decode($this->Module->getInstalled($module)->configs) : new stdClass();

$data = isset($package->configs) == true ? $package->configs : new stdClass();
foreach ($data as $key=>$type) {
	if (isset($configs->$key) == false) $data->$key = $type->value;
	else $data->$key = $configs->$key;
	
	if ($type->type == 'array') $data->$key = implode(',',$data->$key);
}
$results->success = true;
$results->data = $data;
?>