<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트 템플릿 환경설정을 가져온다. 템플릿 환경설정은 각 템플릿폴더의 package.json 파일에 정의되어 있다.
 *
 * @file /modules/admin/process/@getSiteTempletConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $templet 템플릿명
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$templet = Request('templet');

$Templet = $this->IM->getTemplet($this->IM,$templet);
if ($Templet->isLoaded() === true) {
	if ($domain && $language) {
		$site = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('language',$language)->getOne();
		if ($site !== null || $site->templet == $templet) $Templet->setConfigs(json_decode($site->templet_configs));
	}
	$configs = $Templet->getConfigs();
} else {
	$configs = null;
}

$results->success = true;
$results->configs = $configs;
?>