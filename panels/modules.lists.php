<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 모듈을 설치하거나 설치되어 있는 모듈을 관리하기 위한 패널을 제공한다.
 * 
 * @file /modules/admin/panels/modules.lists.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 */
?>
<script>
var panel = new Ext.grid.Panel({
	id:"ModuleList",
	border:false,
	tbar:[
		new Ext.Button({
			text:Admin.getLanguage("modules/lists/updateSize"),
			handler:function() {
			}
		})
	],
	store:new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:ENV.getProcessUrl("admin","@getModuleList"),
			reader:{type:"json"}
		},
		remoteSort:false,
		sorters:[{property:"title",direction:"ASC"}],
		autoLoad:true,
		pageSize:0,
		groupField:"installed",
		groupDir:"DESC",
		fields:["id","module","title","version","description","hash",{name:"db_size",type:"int"},{name:"attachment_size",type:"int"},"installed","installed_hash"],
		listeners:{
			load:function(store,records,success,e) {
				if (success == false) {
					if (e.getError()) {
						Ext.Msg.show({title:Admin.getLanguage("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
					} else {
						Ext.Msg.show({title:Admin.getLanguage("alert/error"),msg:Admin.getLanguage("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
					}
				}
			}
		}
	}),
	width:"100%",
	columns:[{
		text:Admin.getLanguage("modules/lists/columns/title"),
		width:150,
		summaryType:"count",
		dataIndex:"title",
		sortable:true,
		summaryRenderer:function(value) {
			return value+" module"+(value > 1 ? "s" : "");
		}
	},{
		text:Admin.getLanguage("modules/lists/columns/version"),
		width:65,
		align:"center",
		dataIndex:"version"
	},{
		text:Admin.getLanguage("modules/lists/columns/description"),
		minWidth:150,
		flex:1,
		sortable:true,
		dataIndex:"description",
	},{
		text:Admin.getLanguage("modules/lists/columns/author"),
		width:90,
		sortable:true,
		dataIndex:"author",
	},{
		text:Admin.getLanguage("modules/lists/columns/status"),
		width:100,
		dataIndex:"hash",
		align:"center",
		renderer:function(value,p,record) {
			if (record.data.installed == "FALSE") {
				p.style = "color:#666;";
				return Admin.getLanguage("modules/lists/columns/need_install");
			} else if (record.data.installed_hash != value) {
				p.style = "color:red;";
				return Admin.getLanguage("modules/lists/columns/need_update");
			} else {
				p.style = "color:blue;";
				return Admin.getLanguage("modules/lists/columns/updated");
			}
		}
	},{
		text:Admin.getLanguage("modules/lists/columns/db_size"),
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
		text:Admin.getLanguage("modules/lists/columns/attachment_size"),
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
		groupHeaderTpl:'<tpl if="name == \'TRUE\'">'+Admin.getLanguage("modules/lists/columns/installed")+'<tpl elseif="name == \'FALSE\'">'+Admin.getLanguage("modules/lists/columns/not_installed")+'</tpl>',
		hideGroupedHeader:false,
		enableGroupingMenu:false
	}],
	listeners:{
		itemdblclick:function(grid,record) {
			Admin.modules.show(record.data.module);
			return;
			var type = record.data.installed == "FALSE" ? "install" : (record.data.hash != record.data.installed_hash ? "update" : "config");
			
			Ext.Msg.wait(Admin.getLanguage("action/working"),Admin.getLanguage("action/wait"));
			
			$.ajax({
				type:"POST",
				url:ENV.getProcessUrl("admin","@loadModuleConfig"),
				data:{target:record.data.module},
				dataType:"json",
				success:function(result) {
					if (result.success == true) {/*
						if (result.script != null) {
							$("head").append(result.script);
//							$("head")(result.script);
						}*/
						
						if (result.script != null && $("script[src='"+result.script+"']").length == 0) {
							$("head").append($("<script>").attr("src",result.script));
							console.log("script 불러옴");
						}
						
						if ($("script[src='"+result.language+"']").length == 0) {
							$("head").append($("<script>").attr("src",result.language));
							console.log("language 불러옴");
						}
						
						$("head").append($("<script>").attr("src",result.config));
						console.log("config 불러옴");
						
						Ext.Msg.hide();
						
						new Ext.Window({
							id:"ModuleConfigWindow",
							title:Admin.getLanguage("modules/lists/window/"+type),
							width:800,
							height:400,
							modal:true,
							border:false,
							resizeable:false,
							autoScroll:true,
							autoDestory:false,
							items:[
								Admin.getConfigPanel()
							],
							buttons:[
								new Ext.Button({
									text:Admin.getLanguage("modules/lists/window/"+type),
									handler:function() {
										Ext.getCmp("ModuleConfigForm").getForm().submit({
											url:ENV.getProcessUrl("admin","@installModule"),
											params:{target:record.data.module},
											submitEmptyText:false,
											waitTitle:Admin.getLanguage("action/wait"),
											waitMsg:Admin.getLanguage("modules/lists/installing"),
											success:function(form,action) {
												Ext.Msg.show({title:Admin.getLanguage("alert/info"),msg:Admin.getLanguage("modules/lists/installComplete"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													Ext.getCmp("ModuleConfigWindow").close();
													Ext.getCmp("ModuleList").getStore().reload();
												}});
											},
											failure:function(form,action) {
												if (action.result && action.result.message) {
													Ext.Msg.show({title:Admin.getLanguage("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												} else {
													Ext.Msg.show({title:Admin.getLanguage("alert/error"),msg:Admin.getLanguage("error/form"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											}
										});
									}
								}),
								new Ext.Button({
									text:Admin.getLanguage("button/cancel"),
									handler:function() {
										Ext.getCmp("ModuleConfigWindow").close();
									}
								})
							],
							listeners:{
								show:function() {
									Ext.getCmp("ModuleConfigForm").getForm().load({
										url:ENV.getProcessUrl("admin","@getModuleConfig"),
										params:{target:record.data.module},
										waitTitle:Admin.getLanguage("action/wait"),
										waitMsg:Admin.getLanguage("action/loading"),
										success:function(form,action) {
										},
										failure:function(form,action) {
											if (action.result && action.result.message) {
												Ext.Msg.show({title:Admin.getLanguage("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getLanguage("alert/error"),msg:Admin.getLanguage("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										}
									});
								},
								close:function() {
									Admin.resetConfigPanel();
								}
							}
						}).show();
					}
				}
			});
		}
	}
});
</script>