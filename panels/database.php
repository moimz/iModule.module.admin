<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 전체 테이블 목록을 가져온다.
 * 
 * @file /modules/admin/panels/database.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2019. 12. 15.
 */
if (defined('__IM__') == false) exit;
if ($this->checkIp('database') === false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.grid.Panel({
		id:"TableList",
		border:false,
		prefix:"<?php echo __IM_DB_PREFIX__; ?>",
		tbar:[
			new Ext.Button({
				text:"테이블추가",
				iconCls:"mi mi-plus",
				handler:function() {
					
				}
			}),
			"-",
			Admin.searchField("TableListKeyword",250,"테이블명",function(keyword) {
				Ext.getCmp("TableList").getStore().clearFilter();
				
				if (keyword.length > 0) {
					Ext.getCmp("TableList").getStore().filter(function(record) {
						var filter = (record.data.name.toString().indexOf(keyword) > -1) || (record.data.comment.indexOf(keyword) > -1);
						return filter;
					});
				}
			}),
			"->",
			new Ext.Button({
				text:"백업테이블선택",
				iconCls:"xi xi-form-checkout",
				handler:function() {
					Ext.getCmp("TableList").getStore().clearFilter();
					Ext.getCmp("TableList").getSelectionModel().deselectAll();
					
					var count = 0;
					for (var i=0, loop=Ext.getCmp("TableList").getStore().getCount();i<loop;i++) {
						var name = Ext.getCmp("TableList").getStore().getAt(i).get("name");
						var regExp = new RegExp("^"+Ext.getCmp("TableList").prefix+"(.*?)_BK[0-9]{14}$");
						if (name.search(regExp) > -1) {
							Ext.getCmp("TableList").getSelectionModel().select(i,true);
							count++;
						}
					}
					
					if (count == 0) {
						Ext.Msg.show({title:Admin.getText("alert/info"),msg:"백업테이블이 존재하지 않습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
					} else {
						Ext.Msg.show({title:Admin.getText("alert/info"),msg:"백업테이블 "+Ext.util.Format.number(count,"0,000")+"개를 선택하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
					}
				}
			}),
			"-",
			new Ext.Button({
				text:"선택 테이블삭제",
				iconCls:"mi mi-trash",
				handler:function() {
					Admin.database.drop();
				}
			}),
			new Ext.Button({
				text:"선택 테이블비우기",
				iconCls:"xi xi-marquee-remove",
				handler:function() {
					
				}
			})
		],
		store:new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:ENV.getProcessUrl("admin","@getTables"),
				extraParams:{target:"all"},
				reader:{type:"json"}
			},
			remoteSort:false,
			sorters:[{property:"name",direction:"ASC"}],
			autoLoad:true,
			pageSize:0,
			fields:["name","engine",{name:"rows",type:"int"},{name:"data_length",type:"int"},{name:"index_length",type:"int"},{name:"total_length",type:"int"},"collation","comment"],
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
			text:Admin.getText("database/tables/columns/name"),
			width:250,
			dataIndex:"name",
			sortable:true,
			summaryType:"count",
			summaryRenderer:function(value) {
				return value + " Tables";
			}
		},{
			text:Admin.getText("database/tables/columns/engine"),
			width:80,
			dataIndex:"engine",
			align:"center",
			sortable:true
		},{
			text:Admin.getText("database/tables/columns/rows"),
			width:80,
			dataIndex:"rows",
			align:"right",
			sortable:true,
			renderer:function(value) {
				return Ext.util.Format.number(value,"0,000");
			},
			summaryType:"sum",
			summaryRenderer:function(value) {
				return Ext.util.Format.number(value,"0,000");
			}
		},{
			text:Admin.getText("database/tables/columns/data_length"),
			width:100,
			dataIndex:"data_length",
			align:"right",
			sortable:true,
			renderer:function(value) {
				return iModule.getFileSize(value);
			},
			summaryType:"sum",
			summaryRenderer:function(value) {
				return iModule.getFileSize(value);
			}
		},{
			text:Admin.getText("database/tables/columns/index_length"),
			width:100,
			dataIndex:"index_length",
			align:"right",
			renderer:function(value) {
				return iModule.getFileSize(value);
			},
			sortable:true,
			summaryType:"sum",
			summaryRenderer:function(value) {
				return iModule.getFileSize(value);
			}
		},{
			text:Admin.getText("database/tables/columns/total_length"),
			width:100,
			dataIndex:"total_length",
			align:"right",
			sortable:true,
			renderer:function(value) {
				return iModule.getFileSize(value);
			},
			summaryType:"sum",
			summaryRenderer:function(value) {
				return iModule.getFileSize(value);
			}
		},{
			text:Admin.getText("database/tables/columns/collation"),
			width:150,
			dataIndex:"collation",
			sortable:true
		},{
			text:Admin.getText("database/tables/columns/comment"),
			minWidth:100,
			dataIndex:"comment",
			flex:1,
			sortable:true
		}],
		selModel:new Ext.selection.CheckboxModel(),
		bbar:[
			new Ext.Button({
				iconCls:"x-tbar-loading",
				handler:function() {
					Ext.getCmp("TableList").getStore().reload();
				}
			}),
			"-",
			new Ext.button.Segmented({
				id:"TableSegmented",
				allowMultiple:false,
				items:[
					new Ext.Button({
						text:"전체",
						target:"all",
						pressed:true,
						iconCls:"fa fa-check-square-o"
					}),
					new Ext.Button({
						text:"아이모듈",
						target:"used",
						pressed:false,
						iconCls:"fa fa-square-o"
					}),
					new Ext.Button({
						text:"아이모듈외",
						target:"unused",
						pressed:false,
						iconCls:"fa fa-square-o"
					})
				],
				listeners:{
					toggle:function(segmented,button,pressed) {
						for (var i=0, loop=segmented.items.items.length;i<loop;i++) {
							segmented.items.items[i].setIconCls("fa fa-square-o");
						}
						
						Ext.getCmp("TableList").getStore().getProxy().setExtraParam("target",button.target);
						Ext.getCmp("TableList").getStore().reload();
						
						button.setIconCls("fa fa-check-square-o");
					}
				}
			}),
			"->",
			{xtype:"tbtext",text:"항목 더블클릭 : 테이블로 이동 / 항목 우클릭 : 메뉴"}
		],
		features:[{ftype:"summary"}],
		listeners:{
			itemdblclick:function(grid,record) {
				location.href = ENV.DIR + "/admin/database/" + record.data.name;
			},
			itemcontextmenu:function(grid,record,item,index,e) {
				var menu = new Ext.menu.Menu();
				
				menu.addTitle(record.data.name+(record.data.comment ? "("+record.data.comment+")" : ""));
				
				menu.add({
					iconCls:"fa fa-trash",
					text:"테이블 삭제",
					handler:function() {
						Admin.database.drop();
					}
				});
				
				e.stopEvent();
				menu.showAt(e.getXY());
			}
		}
	})
); });
</script>