<?php
/**
 * 이 파일은 iModule 사이트관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 사이트 관리자 페이지에 접근하기 위한 권한이 부족할 경우 사이트관리자로 로그인페이지를 구성한다.
 * 이 파일은 사이트관리자 클래스의 getLoginContext() 함수를 통해 호출된다.
 * 
 * @file /modules/admin/includes/login.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 */
?>
<main>
	<h1><i class="fa fa-lock"></i> Login to control panel</h1>
	
	<div class="inputbox">
		<input type="email" name="email" placeholder="E-mail">
		<input type="password" name="password" placeholder="Password">
		<button type="submit"><i class="fa fa-arrow-right"></i></button>
	</div>
	
	<label class="autoLogin"><input type="checkbox" name="auto"> Keep me signed in</label>
</main>