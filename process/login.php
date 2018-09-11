<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 현재 로그인이 되어 있는 사용자의 권한을 판단하여 사이트관리자 접속여부를 확인한다.
 *
 * @file /modules/admin/process/login.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$results = $this->IM->getModule('member')->doProcess('login');
?>