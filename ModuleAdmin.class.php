<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트관리자를 위한 기능정의 및 통합관리패널을 제공한다.
 * 이 클래스는 모든 관리자페이지 관련 PHP에서 $Admin 변수로 접근할 수 있다.
 * 
 * @file /modules/admin/ModuleAdmin.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 12. 15.
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
	 * http://$domain/admin/$menu/$page/$tab/$view
	 */
	public $menu = null;
	public $page = null;
	public $tab = null;
	public $view = null;
	
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
		$this->table->page_log = 'admin_page_log_table';
		$this->table->process_log = 'admin_process_log_table';
		
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
		 * 접속한 사이트주소 및 사이트변수 정의
		 */
		$this->domain = isset($_SERVER['HTTP_HOST']) == true ? strtolower($_SERVER['HTTP_HOST']) : '';
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
	 * 플러그인 코어 클래스를 반환한다.
	 *
	 * @return Plugin $Plugin
	 */
	function getPlugin() {
		return $this->IM->getPlugin();
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
	 * [코어] 사이트 외부에서 현재 모듈의 API를 호출하였을 경우, API 요청을 처리하기 위한 함수로 API 실행결과를 반환한다.
	 * 소스코드 관리를 편하게 하기 위해 각 요쳥별로 별도의 PHP 파일로 관리한다.
	 *
	 * @param string $protocol API 호출 프로토콜 (get, post, put, delete)
	 * @param string $api API명
	 * @param any $idx API 호출대상 고유값
	 * @param object $params API 호출시 전달된 파라메터
	 * @return object $datas API처리후 반환 데이터 (해당 데이터는 /api/index.php 를 통해 API호출자에게 전달된다.)
	 * @see /api/index.php
	 */
	function getApi($protocol,$api,$idx=null,$params=null) {
		$data = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeGetApi',$this->getModule()->getName(),$api,$values);
		
		/**
		 * 모듈의 api 폴더에 $api 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/api/'.$api.'.'.$protocol.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/api/'.$api.'.'.$protocol.'.php';
		}
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterGetApi',$this->getModule()->getName(),$api,$values,$data);
		
		return $data;
	}
	
	/**
	 * [사이트관리자] 모듈 설정패널을 구성한다.
	 *
	 * @return string $panel 설정패널 HTML
	 */
	function getConfigPanel() {
		/**
		 * 설정패널 PHP에서 iModule 코어클래스와 모듈코어클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Module = $this->getModule();
		
		ob_start();
		INCLUDE $this->getModule()->getPath().'/admin/configs.php';
		$panel = ob_get_contents();
		ob_end_clean();
		
		return $panel;
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
		
		$this->IM->fireEvent('afterGetText',$this->getModule()->getName(),$code,$returnString);
		
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
		
		$this->IM->getSite();
		
		/**
		 * 사이트관리자를 위한 메타태그 구성 (검색엔진 봇 차단 및 아이콘 설정)
		 */
		$this->IM->addHeadResource('meta',array('name'=>'robots','content'=>'noindex,nofollow'));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'57x57','href'=>$this->IM->getSiteEmblem()));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'114x114','href'=>$this->IM->getSiteEmblem()));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'72x72','href'=>$this->IM->getSiteEmblem()));
		$this->IM->addHeadResource('link',array('rel'=>'apple-touch-icon','sizes'=>'144x144','href'=>$this->IM->getSiteEmblem()));
		$this->IM->addHeadResource('link',array('rel'=>'shortcut icon','type'=>'image/x-icon','href'=>$this->IM->getSiteFavicon()));
		
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
		
		/**
		 * 이벤트를 발생시킨다.
		 */
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterGetContext',$this->getModule()->getName(),'login',$values,$html);
		
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
		
		$this->IM->addHeadResource('meta',array('name'=>'viewport','content'=>'width=1200'));
		
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
		$this->IM->loadWebFont('Roboto');
		
		/**
		 * 관리자메뉴를 구성한다.
		 */
		$menus = $this->getMenus();
		
		/**
		 * 현재메뉴가 없을 경우, 첫번째 메뉴를 선택한다.
		 */
		if ($this->menu == null) {
			$this->menu = $menus[0]->menu;
			if ($menus[0]->page !== false) $this->page = $menus[0]->page;
			header("location:".__IM_DIR__.'/admin/'.$this->menu.($this->page ? '/'.$this->page : ''));
			exit;
		}
		
		$permissions = $this->checkPermission();
		if ($permissions !== true && ($this->menu != 'modules' || in_array($this->page,$permissions) == false)) return $this->IM->printError('FORBIDDEN');
		
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
		$view = Request('view');
		
		/**
		 * 컨텍스트 PHP 파일에서 iModule 코어클래스 및 관리자클래스에 접근하기 위한 변수 선언
		 */
		$IM = $this->IM;
		$Admin = $this;
		$mPush = $this->IM->getModule('push');
		
		ob_start();
		
		INCLUDE $this->Module->getPath().'/includes/index.php';
		$html = ob_get_contents();
		ob_end_clean();
		
		/**
		 * 이벤트를 발생시킨다.
		 */
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterGetContext',$this->getModule()->getName(),'index',$values,$html);
		
		$this->saveWebLog();
		
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
			$panel = ob_get_clean();
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
				$panel = ob_get_clean();
			} else {
				$panel = $this->Module->getAdminPanel($this->page);
			}
		}
		
		/**
		 * 1차 메뉴가 plugins 일 경우
		 */
		if ($this->menu == 'plugins') {
			/**
			 * 1차 메뉴가 lists 일 경우, 전체 플러그인관리 패널을, 그렇지 않은 경우 해당 플러그인에서 관리자패널을 가져온다.
			 */
			if ($this->page == 'lists') {
				ob_start();
			
				$IM = $this->IM;
				$Admin = $this;
				
				if (is_file($this->Module->getPath().'/panels/plugins.lists.php') == true) {
					INCLUDE $this->Module->getPath().'/panels/plugins.lists.php';
				}
				$panel = ob_get_clean();
			} else {
//				$panel = $this->Module->getAdminPanel($this->page);
			}
		}
		
		/**
		 * 1차 메뉴가 log 일 경우
		 */
		if ($this->menu == 'log') {
			ob_start();
		
			$IM = $this->IM;
			$Admin = $this;
			
			if (is_file($this->Module->getPath().'/panels/'.$this->menu.'.'.$this->page.'.php') == true) {
				INCLUDE_ONCE $this->Module->getPath().'/panels/'.$this->menu.'.'.$this->page.'.php';
			}
			$panel = ob_get_clean();
		}
		
		/**
		 * 1차 메뉴가 database 일 경우
		 */
		if ($this->menu == 'database') {
			/**
			 * 1차 메뉴(테이블명)이 없을 경우, 전체 테이블 목록패널을 가져온다.
			 */
			if ($this->page == null) {
				ob_start();
			
				$IM = $this->IM;
				$Admin = $this;
				
				if (is_file($this->Module->getPath().'/panels/database.php') == true) {
					INCLUDE $this->Module->getPath().'/panels/database.php';
				}
				$panel = ob_get_clean();
			} else {
				ob_start();
			
				$IM = $this->IM;
				$Admin = $this;
				
				if ($this->db()->exists($this->page,true) == true && is_file($this->Module->getPath().'/panels/database.table.php') == true) {
					INCLUDE $this->Module->getPath().'/panels/database.table.php';
				}
				$panel = ob_get_clean();
			}
		}
		
		return $panel;
	}
	
	/**
	 * HTML 컨텍스트 에디터를 구성한다.
	 *
	 * @param string $domain 도메인
	 * @param string $language 언어셋
	 * @param string $menu 메뉴
	 * @param string $page 페이지
	 * @param object $context 컨텍스트 설정
	 * @return string $editor
	 */
	function getHtmlEditorContext($domain,$language,$menu,$page,$context) {
		if ($this->IM->getModule('member')->isLogged() == false) return $this->getError('REQUIRED_LOGIN');
		if ($this->IM->getModule('member')->isAdmin() == false) return $this->getError('FORBIDDEN');
		
		$html = $context != null && isset($context->html) == true ? $context->html : '';
		$css = $context != null && isset($context->css) == true ? $context->css : '';
		
		$this->IM->addHeadResource('style',$this->getModule()->getDir().'/styles/html.css');
		$this->IM->addHeadResource('script',$this->getModule()->getDir().'/scripts/html.js');
		$this->IM->getModule('wysiwyg')->addCodeMirrorMode('css')->preload();
		$this->IM->addHeadResource('script',$this->IM->getModule('wysiwyg')->getModule()->getDir().'/scripts/codemirror/addon/edit/closetag.js');
		
		$uploader = $this->IM->getModule('attachment')->setTemplet('default')->setModule('admin')->setWysiwyg('wysiwyg')->setLoader($this->IM->getProcessUrl('admin','@getHtmlEditorFiles',array('context'=>Encoder(json_encode(array('domain'=>$domain,'language'=>$language,'menu'=>$menu,'page'=>$page))))))->get();
		$wysiwyg = $this->IM->getModule('wysiwyg')->setId('ModuleAdminHtmlEditor')->setModule('admin')->setName('wysiwyg')->setContent($html)->get(true);
		
		ob_start();
		
		echo PHP_EOL.'<form id="ModuleAdminHtmlEditorForm">'.PHP_EOL;
		echo '<input type="hidden" name="domain" value="'.$domain.'">'.PHP_EOL;
		echo '<input type="hidden" name="language" value="'.$language.'">'.PHP_EOL;
		echo '<input type="hidden" name="menu" value="'.$menu.'">'.PHP_EOL;
		if ($page != null) echo '<input type="hidden" name="page" value="'.$page.'">'.PHP_EOL;
		
		echo '<style data-role="style">'.$css.'</style>';
		
		$IM = $this->IM;
		INCLUDE $this->getModule()->getPath().'/includes/html.php';
		
		echo PHP_EOL.'</form>'.PHP_EOL.'<script>$(document).ready(function() { HtmlEditor.init(); });</script>'.PHP_EOL;
		
		$context = ob_get_clean();
		
		return $context;
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
		
		$menus = array();
		
		/**
		 * 전체 관리자의 경우 관리자 메뉴를 구성한다. 사이트관리자 환경설정에 따라 비활성화 된 메뉴는 제외한다.
		 */
		if ($permissions === true) {
			$allowed = $permissions === true ? array() : array();
			
			/**
			 * 대시보드
			 */
			if (in_array('dashboard',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'dashboard';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-presentation';
				$menu->title = $this->getText('menus/dashboard');
				$menus[] = $menu;
			}
			
			/**
			 * 모듈관리
			 */
			if (in_array('modules',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'modules';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-box';
				$menu->title = $this->getText('menus/modules');
				$menus[] = $menu;
			}
			
			/**
			 * 플러그인관리
			 */
			if (in_array('plugins',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'plugins';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-plug';
				$menu->title = $this->getText('menus/plugins');
				$menus[] = $menu;
			}
			
			/**
			 * 위젯관리
			 */
			if (in_array('widgets',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'widgets';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-stack-paper';
				$menu->title = $this->getText('menus/widgets');
				$menus[] = $menu;
			}
			
			/**
			 * 사이트환경설정
			 */
			if (in_array('configs',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'configs';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-cog';
				$menu->title = $this->getText('menus/configs');
				$menus[] = $menu;
			}
			
			/**
			 * 로그
			 */
			if (in_array('log',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'log';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-time-back';
				$menu->title = $this->getText('menus/log');
				$menus[] = $menu;
			}
			
			/**
			 * 데이터베이스
			 */
			if (in_array('database',$disabledMenus) == false) {
				$menu = new stdClass();
				$menu->menu = 'database';
				$menu->page = false;
				$menu->tab = false;
				$menu->icon = 'xi-db-full';
				$menu->title = $this->getText('menus/database');
				$menus[] = $menu;
			}
			
			/**
			 * @todo 추가 메뉴 구성
			 */
		} else {
			/**
			 * 관리권한이 있는 모듈로 관리자 메뉴를 구성한다.
			 */
			foreach ($permissions as $module) {
				$mModule = $this->IM->getModule($module);
				
				$menu = new stdClass();
				$menu->menu = 'modules';
				$menu->page = $module;
				$menu->tab = false;
				$menu->icon = $mModule->getModule()->getPackage()->icon;
				$menu->title = $mModule->getModule()->getTitle();
				$menus[] = $menu;
			}
		}
		
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
		$permissions = $this->checkPermission();
		
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
			 * 사이트 관리자의 경우 전체 모듈을 관리할 수 있는 메뉴를 구성한다.
			 */
			$page = new stdClass();
			$page->menu = 'modules';
			$page->page = 'lists';
			$page->tab = false;
			$page->icon = 'fa-cubes';
			$page->title = $this->getText('pages/modules/lists');
			$pages[] = $page;
			
			$modules = $this->IM->getModule()->getAdminModules();
			for ($i=0, $loop=count($modules);$i<$loop;$i++) {
				if (in_array($modules[$i]->module,$disabledPages) == false || $permissions === true || in_array('modules/'.$modules[$i]->module,$permissions) == true) {
					$page = new stdClass();
					$page->menu = 'modules';
					$page->page = $modules[$i]->module;
					$page->tab = false;
					$page->icon = isset($this->IM->getModule()->getPackage($modules[$i]->module)->icon) == true ? $this->IM->getModule()->getPackage($modules[$i]->module)->icon : 'fa-cube';
					$page->title = $this->IM->getModule()->getTitle($modules[$i]->module);
					$pages[] = $page;
				}
			}
		}
		
		/**
		 * 1차메뉴가 plugins 일 경우 설치되어 있는 모든 플러그인 목록을 가져온다.
		 * 단, 플러그인중 관리자가 없는 모듈을 제외한다.
		 */
		if ($menu == 'plugins') {
			/**
			 * 사이트 관리자의 경우 전체 모듈을 관리할 수 있는 메뉴를 구성한다.
			 */
			$page = new stdClass();
			$page->menu = 'plugins';
			$page->page = 'lists';
			$page->tab = false;
			$page->icon = 'xi-plug';
			$page->title = $this->getText('pages/plugins/lists');
			$pages[] = $page;
			
			$plugins = $this->IM->getPlugin()->getAdminPlugins();
			for ($i=0, $loop=count($plugins);$i<$loop;$i++) {
				if (in_array($plugins[$i]->plugin,$disabledPages) == false || $permissions === true || in_array('plugins/'.$plugins[$i]->plugin,$permissions) == true) {
					$page = new stdClass();
					$page->menu = 'plugins';
					$page->page = $plugins[$i]->plugin;
					$page->tab = false;
					$page->icon = isset($this->IM->getPlugin()->getPackage($plugins[$i]->plugin)->icon) == true ? $this->IM->getPlugin()->getPackage($plugins[$i]->plugin)->icon : 'fa-cube';
					$page->title = $this->IM->getPlugin()->getTitle($plugins[$i]->plugin);
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
		
		/**
		 * 1차메뉴가 log 일 경우
		 */
		if ($menu == 'log') {
			$page = new stdClass();
			$page->menu = 'log';
			$page->page = 'admin';
			$page->tab = false;
			$page->icon = 'xi-crown';
			$page->title = $this->getText('pages/log/admin');
			$pages[] = $page;
			
			$page = new stdClass();
			$page->menu = 'log';
			$page->page = 'process';
			$page->tab = false;
			$page->icon = 'xi-cog';
			$page->title = $this->getText('pages/log/process');
			$pages[] = $page;
			
			$page = new stdClass();
			$page->menu = 'log';
			$page->page = 'member';
			$page->tab = false;
			$page->icon = 'xi-user';
			$page->title = $this->getText('pages/log/member');
			$pages[] = $page;
		}
		
		/**
		 * 1차메뉴가 database 일 경우
		 */
		if ($menu == 'database') {
			$page = new stdClass();
			$page->menu = 'database';
			$page->page = false;
			$page->tab = false;
			$page->icon = 'xi-paper';
			$page->title = $this->getText('pages/database/all');
			$pages[] = $page;
			
			$tables = $this->db()->tables();
			foreach ($tables as $table) {
				$page = new stdClass();
				$page->menu = 'database';
				$page->page = $table;
				$page->tab = false;
				$page->icon = 'xi-archive';
				$page->title = $table;
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
		
		/**
		 * 모듈별 관리자 권한을 가지고 있는 모듈을 검색한다.
		 */
		$modules = $this->IM->getModule()->getAdminModules();
		$permissions = array();
		foreach ($modules as $module) {
			$mModule = $this->IM->getModule($module->module);
			if (method_exists($mModule,'isAdmin') == true && $mModule->isAdmin() !== false) {
				$permissions[] = $module->module;
			}
		}
		
		return count($permissions) == 0 ? false : $permissions;
	}
	
	/**
	 * 관리자 process 처리권한이 있는지 확인한다.
	 *
	 * @param string $module 모듈명
	 * @return boolean $hasPermission
	 */
	function checkProcessPermission($module,$action=null) {
		$permission = false;
		$this->IM->fireEvent('checkProcessPermission','admin',$module,$action,$permission);
		return $permission;
	}
	
	/**
	 * 위지윅 필드를 정리한다.
	 *
	 * @param string $field 위지윅필드명
	 * @param string $module 모듈명
	 * @param string $target 타겟명
	 * @return int[] $files 정리된 파일고유번호
	 */
	function getWysiwygContent($field,$module,$target) {
		$mAttachment = $this->IM->getModule('attachment');
		
		$text = Request($field) ? Request($field) : '';
		$files = Request($field.'_files') ? explode(',',Request($field.'_files')) : array();
		$delete_files = Request($field.'_delete_files') ? explode(',',Request($field.'_delete_files')) : array();
		
		$content = new stdClass();
		$content->files = array();
		
		for ($i=0, $loop=count($delete_files);$i<$loop;$i++) {
			$mAttachment->fileDelete($delete_files[$i]);
		}
		
		for ($i=0, $loop=count($files);$i<$loop;$i++) {
			if ($mAttachment->getFileInfo($files[$i]) != null) {
				$mAttachment->filePublish($files[$i],$module,$target);
				$content->files[] = $files[$i];
			}
		}
		
		$content->text = $this->IM->getModule('wysiwyg')->encodeContent($text,$content->files);
		
		return $content;
	}
	
	/**
	 * 다른 모듈의 관리자 스크립트를 불러온다.
	 *
	 * @param string $module 모듈명
	 */
	function loadModule($module) {
		if ($this->IM->getModule()->isInstalled($module) == true) {
			$this->IM->addHeadResource('script',$this->IM->getModule($module)->getModule()->getDir().'/admin/scripts/script.js');
			$this->IM->loadLanguage('module',$module,$this->IM->language);
		}
	}
	
	/**
	 * 아이피 제한을 확인한다.
	 *
	 * @param string $target 제한대상
	 * @return boolean $allowed
	 */
	function checkIp($target) {
		if ($target == 'admin') {
			if ($this->getModule()->getConfig('enable_security_mode') !== true) return true;
			if (Request('iModuleAdminAccess','session') === 'TRUE') return true;
			$ips = array_filter(explode("\n",$this->getModule()->getConfig('allow_ip')));
		}
		
		if ($target == 'database') {
			if ($this->getModule()->getConfig('enable_security_database_mode') !== true) return true;
			$ips = array_filter(explode("\n",$this->getModule()->getConfig('allow_database_ip')));
		}
		
		foreach ($ips as $ip) {
			$ip = trim($ip);
			if (strlen($ip) == 0) continue;
			
			$reg_ip = '/^'.$ip.'$/';
			$reg_ip = str_replace('.','\.',$reg_ip);
			$reg_ip = str_replace('*','[0-9]{1,3}',$reg_ip);
			
			if (preg_match($reg_ip,$_SERVER['REMOTE_ADDR']) == true) return true;
		}
		
		return false;
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
		 * 브라우져 타이틀 설정
		 */
		$this->IM->setSiteTitle($this->configs->title);
		
		/**
		 * IP접근제한이 활성화되어 있을 경우
		 */
		if ($this->getModule()->getConfig('enable_security_mode') === true) {
			if (Request('menu') === $this->getModule()->getConfig('emergency_code')) {
				$_SESSION['iModuleAdminAccess'] = 'TRUE';
				header('location:'.__IM_DIR__.'/admin');
				exit;
			}
		}
		
		if ($this->checkIp('admin') !== true) {
			$this->getError('ACCESS_DENIED');
			exit;
		}
		
		/**
		 * 현재 접속한 도메인에 해당하는 사이트가 없을 경우, 유사한 사이트를 찾아 페이지를 이동한다.
		 */
		if ($this->db()->select($this->IM->getTable('site'))->where('domain',$this->domain)->has() == false) {
			$sites = $this->db()->select($this->IM->getTable('site'))->orderBy('sort','asc')->get();
			$isAlias = false;
			for ($i=0, $loop=count($sites);$i<$loop;$i++) {
				if ($sites[$i]->alias == '') continue;
				
				/**
				 * 현재 접속한 도메인을 alias 로 가지고 있는 사이트를 탐색한다.
				 */
				$domains = explode(',',$sites[$i]->alias);
				for ($j=0, $loopj=count($domains);$j<$loopj;$j++) {
					if ($domains[$j] == $this->domain) {
						$this->domain = $sites[$i]->domain;
						$isAlias = true;
						break;
					}
					
					if (preg_match('/\*\./',$domains[$j]) == true) {
						$aliasToken = explode('.',$domains[$j]);
						$domainToken = explode('.',$this->domain);
						$isMatch = true;
						while (count($aliasToken) > 0) {
							$token = array_pop($aliasToken);
							if ($token != '*' && $token != array_pop($domainToken)) {
								$isMatch = false;
							}
						}
						
						if ($isMatch == true) {
							$this->domain = $sites[$i]->domain;
							$isAlias = true;
							break;
						}
					}
				}
			}
			
			/**
			 * 전체 사이트 정보를 참고해도 현재 접속한 도메인의 사이트를 찾을 수 없을 경우 에러메세지를 출력한다.
			 */
			if ($isAlias == false) {
				$this->IM->printError('SITE_NOT_FOUND');
			}
		}
		
		$site = $this->db()->select($this->IM->getTable('site'))->where('domain',$this->domain)->where('is_default','TRUE')->getOne();
		
		/**
		 * 사이트유효성 검사에 따라 확인된 URL로 이동한다.
		 */
		if (($site->is_https == 'TRUE' && IsHttps() == false) || $_SERVER['HTTP_HOST'] != $site->domain) {
			$redirectUrl = ($site->is_https == 'TRUE' ? 'https://' : 'http://').$site->domain.__IM_DIR__;
			if (isset($_SERVER['REDIRECT_URL']) == true) $redirectUrl.= $_SERVER['REDIRECT_URL'];
			else $redirectUrl.= '/admin/';
			
			header("HTTP/1.1 301 Moved Permanently");
			header("location:".$redirectUrl);
			exit;
		}
		
		$this->IM->fireEvent('beforeDoLayout','admin','*');
		
		/**
		 * 사이트관리자 권한이 없을 경우 로그인 컨텍스트를, 로그인이 되어있다면 사이트 관리자 컨텍스트를 가져온다.
		 */
		if ($this->IM->getModule('member')->isLogged() === false || $this->checkPermission() === false) {
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
		$values = new stdClass();
		$values->header = $header;
		$values->footer = $footer;
		$this->IM->fireEvent('afterDoLayout','admin','*',$values,$html);
		
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
		$results = new stdClass();
		
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('beforeDoProcess',$this->getModule()->getName(),$action,$values);
		
		/**
		 * 모듈의 process 폴더에 $action 에 해당하는 파일이 있을 경우 불러온다.
		 */
		if (is_file($this->getModule()->getPath().'/process/'.$action.'.php') == true) {
			INCLUDE $this->getModule()->getPath().'/process/'.$action.'.php';
		}
		
		unset($values);
		$values = (object)get_defined_vars();
		$this->IM->fireEvent('afterDoProcess',$this->getModule()->getName(),$action,$values,$results);
		
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
	 */
	function setSiteImage($domain,$language,$type,$is_reset=null,$is_default=null,$file=null) {
		$is_reset = $is_reset == null ? Request($type.'_reset') == 'on' : $is_reset;
		$is_default = $is_default == null ? Request($type.'_default') == 'on' : $is_default;
		$file = $file == null && isset($_FILES[$type.'_file']['tmp_name']) == true && is_file($_FILES[$type.'_file']['tmp_name']) == true ? $_FILES[$type.'_file']['tmp_name'] : $file;
		if ($file != null && is_file($file) == false) $file = null;
		
		$extension = array('image/png'=>'png','image/x-icon'=>'ico','image/jpeg'=>'jpg','image/gif'=>'gif');
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
						$this->IM->getModule('attachment')->filePublish($logo->$logoType,'site','logo');
					} else {
						$logoIdx = $this->IM->getModule('attachment')->fileSave($logoType.'.'.$extension,$file,'site','logo','PUBLISHED',false);
						$logo->$logoType = $logoIdx;
						
						$this->IM->db()->update($this->IM->getTable('site'),array('logo'=>json_encode($logo,JSON_NUMERIC_CHECK)))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
					}
				}
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
					
					$this->IM->db()->update($this->IM->getTable('site'),array($type=>-1))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
				} elseif ($file != null) {
					if ($oFile > 0) {
						$this->IM->getModule('attachment')->fileReplace($oFile,$type.'.'.$extension,$file,false);
						$this->IM->getModule('attachment')->filePublish($oFile,'site',$type);
					} else {
						$fileIdx = $this->IM->getModule('attachment')->fileSave($type.'.'.$extension,$file,'site',$type,'PUBLISHED',false);
						
						$this->IM->db()->update($this->IM->getTable('site'),array($type=>$fileIdx))->where('domain',$sites[$i]->domain)->where('language',$sites[$i]->language)->execute();
					}
				}
			}
		}
	}
	
	/**
	 * 특정 요청에 대하여 관리자 권한을 확인한다.
	 */
	function isAdmin() {
		if ($this->IM->getModule('member')->isAdmin() == true) return true;
		$action = Request('_action');
		
		if ($action == '@getWysiwygFiles') {
			$idx = Request('idx') ? explode(',',Request('idx')) : array();
			$lists = array();
			for ($i=0, $loop=count($idx);$i<$loop;$i++) {
				$file = $this->IM->getModule('attachment')->getFileInfo($idx[$i]);
				if ($file != null && $file->status != 'DRAFT') {
					if ($this->IM->getModule()->isInstalled($file->module) == false) return false;
					$mModule = $this->IM->getModule($file->module);
					if (method_exists($mModule,'isAdmin') == false || $this->IM->getModule($file->module)->isAdmin() === true) return false;
				}
			}
			return true;
		}
		
		return $this->checkPermission();
	}
	
	/**
	 * 관리자 접근로그를 기록한다.
	 */
	function saveWebLog() {
		if (version_compare($this->getModule()->getInstalled()->version,'3.1.0','>=') == true) {
			$this->db()->replace($this->table->page_log,array('midx'=>$this->IM->getModule('member')->getLogged(),'reg_date'=>time(),'page'=>$_SERVER['REDIRECT_URL'],'ip'=>$_SERVER['REMOTE_ADDR'],'agent'=>$_SERVER['HTTP_USER_AGENT']))->execute();
		}
	}
	
	/**
	 * 관리자 활동로그를 기록한다.
	 *
	 * @param string $module
	 * @param string $action
	 */
	function saveProcessLog($module,$action) {
		if (version_compare($this->getModule()->getInstalled()->version,'3.1.0','>=') == true) {
			$params = $_REQUEST;
			if (isset($params['_language']) == true) unset($params['_language']);
			if (isset($params['_module']) == true) unset($params['_module']);
			if (isset($params['_action']) == true) unset($params['_action']);
			if (isset($params['_dc']) == true) unset($params['_dc']);
			
			if (count($params) > 0) {
				$params = json_encode($params,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} else {
				$params = '';
			}
			
			$this->db()->replace($this->table->process_log,array('midx'=>$this->IM->getModule('member')->getLogged(),'reg_date'=>time(),'module'=>$module,'action'=>$action,'params'=>$params,'ip'=>$_SERVER['REMOTE_ADDR'],'agent'=>$_SERVER['HTTP_USER_AGENT']))->execute();
		}
	}
}
?>