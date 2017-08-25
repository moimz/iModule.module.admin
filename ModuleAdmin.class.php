<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 사이트관리자를 위한 기능정의 및 통합관리패널을 제공한다.
 * 이 클래스는 모든 관리자페이지 관련 PHP에서 $Admin 변수로 접근할 수 있다.
 * 
 * @file /modules/admin/ModuleAdmin.class.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 */
class ModuleAdmin {
	/**
	 * iModule 및 Module 코어클래스
	 */
	private $IM;
	private $Module;
	
	/**
	 * DB 관련 변수정의
	 *
	 * @private string[] $table DB 테이블 별칭 및 원 테이블명을 정의하기 위한 변수
	 */
	private $table;
	
	/**
	 * 사이트관리자 주소에 의해 정의되는 사이트설정변수
	 * http://$domain/admin/$menu/$view/$tab
	 */
	public $menu = null;
	public $page = null;
	public $tab = null;
	
	/**
	 * 언어셋을 정의한다.
	 * 
	 * @private object $lang 현재 사이트주소에서 설정된 언어셋
	 * @private object $oLang package.json 에 의해 정의된 기본 언어셋
	 */
	private $lang = null;
	private $oLang = null;
	
	/**
	 * iModule.config.php 파일을 통한 사이트관리자 환경설정을 저장한다.
	 */
	private $configs = null;
	
	/**
	 * 현재 페이지에 구성된 메뉴와 페이지를 저장한다.
	 */
	private $menus = null;
	private $pages = null;
	
	/**
	 * class 선언
	 *
	 * @param iModule $IM iModule 코어클래스
	 * @param Module $Module Module 코어클래스
	 * @see /classes/iModule.class.php
	 * @see /classes/Module.class.php
	 */
	function __construct($IM,$Module) {
		global $_CONFIGS, $_ADMINS;
		
		/**
		 * iModule 및 Module 코어 선언
		 */
		$this->IM = $IM;
		$this->Module = $Module;
		
		/**
		 * 모듈에서 사용하는 DB 테이블 별칭 정의
		 * @see 모듈폴더의 package.json 의 databases 참고
		 */
		$this->table = new stdClass();
		$this->table->log = 'admin_log_table';
		
		/**
		 * 사이트관리자 언어셋을 지정한다.
		 * @todo 사이트관리자를 변경할 수 있는 설정필요
		 */
		$this->IM->language = 'ko';
		
		/**
		 * iModule.config.php 파일에 사이트관리자 설정이 있다면 해당설정을 저장한다.
		 */
		$this->configs = isset($_ADMINS) == true ? $_ADMINS : new stdClass();
		$this->configs->title = isset($this->configs->title) == true ? $this->configs->title : 'iModule <small>Administrator</small>';
		
		/**
		 * 브라우져 타이틀 설정
		 */
		$this->IM->setSiteTitle($this->configs->title);
		
		/**
		 * 접속한 사이트주소 및 사이트변수 정의
		 */
		$this->menu = Request('menu') == null ? null : Request('menu');
		$this->page = Request('page') == null ? null : Request('page');
		$this->tab = Request('tab') == null ? null : Request('tab');
	}
	
	/**
	 * 모듈 코어 클래스를 반환한다.
	 * 현재 모듈의 각종 설정값이나 모듈의 package.json 설정값을 모듈 코어 클래스를 통해 확인할 수 있다.
	 *
	 * @return Module $Module
	 */
	function getModule() {
		return $this->Module;
	}
	
	/**
	 * iModule core 의 DB클래스를 반환한다.
	 *
	 * @return DB $DB
	 */
	function db() {
		return $this->IM->db();
	}
	
	/**
	 * 모듈에서 사용중인 DB테이블 별칭을 이용하여 실제 DB테이블 명을 반환한다.
	 *
	 * @param string $table DB테이블 별칭
	 * @return string $table 실제 DB테이블 명
	 */
	function getTable($table) {
		return empty($this->table->$table) == true ? null : $this->table->$table;
	}
	
	/**
	 * 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $api API명
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 */
	function getApi($api) {
		$data = new stdClass();
		$values = new stdClass();
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->Module->getPath().'/api/'.$api.'.php') == true) {
			INCLUDE $this->Module->getPath().'/api/'.$api.'.php';
		}
		
		$this->IM->fireEvent('afterGetApi','admin',$api,$values,$data);
		
		return $data;
	}
	
	/**
	 * 언어셋파일에 정의된 코드를 이용하여 사이트에 설정된 언어별로 텍스트를 반환한다.
	 * 코드에 해당하는 문자열이 없을 경우 1차적으로 package.json 에 정의된 기본언어셋의 텍스트를 반환하고, 기본언어셋 텍스트도 없을 경우에는 코드를 그대로 반환한다.
	 *
	 * @param string $code 언어코드
	 * @param string $replacement 일치하는 언어코드가 없을 경우 반환될 메세지 (기본값 : null, $code 반환)
	 * @return string $language 실제 언어셋 텍스트
	 */
	function getText($code,$replacement=null) {
		if ($this->lang == null) {
			if (is_file($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->IM->language.'.json'));
				if ($this->IM->language != $this->getModule()->getPackage()->language && is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
					$this->oLang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				}
			} elseif (is_file($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json') == true) {
				$this->lang = json_decode(file_get_contents($this->getModule()->getPath().'/languages/'.$this->getModule()->getPackage()->language.'.json'));
				$this->oLang = null;
			}
		}
		
		$returnString = null;
		$temp = explode('/',$code);
		
		$string = $this->lang;
		for ($i=0, $loop=count($temp);$i<$loop;$i++) {
			if (isset($string->{$temp[$i]}) == true) {
				$string = $string->{$temp[$i]};
			} else {
				$string = null;
				break;
			}
		}
		
		if ($string != null) {
			$returnString = $string;
		} elseif ($this->oLang != null) {
			if ($string == null && $this->oLang != null) {
				$string = $this->oLang;
				for ($i=0, $loop=count($temp);$i<$loop;$i++) {
					if (isset($string->{$temp[$i]}) == true) {
						$string = $string->{$temp[$i]};
					} else {
						$string = null;
						break;
					}
				}
			}
			
			if ($string != null) $returnString = $string;
		}
		
		/**
		 * 언어셋 텍스트가 없는경우 iModule 코어에서 불러온다.
		 */
		if ($returnString != null) return $returnString;
		elseif (in_array(reset($temp),array('text','button','action')) == true) return $this->IM->getText($code,$replacement);
		else return $replacement == null ? $code : $replacement;
	}
	
	/**
	 * 상황에 맞게 에러코드를 반환한다.
	 *
	 * @param string $code 에러코드
	 * @param object $value(옵션) 에러와 관련된 데이터
	 * @param boolean $isRawData(옵션) RAW 데이터 반환여부
	 * @return string $message 에러 메세지
	 */
	function getErrorText($code,$value=null,$isRawData=false) {
		$message = $this->getText('error/'.$code,$code);
		if ($message == $code) return $this->IM->getErrorText($code,$value,null,$isRawData);
		
		$description = null;
		switch ($code) {
			default :
				if (is_object($value) == false && $value) $description = $value;
		}
		
		$error = new stdClass();
		$error->message = $message;
		$error->description = $description;
		$error->type = 'BACK';
		
		if ($isRawData === true) return $error;
		else return $this->IM->getErrorText($error);
	}
	
	/**
	 * 사이트 레이아웃 헤더 HTML 코드를 가져온다.
	 *
	 * @return string $headerHTML
	 */
	function getHeader() {
		if (defined('__IM_HEADER_INCLUDED__') == true) return;
		
		/**
		 * 사이트관리자를 위한 메타태그 구성 (검색엔진 봇 차단 및 아이콘 설정)
		 */
		$this->IM->addHeadResource('meta',array('name'=>'robots','content'=>'noindex,nofollow'));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'57x57','href'=>__IM_DIR__.'/images/logo/emblem.png'));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'114x114','href'=>__IM_DIR__.'/images/logo/emblem.png'));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'72x72','href'=>__IM_DIR__.'/images/logo/emblem.png'));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'144x144','href'=>__IM_DIR__.'/images/logo/emblem.png'));
		$this->IM->addHeadResource('link',array('rel'=>'shortcut icon','type'=>'image/x-icon','href'=>__IM_DIR__.'/images/logo/favicon.ico'));
		$this->IM->addHeadResource('link',array('rel'=>'mask-icon','href'=>__IM_DIR__.'/images/logo/maskicon.svg','color'=>'#0578bf'));
		
		/**
		 * 사이트관리자를 위한 자바스크립트를 호출한다.
		 */
		$this->IM->addHeadResource('script',$this->Module->getDir().'/scripts/script.js');
		
		/**
		 * 헤더 PHP 파일에서 iModule 코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		
		ob_start();
		INCLUDE __IM_PATH__.'/includes/header.php';
		$header = ob_get_contents();
		ob_end_clean();
		
		return $header;
	}
	
	/**
	 * 사이트 레이아웃 푸터 HTML 코드를 가져온다.
	 *
	 * @return string $footerHTML
	 */
	function getFooter() {
		if (defined('__IM_FOOTER_INCLUDED__') == true) return;
		
		/**
		 * 푸터 PHP 파일에서 iModule 코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		
		ob_start();
		INCLUDE __IM_PATH__.'/includes/footer.php';
		$footer = ob_get_contents();
		ob_end_clean();
		
		return $footer;
	}
	
	/**
	 * 에러메세지를 반환한다.
	 *
	 * @param string $code 에러코드 (에러코드는 iModule 코어에 의해 해석된다.)
	 * @param object $value 에러코드에 따른 에러값
	 * @return $html 에러메세지 HTML
	 */
	function getError($code,$value=null) {
		/**
		 * iModule 코어를 통해 에러메세지를 구성한다.
		 */
		$error = $this->getErrorText($code,$value,true);
		return $this->IM->getError($error);
	}
	
	/**
	 * 사이트관리자 로그인화면을 구성한다.
	 *
	 * @return string $html 로그인 HTML
	 */
	function getLoginContext() {
		/**
		 * 로그인관련 스타일시트와 언어셋에 따라 웹폰트를 불러온다.
		 */
		$this->IM->addHeadResource('style',$this->Module->getDir().'/styles/login.css');
		$this->IM->loadFont();
		
		/**
		 * 컨텍스트 PHP 파일에서 iModule 코어클래스 및 관리자클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Admin = $this;
		
		ob_start();
		echo PHP_EOL.'<form id="ModuleAdminLoginForm">'.PHP_EOL;
		
		INCLUDE $this->Module->getPath().'/includes/login.php';
		
		echo PHP_EOL.'</form>'.PHP_EOL.'<script>$("#ModuleAdminLoginForm").inits(Admin.login);</script>'.PHP_EOL;
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * 사이트관리자 화면을 구성한다.
	 *
	 * @return string $html 관리자화면 HTML
	 */
	function getAdminContext() {
		/**
		 * 관리자권한이 없는 경우, 로그인 컨텍스트를 반환한다.
		 */
		if ($this->checkPermission() === false) return $this->getLoginContext();
		
		/**
		 * ExtJS 라이브러리와 관리자 언어셋을 불러온다.
		 */
		$this->IM->loadExtJs();
		$this->Module->loadLanguage('admin');
		$this->IM->addHeadResource('script',$this->Module->getDir().'/scripts/jquery.cropit.min.js');
		
		/**
		 * Wysiwyg 에디터를 사용하기 위한 코드를 불러온다.
		 */
		$this->IM->getModule('wysiwyg')->preload();
		
		/**
		 * 관리자화면의 스타일시트와 언어셋에 따라 웹폰트를 불러온다.
		 */
		$this->IM->addHeadResource('style',$this->Module->getDir().'/styles/style.css');
		$this->IM->loadFont();
		
		$this->IM->loadWebFont('FontAwesome');
		$this->IM->loadWebFont('XEIcon');
		$this->IM->loadWebFont('XEIcon2');
		
		/**
		 * 관리자메뉴를 구성한다.
		 */
		$menus = $this->getMenus();
		
		/**
		 * 현재메뉴가 없을 경우, 첫번째 메뉴를 선택한다.
		 */
		$this->menu = $this->menu == null ? $menus[0]->menu : $this->menu;
		
		/**
		 * 현재 메뉴에 대한 2차 메뉴(페이지)를 구성한다.
		 */
		$pages = $this->getPages($this->menu);
		
		/**
		 * 현재 2차메뉴가 없고, 2차메뉴가 있을 경우 첫번째 2차메뉴를 선택한다.
		 */
		$this->page = $this->page == null ? (count($pages) > 0 ? $pages[0]->page : false) : $this->page;
		
		/**
		 * 관리패널을 가져온다.
		 */
		$menuTitle = $this->getPanelTitle($this->menu);
		$pageTitle = $this->getPanelTitle($this->menu,$this->page);
		
		$panel = $this->getPanelContext();
		
		$tab = Request('tab');
		
		/**
		 * 컨텍스트 PHP 파일에서 iModule 코어클래스 및 관리자클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Admin = $this;
		
		ob_start();
		
		INCLUDE $this->Module->getPath().'/includes/index.php';
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	/**
	 * 관리패널을 구성한다.
	 *
	 * @return string $panel 관리패널 스크립트
	 */
	function getPanelContext() {
		$panel = null;
		
		/**
		 * 1차 메뉴가 configs 일 경우
		 */
		if ($this->menu == 'configs') {
			ob_start();
			
			$IM = $this->IM;
			$Admin = $this;
			
			if (is_file($this->Module->getPath().'/panels/'.$this->menu.'.'.$this->page.'.php') == true) {
				INCLUDE_ONCE $this->Module->getPath().'/panels/'.$this->menu.'.'.$this->page.'.php';
			}
			$panel = ob_get_contents();
			ob_end_clean();
		}
		
		/**
		 * 1차 메뉴가 modules 일 경우
		 */
		if ($this->menu == 'modules') {
			/**
			 * 1차 메뉴가 lists 일 경우, 전체 모듈관리 패널을, 그렇지 않은 경우 해당 모듈에서 관리자패널을 가져온다.
			 */
			if ($this->page == 'lists') {
				ob_start();
			
				$IM = $this->IM;
				$Admin = $this;
				
				if (is_file($this->Module->getPath().'/panels/modules.lists.php') == true) {
					INCLUDE $this->Module->getPath().'/panels/modules.lists.php';
				}
				$panel = ob_get_contents();
				ob_end_clean();
			} else {
				$panel = $this->Module->getAdminPanel($this->page);
				
				
				/*
				$this->loadLangaugeJs($page);
			
			if (file_exists(__IM_PATH__.'/modules/'.$page.'/admin/config.php') == true) {
				ob_start();
				INCLUDE_ONCE __IM_PATH__.'/modules/'.$page.'/admin/config.php';
				$config = ob_get_contents();
				ob_end_clean();
			} else {
				$config = null;
			}
			
			$mModule = $this->IM->getModule($page);
			if (method_exists($mModule,'getAdminPanel') == true) {
				$panel = $mModule->getAdminPanel($this);
			} elseif (file_exists(__IM_PATH__.'/modules/'.$page.'/admin/index.php') == true) {
				if (file_exists(__IM_PATH__.'/modules/'.$page.'/admin/scripts/'.$page.'.js') == true) {
					$this->addSiteHeader('script',__IM_DIR__.'/modules/'.$page.'/admin/scripts/'.$page.'.js');
				}
				
				ob_start();
				INCLUDE_ONCE __IM_PATH__.'/modules/'.$page.'/admin/index.php';
				$panel = ob_get_contents();
				ob_end_clean();
				
				if ($config != null) {
					$panel.= PHP_EOL.$config.PHP_EOL.'<script>Admin.module.addConfigPanel("'.$page.'",config);</script>'.PHP_EOL;
				}
			} else {
				$panel = '';
			}*/
			}	
		}
		
		return $panel;
	}
	
	/**
	 * 메뉴 URL 을 구한다.
	 * 모든 파라매터값은 옵션이며 입력되지 않거나, NULL 일 경우 현재 접속한 페이지의 정보를 사용한다.
	 * 즉, 모든 파라매터값이 없는 상태로 호출하면 현재 페이지의 URL 을 구할 수 있다.
	 * 파라매터값을 false 로 설정하면 하위주소를 무시한다. $page 값이 false 일 경우 1차 메뉴주소까지만 반환한다.
	 *
	 * @param string $menu 1차 메뉴
	 * @param string $page 2차 메뉴
	 * @param string $tab 관리패널의 탭 아이디
	 * @return string $url;
	 */
	function getUrl($menu=null,$page=null,$tab=false) {
		$menu = $menu === null ? $this->menu : $menu;
		$page = $page === null && $menu == $this->menu ? $this->page : $page;
		
		$url = __IM_DIR__.'/admin';
		if ($menu === null || $menu === false) return $url;
		$url.= '/'.$menu;
		if ($page === null || $page === false) return $url;
		$url.= '/'.$page;
		if ($tab === null || $tab === false) return $url;
		$url.= '/'.$tab;
		
		return $url;
	}
	
	/**
	 * 사이트관리자 환경설정값을 가져온다.
	 *
	 * @param string $key 환경설정키값
	 * @return object $value 환경설정값
	 */
	function getConfig($key) {
		return isset($this->configs->{$key}) == true ? $this->configs->{$key} : null;
	}
	
	/**
	 * 사이트관리자 메뉴를 가져온다.
	 *
	 * @return object[] $menus
	 */
	function getMenus() {
		/**
		 * 사이트관리자 환경설정에 비활성화 메뉴 및 추가된 메뉴를 가져온다.
		 */
		$disabledMenus = $this->getConfig('disabledMenus') != null ? $this->getConfig('disabledMenus') : array();
		$additionalMenu = $this->getConfig('additionalMenu') != null ? $this->getConfig('additionalMenu') : array();
		
		/**
		 * 현재 권한을 가지고 온다.
		 */
		$permissions = $this->checkPermission();
		
		/**
		 * 기본메뉴를 구성한다. 사이트관리자 환경설정에 따라 비활성화 된 메뉴는 제외한다.
		 * 접근권한이 없는 메뉴도 제외한다.
		 */
		$menus = array();
		
		/**
		 * 대시보드
		 */
		if (in_array('dashboard',$disabledMenus) == false || $permissions === true || in_array('dashboard',$permissions) == true) {
			$menu = new stdClass();
			$menu->menu = 'dashboard';
			$menu->page = false;
			$menu->tab = false;
			$menu->icon = 'fa-dashboard';
			$menu->title = $this->getText('menus/dashboard');
			$menus[] = $menu;
		}
		
		/**
		 * 모듈관리
		 */
		if (in_array('modules',$disabledMenus) == false || $permissions === true || in_array('modules',$permissions) == true) {
			$menu = new stdClass();
			$menu->menu = 'modules';
			$menu->page = false;
			$menu->tab = false;
			$menu->icon = 'fa-cube';
			$menu->title = $this->getText('menus/modules');
			$menus[] = $menu;
		}
		
		/**
		 * 에드온관리
		 */
		if (in_array('addons',$disabledMenus) == false || $permissions === true || in_array('addons',$permissions) == true) {
			$menu = new stdClass();
			$menu->menu = 'addons';
			$menu->page = false;
			$menu->tab = false;
			$menu->icon = 'fa-puzzle-piece';
			$menu->title = $this->getText('menus/addons');
			$menus[] = $menu;
		}
		
		/**
		 * 위젯관리
		 */
		if (in_array('widgets',$disabledMenus) == false || $permissions === true || in_array('widgets',$permissions) == true) {
			$menu = new stdClass();
			$menu->menu = 'widgets';
			$menu->page = false;
			$menu->tab = false;
			$menu->icon = 'fa-sticky-note-o';
			$menu->title = $this->getText('menus/widgets');
			$menus[] = $menu;
		}
		
		/**
		 * 사이트환경설정
		 */
		if (in_array('configs',$disabledMenus) == false || $permissions === true || in_array('configs',$permissions) == true) {
			$menu = new stdClass();
			$menu->menu = 'configs';
			$menu->page = false;
			$menu->tab = false;
			$menu->icon = 'fa-cog';
			$menu->title = $this->getText('menus/configs');
			$menus[] = $menu;
		}
		
		/**
		 * @todo 추가 메뉴 구성
		 */
		
		$this->menus = $menus;
		
		return $menus;
	}
	
	/**
	 * 사이트관리자 2차메뉴(페이지)를 가져온다.
	 *
	 * @param string $menu 1차메뉴명
	 * @return object[] $pages
	 */
	function getPages($menu) {
		/**
		 * 사이트관리자 환경설정에 비활성화 메뉴 및 추가된 메뉴를 가져온다.
		 */
		$disabledPages = $this->getConfig('disabledPages') != null && isset($this->getConfig('disabledPages')[$menu]) == true ? $this->getConfig('disabledPages')[$menu] : array();
		$additionalPage = $this->getConfig('additionalPage') != null && isset($this->getConfig('additionalPage')[$menu]) == true ? $this->getConfig('additionalPage')[$menu] : array();
		
		/**
		 * 현재 권한을 가지고 온다.
		 */
		$permissions = $this->checkPermission() === true || in_array($menu.'/*',$this->checkPermission()) == true;
		
		/**
		 * 1차 메뉴별 2차메뉴를 구성한다. 사이트관리자 환경설정에 따라 비활성화 된 메뉴는 제외한다.
		 * 접근권한이 없는 메뉴도 제외한다.
		 */
		$pages = array();
		
		/**
		 * 1차메뉴가 modules 일 경우 설치되어 있는 모든 모듈 목록을 가져온다.
		 * 단, 모듈중 관리자가 없는 모듈을 제외한다.
		 */
		if ($menu == 'modules') {
			/**
			 * 전체 모듈을 관리할 수 있는 메뉴를 구성한다.
			 */
			$page = new stdClass();
			$page->menu = 'modules';
			$page->page = 'lists';
			$page->tab = false;
			$page->icon = 'fa-cubes';
			$page->title = $this->getText('pages/modules/lists');
			$pages[] = $page;
			
			$modules = $this->IM->Module->getAdminModules();
			for ($i=0, $loop=count($modules);$i<$loop;$i++) {
				if (in_array($modules[$i]->module,$disabledPages) == false || $permissions === true || in_array('modules/'.$modules[$i]->module,$permissions) == true) {
					$page = new stdClass();
					$page->menu = 'modules';
					$page->page = $modules[$i]->module;
					$page->tab = false;
					$page->icon = isset($this->IM->Module->getPackage($modules[$i]->module)->icon) == true ? $this->IM->Module->getPackage($modules[$i]->module)->icon : 'fa-cube';
					$page->title = $this->IM->Module->getTitle($modules[$i]->module);
					$pages[] = $page;
				}
			}
		}
		
		/**
		 * 1차메뉴가 configs 일 경우
		 */
		if ($menu == 'configs') {
			/**
			 * 사이트관리
			 */
			if (in_array('configs/sites',$disabledPages) == false || $permissions === true || in_array('configs/sites',$permissions) == true) {
				$page = new stdClass();
				$page->menu = 'configs';
				$page->page = 'sites';
				$page->tab = false;
				$page->icon = 'fa-home';
				$page->title = $this->getText('pages/configs/sites');
				$pages[] = $page;
			}
			
			/**
			 * 사이트맵관리
			 */
			if (in_array('configs/sitemap',$disabledPages) == false || $permissions === true || in_array('configs/sitemap',$permissions) == true) {
				$page = new stdClass();
				$page->menu = 'configs';
				$page->page = 'sitemap';
				$page->tab = false;
				$page->icon = 'fa-sitemap';
				$page->title = $this->getText('pages/configs/sitemap');
				$pages[] = $page;
			}
		}
		
		$this->pages = $pages;
		
		return $pages;
	}
	
	/**
	 * 관리패널의 제목 및 아이콘을 가져온다.
	 *
	 * @param string $menu 1차메뉴명
	 * @param string $page 2차메뉴명 (2차메뉴명이 없을 경우 1차메뉴명이 반환된다.)
	 * @return object $title
	 */
	function getPanelTitle($menu,$page=false) {
		/**
		 * 2차 메뉴명이 없을 경우 1차메뉴명을 반환한다.
		 */
		if ($page == false) {
			for ($i=0, $loop=count($this->menus);$i<$loop;$i++) {
				if ($this->menus[$i]->menu == $menu) return $this->menus[$i];
			}
		}
		
		/**
		 * 2차 메뉴명이 현재 메뉴명과 다를경우 2차메뉴를 구성한다.
		 */
		$pages = $this->menu != $menu ? $this->getPages($menu) : $this->pages;
		for ($i=0, $loop=count($pages);$i<$loop;$i++) {
			if ($pages[$i]->page == $page) return $pages[$i];
		}
		
		return null;
	}
	
	/**
	 * 관리권한이 있는 관리패널을 가져온다.
	 * 모든 관리패널에 대한 권한이 있을경우 true 를 반환하고 모든 권한이 없을 경우 false 를 반환한다.
	 * 이 함수로 권한체크를 하기 위해서는 반드시 === 연산자를 사용하여 권한을 체크할 것!
	 *
	 * @return string[] $permissions 관리권한이 있는 패널
	 * @todo 현재는 사이트관리자에게 모든 권한을 부여하고 개개인에게 맞춤 권한을 제공하지는 않는다.
	 */
	function checkPermission() {
		if ($this->IM->getModule('member')->isAdmin() == true) return true;
		
		return false;
	}
	
	/**
	 * 위지윅 필드를 정리한다.
	 *
	 * @param string $field 위지윅필드명
	 * @param string $module 모듈명
	 * @param string $target 타겟명
	 * @return int[] $files 정리된 파일고유번호
	 */
	function getWysiwygContent($field,$module,$target,$origin=null) {
		$mAttachment = $this->IM->getModule('attachment');
		
		$text = Request($field) ? Request($field) : '';
		$files = Request($field.'_files') ? explode(',',Request($field.'_files')) : array();
		
		if ($origin != null && is_object($origin) == true) {
			$content = $origin;
		} else {
			$content = new stdClass();
		}
		
		$content->text = $this->IM->getModule('wysiwyg')->encodeContent($text,$files);
		$content->files = $files;
		
		for ($i=0, $loop=count($content->files);$i<$loop;$i++) {
			$mAttachment->filePublish($content->files[$i],$module,$target);
		}
		
		return $content;
	}
	
	function doLayout() {
		global $_CONFIGS;
		
		/**
		 * iModule 이 설치가 되지 않은 경우 레이아웃 출력을 중단하고 설치 페이지로 이동한다.
		 */
		if ($_CONFIGS->installed === false) {
			header('location:'.__IM_DIR__.'/install');
			exit;
		}
		
		/**
		 * 사이트관리자 권한이 없을 경우 로그인 컨텍스트를, 로그인이 되어있다면 사이트 관리자 컨텍스트를 가져온다.
		 */
		if ($this->checkPermission() === false) {
			$body = $this->getLoginContext();
		} else {
			$body = $this->getAdminContext();
		}
		
		/**
		 * 사이트 푸터에서 스타일시트나, 자바스크립트 파일을 추가할 수 있으므로, 사이트푸터부터 생성하여 가져온다.
		 */
		$footer = $this->getFooter();
		
		/**
		 * 사이트 헤더를 가져온다.
		 */
		$header = $this->getHeader();
		
		/**
		 * 사이트 레이아웃 HTML 을 만든다.
		 */
		$html = $header.PHP_EOL.$body.PHP_EOL.$footer;
		
		/**
		 * 사이트 로딩타임을 출력한다.
		 */
		$html.= PHP_EOL.'<!-- Load Time : '.$this->IM->getLoadTime().' -->';
		
		/**
		 * 전체 사이트 HTML 을 생성한 뒤 afterDoLayout 이벤트를 발생시킨다.
		 * 전체 사이트 HTML 코드인 $html 변수는 pass by object 로 전달되기 때문에 이벤트리스너에서 조작할 경우 최종출력되는 HTML 코드가 변경된다.
		 */
		$this->IM->fireEvent('afterDoLayout','admin','*',null,null,$html);
		
		/**
		 * 사이트 HTML 코드를 출력한다.
		 */
		echo $html;
	}
	
	/**
	 * 현재 모듈에서 처리해야하는 요청이 들어왔을 경우 처리하여 결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 * 작업코드가 '@' 로 시작할 경우 사이트관리자를 위한 작업으로 최고관리자 권한이 필요하다.
	 *
	 * @param string $action 작업코드
	 * @return object $results 수행결과
	 * @see /process/index.php
	 */
	function doProcess($action) {
		$values = new stdClass();
		$results = new stdClass();
		
		/**
		 * 모듈의 process 폴더에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->Module->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->Module->getPath().'/process/'.$action.'.php';
		}
		
		$this->IM->fireEvent('afterDoProcess','admin',$action,$values,$results);
		
		return $results;
	}
	
	/**
	 * 사이트관련 이미지파일을 업로드하고 저장한다.
	 *
	 * @param string $domain 사이트도메인
	 * @param string $language 사이트언어셋
	 * @param string $type 사이트이미지 타입
	 * @param string $is_reset 초기화할 이미지타입명
	 * @param string $is_default 전체 사이트에 적용할지 여부
	 * @param File $file 업로드되는 파일객체
	 * @param string $maskicon_color Maks아이콘의 경우 색깔코드
	 */
	function setSiteImage($domain,$language,$type,$is_reset=null,$is_default=null,$file=null,$maskicon_color=null) {
		$is_reset = $is_reset == null ? Request($type.'_reset') == 'on' : $is_reset;
		$is_default = $is_default == null ? Request($type.'_default') == 'on' : $is_default;
		$file = $file == null && isset($_FILES[$type.'_file']['tmp_name']) == true && is_file($_FILES[$type.'_file']['tmp_name']) == true ? $_FILES[$type.'_file']['tmp_name'] : $file;
		if ($file != null && is_file($file) == false) $file = null;
		$maskicon_color = $maskicon_color == null ? Request('maskicon_color') : $maskicon_color;
		
		$extension = array('image/png'=>'png','image/svg+xml'=>'svg','image/x-icon'=>'ico','image/jpeg'=>'jpg','image/gif'=>'gif');
		if ($file !== null) {
			$mime = $this->IM->getModule('attachment')->getFileMime($file);
			if (isset($extension[$mime]) == true) $extension = $extension[$mime];
			else $extension = null;
			
			if ($extension == null) $file = null;
		}
		
		$sites = $this->IM->db()->select($this->IM->getTable('site'))->where('domain',$domain);
		if ($language != '*') $sites->where('language',$language);
		$sites = $sites->get();
		for ($i=0, $loop=count($sites);$i<$loop;$i++) {
			if (preg_match('/^logo_(.*?)$/',$type,$match) == true) {
				$logoType = $match[1];
				$logo = $sites[$i]->logo && json_decode($sites[$i]->logo) != null ? json_decode($sites[$i]->logo) : new stdClass();
				
				if ($is_reset == true) {
					if (isset($logo->$logoType) == true && $logo->$logoType > 0) {
						$this->IM->getModule('attachment')->fileDelete($logo->$logoType);
					}
					
					$logo->$logoType = 0;
					$this->IM->db()->update($this->IM->getTable('site'),array('logo'=>json_encode($logo,JSON_NUMERIC_CHECK)))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
				} elseif ($is_default == true) {
					if (isset($logo->$logoType) == true && $logo->$logoType > 0) {
						$this->IM->getModule('attachment')->fileDelete($logo->$logoType);
					}
					
					$logo->$logoType = -1;
					$this->IM->db()->update($this->IM->getTable('site'),array('logo'=>json_encode($logo,JSON_NUMERIC_CHECK)))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
				} elseif ($file != null) {
					if (isset($logo->$logoType) == true && $logo->$logoType > 0) {
						$this->IM->getModule('attachment')->fileReplace($logo->$logoType,$logoType.'.'.$extension,$file,false);
					} else {
						$logoIdx = $this->IM->getModule('attachment')->fileSave($logoType.'.'.$extension,$file,'site','logo','PUBLISHED',false);
						$logo->$logoType = $logoIdx;
						
						$this->IM->db()->update($this->IM->getTable('site'),array('logo'=>json_encode($logo,JSON_NUMERIC_CHECK)))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
					}
				}
			} elseif ($type == 'maskicon') {
				$maskicon = $sites[$i]->maskicon && json_decode($sites[$i]->maskicon) != null ? json_decode($sites[$i]->maskicon) : new stdClass();
				$maskicon->color = $maskicon_color;
				
				if ($is_reset == true) {
					if (isset($maskicon->icon) == true && $maskicon->icon > 0) {
						$this->IM->getModule('attachment')->fileDelete($maskicon->icon);
					}
					
					$maskicon->icon = 0;
				} elseif ($is_default == true) {
					if (isset($maskicon->icon) == true && $maskicon->icon > 0) {
						$this->IM->getModule('attachment')->fileDelete($maskicon->icon);
					}
					
					$maskicon->icon = -1;
				} elseif ($file != null) {
					if (isset($maskicon->icon) == true && $maskicon->icon > 0) {
						$this->IM->getModule('attachment')->fileReplace($maskicon->icon,$type.'.'.$extension,$file,false);
					} else {
						$fileIdx = $this->IM->getModule('attachment')->fileSave('maskicon.'.$extension,$file,'site','maskicon','PUBLISHED',false);
						$maskicon->icon = $fileIdx;
					}
				}
				
				$this->IM->db()->update($this->IM->getTable('site'),array('maskicon'=>json_encode($maskicon,JSON_NUMERIC_CHECK)))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
			} elseif (in_array($type,array('emblem','favicon','image')) == true) {
				$oFile = $sites[$i]->$type;
				if ($is_reset == true) {
					if ($oFile > 0) {
						$this->IM->getModule('attachment')->fileDelete($oFile);
					}
					
					$this->IM->db()->update($this->IM->getTable('site'),array($type=>0))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
				} elseif ($is_default == true) {
					if ($oFile > 0) {
						$this->IM->getModule('attachment')->fileDelete($oFile);
					}
					
					$this->IM->db()->update($this->IM->getTable('site'),array($type=>$oFile))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
				} elseif ($file != null) {
					if ($oFile > 0) {
						$this->IM->getModule('attachment')->fileReplace($oFile,$type.'.'.$extension,$file,false);
					} else {
						$fileIdx = $this->IM->getModule('attachment')->fileSave($type.'.'.$extension,$file,'site',$type,'PUBLISHED',false);
						
						$this->IM->db()->update($this->IM->getTable('site'),array($type=>$fileIdx))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
					}
				}
			}
		}
	}
}
?>