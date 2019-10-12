<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 사이트를 추가한다.
 *
 * @file /modules/admin/process/@getSiteTemplets.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;

$oDomain = Request('oDomain');
$oLanguage = Request('oLanguage');

$errors = array();

$domain = Request('domain');
$language = preg_match('/^[a-z]{2}$/',Request('language')) == true ? Request('language') : $errors['language'] = $this->getErrorText('INVALID_LANGUAGE_CODE',Request('language'));
$alias = Request('alias');
$templet = Request('templet');
$title = Request('title');
$description = Request('description');
$is_ssl = Request('is_ssl');
$member = Request('member');
$is_default = Request('is_default') ? 'TRUE' : 'FALSE';

$templetConfigs = array();
foreach ($this->IM->getTemplet($this->IM,$templet)->getConfigs() as $key=>$value) {
	$templetConfigs[$key] = Request('templet_configs_'.$key);
}
$templetConfigs = json_encode($templetConfigs,JSON_UNESCAPED_UNICODE);

// @todo uploaded file type checking
if ($this->IM->getTemplet($this->IM,$templet)->isLoaded() == false) $errors['templet'] = $this->getErrorText('NOT_FOUND_TEMPLET',$templet);

/**
 * 사이트 추가시 기본 데이터를 추가 후 수정모드로 이동한다.
 */
if ($oDomain == '' && $oLanguage == '') {
	/**
	 * 사이트 중복체크
	 */
	if ($this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('language',$language)->has() == true) {
		$errors['domain'] = $errors['language'] = $this->getErrorText('DUPLICATED');
	}
	
	if (count($errors) == 0) {
		/**
		 * 동일한 도메인의 사이트가 있는지 확인한다.
		 */
		$checkDomain = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->getOne();
		$insert = array();
		$insert['domain'] = $domain;
		$insert['language'] = $language;
		$insert['title'] = $title;
		$insert['alias'] = $alias;
		$insert['description'] = $description;
		$insert['templet'] = $templet;
		$insert['logo'] = '{"default":-1,"footer":-1}';
		$insert['templet_configs'] = $templetConfigs;
		$insert['sort'] = $checkDomain != null ? $checkDomain->sort : ($this->IM->db()->select($this->IM->getTable('site'),'max(sort) as sort')->getOne()->sort + 1);
		
		/**
		 * 사이트를 등록한다.
		 */
		$this->IM->db()->insert($this->IM->getTable('site'),$insert)->execute();
		
		$oDomain = $domain;
		$oLanguage = $language;
	}
}

/**
 * 사이트 수정
 */
if ($oDomain != $domain) {
	if ($this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->has() == true) {
		$errors['domain'] = $this->getErrorText('DUPLICATED');
	}
} elseif ($oLanguage != $language) {
	if ($this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('language',$language)->has() == true) {
		$errors['language'] = $this->getErrorText('DUPLICATED');
	}
}

if (count($errors) == 0) {
	$site = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$oDomain)->where('language',$oLanguage)->getOne();
	if ($site == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
	} else {
		/**
		 * 같은 도메인에 모두 공통으로 적용되어야 하는 값을 일괄 수정한다.
		 */
		$insert = array();
		$insert['domain'] = $domain;
		$insert['alias'] = $alias;
		$insert['is_ssl'] = $is_ssl;
		
		$this->IM->db()->update($this->IM->getTable('site'),$insert)->where('domain',$oDomain)->execute();
		
		/**
		 * 개별 사이트언어별 적용할 데이터를 적용한다.
		 */
		$insert = array();
		$insert['language'] = $language;
		$insert['title'] = $title;
		$insert['description'] = $description;
		$insert['templet'] = $templet;
		$insert['templet_configs'] = $templetConfigs;
		$insert['is_default'] = $is_default;
		
		$this->IM->db()->update($this->IM->getTable('site'),$insert)->where('domain',$domain)->where('language',$oLanguage)->execute();
		
		/**
		 * 기본 언어셋이 없을 경우 현재 사이트를 기본 언어셋으로 지정한다.
		 */
		if ($this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain)->where('is_default','TRUE')->has() == false) {
			$this->IM->db()->update($this->IM->getTable('site'),array('is_default'=>'TRUE'))->where('domain',$domain)->where('language',$language)->execute();
			$is_default = 'TRUE';
		}
		
		/**
		 * 도메인이나 언어셋이 변경되었다면, 하부 사이트맵의 도메인과 언어셋을 일괄 수정한다.
		 */
		if ($oDomain != $domain || $oLanguage != $language) {
			$this->IM->db()->update($this->IM->getTable('sitemap'),array('domain'=>$domain,'language'=>$language))->where('domain',$oDomain)->where('language',$oLanguage)->execute();
		}
		
		$results->success = true;
	}
} else {
	$results->success = false;
	$results->errors = $errors;
}

/**
 * 오류없이 사이트 기본정보를 수정하였다면, 전체적용 및 파일업로드를 처리한다.
 */
if ($results->success == true) {
	$insert = array();
	if (Request('is_default') != null) $insert['is_default'] = 'FALSE';
	if (Request('templet_all') != null) {
		$insert['templet'] = $templet;
		$insert['templet_configs'] = $templetConfigs;
	}
	if (Request('title_all') != null) $insert['title'] = $title;
	if (Request('description_all') != null) $insert['description'] = $description;
	
	if (count($insert) > 0) $this->IM->db()->update($this->IM->getTable('site'),$insert)->where('domain',$domain)->where('language',$language,'!=')->execute();
	
	$this->setSiteImage($domain,Request('logo_default_all') == 'on' ? '*' : $language,'logo_default');
	$this->setSiteImage($domain,Request('logo_footer_all') == 'on' ? '*' : $language,'logo_footer');
	$this->setSiteImage($domain,Request('favicon_all') == 'on' ? '*' : $language,'favicon');
	$this->setSiteImage($domain,Request('emblem_all') == 'on' ? '*' : $language,'emblem');
	$this->setSiteImage($domain,Request('image_all') == 'on' ? '*' : $language,'image');
}
?>