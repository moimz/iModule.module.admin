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
 * @modified 2019. 11. 23.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.tip.QuickTipManager.init(); Ext.getCmp("iModuleAdminPanel").add(
	new Ext.TabPanel({
		id:"TableView",
		border:false,
		tabPosition:"bottom",
		activeTab:0,
		items:[
			new Ext.grid.Panel({
				id:"TableDesc",
				iconCls:"xi xi-layout-top-left-mid",
				title:"테이블구조",
				border:false,
				tbar:[
					new Ext.Button({
						iconCls:"mi mi-plus",
						text:"컬럼추가"
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("admin","@getTableDesc"),
						extraParams:{table:"<?php echo $this->page; ?>"},
						reader:{type:"json"}
					},
					remoteSort:false,
					sorters:[],
					autoLoad:true,
					pageSize:0,
					fields:[],
					listeners:{
						load:function(store,records,success,e) {
							if (success == true) {
								Ext.getCmp("TableData").getHeaderContainer().removeAll();
								for (var i=0, loop=store.getCount();i<loop;i++) {
									var field = store.getAt(i).data;
									
									var width = 100;
									if (field.type.indexOf("int") > -1) {
										width = 100;
									} else if (field.type.indexOf("text") > -1) {
										width = 200;
									} else if (field.length !== null) {
										if (field.length < 30) {
											width = 120;
										} else {
											width = 200;
										}
									}
									
									var editor = new Ext.form.TextField({selectOnFocus:true});
									
									var column = {
										text:field.field,
										tooltip:field.comment ? field.comment : field.field,
										width:width,
										dataIndex:store.getAt(i).get("field"),
										sortable:true,
										hideable:true,
										field:store.getAt(i).data,
										renderer:function(value,p,record,rowIndex,colIndex) {
											var field = Ext.getCmp("TableData").getColumnManager().getHeaderAtIndex(colIndex).field;
											
											if (field.type.indexOf("text") > -1) {
												return value.replace(/(\r\n)/g," ").replace(/</g,"&lt;").replace(/>/g,"&gt;");
											}
											
											return value;
										},
										editor:editor
									};
									
									Ext.getCmp("TableData").getHeaderContainer().insert(column);
									Ext.getCmp("TableData").getStore().reload();
								}
							} else {
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
					text:Admin.getText("database/view/columns/field"),
					width:120,
					dataIndex:"field"
				},{
					text:Admin.getText("database/view/columns/type"),
					width:100,
					dataIndex:"type",
					renderer:function(value,p,record) {
						return record.data.length !== null ? value + "(" + record.data.length + ")" : value;
					}
				},{
					text:Admin.getText("database/view/columns/collation"),
					width:180,
					dataIndex:"collation"
				},{
					text:Admin.getText("database/view/columns/null"),
					width:60,
					dataIndex:"null",
					align:"center",
					renderer:function(value) {
						return value == true ? "YES" : "NO";
					}
				},{
					text:Admin.getText("database/view/columns/extra"),
					width:140,
					dataIndex:"extra",
					renderer:function(value,p,record) {
						var sHTML = "";
						if (record.data.auto_increment == true) sHTML+= "AUTO_INCREMENT";
						
						return sHTML;
					}
				},{
					text:Admin.getText("database/view/columns/comment"),
					minWidth:150,
					dataIndex:"comment",
					flex:1
				}],
				selModel:new Ext.selection.RowModel(),
				bbar:[
					new Ext.Button({
						iconCls:"x-tbar-loading",
						handler:function() {
							Ext.getCmp("TableDesc").getStore().reload();
						}
					}),
					"->",
					{xtype:"tbtext",text:Admin.getText("text/grid_help")}
				],
				listeners:{
					itemdblclick:function(grid,record) {
						location.href = ENV.DIR + "/admin/database/" + record.data.name;
					},
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.addTitle(record.data.title+'('+record.data.name+')');
						
						menu.add({
							iconCls:"fa fa-trash",
							text:"테이블 삭제",
							handler:function() {
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					}
				}
			}),
			new Ext.grid.Panel({
				id:"TableData",
				iconCls:"xi xi-layout-top-left-mid",
				title:"데이터",
				border:false,
				tbar:[
					new Ext.Button({
						text:"데이터추가",
						iconCls:"mi mi-plus",
						handler:function() {
							
						}
					}),
					"-",
					Admin.searchField("TableDataKeyword",300,"WHERE",function(keyword) {
						
					}),
					"->",
					new Ext.Button({
						text:"선택 데이터삭제",
						iconCls:"mi mi-trash",
						handler:function() {
							
						}
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("admin","@getTableDatas"),
						extraParams:{table:"<?php echo $this->page; ?>"},
						reader:{type:"json"}
					},
					remoteSort:true,
					sorters:[],
					autoLoad:false,
					pageSize:50,
					fields:[],
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
					text:"loading...",
					flex:1
				}],
				plugins:new Ext.grid.plugin.CellEditing({clicksToEdit:2}),
				selModel:new Ext.selection.RowModel(),
				bbar:new Ext.PagingToolbar({
					store:null,
					displayInfo:false,
					items:[
						"->",
						{xtype:"tbtext",text:"항목 더블클릭 : 데이터수정 / 항목 우클릭 : 메뉴"}
					],
					listeners:{
						beforerender:function(tool) {
							tool.bindStore(Ext.getCmp(tool.ownerCt.getId()).getStore());
						}
					}
				}),
				listeners:{
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.addTitle(record.data.title+'('+record.data.name+')');
						
						menu.add({
							iconCls:"mi mi-trash",
							text:"데이터 삭제",
							handler:function() {
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					}
				}
			}),
			new Ext.Panel({
				iconCls:"xi xi-code",
				title:"쿼리실행",
				layout:"border",
				border:false,
				items:[
					
					new Ext.form.Panel({
						height:"40%",
						region:"north",
						layout:"fit",
						border:false,
						style:{borderBottom:"1px solid #d0d0d0"},
						fieldDefaults:{labelAlign:"right",anchor:"100%",allowBlank:true},
						split:true,
						tbar:[
							new Ext.Button({
								text:"전체 쿼리실행",
								iconCls:"xi xi-play",
								handler:function() {
									
								}
							})
						],
						items:[
							new Ext.form.TextArea({
								border:false,
								style:{margin:"-1px"},
								layout:"fit"
							})
						]
					}),
					new Ext.Panel({
						region:"center",
						border:false,
						items:[
							new Ext.TabPanel({
								items:[
									new Ext.Panel({
										title:"콘솔",
										layout:"fit"
									})
								]
							})
						]
					})
				]
			})
		]
	})
); });
</script>