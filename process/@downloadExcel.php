<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 생성된 엑셀파일을 다운로드한다.
 *
 * @file /modules/admin/process/@downloadExcel.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2020. 4. 29.
 */
if (defined('__IM__') == false) exit;

$hash = Param('hash');
$document = Param('document');

if ($document == 'log') {
	$log = Param('log');
	if ($log == 'admin') {
		$title = '사이트관리자 접근로그';
	}
}

$mAttachment = $this->IM->getModule('attachment');
$mime = $mAttachment->getFileMime($mAttachment->getTempPath(true).'/'.$hash);

$this->IM->getModule('attachment')->tempFileDownload($hash,true,$title.'.xlsx');
exit;
?>