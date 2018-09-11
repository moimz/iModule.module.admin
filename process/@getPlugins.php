<?php
/**
 * 이 파일은 iPlugin 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 서버에 존재하는 플러그인목록을 가져온다.
 *
 * @file /plugins/admin/process/@getPlugins.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 6. 21.
 */
if (defined('__IM__') == false) exit;

$lists = array();
$pluginsPath = @opendir(__IM_PATH__.'/plugins');
while ($plugin = @readdir($pluginsPath)) {
	if ($plugin != '.' && $plugin != '..' && is_dir(__IM_PATH__.'/plugins/'.$plugin) == true) {
		$package = $this->getPlugin()->getPackage($plugin);
		
		if ($package !== null) {
			$item = array(
				'id'=>$package->id,
				'plugin'=>$plugin,
				'icon'=>$this->getPlugin()->getPackage($plugin)->icon,
				'title'=>$this->getPlugin()->getTitle($plugin),
				'version'=>$package->version,
				'description'=>$this->getPlugin()->getDescription($plugin),
				'hash'=>$this->getPlugin()->getHash($plugin),
			);
			
			$item['author'] = '';
			if (isset($package->author->name) == true) $item['author'].= $package->author->name;
			
			if ($this->getPlugin()->isInstalled($plugin) == true) {
				$installed = $this->getPlugin()->getInstalled($plugin);
				$item['installed'] = true;
				$item['installed_hash'] = $installed->hash;
				$item['db_size'] = $installed->db_size;
				$item['is_active'] = $installed->is_active;
				$item['sort'] = $installed->sort;
			} else {
				$item['installed'] = false;
				$item['installed_hash'] = $item['hash'];
				$item['db_size'] = 0;
				$item['is_active'] = 'FALSE';
				$item['sort'] = 0;
			}
			$item['isConfigPanel'] = $this->getPlugin()->isConfigPanel($plugin);
			
			$lists[] = $item;
		}
	}
}

@closedir($pluginsPath);

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>