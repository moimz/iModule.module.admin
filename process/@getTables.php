<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 전체 테이블 목록 및 통계를 가져온다.
 *
 * @file /modules/admin/process/@getTables.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 11. 23.
 */
if (defined('__IM__') == false) exit;

$lists = $this->db()->tables(true);

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>