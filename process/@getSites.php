<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 생성되어 있는 모든 사이트목록을 가져온다.
 *
 * @file /modules/admin/process/@getSites.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$lists = $this->IM->getSites();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$lists[$i]->grouping = $lists[$i]->sort.'@'.$lists[$i]->domain;
	$lists[$i]->url = ($lists[$i]->is_ssl == 'TRUE' ? 'https://' : 'http://').$lists[$i]->domain.__IM_DIR__.'/'.$lists[$i]->language.'/';
	$lists[$i]->favicon = $lists[$i]->favicon == -1 ? __IM_DIR__.'/images/logo/favicon.ico' : ($lists[$i]->favicon == 0 ? null : __IM_DIR__.'/attachment/view/'.$lists[$i]->favicon.'/favicon.ico');
	$lists[$i]->emblem = $lists[$i]->emblem == -1 ? __IM_DIR__.'/images/logo/emblem.png' : ($lists[$i]->emblem == 0 ? $this->AdminModule->getDir().'/images/empty_square.png' : __IM_DIR__.'/attachment/view/'.$lists[$i]->emblem.'/emblem.png');
	$lists[$i]->favicon = $lists[$i]->favicon == null ? $lists[$i]->emblem : $lists[$i]->favicon;
	$lists[$i]->sort = $lists[$i]->sort * 1000 + ($lists[$i]->is_default == 'TRUE' ? 0 : $i + 1);
	
	$lists[$i]->display = $lists[$i]->title.'('.$lists[$i]->url.')';
	$lists[$i]->value = $lists[$i]->domain.'@'.$lists[$i]->language;
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>