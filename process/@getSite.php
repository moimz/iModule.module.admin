<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 사이트정보를 가져온다.
 *
 * @file /modules/admin/process/@getSite.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 11. 18.
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');
$data = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('language',$language)->getOne();

if ($data != null) {
	$data->logo = $data->logo && json_decode($data->logo) != null ? json_decode($data->logo) : null;
	if ($data->logo == null || empty($data->logo->default) == true) $data->logo->default = -1;
	if ($data->logo == null || empty($data->logo->footer) == true) $data->logo->footer = -1;
	$data->logo_default = $data->logo->default == -1 ? __IM_DIR__.'/images/logo/default.png' : ($data->logo->default == 0 ? $this->getModule()->getDir().'/images/empty_horizontal.png' : __IM_DIR__.'/attachment/view/'.$data->logo->default.'/default.png');
	$data->logo_footer = $data->logo->footer == -1 ? __IM_DIR__.'/images/logo/footer.png' : ($data->logo->footer == 0 ? $this->getModule()->getDir().'/images/empty_square.png' : __IM_DIR__.'/attachment/view/'.$data->logo->footer.'/footer.png');
	
	$data->emblem = $data->emblem == -1 ? __IM_DIR__.'/images/logo/emblem.png' : ($data->emblem == 0 ? $this->getModule()->getDir().'/images/empty_square.png' : __IM_DIR__.'/attachment/view/'.$data->emblem.'/emblem.png');
	$data->favicon = $data->favicon == -1 ? __IM_DIR__.'/images/logo/favicon.ico' : ($data->favicon == 0 ? $this->getModule()->getDir().'/images/empty_square.png' : __IM_DIR__.'/attachment/view/'.$data->favicon.'/favicon.ico');
	$data->image = $data->image == -1 ? __IM_DIR__.'/images/logo/preview.jpg' : ($data->image == 0 ? $this->getModule()->getDir().'/images/empty_horizontal.png' : __IM_DIR__.'/attachment/view/'.$data->image.'/preview.jpg');
	
	$data->is_default = $data->is_default == 'TRUE';
	$results->success = true;
	$results->data = $data;
} else {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
}
?>