<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 엑셀변환
 *
 * @file /modules/admin/process/@getExcel.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2020. 4. 29.
 */
if (defined('__IM__') == false) exit;

$document = Param('document');

if ($document == 'log') {
	$log = Param('log');
	$module = Request('module');
	$keyword = Request('keyword');
	$start_date = Request('start_date') ? strtotime(date('Y-m-d',strtotime(Request('start_date')))) : 0;
	$end_date = Request('end_date') ? strtotime(date('Y-m-d 24:00:00',strtotime(Request('end_date')))) : 0;
	
	if ($log == 'admin') {
		$mMember = $this->IM->getModule('member');
		$lists = $this->db()->select($this->table->page_log.' l','l.*, m.name, m.email')->join($mMember->getTable('member').' m','m.idx=l.midx','LEFT');
		if ($keyword) $lists->where('(m.name like ? or m.email like ? or l.ip like ? or l.page like ?)',array('%'.$keyword.'%','%'.$keyword.'%','%'.$keyword.'%','%'.$keyword.'%'));
		if ($start_date) $lists->where('l.reg_date',$start_date,'>=');
		if ($end_date) $lists->where('l.reg_date',$end_date,'<');
		$lists = $lists->orderBy('l.reg_date','desc')->get();
		
		$columns = array('date','name','email','page','ip','agent');
	}
	
	$mPHPExcel = $this->IM->getModule('admin')->createExcel('사이트관리자 접근로그',$this->getModule()->getPath().'/documents/style.xlsx',count($lists),1);
	$mPHPExcel->setActiveSheetIndex(0);
	
	$columnLengths = array();
	for ($i=0, $loop=count($columns);$i<$loop;$i++) {
		$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
		
		if ($i > 0) {
			$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A1'),$column.'1');
			$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle('A2'),$column.'2');
		}
		
		$mPHPExcel->getActiveSheet()->setCellValue($column.'1',$this->getText('excel/log/'.$columns[$i]));
		$columnLengths[$i] = mb_strlen($this->getText('excel/log/'.$columns[$i]),'utf-8');
	}
	
	$loopnum = 1;
	foreach ($lists as $list) {
		$this->IM->getModule('admin')->createExcelProgress();
		
		for ($i=0, $loop=count($columns);$i<$loop;$i++) {
			$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
			
			if ($loopnum > 1) {
				$mPHPExcel->getActiveSheet()->duplicateStyle($mPHPExcel->getActiveSheet()->getStyle($column.'2'),$column.($loopnum+1));
			}
			
			if ($columns[$i] == 'date') {
				$mPHPExcel->getActiveSheet()->setCellValue($column.($loopnum+1),PHPExcel_Shared_Date::PHPToExcel(new DateTime(date('Y-m-d H:i:s',$list->reg_date))));
				$mPHPExcel->getActiveSheet()->getStyle($column.($loopnum+1))->getNumberFormat()->setFormatCode('yyyy-mm-dd HH:mm:ss');
				$mPHPExcel->getActiveSheet()->getStyle($column.($loopnum+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$columnLengths[$i] = $columnLengths[$i] < 15 ? 15 : $columnLengths[$i];
			} else {
				$value = $list->{$columns[$i]};
				$columnLengths[$i] = $columnLengths[$i] < mb_strlen($value,'utf-8') ? mb_strlen($value,'utf-8') : $columnLengths[$i];
				$mPHPExcel->getActiveSheet()->setCellValue($column.($loopnum+1),$value);
			}
		}
		
		$loopnum++;
	}
	
	for ($i=0, $loop=count($columnLengths);$i<$loop;$i++) {
		$column = $mPHPExcel->getActiveSheet()->getCell()->stringFromColumnIndex($i);
		$length = $columnLengths[$i];
		$length = $length > 100 ? 100 : $length;
		$length = $length < 8 ? 8 : $length;
		$mPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($length * 1.15);
	}
	
	$mPHPExcel->getActiveSheet()->setAutoFilter('A1:'.$column.'1');
}

$this->IM->getModule('admin')->createExcelComplete();
?>