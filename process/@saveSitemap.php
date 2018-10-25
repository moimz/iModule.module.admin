<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 사이트 메뉴를 추가하거나 수정한다.
 *
 * @file /modules/admin/process/@saveSitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 10. 25.
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$mode = Request('mode');

$errors = array();
$title = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$icon_type = Request('icon_type');
$icon = Request('icon');
if ($icon && $icon_type != 'image') $icon = $icon_type.' '.$icon;
$is_footer = Request('is_footer') ? 'TRUE' : 'FALSE';
$is_hide = Request('is_hide') ? 'TRUE' : 'FALSE';
$description = Request('description');
$type = Request('type') ? Request('type') : $errors['type'] = $this->getErrorText('REQUIRED');

$header = new stdClass();
$footer = new stdClass();
if ($type == 'PAGE' || $type == 'LINK') {
	$header->type = 'NONE';
	$footer->type = 'NONE';
} else {
	$header->type = Request('header_type');
	if ($header->type == 'TEXT') {
		$header = $this->IM->getModule('admin')->getWysiwygContent('header_text','core','context.header',$header);
		$header->type = 'TEXT';
	} elseif ($header->type == 'EXTERNAL') {
		$header->external = Request('header_external') ? Request('header_external') : $errors['header_external'] = $this->getErrorText('REQUIRED');
	}
	
	$footer = new stdClass();
	$footer->type = Request('footer_type');
	if ($footer->type == 'TEXT') {
		$footer = $this->IM->getModule('admin')->getWysiwygContent('footer_text','core','context.footer',$footer);
		$footer->type = 'TEXT';
	} elseif ($footer->type == 'EXTERNAL') {
		$footer->external = Request('footer_external') ? Request('footer_external') : $errors['footer_external'] = $this->getErrorText('REQUIRED');
	}
}
$header = json_encode($header,JSON_UNESCAPED_UNICODE);
$footer = json_encode($footer,JSON_UNESCAPED_UNICODE);

if ($mode == 'menu') {
	$oMenu = Request('oMenu');
	$menu = preg_match('/^[a-zA-Z0-9_]+$/',Request('menu')) == true ? Request('menu') : $errors['menu'] = $this->getErrorText('ALPHABET_NUMBER_UNDERBAR_ONLY');
	if (in_array($menu,array('account','module')) == true) $errors['menu'] = $this->getErrorText('RESERVED_NAME');
	
	if ($oMenu != $menu && $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page','')->has() == true) {
		$errors['menu'] = $this->getErrorText('DUPLICATED');
	}
}

if ($mode == 'page') {
	$oMenu = Request('oMenu');
	$oPage = Request('oPage');
	$page = preg_match('/^[a-zA-Z0-9_]+$/',Request('page')) == true ? Request('page') : $errors['page'] = $this->getErrorText('ALPHABET_NUMBER_UNDERBAR_ONLY');
	
	if ($oPage != $page && $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page',$page)->has() == true) {
		$errors['page'] = $this->getErrorText('DUPLICATED');
	}
}

$context = new stdClass();

if ($type == 'MODULE') {
	if ($mode == 'menu') {
		$errors['type'] = $this->getErrorText('NOT_ALLOWED_MODULE_IN_MENU');
	} else {
		$context->module = Request('target') ? Request('target') : $errors['target'] = $this->getErrorText('REQUIRED');
		$context->context = Request('context') ? Request('context') : $errors['context'] = $this->getErrorText('REQUIRED');
		$configs = array();
		foreach ($_POST as $key=>$value) {
			if (preg_match('/^@(.*?)_configs_(.*?)$/',$key,$match) == true && array_key_exists('@'.$match[1],$_POST) == true) {
				if (isset($configs[$match[1].'_configs']) == false) $configs[$match[1].'_configs'] = array();
				$configs[$match[1].'_configs'][$match[2]] = $value;
			} elseif (preg_match('/^@/',$key) == true) {
				$configs[preg_replace('/^@/','',$key)] = $value;
			}
		}
		$context->configs = $configs;
	}
} elseif ($type == 'EXTERNAL') {
	$context->external = Request('external') ? Request('external') : $errors['external'] = $this->getErrorText('REQUIRED');
} elseif ($type == 'PAGE') {
	if ($mode == 'page') {
		$errors['type'] = $this->getErrorText('NOT_ALLOWED_SUBPAGE_IN_PAGE');
	} else {
		if (Request('subpage_create') == 'on') {
			$context->page = Request('subpage_code') ? Request('subpage_code') : $errors['subpage_code'] = $this->getErrorText('REQUIRED');
		} else {
			$context->page = Request('subpage') ? Request('subpage') : $errors['subpage'] = $this->getErrorText('REQUIRED');
		}
	}
} elseif ($type == 'WIDGET') {
	$context->widget = Request('widget') && json_decode(Request('widget')) != null ? json_decode(Request('widget')) : array();
} elseif ($type == 'LINK') {
	$context->link = Request('link_url') ? Request('link_url') : $errors['link_url'] = $this->getErrorText('REQUIRED');
	$context->target = Request('link_target') ? Request('link_target') : $errors['link_target'] = $this->getErrorText('REQUIRED');
}

if ($type == 'LINK' || $type == 'PAGE') {
	$layout = 'NONE';
} else {
	$layout = Request('layout') ? Request('layout') : $errors['layout'] = $this->getErrorText('REQUIRED');
}

if (count($errors) == 0) {
	$insert = array();
	$insert['domain'] = $domain;
	$insert['language'] = $language;
	$insert['icon'] = $icon;
	$insert['title'] = $title;
	$insert['is_footer'] = $is_footer;
	$insert['is_hide'] = $is_hide;
	$insert['description'] = $description;
	$insert['type'] = $type;
	$insert['layout'] = $layout;
	$insert['header'] = $header;
	$insert['footer'] = $footer;
	$insert['context'] = json_encode($context,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	
	$oData = null;
	
	if ($mode == 'menu') {
		$insert['menu'] = $menu;
		$insert['page'] = '';
		
		if ($oMenu) {
			$oData = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page','')->getOne();
			$this->IM->db()->update($this->IM->getTable('sitemap'),$insert)->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page','')->execute();
			$this->IM->db()->update($this->IM->getTable('sitemap'),array('menu'=>$menu))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->execute();
		} else {
			$sort = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('page','')->orderBy('sort','desc')->getOne();
			$insert['sort'] = $sort == null ? 0 : $sort->sort + 1;
			
			$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
		}
		
		if ($type == 'PAGE' && $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page',$context->page)->has() == false) {
			$sort = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page','','!=')->orderBy('sort','desc')->getOne();
			$insert['sort'] = $sort == null ? 0 : $sort->sort + 1;
			$insert['page'] = $context->page;
			$insert['title'] = 'EMPTY PAGE';
			$insert['layout'] = $layout;
			$insert['type'] = 'EMPTY';
			$insert['context'] = '{}';
			$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
		}
	}
	
	if ($mode == 'page') {
		$insert['menu'] = $oMenu;
		$insert['page'] = $page;
		
		if ($oPage) {
			$oData = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page',$oPage)->getOne();
			$this->IM->db()->update($this->IM->getTable('sitemap'),$insert)->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page',$oPage)->execute();
			/**
			 * 페이지주소가 변경되었을 경우, 1차메뉴에서 이 메뉴를 사용하고 있는 1차메뉴를 찾아 수정한다.
			 */
			if ($oPage != $page) {
				$menus = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page','')->get();
				
				for ($i=0, $loop=count($menus);$i<$loop;$i++) {
					if ($menus[$i]->type == 'PAGE') {
						$context = json_decode($menus[$i]->context);
						if ($context->page == $oPage) {
							$context->page = $page;
							$context = json_encode($context,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
							$this->IM->db()->update($this->IM->getTable('sitemap'),array('context'=>$context))->where('domain',$domain)->where('language',$language)->where('menu',$menus[$i]->menu)->where('page','')->execute();
						}
					}
				}
			}
		} else {
			$sort = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$oMenu)->where('page','','!=')->orderBy('sort','desc')->getOne();
			$insert['sort'] = $sort == null ? 0 : $sort->sort + 1;
			$this->IM->db()->insert($this->IM->getTable('sitemap'),$insert)->execute();
		}
	}
	
	/**
	 * 페이지 대표이미지를 등록한다.
	 */
	$image_delete = Request('image_delete') != null || $type == 'LINK';
	if ($image_delete == true && $oData != null && $oData->image > 0) {
		$this->IM->getModule('attachment')->fileDelete($oData->image);
		$this->db()->update($this->table->sitemap,array('image'=>0))->where('role',$insert['role'])->where('menu',$insert['menu'])->where('page',$insert['page'])->execute();
	} else {
		if (isset($_FILES['image']['tmp_name']) == true && $_FILES['image']['tmp_name']) {
			if ($oData != null && $oData->image > 0) {
				$this->IM->getModule('attachment')->fileReplace($oData->image,$_FILES['image']['name'],$_FILES['image']['tmp_name']);
			} else {
				$image = $this->IM->getModule('attachment')->fileSave($_FILES['image']['name'],$_FILES['image']['tmp_name'],'site','page','PUBLISHED');
				if ($image !== false) {
					$this->db()->update($this->table->sitemap,array('image'=>$image))->where('role',$insert['role'])->where('menu',$insert['menu'])->where('page',$insert['page'])->execute();
				}
			}
		}
	}
	
	$results->success = true;
} else {
	$results->success = false;
	$results->errors = $errors;
}