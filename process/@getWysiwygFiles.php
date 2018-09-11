<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 위지윅 에디터에 첨부된 파일목록을 불러온다.
 *
 * @file /modules/admin/process/@getWysiwygFiles.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$idx = Request('idx') ? explode(',',Request('idx')) : array();
$lists = array();
for ($i=0, $loop=count($idx);$i<$loop;$i++) {
	$lists[$i] = $this->IM->getModule('attachment')->getFileInfo($idx[$i]);
}

$results->success = true;
$results->lists = $lists;
?>