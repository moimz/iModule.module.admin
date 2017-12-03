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
if (defined('__IM__') == false) exit;
?>
<header id="iModuleAdminHeader">
	<h1><?php echo $this->getConfig('title'); ?></h1>
	
	<ul>
		<?php for ($i=0, $loop=count($menus);$i<$loop;$i++) { ?>
		<li<?php echo $menus[$i]->menu == $this->menu && ($menus[$i]->page == false || $menus[$i]->page == $this->page) ? ' class="selected"' : ''; ?>><a href="<?php echo $this->getUrl($menus[$i]->menu,$menus[$i]->page,$menus[$i]->tab); ?>"><i class="<?php echo substr($menus[$i]->icon,0,2); ?> <?php echo $menus[$i]->icon; ?>"></i><?php echo $menus[$i]->title; ?></a></li>
		<?php } ?>
	</ul>
	
	<aside>
		<button type="button" onclick="Admin.logout();"><?php echo $this->getText('button/logout'); ?></button>
		<i class="photo" style="background-image:url(<?php echo $this->IM->getModule('member')->getMember()->photo; ?>);"></i>
		<?php echo $this->IM->getModule('member')->getMember()->nickname; ?>
	</aside>
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
			<?php if (count($pages) > 0 && $permissions === true) { ?>
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
			new Ext.Panel({
				id:"iModuleAdminPanel",
				<?php if ($permissions === true) { ?>
				title:"<?php echo $pageTitle->title; ?>",
				iconCls:"<?php echo substr($pageTitle->icon,0,2); ?> <?php echo $pageTitle->icon; ?>",
				<?php } ?>
				region:"center",
				<?php if (count($pages) > 0) { ?>
				cls:"x-main-panel",
				<?php } ?>
				border:false,
				layout:"fit",
				items:[
					
				],
				listeners:{
					add:function(panel,content) {
						if (content.is("tabpanel") == true) {
							<?php if ($tab) { ?>
							if (Ext.getCmp("<?php echo $tab; ?>")) {
								setTimeout(function(tabs,tab) { if (tabs.getActiveTab().getId() == "<?php echo $tab; ?>") { tabs.fireEvent("tabchange",tabs,tab); } else { tabs.setActiveTab(tab); } },1000,Ext.getCmp(content.getId()),Ext.getCmp("<?php echo $tab; ?>"));
							}
							<?php } else { ?>
							content.fireEvent("tabchange",content,content.getActiveTab());
							<?php } ?>
							
							if (Admin.getMenu() == "modules") {
								content.on("tabchange",function(tabs,tab) {
									if (Admin.getTab() != tab.getId() && history.replaceState) {
										history.replaceState({tab:tab.getId()},tab.getTitle()+" - "+Ext.getCmp("iModuleAdminPanel").getTitle(),"/admin/modules/"+Admin.getPage()+"/"+tab.getId());
										document.title = tab.getTitle()+" - <?php echo $pageTitle->title; ?>";
									}
								});
								
								content.on("afterrender",function(tabs) {
									var tab = tabs.getActiveTab();
									
									if (Admin.getTab() != tab.getId() && history.replaceState) {
										history.replaceState({tab:tab.getId()},tab.getTitle()+" - "+Ext.getCmp("iModuleAdminPanel").getTitle(),"/admin/modules/"+Admin.getPage()+"/"+tab.getId());
									}
									
									document.title = tab.getTitle()+" - <?php echo $pageTitle->title; ?>";
								});
							}
						}
					}
				}
			}),
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

$(window).on("popstate",function(e) {
	location.href = location.href;
});
</script>

<?php echo $panel; ?>