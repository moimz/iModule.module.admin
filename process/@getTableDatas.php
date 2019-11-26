<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 테이블 데이터를 가져온다.
 *
 * @file /modules/admin/process/@getTableDatas.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 11. 23.
 */
if (defined('__IM__') == false) exit;

$table = Param('table');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir');
$where = Request('where') ? preg_replace('/^where /','',trim(base64_decode(Request('where')))) : null;

$lists = $this->db()->setPrefix('')->select($table);
if ($where) $lists->where($where);
$total = $lists->copy()->count();

if ($sort) $lists->orderBy($sort,$dir);

$lists = $lists->limit($start,$limit)->get();

$results->success = true;
$results->lists = $lists;
$results->total = $total;
?>