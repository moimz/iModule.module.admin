<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 위지윅 에디터에 첨부된 파일을 삭제한다.
 *
 * @file /modules/admin/process/@getWysiwygFiles.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$idx = Request('idx');

$results->success = $this->IM->getModule('attachment')->fileDelete($idx);
$results->idx = $idx;
?>