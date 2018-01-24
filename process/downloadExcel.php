<?php
/**
 * 이 파일은 iModule CTL 모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 생성된 엑셀파일을 다운로드한다.
 *
 * @file /modules/admin/process/downloadExcel.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2018. 1. 18.
 */
if (defined('__IM__') == false) exit;

$hash = Request('hash');
$title = urldecode(Request('title'));
$this->IM->getModule('attachment')->tempFileDownload($hash,true,$title.'.xlsx');
exit;
?>