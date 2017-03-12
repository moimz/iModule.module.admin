<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 템플릿의 환경설정폼을 가져온다.
 *
 * @file /modules/admin/process/@getTempletConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$name = Request('name');
$type = Request('type');
$target = Request('target');
$templet = Request('templet');
$position = Request('position');

if ($type == 'core') {
	if ($target == 'site') {
		$domain = Request('domain');
		$language = Request('language');
		
		$Templet = $this->IM->getTemplet($this->IM,$templet);
		
		if ($Templet->isLoaded() === true) {
			if ($domain && $language) {
				$site = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('language',$language)->getOne();
				if ($site !== null && $site->templet == $templet) $Templet->setConfigs(json_decode($site->templet_configs));
			}
			$configs = $Templet->getConfigs();
		} else {
			$configs = null;
		}
	}
}

if ($type == 'module') {
	$Templet = $this->IM->getModule($target)->getTemplet($templet);
	
	if ($position == 'module') {
		if ($this->IM->getModule()->isInstalled($target) == true && $this->IM->getModule($target)->getModule()->getConfig($name.'_configs') != null) {
			$Templet->setConfigs($this->IM->getModule($target)->getModule()->getConfig($name.'_configs'));
		}
	}
	
	if ($position == 'sitemap') {
		$domain = Request('domain');
		$language = Request('language');
		$menu = Request('menu');
		$page = Request('page');
		$parent = Request('parent');
		
		if ($menu && $page) {
			$sitemap = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page',$page)->getOne();
			if ($sitemap != null && $sitemap->type == 'MODULE') {
				$name = preg_replace('/^@/','',$name);
				$context = json_decode($sitemap->context);
				
				if ($context->module == $parent && $context->configs->{$name} == $templet && isset($context->configs->{$name.'_configs'}) == true) {
					$Templet->setConfigs($context->configs->{$name.'_configs'});
				}
			}
		}
	}
	
	$configs = $Templet->getConfigs();
}

$results->success = true;
$results->configs = $configs;
?>