<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트관리자 패널을 구성하기 위한 파일로 ExtJS 라이브러리를 사용하여 화면을 구성한다.
 * 
 * @file /modules/admin/includes/index.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2020. 1. 16.
 */
if (defined('__IM__') == false) exit;
?>
<header id="iModuleAdminHeader">
	<h1><?php echo $this->getConfig('icon') == null ? '' : '<i style="background-image:url('.$this->getConfig('icon').');"></i>'; ?><?php echo $this->getConfig('title'); ?></h1>
	
	<ul data-role="menu">
		<li data-role="more">
			<button type="button"><i class="mi mi-angle-down"></i></button>
			<ul></ul>
		</li>
		<?php for ($i=0, $loop=count($menus);$i<$loop;$i++) { if (isset($menus[$i]->menu) == true) { ?>
		<li<?php echo $menus[$i]->menu == $this->menu && ($menus[$i]->page == false || $menus[$i]->page == $this->page) ? ' class="selected"' : ''; ?>><a href="<?php echo $this->getUrl($menus[$i]->menu,$menus[$i]->page,$menus[$i]->tab); ?>"><i class="<?php echo substr($menus[$i]->icon,0,2); ?> <?php echo $menus[$i]->icon; ?>"></i><?php echo $menus[$i]->title; ?></a></li>
		<?php } else { ?>
		<li><a href="<?php echo $menus[$i]->link; ?>" target="<?php echo $menus[$i]->target; ?>"><i class="<?php echo substr($menus[$i]->icon,0,2); ?> <?php echo $menus[$i]->icon; ?>"></i><?php echo $menus[$i]->title; ?></a></li>
		<?php }} ?>
	</ul>
	
	<ul data-role="button">
		<li data-role="name"><?php echo $this->IM->getModule('member')->getMember()->nickname; ?></li>
		<li data-role="push">
			<button type="button" data-action="push" style="background-image:url(<?php echo $this->IM->getModule('member')->getMember()->photo; ?>);"></button>
			<label data-module="push" data-role="count"></label>
		</li>
		<li data-role="logout"><button type="button" data-action="logout"><?php echo $this->getText('button/logout'); ?></button></li>
	</ul>
</header>

<?php if (count($pages) > 0) { ?>
<aside id="iModuleAdminPages">
	<ul>
		<?php for ($i=0, $loop=count($pages);$i<$loop;$i++) { ?>
		<li<?php echo $pages[$i]->page == $this->page ? ' class="selected"' : ''; ?> data-page="<?php echo $pages[$i]->page; ?>" data-title="<?php echo $pages[$i]->title; ?>"><a href="<?php echo $this->getUrl($pages[$i]->menu,$pages[$i]->page,$pages[$i]->tab); ?>"><i class="<?php echo substr($pages[$i]->icon,0,2); ?> <?php echo $pages[$i]->icon; ?>"></i><?php echo $pages[$i]->title; ?></a></li>
		<?php } ?>
	</ul>
</aside>
<?php } ?>

<footer id="iModuleAdminFooter">
	Copyright (c) <?php echo date('Y'); ?> iModule v3.0, MIT License / <?php echo $_SERVER['SERVER_ADDR']; ?>
</footer>

<div data-role="push">
	<h6>
		<div><?php echo $mPush->getText('text/push'); ?></div>
		
		<div class="button">
			<button type="button" onclick="Push.readAll();"><?php echo $mPush->getText('button/read_all'); ?></button>
			<button type="button" onclick="Push.settingPopup();"><?php echo $mPush->getText('button/setting'); ?></button>
		</div>
	</h6>
	
	<ul data-module="push"></ul>
	
	<button type="button" data-action="show_all" onclick="Push.listPopup();"><?php echo $mPush->getText('button/show_all'); ?></button>
</div>

<script>
Ext.onReady(function () {
	new Ext.Viewport({
		id:"AdminViewport",
		layout:{type:"border"},
		tab:"<?php echo $tab ? $tab : 'null'; ?>",
		view:"<?php echo $view ? $view : 'null'; ?>",
		items:[
			new Ext.Panel({
				region:"north",
				height:52,
				border:false,
				contentEl:"iModuleAdminHeader",
				cls:"x-visible-panel"
			}),
			<?php if (count($pages) > 0 && $permissions === true) { ?>
			new Ext.Panel({
				title:"<?php echo $menuTitle->title; ?>",
				iconCls:"<?php echo substr($menuTitle->icon,0,2); ?> <?php echo $menuTitle->icon; ?>",
				region:"west",
				tbar:[
					new Ext.form.TextField({
						emptyText:Admin.getText("text/menu_keyword"),
						flex:1,
						enableKeyEvents:true,
						listeners:{
							keyup:function(form) {
								var $page = $("#iModuleAdminPages");
								var keyword = form.getValue();
								if (keyword.length == 0) {
									$("li",$page).show();
								} else {
									$("li",$page).each(function() {
										if ($(this).attr("data-page").indexOf(keyword) > -1 || $(this).attr("data-title").indexOf(keyword) > -1) {
											$(this).show();
										} else {
											$(this).hide();
										}
									});
								}
								
								Admin.current.page.keyword = keyword;
								Admin.current.save();
							},
							afterRender:function(form) {
								if (Admin.current.page.keyword) {
									form.setValue(Admin.current.page.keyword);
									form.fireEvent("keyup",form);
								}
							}
						}
					})
				],
				width:230,
				collapsible:true,
				scrollable:"y",
				collapsedCls:"x-main-collapsed",
				hidden:<?php echo isset($ADMIN->_ADMINS->hideModules) == true && $ADMIN->_ADMINS->hideModules == true && $ADMIN->menu == 'module' ? 'true' : 'false'; ?>,
				contentEl:"iModuleAdminPages",
				listeners:{
					afterRender:function(panel) {
						panel.body.on("scroll",function(e) {
							Admin.current.page.scroll = e.target.scrollTop;
							Admin.current.save();
						});
						
						setTimeout(function(panel) {
							panel.getScrollable().scrollTo(0,Admin.current.page.scroll);
						},100,panel);
					}
				}
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
				tab:"<?php echo $tab ? $tab : 'null'; ?>",
				items:[],
				listeners:{
					add:function(panel,content) {
						if (content.is("tabpanel") == true) {
							if (Ext.getCmp("iModuleAdminPanel").tab != null) {
								setTimeout(function(tabs,tab) {
									if (tabs.getActiveTab().getId() == tab) {
										tabs.fireEvent("tabchange",tabs,Ext.getCmp(tab));
									} else if (Ext.getCmp(tab)) {
										tabs.setActiveTab(Ext.getCmp(tab));
									}
									
									Ext.getCmp("iModuleAdminPanel").tab = null;
								},100,Ext.getCmp(content.getId()),panel.tab);
							} else {
								content.fireEvent("tabchange",content,content.getActiveTab());
							}
							
							if (Admin.getMenu() == "modules") {
								content.on("tabchange",function(tabs,tab) {
									if (Admin.getTab() != tab.getId() && history.replaceState) {
										if (Ext.getCmp("iModuleAdminPanel").tab == null) history.replaceState({tab:tab.getId()},tab.getTitle()+" - "+Ext.getCmp("iModuleAdminPanel").getTitle(),"/admin/modules/"+Admin.getPage()+"/"+tab.getId());
										document.title = tab.getTitle()+" - <?php echo $pageTitle->title; ?>";
									}
								});
								
								content.on("afterrender",function(tabs) {
									var tab = tabs.getActiveTab();
									
									if (Admin.getTab() != tab.getId() && history.replaceState) {
										if (Ext.getCmp("iModuleAdminPanel").tab == null) history.replaceState({tab:tab.getId()},tab.getTitle()+" - "+Ext.getCmp("iModuleAdminPanel").getTitle(),"/admin/modules/"+Admin.getPage()+"/"+tab.getId());
									}
									
									document.title = tab.getTitle()+" - <?php echo $pageTitle->title; ?>";
								});
							}
						}
						
						$(document).triggerHandler("iModuleAdminPanelRedy");
					}
				}
			}),
			new Ext.Panel({
				region:"south",
				height:25,
				border:false,
				contentEl:"iModuleAdminFooter"
			})
		],
		listeners:{
			afterRender:function() {
				$(document).triggerHandler("iModuleAdminReady");
			},
			resize:function() {
				var $header = $("#iModuleAdminHeader");
				var $menus = $("ul[data-role=menu]",$header);
				var $items = $("> li",$menus);
				var $lists = $("> li[data-role=more] > ul",$menus);
				$lists.empty();
				$items.show();
				
				var count = 0;
				var limitWidth = $menus.innerWidth();
				var currentWidth = 0;
				var is_more = false;
				$items.each(function() {
					var width = $(this).outerWidth();
					if (currentWidth + width + 40 > limitWidth) {
						is_more = true;
						return false;
					}
					
					currentWidth+= width;
					count++;
				});
				
				if (is_more == true) {
					for (var i=loop=$items.length - 1;i>=Math.max(1,count);i--) {
						var $menu = $items.eq(i).clone(true);
						
						$lists.prepend($menu);
						$items.eq(i).hide();
					}
				} else {
					$items.eq(0).hide();
				}
			}
		}
	}).updateLayout();
	
	try {
		document.fonts.ready.then(function() {
			setTimeout(function() { Ext.getCmp("AdminViewport").updateLayout(); Ext.getCmp("AdminViewport").fireEvent("resize"); },1000);
		});
	} catch (e) {}
});

$(window).on("popstate",function(e) {
	location.href = location.href;
});

$(document).ready(function() {
	$("#iModuleAdminHeader ul[data-role=menu] > li[data-role=more] > button").on("click",function() {
		$(this).parent().toggleClass("on");
	});
});
</script>

<?php echo $panel; ?>

<iframe name="downloadFrame" style="display:none;"></iframe>