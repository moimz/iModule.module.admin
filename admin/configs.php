<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈 환경설정 패널을 구성한다.
 * 
 * @file /modules/admin/admin/configs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2018. 3. 18.
 */
if (defined('__IM__') == false) exit;
?>
<script>
new Ext.form.Panel({
	id:"ModuleConfigForm",
	border:false,
	bodyPadding:10,
	width:700,
	fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:true},
	items:[
		new Ext.form.FieldSet({
			title:"관리자 보안설정",
			items:[
				new Ext.form.Checkbox({
					fieldLabel:"IP제한 활성화",
					name:"enable_security_mode",
					boxLabel:"관리자모드를 특정 IP에서만 접근할 수 있도록 설정합니다.",
					uncheckedValue:"off",
					listeners:{
						change:function(form,checked) {
							form.getForm().findField("allow_ip").setDisabled(!checked);
							form.getForm().findField("emergency_code").setDisabled(!checked);
						}
					}
				}),
				new Ext.form.TextArea({
					fieldLabel:"접근허용IP",
					name:"allow_ip",
					disabled:true,
					afterBodyEl:'<div class="x-form-help">관리자모드에 접근을 허용할 아이피를 줄바꿈으로 구분하여 입력하여 주십시오.<br>특정IP 또는 IP대역(123.123.123.* 또는 123.*.*.* 등)으로 입력하실 수 있습니다.</div>'
				}),
				new Ext.form.TextField({
					fieldLabel:"비상코드",
					name:"emergency_code",
					disabled:true,
					afterBodyEl:'<div class="x-form-help">접근허용IP가 아닌 곳에서 일시적으로 관리자모드에 접근하고자 할때 사용할 코드를 입력하여 주십시오.<br>비상코드를 이용해 관리자모드에 접근하실려면 '+location.protocol+'//'+location.host+ENV.DIR+'/admin/[입력한비상코드] 주소를 통해 접근가능합니다.</div>'
				})
			]
		})
	]
});
</script>