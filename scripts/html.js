/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * HTML 컨텍스트 에디터 UI 이벤트를 정의한다.
 * 
 * @file /modules/admin/scripts/html.js
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 24.
 */
var htmlCodeMirror = null;
var cssCodeMirror = null;

var HtmlEditor = {
	init:function() {
		var $form = $("#ModuleAdminHtmlEditorForm");
		
		var $context = $("div[data-role=context][data-type=html]");
		var $tab = $("div[data-role=tab][data-name=html]",$form);
		
		var $style = $("style[data-role=style]",$form);
		var $html = $("textarea[name=html]",$form);
		var $css = $("textarea[name=css]",$form);
		
		var $wysiwyg = $("#ModuleAdminHtmlEditor");
		
		$tab.on("tabchange",function(e,tab) {
			if (tab == "html") {
				if (htmlCodeMirror == null) {
					htmlCodeMirror = CodeMirror.fromTextArea($html.get(0),{
						mode:"text/html",
						indentUnit:4,
						indentWithTabs:true,
						tabSize:4,
						lineNumbers:true,
						lineWrapping:true,
						autoCloseTags:true
					});
					
					htmlCodeMirror.on("change",function(a,b,c) {
						$wysiwyg.froalaEditor("html.set",htmlCodeMirror.doc.getValue());
					});
				}
				
				htmlCodeMirror.doc.setValue(html_beautify($wysiwyg.froalaEditor("html.get"),{
					indent_size:1,
					indent_char:"\t"
				}));
			}
			
			if (tab == "css") {
				if (cssCodeMirror == null) {
					cssCodeMirror = CodeMirror.fromTextArea($css.get(0),{
						mode:"css",
						indentUnit:1,
						indentWithTabs:true,
						lineNumbers:true,
						lineWrapping:true,
						autoCloseTags:true
					});
					
					cssCodeMirror.on("change",function(a,b,c) {
						$style.text(cssCodeMirror.doc.getValue());
					});
				}
				
				cssCodeMirror.doc.setValue(css_beautify($style.text(),{
					indent_size:1,
					indent_char:"\t"
				}));
			}
		});
		
		$form.inits(HtmlEditor.submit);
	},
	submit:function($form) {
		var $style = $("style[data-role=style]",$form);
		var $wysiwyg = $("#ModuleAdminHtmlEditor");
		
		$("textarea[name=css]",$form).val($style.text());
		$("textarea[name=html]",$form).val($wysiwyg.froalaEditor("html.get"));
		
		$form.send(ENV.getProcessUrl("admin","@saveHtmlContext"),function(result) {
			if (result.success == true) {
				if ($("input[name=page]",$form).length == 1) {
					location.replace(ENV.getUrl($("input[name=menu]",$form).val(),$("input[name=page]",$form).val(),false));
				} else {
					location.replace(ENV.getUrl($("input[name=menu]",$form).val(),false));
				}
			}
		});
	}
}