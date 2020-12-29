<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 캐시파일을 관리한다.
 * 
 * @file /modules/admin/panels/configs.cache.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2019. 12. 13.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.grid.Panel({
		id:"CacheList",
		border:false,
		tbar:[
			Admin.searchField("CacheKeyword",200,"모듈검색",function(keyword) {
				Ext.getCmp("CacheList").getStore().clearFilter();
				
				if (keyword.length > 0) {
					Ext.getCmp("CacheList").getStore().filter(function(record) {
						return record.data.name.indexOf(keyword) > -1;
					});
				}
			}),
			"-",
			new Ext.Button({
				iconCls:"mi mi-trash",
				text:"선택된 캐시파일 삭제",
				handler:function() {
					Admin.configs.cache.delete();
				}
			})
		],
		store:new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:ENV.getProcessUrl("admin","@getCaches"),
				reader:{type:"json"}
			},
			remoteSort:false,
			sorters:[{property:"reg_date",direction:"DESC"}],
			autoLoad:true,
			pageSize:0,
			fields:["idx","name","module","target","path","type","size","reg_date"],
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
			text:Admin.getText("configs/cache/columns/name"),
			width:250,
			sortable:true,
			dataIndex:"name"
		},{
			text:Admin.getText("configs/cache/columns/module"),
			width:160,
			dataIndex:"module",
			sortable:true,
			renderer:function(value,p,record) {
				return '<i class="icon ' + record.data.module_icon + '"></i>' + value;
			}
		},{
			text:Admin.getText("configs/cache/columns/size"),
			width:90,
			dataIndex:"size",
			align:"right",
			sortable:true,
			renderer:function(value) {
				return iModule.getFileSize(value);
			}
		},{
			text:Admin.getText("configs/cache/columns/path"),
			minWidth:300,
			flex:1,
			dataIndex:"path",
			sortable:true,
			renderer:function(value) {
				var temp = value.split("/");
				var name = temp.pop();
				return '<span style="color:#999;">' + temp.join("/") + '/</span>' + name;
			}
		},{
			text:Admin.getText("configs/cache/columns/reg_date"),
			width:145,
			dataIndex:"reg_date",
			sortable:true,
			renderer:function(value) {
				return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm");
			}
		}],
		selModel:new Ext.selection.CheckboxModel(),
		bbar:[
			new Ext.Button({
				iconCls:"x-tbar-loading",
				handler:function() {
					Ext.getCmp("CacheList").getStore().reload();
				}
			}),
			"->",
			{xtype:"tbtext",text:Admin.getText("text/grid_help")}
		],
		listeners:{
			itemdblclick:function(grid,record) {
				Admin.configs.cache.view(record.data.name);
			},
			itemcontextmenu:function(grid,record,item,index,e) {
				var menu = new Ext.menu.Menu();
				
				menu.addTitle(record.data.name);
				
				menu.add({
					iconCls:"xi xi-form",
					text:"캐시파일 보기",
					handler:function() {
						Admin.configs.cache.view(record.data.name);
					}
				});
				
				menu.add({
					iconCls:"mi mi-trash",
					text:"캐시파일 삭제",
					handler:function() {
						Admin.configs.cache.delete();
					}
				});
				
				e.stopEvent();
				menu.showAt(e.getXY());
			}
		}
	})
); });
</script>