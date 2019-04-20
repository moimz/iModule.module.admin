<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 사이트 메뉴 정보를 가져온다.
 *
 * @file /modules/admin/process/@getMenu.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 4. 20.
 */
if (defined('__IM__') == false) exit;

$domain = Param('domain');
$language = Param('language');
$menu = Param('menu');
$page = Param('page');

$data = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page',$page)->getOne();
if ($data == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}

if ($data->type == 'GROUPSTART') {
	
} else {
	$header = json_decode($data->header);
	$data->header_type = $header->type;
	if ($data->header_type == 'EXTERNAL') {
		$data->header_external = $header->external;
	} elseif ($data->header_type == 'TEXT') {
		$data->header_text = $this->IM->getModule('wysiwyg')->decodeContent($header->text,false);
		$data->header_text_files = $header->files;
	}
	
	$footer = json_decode($data->footer);
	$data->footer_type = $footer->type;
	if ($data->footer_type == 'EXTERNAL') {
		$data->footer_external = $footer->external;
	} elseif ($data->footer_type == 'TEXT') {
		$data->footer_text = $this->IM->getModule('wysiwyg')->decodeContent($footer->text,false);
		$data->footer_text_files = $footer->files;
	}
}

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
?>