<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트관리자 접근로그를 확인한다.
 * 
 * @file /modules/admin/panels/log.admin.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2020. 4. 29.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.Panel({
		border:false,
		layout:{type:"hbox",align:"stretch"},
		tbar:[
			new Ext.form.DateField({
				id:"LogStartDate",
				emptyText:"시작",
				format:"Y-m-d",
				width:125
			}),
			new Ext.form.DateField({
				id:"LogEndDate",
				emptyText:"종료",
				format:"Y-m-d",
				width:125
			}),
			Admin.searchField("LogKeyword",300,"접속자(이름/이메일/접근아이피/페이지) 검색",function(keyword) {
				Ext.getCmp("LogList").getStore().getProxy().setExtraParam("keyword",keyword);
				Ext.getCmp("LogList").getStore().getProxy().setExtraParam("start_date",Ext.getCmp("LogStartDate").getValue());
				Ext.getCmp("LogList").getStore().getProxy().setExtraParam("end_date",Ext.getCmp("LogEndDate").getValue());
				Ext.getCmp("LogList").getStore().loadPage(1);
			}),
			"->",
			new Ext.Button({
				iconCls:"fa fa-file-excel-o",
				text:"엑셀변환",
				handler:function() {
					new Ext.Window({
						id:"LogExcelProgressWindow",
						title:"엑셀 변환중 ...",
						width:500,
						modal:true,
						bodyPadding:5,
						closable:false,
						items:[
							new Ext.ProgressBar({
								id:"LogExcelProgressBar"
							})
						],
						listeners:{
							show:function() {
								Ext.getCmp("LogExcelProgressBar").updateProgress(0,"데이터 준비중입니다. 잠시만 기다려주십시오.");
								
								var params = Ext.getCmp("LogList").getStore().getProxy().extraParams;
								params.document = "log";

								$.ajax({
									url:ENV.getProcessUrl("admin","@getExcel"),
									method:"POST",
									timeout:0,
									data:params,
									xhr:function() {
										var xhr = $.ajaxSettings.xhr();

										xhr.addEventListener("progress",function(e) {
											if (e.lengthComputable) {
												Ext.getCmp("LogExcelProgressBar").updateProgress(e.loaded/e.total,Ext.util.Format.number(e.loaded - 1,"0,000")+" / "+Ext.util.Format.number(e.total,"0,000")+" ("+(e.loaded / e.total * 100).toFixed(2)+"%)",true);
											}
										});

										return xhr;
									},
									success:function(result,b,xhr) {
										var hash = xhr.getResponseHeader("X-Excel-File");
										if (hash && hash.length == 32) {
											Ext.getCmp("LogExcelProgressBar").updateProgress(1,"변환완료. 곧 다운로드가 시작됩니다.",true);
											setTimeout(function() {
												Ext.getCmp("LogExcelProgressWindow").close();

												var query = "hash="+hash+"&document=log&log="+params.log;
												downloadFrame.location.replace(ENV.getProcessUrl("admin","@downloadExcel")+"?"+query);
											},1000);
										} else {
											if (result.message) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:"엑셀변환 중 에러가 발생하였거나, 엑셀로 변환할 데이터가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
											Ext.getCmp("LogExcelProgressWindow").close();
										}
									},
									error:function() {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:"엑셀변환 중 에러가 발생하였습니다. 잠시후 다시 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										Ext.getCmp("LogExcelProgressWindow").close();
									}
								});
							}
						}
					}).show();
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
						extraParams:{log:"admin"},
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
					text:"접근페이지",
					dataIndex:"page",
					width:200,
					renderer:function(value) {
						return '<a href="' + ENV.DIR + value + '" target="_blank">' + value + '</a>';
					}
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
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.addTitle(moment(record.data.reg_date * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss"));
						
						menu.add({
							iconCls:"xi xi-user",
							text:record.data.name + " 검색",
							handler:function() {
								Ext.getCmp("LogKeyword").setValue(record.data.name);
								Ext.getCmp("LogList").getStore().getProxy().setExtraParam("keyword",record.data.name);
								Ext.getCmp("LogList").getStore().loadPage(1);
							}
						});
						
						menu.add({
							iconCls:"xi xi-home-network",
							text:record.data.ip + " 검색",
							handler:function() {
								Ext.getCmp("LogKeyword").setValue(record.data.ip);
								Ext.getCmp("LogList").getStore().getProxy().setExtraParam("keyword",record.data.ip);
								Ext.getCmp("LogList").getStore().loadPage(1);
							}
						});
						
						menu.add({
							iconCls:"xi xi-paper",
							text:record.data.page + " 검색",
							handler:function() {
								Ext.getCmp("LogKeyword").setValue(record.data.page);
								Ext.getCmp("LogList").getStore().getProxy().setExtraParam("keyword",record.data.page);
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
							$("#Log").html(data.name + " (" + data.midx + ")<br>" + data.ip + "<br>" + data.agent);
							$("#LogTime").text(moment(data.reg_date * 1000).locale($("html").attr("lang")).format("YYYY.MM.DD(dd) HH:mm:ss"));
							$("#LogPage").html('<a href="' + ENV.DIR + data.page + '" target="_blank">' + data.page + '</a>');
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
				html:'<div style="padding:10px; while-space:normal; word-break:break-all;"><b>User : </b><div id="Log" style="padding:0px 10px; margin:10px 0px 20px 10px; border-left:2px solid #eee; line-height:1.6;"></div><b>Time : </b><div id="LogTime" style="padding:0px 10px; margin:10px 0px 20px 10px; border-left:2px solid #eee;"></div><b>page : </b><div id="LogPage" style="padding:10px; margin:10px 0px 20px 10px; border-left:2px solid #eee;"></div></div>',
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