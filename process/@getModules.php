<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 서버에 존재하는 모듈목록을 가져온다.
 *
 * @file /modules/admin/process/@getModules.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 1. 6.
 */
if (defined('__IM__') == false) exit;

$lists = array();
$modulesPath = @opendir(__IM_PATH__.'/modules');
while ($module = @readdir($modulesPath)) {
	if ($module != '.' && $module != '..' && is_dir(__IM_PATH__.'/modules/'.$module) == true) {
		$package = $this->getModule()->getPackage($module);
		
		if ($package !== null) {
			$item = array(
				'id'=>$package->id,
				'module'=>$module,
				'icon'=>$this->getModule()->getPackage($module)->icon,
				'title'=>$this->getModule()->getTitle($module),
				'version'=>$package->version,
				'description'=>$this->getModule()->getDescription($module),
				'hash'=>$this->getModule()->getHash($module)
			);
			
			$item['author'] = '';
			if (isset($package->author->name) == true) $item['author'].= $package->author->name;
			
			if ($this->getModule()->isInstalled($module) == true) {
				$installed = $this->getModule()->getInstalled($module);
				$item['installed'] = true;
				$item['installed_hash'] = $installed->hash;
				$item['db_size'] = $installed->db_size;
				$item['attachment_size'] = $installed->attachment_size;
				$item['sort'] = $installed->sort;
			} else {
				$item['installed'] = false;
				$item['installed_hash'] = $item['hash'];
				$item['db_size'] = 0;
				$item['attachment_size'] = 0;
				$item['sort'] = 0;
			}
			$item['isConfigPanel'] = $this->getModule()->isConfigPanel($module);
			$item['installed_date'] = filemtime(__IM_PATH__.'/modules/'.$module.'/package.json');
			
			$lists[] = $item;
		}
	}
}

@closedir($modulesPath);

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>