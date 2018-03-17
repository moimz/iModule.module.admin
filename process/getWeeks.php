<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 년도별 주차를 가져온다.
 *
 * @file /modules/admin/process/getWeeks.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$year = Request('year');
$start_date = strtotime($year.'-01-01');
$start_day = date('w',$start_date);

$end_date = strtotime($year.'-12-31');
$end_day = date('w',$end_date);

$lists = array();
$week = 1;
for ($i=$start_date - $start_day * 60 * 60 * 24, $loop=$end_date + (7 - $end_day) * 60 * 60 * 24;$i<$loop;$i=$i+60*60*24*7) {
	$lists[] = array('display'=>$week.'주차 ('.date('Y.m.d',$i).' ~ '.date('Y.m.d',$i+60*60*24*7 - 1).')','value'=>date('Y-m-d',$i));
	$week++;
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>