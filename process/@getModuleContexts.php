<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 특정모듈의 전체 컨텍스트 목록을 가져온다.
 *
 * @file /modules/admin/process/@getModuleContexts.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 4. 9.
 */
if (defined('__IM__') == false) exit;

$module = Request('target');
$contexts = $module ? $this->Module->getContexts($module) : array();

$results->success = true;
$results->lists = $contexts;
$results->total = count($contexts);
?>