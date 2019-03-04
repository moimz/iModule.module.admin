<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트 관리자 페이지에 접근하기 위한 권한이 부족할 경우 사이트관리자로 로그인페이지를 구성한다.
 * 이 파일은 사이트관리자 클래스의 getLoginContext() 함수를 통해 호출된다.
 * 
 * @file /modules/admin/includes/login.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 2. 26.
 */
if (defined('__IM__') == false) exit;
?>
<main>
	<h1><i class="mi mi-lock"></i> Login to control panel</h1>
	
	<div class="inputbox">
		<div data-role="input">
			<input type="email" name="email" placeholder="E-mail">
		</div>
		<div data-role="input">
			<input type="password" name="password" placeholder="Password">
		</div>
		<button type="submit"><i class="mi mi-go-bold"></i></button>
	</div>
	
	<label class="remember"><input type="checkbox" name="remember"> Keep me signed in</label>
</main>