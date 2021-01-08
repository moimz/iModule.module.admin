<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 데이터베이스 관리에서 쿼리를 실행한다.
 *
 * @file /modules/admin/process/@runQuery.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2021. 1. 8.
 */
if (defined('__IM__') == false) exit;

$query = Request('q') ? base64_decode(Request('q')) : '';
if ($query === null) {
	$results->success = false;
	$results->message = 'Problem preparing query';
	return;
}

$querys = array();

$splitPosition = -1;
$isQuote = null;
for ($i=0, $loop=mb_strlen($query,'UTF-8');$i<$loop;$i++) {
	$pString = $i == 0 ? '' : mb_substr($query,$i-1,1);
	$string = mb_substr($query,$i,1,'UTF-8');
	if ($string == "'" || $string == '"') {
		if ($pString === '\\') continue;
		
		if ($isQuote === null) $isQuote = $string;
		elseif ($isQuote === $string) $isQuote = null;
	}
	
	if ($string == ';') {
		if ($isQuote !== null) continue;
		$querys[] = trim(mb_substr($query,$splitPosition + 1,$i - $splitPosition,'UTF-8'));
		$splitPosition = $i;
	}
}

$last = trim(mb_substr($query,$splitPosition + 1,null,'UTF-8'));
if (strlen($last) > 0) $querys[] = $last;

$mysqli = $this->db()->mysqli();

$runs = array();
$mysqli->autocommit(false);
foreach ($querys as $query) {
	if (!$stmt = $mysqli->prepare($query)) {
		$results->success = false;
		$results->querys = $querys;
		$results->error = 'Problem preparing query :<br>'.$query;
		$mysqli->rollback();
		return;
	}
	$stmt->execute();
	$meta = $stmt->result_metadata();
	
	$run = new stdClass();
	$run->query = $query;
	
	if ($meta === false) {
		$run->type = 'console';
		$run->message = 'Query OK, '.$stmt->affected_rows.' row'.($stmt->affected_rows > 1 ? 's' : '').' affected';
	} else {
		$run->type = 'list';
		$row = array();
		$fields = array();
		$parameters = array();
		while ($field = $meta->fetch_field()) {
			$fields[] = $field;
			$row[$field->name] = null;
			$parameters[] = &$row[$field->name];
		}
		$run->fields = $fields;
		$stmt->store_result();
		
		call_user_func_array(array($stmt,'bind_result'),$parameters);
		$datas = array();
		while ($stmt->fetch()) {
			$data = array();
			foreach ($row as $key=>$val) {
				$data[$key] = isset($val) == false || $val === null ? '' : $val;
			}
			array_push($datas,(object)$data);
		}
		$run->datas = $datas;
	}
	$stmt->free_result();
	
	$runs[] = $run;
}

$mysqli->commit();
$mysqli->autocommit(true);

$results->success = true;
$results->runs = $runs;
?>