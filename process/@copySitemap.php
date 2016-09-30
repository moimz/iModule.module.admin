<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트맵을 다른 대상으로 부터 복제한다.
 *
 * @file /modules/admin/process/@copySitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $mode 복제모드 (menu or page)
 * @post string $domain 사이트도메인
 * @post string $language 사이트언어셋
 * @post string $menu 메뉴명
 * @post string $oDomain 복제해올 사이트도메인
 * @post string $oLanguage 복제해올 사이트언어셋
 * @post string $oMenu 복제해올 사이트 1차메뉴
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$errors = array();
$mode = Request('mode');
$domain = Request('domain');
$language = Request('language');
$menu = Request('menu');
$oDomain = Request('oDomain');
$oLanguage = Request('oLanguage');
$oMenu = Request('oMenu');
$oPage = Request('oPage');
if ($oDomain == '' || $oLanguage == '') {
	$errors['site'] = $this->getErrorMessage('REQUIRED');
}

if ($mode == 'menu') {
	if (!$oMenu) {
		$errors['oMenu'] = $this->getErrorMessage('REQUIRED');
	} elseif ($this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->has() == true) {
		$errors['oMenu'] = $this->getErrorMessage('DUPLICATED');
	}
	
	$target = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$oDomain)->where('language',$oLanguage)->where('menu',$oMenu)->where('page','')->getOne();
	if ($target == null) $errors['menu'] = $this->getErrorMessage('NOT_FOUND',$oMenu);
} else {
	if (!$oPage) {
		$errors['oPage'] = $this->getErrorMessage('REQUIRED');
	} elseif ($this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page',$oPage)->has() == true) {
		$errors['oPage'] = $this->getErrorMessage('DUPLICATED');
	}
	
	$target = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$oDomain)->where('language',$oLanguage)->where('menu',$oMenu)->where('page',$oPage)->getOne();
	if ($target == null) $errors['oPage'] = $this->getErrorMessage('NOT_FOUND',$oMenu);
}

if (count($errors) == 0) {
	if ($mode == 'menu') {
		$sort = $this->IM->db()->select($this->IM->getTable('sitemap'),'max(sort) as sort')->where('domain',$domain)->where('language',$language)->where('page','')->getOne();
		$sort = $sort->sort + 1;
	
		$insert = (array)$target;
		$insert['domain'] = $domain;
		$insert['language'] = $language;
		$insert['sort'] = $sort;
	
		if ($target->image > 0) $insert['image'] = $this->IM->getModule('attachment')->fileCopy($target->image);
		$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
	
		if (Request('is_include') == 'on') {
			$pages = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$oDomain)->where('language',$oLanguage)->where('menu',$oMenu)->where('page','','!=')->get();
			for ($i=0, $loop=count($pages);$i<$loop;$i++) {
				$insert = (array)$pages[$i];
				$insert['domain'] = $domain;
				$insert['language'] = $language;
				if ($pages[$i]->image > 0) $insert['image'] = $this->IM->getModule('attachment')->fileCopy($pages[$i]->image);
				
				$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
			}
		}
	} else {
		$sort = $this->IM->db()->select($this->IM->getTable('sitemap'),'max(sort) as sort')->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page','','!=')->getOne();
		$sort = $sort->sort + 1;
		
		$insert = (array)$target;
		$insert['domain'] = $domain;
		$insert['language'] = $language;
		$insert['menu'] = $menu;
		$insert['sort'] = $sort;
		
		if ($target->image > 0) $insert['image'] = $this->IM->getModule('attachment')->fileCopy($target->image);
		
		$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
		
		$results->success = true;
	}
	
	$results->success = true;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>