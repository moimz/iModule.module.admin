<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * HTML 편집도구 출력한다.
 * 
 * @file /modules/admin/includes/html.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 24.
 */
if (defined('__IM__') == false) exit;
?>
<div data-role="box" class="blue">
	<p><i></i>본문편집 탭에서 본문을 바로편집하거나, HTML편집 또는 스타일시트 편집 탭에서 각각의 소스를 편집할 수 있습니다.</p>
	<p><i></i>본문에 포함될 이미지 또는 파일은 본문편집탭 하단의 파일첨부 버튼을 클릭하여 추가할 수 있습니다.</p>
</div>

<div data-role="tabbar">
	<div>
		<ul data-role="tab" data-name="html">
			<li data-tab="editor"><button type="button">본문편집</button></li>
			<li data-tab="html"><button type="button">HTML편집</button></li>
			<li data-tab="css"><button type="button">스타일시트편집</button></li>
		</ul>
	</div>
</div>

<div data-role="tab" data-name="html">
	<div data-tab="editor">
		<div data-role="box" class="yellow">
			<p><i></i>수정하고자 하는 텍스트영역을 드래그하면 선택한 영역의 스타일을 변경할 수 있는 에디터메뉴가 나타납니다.</p>
			<p><i></i>또는 특정 위치에서 마우스 오른쪽버튼으로 클릭하면 이미지 또는 테이블 등을 추가할 수 있는 에디터메뉴가 나타납니다.</p>
		</div>
		
		<div data-role="line"><span>본문영역 시작</span></div>
		<div data-role="context" data-type="html">
			<?php echo $wysiwyg; ?>
		</div>
		<div data-role="line"><span>본문영역 끝</span></div>
		
		<?php echo $uploader; ?>
	</div>
	
	<div data-tab="html">
		<div data-role="box" class="yellow">
			<p><i></i>입력한 HTML태그는 자동으로 정렬되며, 태그자동완성 기능이 제공됩니다.</p>
			<p><i></i>HTML태그 편집 후 본문편집 탭을 클릭하면 입력한 변경사항이 자동으로 반영됩니다.</p>
		</div>
		
		<textarea name="html"></textarea>
	</div>
	
	<div data-tab="css">
		<div data-role="box" class="yellow">
			<p><i></i>사이트의 다른영역에 영향이 없도록 가급적 div[data-role=content][data-type=html] 셀렉터를 사용하여 주시기 바랍니다.</p>
			<p><i class="hidden"></i>(예 : div[data-role=context][data-type=html] .yourClassName {yourClassOptions})</p>
		</div>
		
		<textarea name="css"></textarea>
	</div>
</div>

<div data-role="button">
	<button type="submit">저장하기</button>
	<a href="<?php echo $IM->getUrl($menu,$page,false); ?>">돌아가기</a>
</div>