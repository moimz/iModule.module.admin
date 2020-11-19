<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * HTML 컨텍스트 내용을 저장한다.
 * 
 * @file /modules/admin/process/@saveHtmlContext.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 3. 24.
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$menu = Request('menu') ? Request('menu') : '';
$page = Request('page') ? Request('page') : '';

$sitemap = $this->IM->db()->select($this->IM->getTable('sitemap'))->where('domain',$domain)->where('language',$language)->where('menu',$menu)->where('page',$page)->getOne();

if ($sitemap == null || $sitemap->type != 'HTML') {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND_PAGE');
	return;
}

$files = array();
$attachments = is_array(Request('attachments')) == true ? Request('attachments') : array();
for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
	$fileIdx = Decoder($attachments[$i]);
	if ($fileIdx !== false) {
		$files[] = $fileIdx;
		$this->IM->getModule('attachment')->filePublish($fileIdx);
	}
}

$html = $this->IM->getModule('wysiwyg')->encodeContent(Request('html'),$files);
$css = Request('css');

$context = new stdClass();
$context->html = $html;
$context->css = $css;
$context->files = $files;

$this->IM->db()->update($this->IM->getTable('sitemap'),array('context'=>json_encode($context,JSON_UNESCAPED_UNICODE)))->where('domain',$sitemap->domain)->where('language',$sitemap->language)->where('menu',$sitemap->menu)->where('page',$sitemap->page)->execute();

/**
 * 사이트맵 캐시를 제거한다.
 */
$this->IM->cache()->reset('core','sitemap','all');

$results->success = true;
?>