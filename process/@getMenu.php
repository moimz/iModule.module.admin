<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트 메뉴 정보를 가져온다.
 *
 * @file /modules/admin/process/@getMenu.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$menu = Request('menu');
$page = Request('page');

$data = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu);
if ($page) $data->where('page',$page);
$data = $data->getOne();

if ($data != null) {
	if (preg_match('/^(fa|xi|xi2) /',$data->icon,$match) == true) {
		$data->icon_type = $match[1];
		$data->icon = preg_replace('/^(fa|xi|xi2) /','',$data->icon);
	} elseif ($data->icon) {
		$data->icon_type = 'image';
		$data->icon = $data->icon;
	} else {
		$data->icon_type = '';
		$data->icon = '';
	}
	
	$context = json_decode($data->context);
	$data->is_footer = $data->is_footer == 'TRUE';
	$data->is_hide = $data->is_hide == 'TRUE';
	
	if ($data->type == 'MODULE') {
		$data->target = $context->module;
		$data->_context = $context->context;
		$data->_configs = isset($context->configs) == true ? $context->configs : new stdClass();
	} elseif ($data->type == 'EXTERNAL') {
		$data->external = $context->external;
	} elseif ($data->type == 'PAGE') {
		$data->subpage = $context->page;
	} elseif ($data->type == 'WIDGET') {
		$data->widget = json_encode($context->widget,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
	} elseif ($data->type == 'LINK') {
		$data->link_url = $context->link;
		$data->link_target = $context->target;
	}
	
	unset($data->context);
	
	$results->success = true;
	$results->data = $data;
} else {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
}
?>