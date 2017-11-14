<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트에 생성되어 있는 1차메뉴 또는 2차메뉴 목록을 가져온다.
 *
 * @file /modules/admin/process/@getSitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $domain 사이트도메인
 * @post string $language 사이트언어셋
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$menu = Request('menu');

/**
 * 사이트정보를 가져온다.
 */
$site = $this->IM->getSites($domain,$language,true);

if (strpos($site->templet,'#') === 0 && $this->IM->getModule()->isSitemap(substr($site->templet,1)) == true) {
	$results->success = false;
	$results->message = str_replace('{MODULE}',$this->IM->getModule()->getName(substr($site->templet,1)),$this->getErrorText('SITEMAP_FROM_MODULE'));
} else {
	$lists = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language);
	if ($menu == null) $lists->where('page','');
	else $lists->where('menu',$menu)->where('page','','!=');
	$lists = $lists->orderBy('sort','asc')->get();
	
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$lists[$i]->url = ($site->is_ssl == 'TRUE' ? 'https://' : 'http://').$site->domain.__IM_DIR__.'/'.$site->language.'/';
		if ($lists[$i]->page) $lists[$i]->url.= $lists[$i]->menu.'/';
		
		
		$lists[$i]->sort = $i;
		$lists[$i]->icon = $this->IM->parseIconString($lists[$i]->icon);
		
		$context = json_decode($lists[$i]->context);
		if ($lists[$i]->type == 'EXTERNAL') {
			$lists[$i]->context = $context->external;
		} elseif ($lists[$i]->type == 'MODULE') {
			$lists[$i]->context = $this->Module->getTitle($context->module).' - '.$this->Module->getContextTitle($context->context,$context->module);
		} elseif ($lists[$i]->type == 'PAGE') {
			$lists[$i]->context = $this->IM->getPages($lists[$i]->menu,$context->page,$lists[$i]->domain,$lists[$i]->language)->title.'('.$context->page.')';
		} elseif ($lists[$i]->type == 'LINK') {
			$lists[$i]->context = $context->link;
		}
		
		$this->IM->db()->update($this->IM->getTable('sitemap'),array('sort'=>$i))->where('domain',$domain)->where('language',$language)->where('menu',$lists[$i]->menu)->where('page',$lists[$i]->page)->execute();
	}
	
	$results->success = true;
	$results->lists = $lists;
	$results->count = count($lists);
}
?>