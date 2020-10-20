<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트로그를 불러온다.
 *
 * @file /plugins/admin/process/@getLogs.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2020. 10. 20.
 */
if (defined('__IM__') == false) exit;

$log = Param('log');
$module = Request('module');
$user = Request('user');
$keyword = Request('keyword');
$start_date = Request('start_date') ? strtotime(date('Y-m-d',strtotime(Request('start_date')))) : 0;
$end_date = Request('end_date') ? strtotime(date('Y-m-d 24:00:00',strtotime(Request('end_date')))) : 0;
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir');

$lists = array();
$total = 0;

if ($log == 'admin') {
	$sort = 'l.'.$sort;

	$mMember = $this->IM->getModule('member');
	$lists = $this->db()->select($this->table->page_log.' l','l.*, m.name, m.email')->join($mMember->getTable('member').' m','m.idx=l.midx','LEFT');
	if ($keyword) $lists->where('(m.name like ? or m.email like ? or l.ip like ? or l.page like ?)',array('%'.$keyword.'%','%'.$keyword.'%','%'.$keyword.'%','%'.$keyword.'%'));
	if ($start_date) $lists->where('l.reg_date',$start_date,'>=');
	if ($end_date) $lists->where('l.reg_date',$end_date,'<');
	$total = $lists->copy()->count();
	$lists = $lists->orderBy($sort,$dir)->limit($start,$limit)->get();
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$lists[$i]->photo = $this->IM->getModuleUrl('member','photo',$lists[$i]->midx,'profile.jpg');
	}
}

if ($log == 'process') {
	$sort = 'l.'.$sort;

	$mMember = $this->IM->getModule('member');
	$lists = $this->db()->select($this->table->process_log.' l','l.*, m.name, m.email')->join($mMember->getTable('member').' m','m.idx=l.midx','LEFT');
	if ($user) $lists->where('(m.name like ? or m.email like ? or l.ip like ?)',array('%'.$user.'%','%'.$user.'%','%'.$user.'%'));
	if ($module) $lists->where('l.module',$module);
	if ($keyword) $lists->where('l.action',$keyword,'LIKE');
	$total = $lists->copy()->count();
	$lists = $lists->orderBy($sort,$dir)->limit($start,$limit)->get();
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$lists[$i]->icon = $this->IM->getModule()->getIcon($lists[$i]->module);
		$lists[$i]->title = $this->IM->getModule()->getTitle($lists[$i]->module);
		$lists[$i]->photo = $this->IM->getModuleUrl('member','photo',$lists[$i]->midx,'profile.jpg');
	}
}

if ($log == 'member') {
	$sort = 'a.'.$sort;

	$mMember = $this->IM->getModule('member');
	$lists = $mMember->db()->select($mMember->getTable('activity').' a','a.*, m.name, m.email')->join($mMember->getTable('member').' m','m.idx=a.midx','LEFT');
	if ($user) $lists->where('(m.name like ? or m.email like ? or a.ip like ?)',array('%'.$user.'%','%'.$user.'%','%'.$user.'%'));
	if ($module) $lists->where('a.module',$module);
	if ($keyword) $lists->where('a.code',$keyword,'LIKE');
	$total = $lists->copy()->count();
	$lists = $lists->orderBy($sort,$dir)->limit($start,$limit)->get();
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$lists[$i]->reg_date = $lists[$i]->reg_date / 1000;
		$lists[$i]->icon = $this->IM->getModule()->getIcon($lists[$i]->module);
		$lists[$i]->title = $this->IM->getModule()->getTitle($lists[$i]->module);
		$lists[$i]->photo = $this->IM->getModuleUrl('member','photo',$lists[$i]->midx,'profile.jpg');
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = $total;
?>