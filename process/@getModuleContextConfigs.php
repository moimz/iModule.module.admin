<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모듈의 컨텍스트에 따른 환경설정을 가져온다.
 *
 * @file /modules/admin/process/@copySitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @post string $domain 사이트도메인
 * @post string $language 사이트언어셋
 * @post string $target 모듈명
 * @post string $context 컨텍스트명
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$menu = Request('menu');
$page = Request('page');
$module = Request('target');
$context = Request('context');

$this->IM->initSites(true);

$results->success = true;
$results->configs = $this->IM->getModule()->getContextConfigs($domain,$language,$menu,$page,$module,$context);
?>