<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 모듈의 컨텍스트에 따른 환경설정을 가져온다.
 *
 * @file /modules/admin/process/@getModuleContextConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$domain = Param('domain');
$language = Param('language');
$menu = Param('menu');
$page = Param('page');
$module = Param('target');
$context = Param('context');

$this->IM->initSites(true);

$results->success = true;
$results->configs = $this->IM->getModule()->getContextConfigs($domain,$language,$menu,$page,$module,$context);
?>