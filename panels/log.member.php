<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트관리자 활동로그를 확인한다.
 * 
 * @file /modules/admin/panels/log.process.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2019. 12. 17.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.Panel({
		border:false,
		layout:{type:"hbox",align:"stretch"},
		tbar:[
			Admin.searchField("LogUserKeyword",260,"접속자(이름/이메일/접근아이피) 검색",function(keyword) {
				Ext.getCmp("LogList").getStore().clearFilter();
				
				if (keyword.length > 0) {
					Ext.getCmp("LogList").getStore().getProxy().setExtraParam("user",keyword);
					Ext.getCmp("LogList").getStore().loadPage(1);
				}
			}),
			"-",
			new Ext.form.ComboBox({
				id:"LogModule",
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						url:ENV.getProcessUrl("admin","@getInstalledModules"),
						extraParams:{is_all:"true",is_admin:"true"},
						reader:{type:"json"}
					},
					autoLoad:true,
					remoteSort:false,
					sorters:[{property:"sort",direction:"ASC"}],
					fields:["idx","title",{name:"sort",type:"int"}]
				}),
				width:140,
				editable:false,
				displayField:"title",
				valueField:"module",
				value:"",
				matchFieldWidth:false,
				listConfig:{
					minWidth:140
				},
				listeners:{
					change:function(form,value) {
						Ext.getCmp("LogList").getStore().getProxy().setExtraParam("module",value);
						Ext.getCmp("LogList").getStore().loadPage(1);
					}
				}
			}),
			Admin.searchField("LogKeyword",240,"활동코드 검색",function(keyword) {
				Ext.getCmp("LogList").getStore().clearFilter();
				
				if (keyword.length > 0) {
					Ext.getCmp("LogList").getStore().getProxy().setExtraParam("keyword",keyword);
					Ext.getCmp("LogList").getStore().loadPage(1);
				}
			})
		],
		items:[
			new Ext.grid.Panel({
				id:"LogList",
				border:false,
				layout:"fit",
				flex:1,
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("admin","@getLogs"),
						extraParams:{log:"member"},
						reader:{type:"json"}
					},
					remoteSort:true,
					sorters:[{property:"reg_date",direction:"DESC"}],
					autoLoad:true,
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
					text:"일시",
					dataIndex:"reg_date",
					width:160,
					renderer:function(value) {
						return moment(value * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss");
					}
				},{
					text:"접속자",
					dataIndex:"name",
					width:140,
					renderer:function(value,p,record) {
						return '<i style="width:24px; height:24px; float:left; display:block; background:url('+record.data.photo+'); background-size:cover; background-repeat:no-repeat; border:1px solid #ccc; border-radius:50%; margin:-3px 5px -3px -5px;"></i>'+value;
					}
				},{
					text:"이메일",
					dataIndex:"email",
					width:180
				},{
					text:"모듈",
					dataIndex:"module",
					width:150,
					renderer:function(value,p,record) {
						var icon = record.data.icon.split("-");
						return '<i class="icon '+icon.shift()+" "+record.data.icon+'"></i>'+record.data.title;
					}
				},{
					text:"활동코드",
					dataIndex:"code",
					width:200
				},{
					text:"접근아이피",
					dataIndex:"ip",
					width:140
				},{
					text:"접근브라우저",
					dataIndex:"agent",
					minWidth:140,
					flex:1
				}],
				selModel:new Ext.selection.RowModel({mode:"SINGLE"}),
				listeners:{
					itemdblclick:function(grid,record) {
						Admin.plugins.show(record.data.plugin);
					},
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.addTitle(moment(record.data.reg_date * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss"));
						
						menu.add({
							iconCls:"xi xi-user",
							text:record.data.name + " 검색",
							handler:function() {
								Ext.getCmp("LogUserKeyword").setValue(record.data.name);
								Ext.getCmp("LogList").getStore().getProxy().setExtraParam("user",record.data.name);
								Ext.getCmp("LogList").getStore().loadPage(1);
							}
						});
						
						menu.add({
							iconCls:"xi xi-home-network",
							text:record.data.ip + " 검색",
							handler:function() {
								Ext.getCmp("LogUserKeyword").setValue(record.data.ip);
								Ext.getCmp("LogList").getStore().getProxy().setExtraParam("user",record.data.ip);
								Ext.getCmp("LogList").getStore().loadPage(1);
							}
						});
						
						menu.add({
							iconCls:"xi xi-box",
							text:record.data.title + " 검색",
							handler:function() {
								Ext.getCmp("LogModule").setValue(record.data.module);
							}
						});
						
						menu.add({
							iconCls:"xi xi-cog",
							text:record.data.code + " 검색",
							handler:function() {
								Ext.getCmp("LogKeyword").setValue(record.data.code);
								Ext.getCmp("LogList").getStore().getProxy().setExtraParam("keyword",record.data.code);
								Ext.getCmp("LogList").getStore().loadPage(1);
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					},
					selectionchange:function(selection,selected) {
						if (selected.length == 0) {
							Ext.getCmp("LogDetail").hide();
						} else if (selected.length == 1) {
							var data = selected[0].data;
							Ext.getCmp("LogDetail").setTitle(moment(data.reg_date * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss") + " - " + data.name);
							$("#LogUser").html(data.name + " (" + data.midx + ")<br>" + data.ip + "<br>" + data.agent);
							$("#LogTime").text(moment(data.reg_date * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss"));
							$("#LogModuleTitle").text(data.title + "(" + data.module + ")");
							$("#LogRequest").text(JSON.stringify(JSON.parse(data.content),undefined,4));
							Ext.getCmp("LogDetail").show();
						}
					}
				}
			}),
			new Ext.Panel({
				id:"LogDetail",
				title:"",
				cls:"x-main-default-panel",
				hidden:true,
				margin:"-1 -1 -1 0",
				scrollable:true,
				html:'<div style="padding:10px; while-space:normal; word-break:break-all;"><b>User : </b><div id="LogUser" style="padding:0px 10px; margin:10px 0px 20px 10px; border-left:2px solid #eee; line-height:1.6;"></div><b>Time : </b><div id="LogTime" style="padding:0px 10px; margin:10px 0px 20px 10px; border-left:2px solid #eee;"></div><b>Module : </b><div id="LogModuleTitle" style="padding:0px 10px; margin:10px 0px 20px 10px; border-left:2px solid #eee;"></div><b>Contents : </b><pre id="LogRequest" style="padding:0px 10px; margin:10px 0px 20px 10px; border-left:2px solid #eee;"></pre></div>',
				tools:[{
					type:"close",
					callback:function() {
						Ext.getCmp("LogList").getSelectionModel().deselectAll();
					}
				}],
				width:400
			})
		],
		bbar:new Ext.PagingToolbar({
			store:null,
			displayInfo:false,
			items:[
				"->",
				{xtype:"tbtext",text:"항목선택 : 상세정보보기 / 항목 우클릭 : 메뉴"}
			],
			listeners:{
				beforerender:function(tool) {
					tool.bindStore(Ext.getCmp("LogList").getStore());
				}
			}
		})
	})
); });
</script>