<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * HTML 편집도구에 첨부된 파일목록을 가져온다.
 * 
 * @file /modules/admin/process/@getHtmlEditorFiles.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 24.
 */
if (defined('__IM__') == false) exit;

$context = Decoder(Request('context'));
if ($context === false) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND_PAGE');
	return;
}

$context = json_decode($context);
$sitemap = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$context->domain)->where('language',$context->language)->where('menu',$context->menu)->where('page',$context->page)->getOne();
if ($sitemap == null || $sitemap->type != 'HTML') {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND_PAGE');
	return;
}

$context = json_decode($sitemap->context);
$files = $context != null && isset($context->files) == true && is_array($context->files) == true ? $context->files : array();
for ($i=0, $loop=count($files);$i<$loop;$i++) {
	$files[$i] = $this->IM->getModule('attachment')->getFileInfo($files[$i]);
}

$results->success = true;
$results->files = $files;
?>