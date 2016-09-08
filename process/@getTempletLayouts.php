<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트 템플릿의 레이아웃 종류를 가져온다.
 *
 * @file /modules/admin/process/@getTempletLayouts.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');

$site = $this->IM->getSites($domain,$language);
$templet = json_decode(file_get_contents(__IM_PATH__.'/templets/'.$site->templet.'/package.json'));

$lists = array();
foreach ($templet->layouts as $layout=>$description) {
	$lists[] = array(
		'layout'=>$layout,
		'description'=>isset($description->{$this->IM->language}) == true ? $description->{$this->IM->language} : ''
	);
}

$results->success = true;
$results->lists = $lists;
$results->count = count($lists);
?>