<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 사이트 메뉴의 순서를 저장한다.
 *
 * @file /modules/admin/process/@saveSitemapSort.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$updated = json_decode(Request('updated'));

for ($i=0, $loop=count($updated);$i<$loop;$i++) {
	$this->IM->db()->update($this->IM->getTable('sitemap'),array('sort'=>$updated[$i]->sort))->where('domain',$updated[$i]->domain)->where('language',$updated[$i]->language)->where('menu',$updated[$i]->menu)->where('page',$updated[$i]->page)->execute();
}

$results->success = true;
?>