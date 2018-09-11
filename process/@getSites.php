<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 생성되어 있는 모든 사이트목록을 가져온다.
 *
 * @file /modules/admin/process/@getSites.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$is_sitemap = Request('is_sitemap') == 'true';
$sites = $this->IM->getSites();
$lists = array();
foreach ($sites as $site) {
	if ($is_sitemap == true) {
		$temp = explode('.',substr($site->templet,1));
		if ($this->IM->getModule()->isSitemap($temp[0]) == true) continue;
	}
	
	$site->grouping = $site->sort.'@'.$site->domain;
	$site->url = ($site->is_ssl == 'TRUE' ? 'https://' : 'http://').$site->domain.__IM_DIR__.'/'.$site->language.'/';
	$site->favicon = $site->favicon == -1 ? __IM_DIR__.'/images/logo/favicon.ico' : ($site->favicon == 0 ? null : __IM_DIR__.'/attachment/view/'.$site->favicon.'/favicon.ico');
	$site->emblem = $site->emblem == -1 ? __IM_DIR__.'/images/logo/emblem.png' : ($site->emblem == 0 ? $this->getModule()->getDir().'/images/empty_square.png' : __IM_DIR__.'/attachment/view/'.$site->emblem.'/emblem.png');
	$site->favicon = $site->favicon == null ? $site->emblem : $site->favicon;
	$site->sort = $site->sort * 1000 + ($site->is_default == 'TRUE' ? 0 : $i + 1);
	
	$site->display = $site->title.'('.$site->url.')';
	$site->value = $site->domain.'@'.$site->language;
	
	$Templet = $this->IM->getTemplet($this->IM,$site->templet);
	$site->templet = $Templet->getTitle();
	
	$lists[] = $site;
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>