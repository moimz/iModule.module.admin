<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 전체 테이블 목록 및 통계를 가져온다.
 *
 * @file /modules/admin/process/@getTables.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 12. 15.
 */
if (defined('__IM__') == false) exit;

if ($this->checkIp('database') === false) {
	$results->success = false;
	$results->message = $this->getErrorText('FORBIDDEN');
	return;
}

$target = Request('target') ? Request('target') : 'all';
$lists = $this->db()->tables(true);

if ($target != 'all') {
	$databases = array();
	
	/**
	 * 코어의 데이터베이스명을 가져온다.
	 */
	$package = json_decode(file_get_contents(__IM_PATH__.'/package.json'));
	$databases = array_merge($databases,array_keys((array)$package->databases));
	
	/**
	 * 전체모듈의 데이터베이스명을 가져온다.
	 */
	$modules = $this->getModule()->getModules();
	foreach ($modules as $module) {
		if ($this->getModule()->isInstalled($module->module) === false) continue;
		$package = $this->getModule()->getPackage($module->module);
		if (isset($package->databases) == false) continue;
		
		$databases = array_merge($databases,array_keys((array)$package->databases));
	}
	
	$databases = array_map(function($name) { return __IM_DB_PREFIX__.$name; },$databases);
	
	$targets = array();
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		if (in_array($lists[$i]->name,$databases) === ($target == 'used')) $targets[] = $lists[$i];
	}
	
	$lists = $targets;
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>