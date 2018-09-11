<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 관리자모듈에 의해 생성된 엑셀파일로 변환한다.
 *
 * @file /modules/admin/process/downloadExcel.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$hash = Request('hash');
$title = urldecode(Request('title'));
$this->IM->getModule('attachment')->tempFileDownload($hash,true,$title.'.xlsx');
exit;
?>