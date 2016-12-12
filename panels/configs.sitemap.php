<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 사이트맵을 구성한다.
 * 
 * @file /modules/admin/panels/configs.sitemap.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 */
 
if (defined('__IM__') == false) exit;
?>
<script>
var panel = new Ext.Panel({
	border:false,
	layout:"fit",
	tbar:[
		new Ext.form.ComboBox({
			id:"SiteList",
			store:new Ext.data.JsonStore({
				proxy:{
					type:"ajax",
					simpleSortMode:true,
					url:ENV.getProcessUrl("admin","@getSites"),
					reader:{type:"json"}
				},
				remoteSort:false,
				sorters:[{property:"sort",direction:"ASC"}],
				autoLoad:true,
				pageSize:0,
				fields:["display","value"],
				listeners:{
					load:function(store,records,success,e) {
						Ext.getCmp("MenuList").disable();
						Ext.getCmp("PageList").disable();
						
						if (success == true) {
							if (store.getCount() > 0 && store.findExact("value",Ext.getCmp("SiteList").getValue(),0) == -1) {
								Ext.getCmp("SiteList").setValue(store.getAt(0).get("value"));
							}
						} else {
							if (e.getError()) {
								Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
							} else {
								Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
							}
						}
					}
				}
			}),
			autoLoadOnValue:true,
			editable:false,
			displayField:"display",
			valueField:"value",
			width:300,
			listeners:{
				change:function(form,value) {
					if (value) {
						var temp = value.split("@");
						var domain = temp[0];
						var language = temp[1];
						Ext.getCmp("MenuList").getStore().getProxy().setExtraParam("domain",domain);
						Ext.getCmp("MenuList").getStore().getProxy().setExtraParam("language",language);
						Ext.getCmp("PageList").getStore().getProxy().setExtraParam("domain",domain);
						Ext.getCmp("PageList").getStore().getProxy().setExtraParam("language",language);
						Ext.getCmp("MenuList").getStore().reload();
					}
				}
			}
		}),
		new Ext.Button({
			iconCls:"fa fa-plus",
			text:Admin.getText("configs/sites/add_site"),
			handler:function() {
				Admin.configs.sites.add();
			}
		})
	],
	items:[
		new Ext.Panel({
			layout:{type:"hbox",align:"stretch"},
			border:false,
			padding:5,
			items:[
				new Ext.grid.Panel({
					id:"MenuList",
					flex:4,
					border:true,
					disabled:true,
					selected:null,
					title:Admin.getText("configs/sitemap/menu_list"),
					tbar:[
						new Ext.Button({
							text:Admin.getText("configs/sitemap/add_menu"),
							handler:function() {
								Admin.configs.sitemap.menu();
							}
						}),
						new Ext.Button({
							text:Admin.getText("configs/sitemap/load_menu"),
							handler:function() {
								Admin.configs.sitemap.loadMenu();
							}
						})
					],
					store:new Ext.data.JsonStore({
						proxy:{
							type:"ajax",
							simpleSortMode:true,
							url:ENV.getProcessUrl("admin","@getSitemap"),
							extraParams:{domain:"",language:""},
							reader:{type:"json"}
						},
						remoteSort:false,
						sorters:[{property:"sort",direction:"ASC"}],
						autoLoad:false,
						pageSize:0,
						fields:["menu","page","icon","title","type","context"],
						listeners:{
							load:function(store,records,success,e) {
								Ext.getCmp("MenuList").enable();
								Ext.getCmp("PageList").disable();
								
								if (success == false) {
									if (e.getError()) {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
									}
								} else {
									if (Ext.getCmp("MenuList").selected != null) {
										var select = Ext.getCmp("MenuList").getStore().find("menu",Ext.getCmp("MenuList").selected,0,false,false,true);
										if (select > -1 ) Ext.getCmp("MenuList").getSelectionModel().select(select);
										Ext.getCmp("MenuList").selected = null;
									} else {
										Ext.getCmp("PageList").getStore().removeAll();
									}
								}
							}
						}
					}),
					columns:[{
						text:Admin.getText("configs/sitemap/columns/menu"),
						width:100,
						dataIndex:"menu"
					},{
						text:Admin.getText("configs/sitemap/columns/menu_title"),
						minWidth:150,
						flex:1,
						dataIndex:"title",
						renderer:function(value,p,record) {
							return record.data.icon+" "+value;
						}
					},{
						text:Admin.getText("configs/sitemap/columns/type"),
						width:80,
						dataIndex:"type",
						renderer:function(value) {
							return Admin.getText("configs/sitemap/type/"+value) ? Admin.getText("configs/sitemap/type/"+value) : value;
						}
					},{
						text:Admin.getText("configs/sitemap/columns/context"),
						width:160,
						dataIndex:"context"
					}],
					selModel:new Ext.selection.RowModel(),
					bbar:[
						new Ext.Button({
							text:'<i class="fa fa-caret-up"></i>',
							handler:function() {
								Admin.gridSort(Ext.getCmp("MenuList"),"sort","up");
								Admin.gridSave(Ext.getCmp("MenuList"),ENV.getProcessUrl("admin","@saveSitemapSort"),500);
							}
						}),
						new Ext.Button({
							text:'<i class="fa fa-caret-down"></i>',
							handler:function() {
								Admin.gridSort(Ext.getCmp("MenuList"),"sort","down");
								Admin.gridSave(Ext.getCmp("MenuList"),ENV.getProcessUrl("admin","@saveSitemapSort"),500);
							}
						}),
						"-",
						new Ext.Button({
							iconCls:"x-tbar-loading",
							handler:function() {
								Ext.getCmp("MenuList").getStore().reload();
							}
						}),
						"->",
						{xtype:"tbtext",text:Admin.getText("text/grid_help")}
					],
					listeners:{
						select:function(grid,record) {
							Ext.getCmp("PageList").getStore().getProxy().setExtraParam("menu",record.data.menu);
							Ext.getCmp("PageList").getStore().reload();
						},
						itemdblclick:function(grid,record) {
							Admin.configs.sitemap.menu(record.data.menu);
						}
					}
				}),
				new Ext.grid.Panel({
					id:"PageList",
					flex:5,
					border:true,
					disabled:true,
					selected:null,
					title:Admin.getText("configs/sitemap/page_list"),
					tbar:[
						new Ext.Button({
							text:Admin.getText("configs/sitemap/add_page"),
							handler:function() {
								Admin.configs.sitemap.page();
							}
						}),
						new Ext.Button({
							text:Admin.getText("configs/sitemap/load_page"),
							handler:function() {
								Admin.configs.sitemap.loadPage();
							}
						})
					],
					style:{marginLeft:"5px"},
					store:new Ext.data.JsonStore({
						proxy:{
							type:"ajax",
							simpleSortMode:true,
							url:ENV.getProcessUrl("admin","@getSitemap"),
							extraParams:{domain:"",language:"",menu:""},
							reader:{type:"json"}
						},
						remoteSort:false,
						sorters:[{property:"sort",direction:"ASC"}],
						autoLoad:false,
						pageSize:0,
						fields:["menu","page","icon","title","type","context"],
						listeners:{
							load:function(store,records,success,e) {
								Ext.getCmp("PageList").enable();
								
								if (success == false) {
									if (e.getError()) {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
									}
								} else {
									if (Ext.getCmp("PageList").selected != null) {
										var select = Ext.getCmp("PageList").getStore().find("page",Ext.getCmp("PageList").selected,0,false,false,true);
										if (select > -1 ) Ext.getCmp("PageList").getSelectionModel().select(select);
										Ext.getCmp("PageList").selected = null;
									}
								}
							}
						}
					}),
					columns:[{
						text:Admin.getText("configs/sitemap/columns/page"),
						width:100,
						dataIndex:"page"
					},{
						text:Admin.getText("configs/sitemap/columns/page_title"),
						minWidth:150,
						flex:1,
						dataIndex:"title"
					},{
						text:Admin.getText("configs/sitemap/columns/type"),
						width:80,
						dataIndex:"type",
						renderer:function(value) {
							return Admin.getText("configs/sitemap/type/"+value);
						}
					},{
						text:Admin.getText("configs/sitemap/columns/context"),
						width:200,
						dataIndex:"context"
					}],
					selModel:new Ext.selection.RowModel(),
					bbar:[
						new Ext.Button({
							text:'<i class="fa fa-caret-up"></i>',
							handler:function() {
								Admin.gridSort(Ext.getCmp("PageList"),"sort","up");
								Admin.gridSave(Ext.getCmp("PageList"),ENV.getProcessUrl("admin","@saveSitemapSort"),500);
							}
						}),
						new Ext.Button({
							text:'<i class="fa fa-caret-down"></i>',
							handler:function() {
								Admin.gridSort(Ext.getCmp("PageList"),"sort","down");
								Admin.gridSave(Ext.getCmp("PageList"),ENV.getProcessUrl("admin","@saveSitemapSort"),500);
							}
						}),
						"-",
						new Ext.Button({
							iconCls:"x-tbar-loading",
							handler:function() {
								Ext.getCmp("PageList").getStore().reload();
							}
						}),
						"->",
						{xtype:"tbtext",text:Admin.getText("text/grid_help")}
					],
					listeners:{
						itemdblclick:function(grid,record) {
							Admin.configs.sitemap.page(record.data.page);
						}
					}
				})
			]
		})
	]
});
</script>