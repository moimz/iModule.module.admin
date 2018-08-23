<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트에 생성되어 있는 1차메뉴 또는 2차메뉴를 삭제한다.
 *
 * @file /modules/admin/process/@deleteSitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 8. 23.
 */
if (defined('__IM__') == false) exit;

$domain = Param('domain');
$language = Param('language');
$menu = Param('menu');
$page = Request('page');

$sitemap = $this->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu);
if ($page) $sitemap->where('page',$page);
$sitemap = $sitemap->getOne();

if ($sitemap == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}

$this->db()->delete($this->IM->getTable('sitemap'))->where('domain',$sitemap->domain)->where('language',$sitemap->language)->where('menu',$sitemap->menu)->where('page',$sitemap->page)->execute();

if ($sitemap->page == '') {
	$this->db()->delete($this->IM->getTable('sitemap'))->where('domain',$sitemap->domain)->where('language',$sitemap->language)->where('menu',$sitemap->menu)->execute();
} else {
	$menu = $this->db()->select($this->IM->getTable('sitemap'))->where('domain',$sitemap->domain)->where('language',$sitemap->language)->where('menu',$sitemap->menu)->getOne();
	if ($menu->type == 'PAGE') {
		$context = json_decode($menu->context);
		if ($context->page == $sitemap->page) {
			$this->db()->update($this->IM->getTable('sitemap'),array('type'=>'EMPTY','context'=>'{}'))->where('domain',$sitemap->domain)->where('language',$sitemap->language)->where('menu',$sitemap->menu)->execute();
		}
	}
}

if ($sitemap->image > 0) {
	$this->IM->getModule('attachment')->fileDelete($sitemap->image);
}

$results->success = true;
?>