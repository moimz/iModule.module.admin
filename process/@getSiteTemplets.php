<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 서버에 존재하는 사이트 템플릿 목록을 가져온다.
 *
 * @file /modules/admin/process/@getSiteTemplets.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$lists = array();
$templetsPath = @opendir(__IM_PATH__.'/templets');
while ($templet = @readdir($templetsPath)) {
	if ($templet != '.' && $templet != '..' && is_dir(__IM_PATH__.'/templets/'.$templet) == true) {
		$package = json_decode(file_get_contents(__IM_PATH__.'/templets/'.$templet.'/package.json'));
		$lists[] = array(
			'templet'=>$templet,
			'title'=>(isset($package->title->{$this->IM->language}) == true ? $package->title->{$this->IM->language} : $package->title->{$package->IM->language}).'('.$templet.')'
		);
	}
}
@closedir($templetsPath);

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>