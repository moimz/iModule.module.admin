<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈의 설정패널을 불러온다. 모듈의 설정패널은 각 모듈폴더의 admin/config.php 파일에 정의되어 있다.
 *
 * @file /modules/admin/process/@getTempletConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $templet 템플릿명
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$templet = Request('templet');
$package = json_decode(file_get_contents(__IM_PATH__.'/templets/'.$templet.'/package.json'));

$configs = array();

if ($package != null && isset($package->configs) == true) {
	foreach ($package->configs as $key=>$value) {
		$config = new stdClass();
		$config->name = $key;
		$config->type = $value->type;
		$config->title = isset($value->title->{$this->IM->language}) == true ? $value->title->{$this->IM->language} : $value->title->{$package->language};
		$config->help = isset($value->help->{$this->IM->language}) == true ? $value->help->{$this->IM->language} : $value->help->{$package->language};
		$configs[] = $config;
	}
}

$results->success = true;
$results->configs = $configs;
?>