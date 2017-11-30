<?php
/**
 * 이 파일은 iModule CTL 모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 엑셀변환
 *
 * @file /modules/admin/process/getExcel.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2017. 11. 29.
 */
if (defined('__IM__') == false) exit;

$document = Request('document');
$attachments = array();

$cells = json_decode(Request('cells'));
$datas = json_decode(Request('datas'));

if (count($datas) == 0) {
	header("X-Excel-File:");
	exit;
}

$fileName = md5(time());
header("Content-Length:".count($datas));
header("X-Excel-File:".$fileName);

$mPHPExcel = new PHPExcel();
$mPHPExcelReader = new PHPExcelReader($this->getModule()->getPath().'/documents/style.xlsx');
$mPHPExcel = $mPHPExcelReader->GetExcel();

$columnLengths = array();

for ($i=0, $loop=count($cells);$i<$loop;$i++) {
	$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
	
	if ($i > 0) {
		$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A1'),$column.'1');
		$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A2'),$column.'2');
	}
	
	$mPHPExcel->getActiveSheet()->setCellValue($column.'1',$cells[$i]->title);
	$columnLengths[$i] = strlen($cells[$i]->title);
	
	if ($cells[$i]->align == "center") $mPHPExcel->getActiveSheet()->getStyle($column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	elseif ($cells[$i]->align == "right") $mPHPExcel->getActiveSheet()->getStyle($column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
}

$loopnum = 1;
foreach ($datas as $data) {
	for ($i=0, $loop=count($cells);$i<$loop;$i++) {
		$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
		
		if ($loopnum > 1) {
			$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle($column.'2'),$column.($loopnum+1));
		}
		
		$value = isset($data->{$cells[$i]->dataIndex}) == true ? $data->{$cells[$i]->dataIndex} : '';
		$columnLengths[$i] = $columnLengths[$i] < strlen($value) ? strlen($value) : $columnLengths[$i];
		
		$mPHPExcel->getActiveSheet()->setCellValue($column.($loopnum+1),$value);
	}
	
	$loopnum++;
}

for ($i=0, $loop=count($columnLengths);$i<$loop;$i++) {
	$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
	$length = $columnLengths[$i];
	$length = $length > 35 ? 35 : $length;
	$length = $length < 8 ? 8 : $length;
	$mPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($length * 1.15);
}

$mPHPExcel->getActiveSheet()->mergeCells('A'.($loopnum+2).':'.$column.($loopnum+2));
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getFont()->setSize(9);
$mPHPExcel->getActiveSheet()->getStyle('A'.($loopnum+2))->getFont()->setColor(new PHPExcel_Style_Color('FF666666'));
$mPHPExcel->getActiveSheet()->setCellValue('A'.($loopnum+2),date('Y년 m월 d일 H시 m분').' / '.$this->IM->getModule('member')->getMember()->name);

$mPHPExcel->getActiveSheet()->setAutoFilter('A1:'.$column.'1');
$mPHPExcel->getActiveSheet()->freezePane('D2');

$fileName = md5(time());
header("X-Excel-File:".$fileName);
$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
$excel = $mPHPExcelWriter->WriteExcel($fileName);

ForceFlush();
exit;