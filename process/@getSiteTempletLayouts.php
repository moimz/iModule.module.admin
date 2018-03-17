<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트 템플릿의 레이아웃 종류를 가져온다.
 *
 * @file /modules/admin/process/@getTempletLayouts.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');

$site = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('language',$language)->getOne();
$layouts = $this->IM->getTemplet($this->IM,$site->templet)->getLayouts();

$results->success = true;
$results->lists = $layouts;
$results->count = count($layouts);
?>