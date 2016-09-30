<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 사이트관리자 패널을 구성하기 위한 파일로 ExtJS 라이브러리를 사용하여 화면을 구성한다.
 * 
 * @file /modules/admin/includes/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.160903
 */
?>
<header id="iModuleAdminHeader">
	<h1><?php echo $this->getConfig('title'); ?></h1>
	
	<ul>
		<?php for ($i=0, $loop=count($menus);$i<$loop;$i++) { ?>
		<li<?php echo $menus[$i]->menu == $this->menu && ($menus[$i]->page == false || $menus[$i]->menu == $this->page) ? ' class="selected"' : ''; ?>><a href="<?php echo $this->getUrl($menus[$i]->menu,$menus[$i]->page,$menus[$i]->tab); ?>"><i class="<?php echo substr($menus[$i]->icon,0,2); ?> <?php echo $menus[$i]->icon; ?>"></i><?php echo $menus[$i]->title; ?></a></li>
		<?php } ?>
	</ul>
</header>

<?php if (count($pages) > 0) { ?>
<aside id="iModuleAdminPages">
	<ul>
		<?php for ($i=0, $loop=count($pages);$i<$loop;$i++) { ?>
		<li<?php echo $pages[$i]->page == $this->page ? ' class="selected"' : ''; ?>><a href="<?php echo $this->getUrl($pages[$i]->menu,$pages[$i]->page,$pages[$i]->tab); ?>"><i class="<?php echo substr($pages[$i]->icon,0,2); ?> <?php echo $pages[$i]->icon; ?>"></i><?php echo $pages[$i]->title; ?></a></li>
		<?php } ?>
	</ul>
</aside>
<?php } ?>

<footer id="iModuleAdminFooter">
	Copyright (c) <?php echo date('Y'); ?> iModule v3.0, MIT License
</footer>

<script>
var panel = null;
</script>

<?php echo $panel; ?>

<script>
if (panel == null) {
	var center = new Ext.Panel({
		title:"<?php echo $pageTitle->title; ?>",
		iconCls:"<?php echo substr($pageTitle->icon,0,2); ?> <?php echo $pageTitle->icon; ?>",
		region:"center",
		cls:"<?php echo count($pages) == 0 ? '' : 'x-main-panel'; ?>",
		border:false,
		html:"NOPE"
	});
} else {
	var center = new Ext.Panel({
		title:"<?php echo $pageTitle->title; ?>",
		iconCls:"<?php echo substr($pageTitle->icon,0,2); ?> <?php echo $pageTitle->icon; ?>",
		region:"center",
		cls:"<?php echo count($pages) == 0 ? '' : 'x-main-panel'; ?>",
		layout:"fit",
		border:false,
		items:[
			panel
		]
	});
}

Ext.onReady(function () {
	new Ext.Viewport({
		id:"AdminViewport",
		layout:{type:"border"},
		items:[
			new Ext.Panel({
				region:"north",
				height:52,
				border:false,
				contentEl:"iModuleAdminHeader"
			}),
			<?php if (count($pages) > 0) { ?>
			new Ext.Panel({
				title:"<?php echo $menuTitle->title; ?>",
				iconCls:"<?php echo substr($menuTitle->icon,0,2); ?> <?php echo $menuTitle->icon; ?>",
				region:"west",
				width:230,
				collapsible:true,
				autoScroll:true,
				hidden:<?php echo isset($ADMIN->_ADMINS->hideModules) == true && $ADMIN->_ADMINS->hideModules == true && $ADMIN->menu == 'module' ? 'true' : 'false'; ?>,
				contentEl:"iModuleAdminPages"
			}),
			<?php } ?>
			center,
			new Ext.Panel({
				region:"south",
				height:25,
				border:false,
				contentEl:"iModuleAdminFooter"
			})
		]
	}).updateLayout();
	
	try {
		document.fonts.ready.then(function() {
			setTimeout(function() { Ext.getCmp("AdminViewport").updateLayout(); },1000);
		});
	} catch (e) {}
});
</script>