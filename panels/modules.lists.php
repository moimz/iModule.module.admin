<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈을 설치하거나 설치되어 있는 모듈을 관리하기 위한 패널을 제공한다.
 * 
 * @file /modules/admin/panels/modules.lists.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2019. 2. 6.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.grid.Panel({
		id:"ModuleList",
		border:false,
		tbar:[
			new Ext.Button({
				iconCls:"xi xi-list-ol",
				text:Admin.getText("modules/lists/sort"),
				handler:function() {
					Admin.modules.sort();
				}
			}),
			"-",
			new Ext.Button({
				iconCls:"fa fa-refresh",
				text:Admin.getText("modules/lists/update_size"),
				handler:function(button) {
					button.disable();
					button.setIconCls("mi mi-loading");
					$.send(ENV.getProcessUrl("admin","@updateModuleSize"),function(result) {
						button.enable();
						button.setIconCls("fa fa-refresh");
						if (result.success == true) Ext.getCmp("ModuleList").getStore().reload();
					});
				}
			})
		],
		store:new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:ENV.getProcessUrl("admin","@getModules"),
				reader:{type:"json"}
			},
			remoteSort:false,
			sorters:[{property:"sort",direction:"ASC"},{property:"title",direction:"ASC"}],
			autoLoad:true,
			pageSize:0,
			groupField:"installed",
			groupDir:"DESC",
			fields:["id","module","title","version","description","hash",{name:"db_size",type:"int"},{name:"attachment_size",type:"int"},{name:"installed",type:"boolean"},"installed_hash",{name:"isConfigPanel",type:"boolean"},{name:"sort",type:"int"}],
			listeners:{
				load:function(store,records,success,e) {
					if (success == false) {
						if (e.getError()) {
							Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						} else {
							Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
					}
				}
			}
		}),
		columns:[{
			text:Admin.getText("modules/lists/columns/title"),
			width:180,
			summaryType:"count",
			dataIndex:"title",
			sortable:true,
			renderer:function(value,p,record) {
				var icon = record.data.icon.split("-");
				
				return '<i class="icon '+icon.shift()+" "+record.data.icon+'"></i>'+value;
			},
			summaryRenderer:function(value) {
				return value+" module"+(value > 1 ? "s" : "");
			}
		},{
			text:Admin.getText("modules/lists/columns/version"),
			width:65,
			align:"center",
			dataIndex:"version"
		},{
			text:Admin.getText("modules/lists/columns/description"),
			minWidth:150,
			flex:1,
			sortable:true,
			dataIndex:"description",
		},{
			text:Admin.getText("modules/lists/columns/installed_date"),
			width:110,
			sortable:true,
			dataIndex:"installed_date",
			align:"center",
			renderer:function(value) {
				return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd)")
			}
		},{
			text:Admin.getText("modules/lists/columns/author"),
			width:90,
			sortable:true,
			dataIndex:"author",
		},{
			text:Admin.getText("modules/lists/columns/status"),
			width:100,
			dataIndex:"hash",
			align:"center",
			renderer:function(value,p,record) {
				if (record.data.installed == false) {
					p.style = "color:#666;";
					return Admin.getText("modules/lists/columns/need_install");
				} else if (record.data.installed_hash != value) {
					p.style = "color:red;";
					return Admin.getText("modules/lists/columns/need_update");
				} else {
					p.style = "color:blue;";
					return Admin.getText("modules/lists/columns/updated");
				}
			}
		},{
			text:Admin.getText("modules/lists/columns/db_size"),
			dataIndex:"db_size",
			width:110,
			align:"right",
			summaryType:"sum",
			renderer:function(value) {
				return iModule.getFileSize(value);
			},
			summaryRenderer:function(value) {
				return iModule.getFileSize(value);
			}
		},{
			text:Admin.getText("modules/lists/columns/attachment_size"),
			dataIndex:"attachment_size",
			width:110,
			align:"right",
			summaryType:"sum",
			renderer:function(value) {
				return iModule.getFileSize(value);
			},
			summaryRenderer:function(value) {
				return iModule.getFileSize(value);
			}
		}],
		selModel:new Ext.selection.RowModel(),
		features:[{
			ftype:"groupingsummary",
			groupHeaderTpl:'<tpl if="name == \'true\'">'+Admin.getText("modules/lists/columns/installed")+'<tpl elseif="name == \'false\'">'+Admin.getText("modules/lists/columns/not_installed")+'</tpl>',
			hideGroupedHeader:false,
			enableGroupingMenu:false
		}],
		bbar:[
			new Ext.Button({
				iconCls:"x-tbar-loading",
				handler:function() {
					Ext.getCmp("ModuleList").getStore().reload();
				}
			}),
			"->",
			{xtype:"tbtext",text:Admin.getText("text/grid_help")}
		],
		listeners:{
			itemdblclick:function(grid,record) {
				Admin.modules.show(record.data.module);
			},
			itemcontextmenu:function(grid,record,item,index,e) {
				var menu = new Ext.menu.Menu();
				
				menu.add('<div class="x-menu-title">'+record.data.title+'('+record.data.module+')</div>');
				menu.add({
					iconCls:"fa fa-cube",
					text:Admin.getText("modules/menus/detail"),
					handler:function() {
						Admin.modules.show(record.data.module);
					}
				});
				
				if (record.data.installed === false) {
					menu.add({
						iconCls:"fa fa-hdd-o",
						text:Admin.getText("modules/menus/install"),
						handler:function() {
							Admin.modules.install(record.data.module);
						}
					});
				} else if (record.data.installed_hash != record.data.hash) {
					menu.add({
						iconCls:"fa fa-hdd-o",
						text:Admin.getText("modules/menus/update"),
						handler:function() {
							Admin.modules.install(record.data.module);
						}
					});
				} else if (record.data.isConfigPanel == true) {
					menu.add({
						iconCls:"fa fa-cog",
						text:Admin.getText("modules/menus/config"),
						handler:function() {
							Admin.modules.install(record.data.module);
						}
					});
				}
				
				e.stopEvent();
				menu.showAt(e.getXY());
			}
		}
	})
); });
</script>