<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 서버에 존재하는 외부페이지를 불러온다.
 *
 * @file /modules/admin/process/@getExternals.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2019. 10. 7.
 */
if (defined('__IM__') == false) exit;

$domain = Request('domain');
$language = Request('language');

/**
 * 공용 외부파일폴더를 탐색한다.
 */
$lists = array();
$lists[] = array('path'=>'','display'=>'선택안함');
$externalsPath = @opendir(__IM_PATH__.'/externals');
if ($externalsPath) {
    while ($external = @readdir($externalsPath)) {
        if ($external != '.' && $external != '..' && is_file(__IM_PATH__ . '/externals/' . $external) == true) {
            $lists[] = array(
                'path' => __IM_DIR__ . '/externals/' . $external,
                'display' => __IM_DIR__ . '/externals/' . $external
            );
        }
    }
    @closedir($externalsPath);
}
/**
 * 사이트템플릿의 외부파일폴더를 탐색한다.
 */
$templetsPath = @opendir(__IM_PATH__.'/templets');
if ($templetsPath) {
    while ($templet = @readdir($templetsPath)) {
        if ($templet != '.' && $templet != '..' && is_dir(__IM_PATH__ . '/templets/' . $templet) == true) {
            $externalsPath = @opendir(__IM_PATH__ . '/templets/' . $templet . '/externals');
            while ($external = @readdir($externalsPath)) {
                if ($external != '.' && $external != '..' && is_file(__IM_PATH__ . '/templets/' . $templet . '/externals/' . $external) == true) {
                    $lists[] = array(
                        'path' => __IM_DIR__ . '/templets/' . $templet . '/externals/' . $external,
                        'display' => __IM_DIR__ . '/templets/' . $templet . '/externals/' . $external
                    );
                }
            }
            @closedir($externalsPath);
        }
    }
    @closedir($templetsPath);
}

/**
 * 사이트 탬플릿을 사용하거나 외부파일을 가지고 있는 모듈의 템플릿파일에서 탐색한다.
 */
$modules = $this->IM->db()->select($this->IM->getModule()->getTable('module'))->where('is_templet','TRUE')->orWhere('is_external','TRUE')->get();
for ($i=0, $loop=count($modules);$i<$loop;$i++) {
	if (is_file($this->IM->getModule()->getPath($modules[$i]->module).'/templets/package.json') == true) {
		$externalsPath = @opendir($this->IM->getModule()->getPath($modules[$i]->module).'/templets/externals');
		while ($external = @readdir($externalsPath)) {
			if ($external != '.' && $external != '..' && is_file($this->IM->getModule()->getPath($modules[$i]->module).'/templets/externals/'.$external) == true) {
				$lists[] = array(
					'path'=>$this->IM->getModule()->getDir($modules[$i]->module).'/templets/externals/'.$external,
					'display'=>$this->IM->getModule()->getDir($modules[$i]->module).'/templets/externals/'.$external
				);
			}
		}
	} else {
		$templetsPath = @opendir($this->IM->getModule()->getPath($modules[$i]->module).'/templets');
		while ($templet = @readdir($templetsPath)) {
			if ($templet != '.' && $templet != '..' && is_dir($this->IM->getModule()->getPath($modules[$i]->module).'/templets/'.$templet) == true) {
				$externalsPath = @opendir($this->IM->getModule()->getPath($modules[$i]->module).'/templets/'.$templet.'/externals');
				while ($external = @readdir($externalsPath)) {
					if ($external != '.' && $external != '..' && is_file($this->IM->getModule()->getPath($modules[$i]->module).'/templets/'.$templet.'/externals/'.$external) == true) {
						$lists[] = array(
							'path'=>$this->IM->getModule()->getDir($modules[$i]->module).'/templets/'.$templet.'/externals/'.$external,
							'display'=>$this->IM->getModule()->getDir($modules[$i]->module).'/templets/'.$templet.'/externals/'.$external
						);
					}
				}
				@closedir($externalsPath);
			}
		}
		@closedir($templetsPath);
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>