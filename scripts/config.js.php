<?php
REQUIRE_ONCE str_replace(DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'config.js.php','',$_SERVER['SCRIPT_FILENAME']).'/configs/init.config.php';
header('Content-Type: application/x-javascript; charset=utf-8');

$module = Request('module');

if (is_file(__IM_PATH__.'/modules/'.$module.'/admin/config.php') == true) {
	ob_start();
	INCLUDE_ONCE __IM_PATH__.'/modules/'.$module.'/admin/config.php';
	$config = ob_get_contents();
	ob_end_clean();
	$config = trim(preg_replace('/<\/?script>/','',$config));
	
	echo 'Admin.setConfigPanel('.preg_replace('/var config = /','',preg_replace('/;$/','',$config)).');';
	/*
	$config = preg_replace_callback('/([A-Za-z]+)\.getLanguage\((.*?)\)/',function($match) {
		global $IM;
		
		$mModule = $IM->getModule('admin')->getModule(strtolower($match[1]));
		return '"'.$mModule->getLanguage(preg_replace('/\'|"/','',$match[2])).'"';
	},$config);
	*/
} else {
?>
Admin.setConfigPanel(new Ext.form.FormPanel({
	id:"ModuleConfigForm",
	border:false,
	bodyPadding:10,
	fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:true},
	items:[]
}));
<?php } ?>