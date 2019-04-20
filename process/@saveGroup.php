<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 사이트 2차 메뉴에 그룹을 추가하거나 수정한다.
 *
 * @file /modules/admin/process/@saveGroup.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 4. 20.
 */
if (defined('__IM__') == false) exit;

$domain = Param('domain');
$language = Param('language');
$menu = Param('menu');
$group = Request('group');

$errors = array();
$title = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$icon = Request('icon');
if ($icon && $icon_type != 'image') $icon = $icon_type.' '.$icon;

if (count($errors) == 0) {
	$insert = array();
	
	if ($group) {
		$insert['title'] = $title;
		if ($icon) $insert['icon'] = $icon;
		
		$this->IM->db()->update($this->IM->getTable('sitemap'),$insert)->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page','^'.$group)->execute();
		$this->IM->db()->update($this->IM->getTable('sitemap'),$insert)->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page','$'.$group)->execute();
	} else {
		$this->IM->db()->setLockMethod('WRITE')->lock($this->IM->getTable('sitemap'));
		$group = substr(md5(time().rand(0,100000)),0,10);
		while (true) {
			if ($this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page','^'.$group)->has() == false) break;
			$group = substr(md5(time().rand(0,100000)),0,20);
		}
		
		$sort = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->count();
		
		$insert['domain'] = $domain;
		$insert['language'] = $language;
		$insert['menu'] = $menu;
		$insert['page'] = '^'.$group;
		if ($icon) $insert['icon'] = $icon;
		$insert['title'] = $title;
		$insert['type'] = 'GROUPSTART';
		$insert['layout'] = 'EMPTY';
		$insert['context'] = '{}';
		$insert['header'] = '{}';
		$insert['footer'] = '{}';
		$insert['sort'] = $sort;
		$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
		
		$insert['page'] = '$'.$group;
		$insert['type'] = 'GROUPEND';
		$insert['sort'] = $sort + 1;
		$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
		
		$this->IM->db()->unlock();
	}
	
	$results->success = true;
	$results->group = $group;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>