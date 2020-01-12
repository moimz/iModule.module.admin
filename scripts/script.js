/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 관리자모듈 UI를 구성한다.
 * 
 * @file /modules/admin/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.1.0
 * @modified 2020. 1. 12.
 */
var Admin = {
	/**
	 * 페이지명을 가져온다.
	 */
	getMenu:function() {
		var path = location.pathname.replace(/^\//,'').split("/");
		if (path.length > 1) return path[1];
		else return "";
	},
	/**
	 * 페이지명을 가져온다.
	 */
	getPage:function() {
		var path = location.pathname.replace(/^\//,'').split("/");
		if (path.length > 2) return path[2];
		else return "";
	},
	/**
	 * 탭명을 가져온다.
	 */
	getTab:function() {
		var path = location.pathname.replace(/^\//,'').split("/");
		if (path.length > 3) return path[3];
		else return "";
	},
	/**
	 * 사이트관리자 로그인 처리
	 */
	login:function($form) {
		$form.send(ENV.getProcessUrl("admin","login"),function(result) {
			if (result.success == true) {
				$.send(ENV.getProcessUrl("admin","checkPermission"),function(result) {
					if (result.success == true) {
						location.replace(location.href);
					} else {
						$("main").addClass("error").shake();
						$form.status("error");
					}
				});
			} else {
				$("main").addClass("error").shake();
			}
		});
	},
	/**
	 * 관리자패널의 현재 설정값을 저장하거나 가져온다.
	 */
	current:{
		page:null,
		init:function() {
			Admin.current.page = iModule.session("admin.current.page") == null || iModule.session("admin.current.page").menu != Admin.getMenu() ? {menu:Admin.getMenu(),keyword:"",scroll:0} : iModule.session("admin.current.page");
		},
		save:function() {
			iModule.session("admin.current.page",Admin.current.page);
		}
	},
	/**
	 * 모듈
	 */
	modules:{
		/**
		 * 모듈 기본정보
		 *
		 * @param string module 모듈명
		 */
		show:function(module) {
			new Ext.Window({
				id:"ModuleWindow",
				title:Admin.getText("action/wait"),
				width:800,
				height:600,
				modal:true,
				border:false,
				resizeable:false,
				autoScroll:true,
				items:[
					new Ext.form.Panel({
						id:"ModuleForm",
						border:false,
						bodyPadding:10,
						fieldDefaults:{labelAlign:"right",labelWidth:80,anchor:"100%",allowBlank:true},
						items:[
							new Ext.form.FieldSet({
								title:Admin.getText("modules/show/default"),
								items:[
									new Ext.form.FieldContainer({
										layout:"hbox",
										style:{marginBottom:0},
										items:[
											new Ext.Panel({
												width:120,
												border:false,
												items:[
													new Ext.Panel({
														id:"ModuleIcon",
														border:false,
														bodyCls:"im-icon-default",
														html:'<i class="mi mi-loading"></i>',
													}),
													new Ext.Button({
														id:"ModuleInstallButton1",
														hidden:true,
														style:{width:"100%",marginTop:"5px",borderRadius:"3px"},
														handler:function() {
															Ext.getCmp("ModuleWindow").close();
															Admin.modules.install(module);
														}
													})
												]
											}),
											new Ext.form.FieldContainer({
												layout:"vbox",
												flex:1,
												fieldDefaults:{margin:0,padding:0},
												items:[
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("modules/show/version"),
														name:"version"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("modules/show/author"),
														name:"author"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("modules/show/homepage"),
														name:"homepage"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("modules/show/language"),
														name:"language"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("modules/show/hash"),
														name:"hash"
													})
												]
											})
										]
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Admin.getText("modules/show/description"),
								items:[
									new Ext.form.DisplayField({
										name:"description",
										style:{marginBottom:0}
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Admin.getText("modules/show/functions"),
								items:[
									new Ext.form.CheckboxGroup({
										columns:4,
										style:{marginBottom:0},
										items:[
											new Ext.form.Checkbox({
												name:"global",
												boxLabel:Admin.getText("modules/show/global"),
												flex:1,
												readOnly:true
											}),
											new Ext.form.Checkbox({
												name:"admin",
												boxLabel:Admin.getText("modules/show/admin"),
												flex:1,
												readOnly:false
											}),
											new Ext.form.Checkbox({
												name:"widget",
												boxLabel:Admin.getText("modules/show/widget"),
												flex:1,
												readOnly:false
											}),
											new Ext.form.Checkbox({
												name:"cron",
												boxLabel:Admin.getText("modules/show/cron"),
												flex:1,
												readOnly:true
											}),
											new Ext.form.Checkbox({
												name:"templet",
												boxLabel:Admin.getText("modules/show/templet"),
												flex:1,
												readOnly:true
											}),
											new Ext.form.Checkbox({
												name:"sitemap",
												boxLabel:Admin.getText("modules/show/sitemap"),
												flex:1,
												readOnly:true
											}),
											new Ext.form.Checkbox({
												name:"context",
												boxLabel:Admin.getText("modules/show/context"),
												flex:1,
												readOnly:true
											}),
											new Ext.form.Checkbox({
												name:"external",
												boxLabel:Admin.getText("modules/show/external"),
												flex:1,
												readOnly:true
											})
										]
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Admin.getText("modules/show/dependencies"),
								items:[
									new Ext.form.CheckboxGroup({
										id:"ModuleDependencies",
										columns:4,
										style:{marginBottom:0},
										items:[]
									})
								]
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						id:"ModuleInstallButton2",
						hidden:true,
						handler:function() {
							Ext.getCmp("ModuleWindow").close();
							Admin.modules.install(module);
						}
					}),
					new Ext.Button({
						text:Admin.getText("button/close"),
						handler:function() {
							Ext.getCmp("ModuleWindow").close();
						}
					})
				],
				listeners:{
					show:function() {
						Ext.getCmp("ModuleForm").getForm().load({
							url:ENV.getProcessUrl("admin","@getModule"),
							params:{target:module},
							waitTitle:Admin.getText("action/wait"),
							waitMsg:Admin.getText("action/loading"),
							success:function(form,action) {
								Ext.getCmp("ModuleWindow").setTitle(action.result.data.title);
								$("#ModuleIcon i.mi").removeClass("mi mi-loading").addClass(action.result.data.icon.substr(0,2)).addClass(action.result.data.icon);
								
								for (var i=0, loop=action.result.data.dependencies.length;i<loop;i++) {
									Ext.getCmp("ModuleDependencies").add(
										new Ext.form.Checkbox({
											boxLabel:action.result.data.dependencies[i].name,
											checked:action.result.data.dependencies[i].checked,
											readOnly:true
										})
									);
								}
								
								Ext.getCmp("ModuleInstallButton1").show();
								Ext.getCmp("ModuleInstallButton2").show();
								if (action.result.data.isLatest == true) {
									Ext.getCmp("ModuleInstallButton1").setText(Admin.getText("modules/show/installed"));
									Ext.getCmp("ModuleInstallButton1").disable();
									
									if (action.result.data.isConfigPanel == true) {
										Ext.getCmp("ModuleInstallButton2").setText(Admin.getText("modules/show/setting"));
									} else {
										Ext.getCmp("ModuleInstallButton2").hide();
									}
								} else if (action.result.data.isInstalled == true) {
									Ext.getCmp("ModuleInstallButton1").setText(Admin.getText("modules/show/update"));
									Ext.getCmp("ModuleInstallButton2").setText(Admin.getText("modules/show/update"));
								} else {
									Ext.getCmp("ModuleInstallButton1").setText(Admin.getText("modules/show/install"));
									Ext.getCmp("ModuleInstallButton2").setText(Admin.getText("modules/show/install"));
								}
							},
							failure:function(form,action) {
								if (action.result && action.result.message) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						});
					}
				}
			}).show();
		},
		/**
		 * 모듈순서변경
		 */
		sort:function() {
			new Ext.Window({
				id:"ModuleSortWindow",
				title:Admin.getText("modules/lists/sort_title"),
				width:800,
				height:600,
				modal:true,
				border:false,
				resizeable:false,
				layout:"fit",
				items:[
					new Ext.grid.Panel({
						id:"ModuleSortList",
						border:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:ENV.getProcessUrl("admin","@getInstalledModules"),
								reader:{type:"json"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:0,
							groupDir:"DESC",
							fields:["id","module","icon","title","version","description",{name:"sort",type:"int"}],
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
							dataIndex:"title",
							renderer:function(value,p,record) {
								var icon = record.data.icon.split("-");
								
								return '<i class="icon '+icon.shift()+" "+record.data.icon+'"></i>'+value;
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
							dataIndex:"description",
						}],
						selModel:new Ext.selection.CheckboxModel(),
						bbar:[
							new Ext.Button({
								iconCls:"fa fa-caret-up",
								handler:function() {
									Admin.gridSort(Ext.getCmp("ModuleSortList"),"sort","up");
								}
							}),
							new Ext.Button({
								iconCls:"fa fa-caret-down",
								handler:function() {
									Admin.gridSort(Ext.getCmp("ModuleSortList"),"sort","down");
								}
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:Admin.getText("button/confirm"),
						handler:function() {
							var updated = Ext.getCmp("ModuleSortList").getStore().getUpdatedRecords();
							for (var i=0, loop=updated.length;i<loop;i++) {
								for (var key in updated[i].data) {
									updated[i].data[key] = typeof updated[i].data[key] == "string" ? $.trim(updated[i].data[key]) : updated[i].data[key];
								}
								updated[i] = updated[i].data;
							}
							
							Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
							$.send(ENV.getProcessUrl("admin","@saveModuleSort"),{updated:JSON.stringify(updated)},function(result) {
								if (result.success == true) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										location.replace(location.href);
									}});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							});
						}
					}),
					new Ext.Button({
						text:Admin.getText("button/close"),
						handler:function() {
							Ext.getCmp("ModuleSortWindow").close();
						}
					})
				]
			}).show();
		},
		/**
		 * 모듈 설치/업데이트/설정
		 *
		 * @param string module 모듈명
		 */
		install:function(module) {
			Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
			
			Admin.modules.installReady = {};
			$(document).off("Admin.modules.installReady");
			
			$.send(ENV.getProcessUrl("admin","@getModuleConfigPanel"),{target:module},function(result) {
				if (result.success == true) {
					if (result.isLatest === true) {
						var type = "config";
					} else if (result.isInstalled == true) {
						var type = "update";
					} else {
						var type = "install";
					}
					
					if (result.panel == null) {
						$.send(ENV.getProcessUrl("admin","@installModule"),{target:module},function(result) {
							if (result.success == true) {
								Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("modules/lists/installed"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									if (type == "install") {
										location.replace(location.href);
									} else {
										Ext.getCmp("ModuleList").getStore().reload();
									}
								}});
							}
						});
					} else {
						/**
						 * 모듈 스크립트가 있다면 불러오기
						 */
						if (result.script != null && $("script[src='"+result.script+"']").length == 0) {
							var $script = $("<script>").attr("src",result.script);
							$("head").append($script);
						}
						
						/**
						 * 모듈 언어팩 불러오기
						 */
						if ($("script[src='"+result.language+"']").length == 0) {
							var $language = $("<script>").attr("src",result.language);
							$("head").append($language);
						}
						
						/**
						 * 패널처리
						 */
						var $panel = $("<div>").attr("data-role","config").attr("data-module",module);
						$panel.html(result.panel);
						
						$("body").append($panel);
						
						Ext.Msg.hide();
						
						new Ext.Window({
							id:"ModuleConfigsWindow",
							title:Admin.getText("modules/lists/window/"+type),
							modal:true,
							border:false,
							resizeable:false,
							autoScroll:true,
							items:[
								Ext.getCmp("ModuleConfigForm")
							],
							buttons:[
								new Ext.Button({
									text:Admin.getText("button/confirm"),
									handler:function() {
										Ext.getCmp("ModuleConfigForm").getForm().submit({
											url:ENV.getProcessUrl("admin","@installModule"),
											params:{target:module},
											submitEmptyText:false,
											waitTitle:Admin.getText("action/wait"),
											waitMsg:Admin.getText("modules/lists/installing"),
											success:function(form,action) {
												Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("modules/lists/installed"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													if (type == "install") {
														Ext.getCmp("ModuleConfigsWindow").close();
														location.replace(location.href);
													} else {
														Ext.getCmp("ModuleConfigsWindow").close();
														Ext.getCmp("ModuleList").getStore().reload();
													}
												}});
											},
											failure:function(form,action) {
												if (action.result && action.result.message) {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												} else {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											}
										});
									}
								}),
								new Ext.Button({
									text:Admin.getText("button/cancel"),
									handler:function() {
										Ext.getCmp("ModuleConfigsWindow").close();
									}
								})
							],
							listeners:{
								show:function() {
									Ext.getCmp("ModuleConfigForm").getForm().load({
										url:ENV.getProcessUrl("admin","@getModuleConfigs"),
										params:{target:module},
										waitTitle:Admin.getText("action/wait"),
										waitMsg:Admin.getText("action/loading"),
										success:function(form,action) {
										},
										failure:function(form,action) {
											if (action.result && action.result.message) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										}
									});
								},
								close:function() {
									$("div[data-role=config][data-module="+module+"]").remove();
								}
							}
						}).show();
					}
				}
			});
		}
	},
	/**
	 * 플러그인
	 */
	plugins:{
		/**
		 * 플러그인 기본정보
		 *
		 * @param string plugin 플러그인명
		 */
		show:function(plugin) {
			new Ext.Window({
				id:"PluginWindow",
				title:Admin.getText("action/wait"),
				width:800,
				height:600,
				modal:true,
				border:false,
				resizeable:false,
				autoScroll:true,
				items:[
					new Ext.form.Panel({
						id:"PluginForm",
						border:false,
						bodyPadding:10,
						fieldDefaults:{labelAlign:"right",labelWidth:80,anchor:"100%",allowBlank:true},
						items:[
							new Ext.form.FieldSet({
								title:Admin.getText("plugins/show/default"),
								items:[
									new Ext.form.FieldContainer({
										layout:"hbox",
										items:[
											new Ext.Panel({
												width:120,
												border:false,
												items:[
													new Ext.Panel({
														id:"PluginIcon",
														border:false,
														bodyCls:"im-icon-default",
														html:'<i class="mi mi-loading"></i>',
													}),
													new Ext.Button({
														id:"PluginInstallButton1",
														hidden:true,
														style:{width:"100%",marginTop:"5px",borderRadius:"3px"},
														handler:function() {
															Ext.getCmp("PluginWindow").close();
															Admin.plugins.install(plugin);
														}
													})
												]
											}),
											new Ext.form.FieldContainer({
												layout:"vbox",
												flex:1,
												fieldDefaults:{margin:0,padding:0},
												items:[
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("plugins/show/version"),
														name:"version"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("plugins/show/author"),
														name:"author"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("plugins/show/homepage"),
														name:"homepage"
													}),
													new Ext.form.DisplayField({
														fieldLabel:Admin.getText("plugins/show/language"),
														name:"language"
													})
												]
											})
										]
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Admin.getText("plugins/show/description"),
								items:[
									new Ext.form.DisplayField({
										name:"description"
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Admin.getText("plugins/show/functions"),
								items:[
									new Ext.form.CheckboxGroup({
										columns:2,
										items:[
											new Ext.form.Checkbox({
												name:"database",
												boxLabel:Admin.getText("plugins/show/database"),
												flex:1,
												readOnly:true
											}),
											new Ext.form.Checkbox({
												name:"admin",
												boxLabel:Admin.getText("plugins/show/admin"),
												flex:1,
												readOnly:false
											})
										]
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Admin.getText("plugins/show/dependencies"),
								items:[
									new Ext.form.CheckboxGroup({
										id:"PluginDependencies",
										columns:2,
										items:[]
									})
								]
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						id:"PluginInstallButton2",
						hidden:true,
						handler:function() {
							Ext.getCmp("PluginWindow").close();
							Admin.plugins.install(plugin);
						}
					}),
					new Ext.Button({
						text:Admin.getText("button/close"),
						handler:function() {
							Ext.getCmp("PluginWindow").close();
						}
					})
				],
				listeners:{
					show:function() {
						Ext.getCmp("PluginForm").getForm().load({
							url:ENV.getProcessUrl("admin","@getPlugin"),
							params:{target:plugin},
							waitTitle:Admin.getText("action/wait"),
							waitMsg:Admin.getText("action/loading"),
							success:function(form,action) {
								Ext.getCmp("PluginWindow").setTitle(action.result.data.title);
								$("#PluginIcon i.mi").removeClass("mi mi-loading").addClass(action.result.data.icon.substr(0,2)).addClass(action.result.data.icon);
								
								for (var i=0, loop=action.result.data.dependencies.length;i<loop;i++) {
									Ext.getCmp("PluginDependencies").add(
										new Ext.form.Checkbox({
											boxLabel:action.result.data.dependencies[i].name,
											checked:action.result.data.dependencies[i].checked,
											readOnly:true
										})
									);
								}
								
								Ext.getCmp("PluginInstallButton1").show();
								Ext.getCmp("PluginInstallButton2").show();
								if (action.result.data.isLatest == true) {
									Ext.getCmp("PluginInstallButton1").setText(Admin.getText("plugins/show/installed"));
									Ext.getCmp("PluginInstallButton1").disable();
									
									if (action.result.data.isConfigPanel == true) {
										Ext.getCmp("PluginInstallButton2").setText(Admin.getText("plugins/show/setting"));
									} else {
										Ext.getCmp("PluginInstallButton2").hide();
									}
								} else if (action.result.data.isInstalled == true) {
									Ext.getCmp("PluginInstallButton1").setText(Admin.getText("plugins/show/update"));
									Ext.getCmp("PluginInstallButton2").setText(Admin.getText("plugins/show/update"));
								} else {
									Ext.getCmp("PluginInstallButton1").setText(Admin.getText("plugins/show/install"));
									Ext.getCmp("PluginInstallButton2").setText(Admin.getText("plugins/show/install"));
								}
							},
							failure:function(form,action) {
								if (action.result && action.result.message) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						});
					}
				}
			}).show();
		},
		/**
		 * 플러그인순서변경
		 */
		sort:function() {
			new Ext.Window({
				id:"PluginSortWindow",
				title:Admin.getText("plugins/lists/sort_title"),
				width:800,
				height:600,
				modal:true,
				border:false,
				resizeable:false,
				layout:"fit",
				items:[
					new Ext.grid.Panel({
						id:"PluginSortList",
						border:true,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:ENV.getProcessUrl("admin","@getInstalledPlugins"),
								reader:{type:"json"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:0,
							groupDir:"DESC",
							fields:["id","plugin","icon","title","version","description",{name:"sort",type:"int"}],
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
							text:Admin.getText("plugins/lists/columns/title"),
							width:180,
							dataIndex:"title",
							renderer:function(value,p,record) {
								var icon = record.data.icon.split("-");
								
								return '<i class="icon '+icon.shift()+" "+record.data.icon+'"></i>'+value;
							}
						},{
							text:Admin.getText("plugins/lists/columns/version"),
							width:65,
							align:"center",
							dataIndex:"version"
						},{
							text:Admin.getText("plugins/lists/columns/description"),
							minWidth:150,
							flex:1,
							dataIndex:"description",
						}],
						selModel:new Ext.selection.CheckboxModel(),
						bbar:[
							new Ext.Button({
								iconCls:"fa fa-caret-up",
								handler:function() {
									Admin.gridSort(Ext.getCmp("PluginSortList"),"sort","up");
								}
							}),
							new Ext.Button({
								iconCls:"fa fa-caret-down",
								handler:function() {
									Admin.gridSort(Ext.getCmp("PluginSortList"),"sort","down");
								}
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:Admin.getText("button/confirm"),
						handler:function() {
							var updated = Ext.getCmp("PluginSortList").getStore().getUpdatedRecords();
							for (var i=0, loop=updated.length;i<loop;i++) {
								for (var key in updated[i].data) {
									updated[i].data[key] = typeof updated[i].data[key] == "string" ? $.trim(updated[i].data[key]) : updated[i].data[key];
								}
								updated[i] = updated[i].data;
							}
							
							Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
							$.send(ENV.getProcessUrl("admin","@savePluginSort"),{updated:JSON.stringify(updated)},function(result) {
								if (result.success == true) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("PluginSortWindow").close();
									}});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							});
						}
					}),
					new Ext.Button({
						text:Admin.getText("button/close"),
						handler:function() {
							Ext.getCmp("PluginSortWindow").close();
						}
					})
				]
			}).show();
		},
		/**
		 * 플러그인 설치/업데이트/설정
		 *
		 * @param string plugin 플러그인명
		 */
		install:function(plugin) {
			Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
			
			Admin.plugins.installReady = {};
			$(document).off("Admin.plugins.installReady");
			
			$.send(ENV.getProcessUrl("admin","@getPluginConfigPanel"),{target:plugin},function(result) {
				if (result.success == true) {
					if (result.isLatest === true) {
						var type = "config";
					} else if (result.isInstalled == true) {
						var type = "update";
					} else {
						var type = "install";
					}
					
					if (result.panel == null) {
						$.send(ENV.getProcessUrl("admin","@installPlugin"),{target:plugin},function(result) {
							if (result.success == true) {
								Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("plugins/lists/installed"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									if (type == "install") {
										location.replace(location.href);
									} else {
										Ext.getCmp("PluginList").getStore().reload();
									}
								}});
							}
						});
					} else {
						/**
						 * 플러그인 언어팩 불러오기
						 */
						if ($("script[src='"+result.language+"']").length == 0) {
							var $language = $("<script>").attr("src",result.language);
							$("head").append($language);
						}
						
						/**
						 * 패널처리
						 */
						var $panel = $("<div>").attr("data-role","config").attr("data-plugin",plugin);
						$panel.html(result.panel);
						
						$("body").append($panel);
						
						Ext.Msg.hide();
						
						new Ext.Window({
							id:"PluginConfigsWindow",
							title:Admin.getText("plugins/lists/window/"+type),
							modal:true,
							border:false,
							resizeable:false,
							autoScroll:true,
							items:[
								Ext.getCmp("PluginConfigForm")
							],
							buttons:[
								new Ext.Button({
									text:Admin.getText("button/confirm"),
									handler:function() {
										Ext.getCmp("PluginConfigForm").getForm().submit({
											url:ENV.getProcessUrl("admin","@installPlugin"),
											params:{target:plugin},
											submitEmptyText:false,
											waitTitle:Admin.getText("action/wait"),
											waitMsg:Admin.getText("plugins/lists/installing"),
											success:function(form,action) {
												Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("plugins/lists/installed"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													if (type == "install") {
														Ext.getCmp("PluginConfigsWindow").close();
														location.replace(location.href);
													} else {
														Ext.getCmp("PluginConfigsWindow").close();
														Ext.getCmp("PluginList").getStore().reload();
													}
												}});
											},
											failure:function(form,action) {
												if (action.result && action.result.message) {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												} else {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											}
										});
									}
								}),
								new Ext.Button({
									text:Admin.getText("button/cancel"),
									handler:function() {
										Ext.getCmp("PluginConfigsWindow").close();
									}
								})
							],
							listeners:{
								show:function() {
									Ext.getCmp("PluginConfigForm").getForm().load({
										url:ENV.getProcessUrl("admin","@getPluginConfigs"),
										params:{target:plugin},
										waitTitle:Admin.getText("action/wait"),
										waitMsg:Admin.getText("action/loading"),
										success:function(form,action) {
										},
										failure:function(form,action) {
											if (action.result && action.result.message) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										}
									});
								},
								close:function() {
									$("div[data-role=config][data-plugin="+plugin+"]").remove();
								}
							}
						}).show();
					}
				}
			});
		}
	},
	/**
	 * 사이트설정
	 */
	configs:{
		/**
		 * 사이트관리
		 */
		sites:{
			/**
			 * 사이트 추가/수정
			 *
			 * @param string domain 사이트도메인
			 * @param string language 사이트 언어셋
			 */
			add:function(domain,language) {
				var type = "add";
				if (domain && language) type = "modify";
				
				new Ext.Window({
					id:"SiteConfigWindow",
					title:Admin.getText("configs/sites/window/"+type),
					width:800,
					modal:true,
					border:false,
					resizeable:false,
					autoScroll:true,
					items:[
						new Ext.form.Panel({
							id:"SiteConfigForm",
							border:false,
							bodyPadding:10,
							fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
							items:[
								new Ext.form.Hidden({
									name:"oDomain",
									value:domain ? domain : ""
								}),
								new Ext.form.Hidden({
									name:"oLanguage",
									value:language ? language : ""
								}),
								new Ext.form.FieldSet({
									title:Admin.getText("configs/sites/form/site_config"),
									items:[
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sites/form/domain"),
											layout:"hbox",
											items:[
												new Ext.form.ComboBox({
													name:"is_https",
													store:new Ext.data.ArrayStore({
														fields:["display","value"],
														data:[["http://","FALSE"],["https://","TRUE"]]
													}),
													displayField:"display",
													valueField:"value",
													value:"FALSE",
													width:100,
													margin:"0 5 0 0"
												}),
												new Ext.form.TextField({
													name:"domain",
													flex:1
												})
											]
										}),
										new Ext.form.TextField({
											fieldLabel:Admin.getText("configs/sites/form/alias"),
											name:"alias",
											emptyText:"*.examples.kr,beta.examples.kr,etc.com",
											allowBlank:true,
											afterBodyEl:'<div class="x-form-help">'+Admin.getText("configs/sites/form/alias_help")+'</div>'
										}),
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sites/form/member").label,
											name:"member",
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[[Admin.getText("configs/sites/form/member/UNIVERSAL"),"UNIVERSAL"],[Admin.getText("configs/sites/form/member/UNIQUE"),"UNIQUE"]]
											}),
											displayField:"display",
											valueField:"value",
											value:"UNIVERSAL"
										})
									]
								}),
								new Ext.form.FieldSet({
									title:Admin.getText("configs/sites/form/language_config"),
									items:[
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("text/language_code"),
											layout:"hbox",
											items:[
												new Ext.form.TextField({
													name:"language",
													width:50,
													length:2,
													minLength:2,
													maxLength:2,
													style:{marginRight:"5px"}
												}),
												new Ext.Button({
													text:Admin.getText("button/language_search"),
													handler:function() {
														window.open("http://www.mcanerin.com/en/articles/meta-language.asp");
													}
												}),
												new Ext.form.Checkbox({
													name:"is_default",
													boxLabel:Admin.getText("configs/sites/form/is_default"),
													style:{marginLeft:"5px"}
												}),
												new Ext.form.DisplayField({
													flex:1,
													value:Admin.getText("text/language_code_help"),
													style:{textAlign:"right"}
												})
											]
										}),
										Admin.templetField(Admin.getText("configs/sites/form/templet"),"templet","core","site",false),
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sites/form/title"),
											layout:"hbox",
											items:[
												new Ext.form.TextField({
													name:"title",
													flex:1
												}),
												new Ext.form.Checkbox({
													boxLabel:Admin.getText("configs/sites/form/apply_all_site"),
													name:"title_all",
													style:{marginLeft:"5px"}
												})
											]
										}),
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sites/form/description"),
											layout:"hbox",
											items:[
												new Ext.form.TextArea({
													name:"description",
													flex:1,
													allowBlank:true,
													margin:"0 5 5 0",
												}),
												new Ext.form.Checkbox({
													boxLabel:Admin.getText("configs/sites/form/apply_all_site"),
													name:"description_all"
												})
											]
										}),
										Admin.configs.sites.getSiteImageField("logo_default"),
										Admin.configs.sites.getSiteImageField("logo_footer"),
										Admin.configs.sites.getSiteImageField("emblem"),
										Admin.configs.sites.getSiteImageField("favicon"),
										Admin.configs.sites.getSiteImageField("image")
									]
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:Admin.getText("button/confirm"),
							handler:function() {
								Ext.getCmp("SiteConfigForm").getForm().submit({
									url:ENV.getProcessUrl("admin","@saveSite"),
									submitEmptyText:false,
									waitTitle:Admin.getText("action/wait"),
									waitMsg:Admin.getText("action/saving"),
									success:function(form,action) {
										Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											Ext.getCmp("SiteConfigWindow").close();
											Ext.getCmp("SiteList").getStore().reload();
										}});
									},
									failure:function(form,action) {
										if (action.result) {
											if (action.result.message) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									}
								});
							}
						}),
						new Ext.Button({
							text:Admin.getText("button/cancel"),
							handler:function() {
								Ext.getCmp("SiteConfigWindow").close();
							}
						})
					],
					listeners:{
						show:function() {
							if (type == "modify") {
								Ext.getCmp("SiteConfigForm").getForm().findField("templet").setValue(null);
								Ext.getCmp("SiteConfigForm").getForm().load({
									url:ENV.getProcessUrl("admin","@getSite"),
									params:{domain:domain,language:language},
									waitTitle:Admin.getText("action/wait"),
									waitMsg:Admin.getText("action/loading"),
									success:function(form,action) {
										Ext.getCmp("SiteConfigForm").getForm().findField("logo_default").defaultImage = Ext.getCmp("SiteConfigForm").getForm().findField("logo_default").getValue();
										Ext.getCmp("SiteConfigForm").getForm().findField("logo_footer").defaultImage = Ext.getCmp("SiteConfigForm").getForm().findField("logo_footer").getValue();
										Ext.getCmp("SiteConfigForm").getForm().findField("emblem").defaultImage = Ext.getCmp("SiteConfigForm").getForm().findField("emblem").getValue();
										Ext.getCmp("SiteConfigForm").getForm().findField("favicon").defaultImage = Ext.getCmp("SiteConfigForm").getForm().findField("favicon").getValue();
										Ext.getCmp("SiteConfigForm").getForm().findField("image").defaultImage = Ext.getCmp("SiteConfigForm").getForm().findField("image").getValue();
									},
									failure:function(form,action) {
										if (action.result && action.result.message) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
										Ext.getCmp("SitemapConfigWindow").close();
									}
								});
							}
						}
					}
				}).show();
			},
			/**
			 * 사이트템플릿의 환경설정을 가져와 사이트 추가 / 수정 폼에 추가한다.
			 *
			 * @param string templet 템플릿명
			 */
			getTempletConfigs:function(domain,language,templet) {
				$.send(ENV.getProcessUrl("admin","@getSiteTempletConfigs"),{domain:domain,language:language,templet:templet},function(result) {
					if (result.success == true) {
						Admin.setTempletConfigs("SiteConfigTempletConfigs","@templet_configs-",result.configs,true);
					}
				});
			},
			/**
			 * 사이트 추가/수정시 필요한 이미지 업로드 폼을 가져온다.
			 *
			 * @param string name 필드명
			 * @param string type 이미지크기 타입 (square : 정사각형, horizontal : 가로형, vertical : 세로형)
			 * @param string placeholder(옵션) 업로드창에 보일 설명
			 * @param string applyAll(옵션) 전체적용 체크박스 (null : 전체적용없음(기본), site : 동일도메인 전체적용, templet : 동일템플릿 전체적용)
			 * @param string extension(옵션) 이미지 확장자 타입 (기본 : image/gif,image/jpg,image/jpeg,image/png)
			 * @return Field field
			 */
			getSiteImageField:function(name) {
				var previewWidth = 0;
				var emptyImage = ENV.DIR+"/modules/admin/images/empty_";
				if (name == "logo_default" || name == "image") {
					emptyImage+= "horizontal.png";
					previewWidth = 130;
				} else {
					emptyImage+= "square.png";
					previewWidth = 60;
				}
				
				var defaultImage = ENV.DIR+"/images/logo/";
				if (name == "logo_default") {
					defaultImage+= "default.png";
				} else if (name == "logo_footer") {
					defaultImage+= "footer.png";
				} else if (name == "favicon") {
					defaultImage+= name+".ico";
				} else {
					defaultImage+= name+".png";
				}
				
				var extension = "image/gif,image/jpg,image/jpeg,image/png";
				if (name == "favicon") {
					var extension = "image/x-icon";
				}
				
				return new Ext.form.FieldContainer({
					fieldLabel:Admin.getText("configs/sites/form/"+name),
					layout:"hbox",
					items:[
						new Ext.form.Hidden({
							name:name,
							defaultImage:emptyImage,
							listeners:{
								change:function(form,value) {
									if (emptyImage == value) {
										Ext.getCmp(form.name+"_preview").setBodyStyle("background","url("+emptyImage+") no-repeat 0 50%");
										Ext.getCmp(form.name+"_preview").setBodyStyle("backgroundSize","contain");
										$("#"+form.name+"_preview-innerCt").css("background","transparent");
									} else {
										Ext.getCmp(form.name+"_preview").setBodyStyle("background","url("+ENV.DIR+"/modules/admin/images/transparent_pattern.png)");
										$("#"+form.name+"_preview-innerCt").css("background","url("+value+") no-repeat 50% 50%").css("backgroundSize","contain");
									}
								}
							}
						}),
						new Ext.Panel({
							id:name+"_preview",
							border:false,
							padding:0,
							width:previewWidth,
							height:60,
							style:{marginRight:"5px"},
							listeners:{
								render:function(panel) {
									panel.setBodyStyle("background","url("+emptyImage+") no-repeat 0 50%");
									panel.setBodyStyle("backgroundSize","contain");
									$("#"+panel.getId()+"-innerCt").css("background","transparent");
								}
							}
						}),
						new Ext.form.FieldContainer({
							flex:1,
							layout:{type:"vbox",align:"stretch"},
							items:[
								new Ext.form.FileUploadField({
									name:name+"_file",
									buttonText:Admin.getText("configs/sites/form/select_file"),
									allowBlank:true,
									clearOnSubmit:false,
									accept:extension,
									emptyText:Admin.getText("configs/sites/form/"+name+"_help"),
									style:{marginBottom:0},
									listeners:{
										change:function(form,value) {
											var name = form.name.split("_");
											name.pop();
											name = name.join("_");
											if (value) {
												Ext.getCmp("SiteConfigForm").getForm().findField(name+"_reset").setValue(false);
												Ext.getCmp("SiteConfigForm").getForm().findField(name+"_default").setValue(false);
											}
										}
									}
								}),
								new Ext.form.FieldContainer({
									layout:"hbox",
									items:[
										new Ext.form.Checkbox({
											name:name+"_reset",
											boxLabel:Admin.getText("configs/sites/form/reset_file"),
											flex:1,
											listeners:{
												change:function(form,value) {
													var name = form.name.split("_");
													name.pop();
													name = name.join("_");
													Ext.getCmp("SiteConfigForm").getForm().findField(name+"_default").setValue(false);
													Ext.getCmp("SiteConfigForm").getForm().findField(name+"_default").setDisabled(value);
													if (value == true) {
														Ext.getCmp("SiteConfigForm").getForm().findField(name+"_file").reset();
														Ext.getCmp("SiteConfigForm").getForm().findField(name).setValue(emptyImage);
													} else {
														Ext.getCmp("SiteConfigForm").getForm().findField(name).setValue(Ext.getCmp("SiteConfigForm").getForm().findField(name).defaultImage);
													}
												}
											}
										}),
										new Ext.form.Checkbox({
											name:name+"_default",
											boxLabel:Admin.getText("configs/sites/form/default_file"),
											flex:1,
											listeners:{
												change:function(form,value) {
													var name = form.name.split("_");
													name.pop();
													name = name.join("_");
													Ext.getCmp("SiteConfigForm").getForm().findField(name+"_reset").setValue(false);
													Ext.getCmp("SiteConfigForm").getForm().findField(name+"_reset").setDisabled(value);
													if (value == true) {
														Ext.getCmp("SiteConfigForm").getForm().findField(name+"_file").reset();
														Ext.getCmp("SiteConfigForm").getForm().findField(name).setValue(defaultImage);
													} else {
														Ext.getCmp("SiteConfigForm").getForm().findField(name).setValue(Ext.getCmp("SiteConfigForm").getForm().findField(name).defaultImage);
													}
												}
											}
										})
									]
								})
							]
						}),
						new Ext.form.Checkbox({
							boxLabel:Admin.getText("configs/sites/form/apply_all_site"),
							name:name+"_all",
							style:{marginLeft:"5px"}
						})
					]
				});
			}
		},
		/**
		 * 사이트맵 관리
		 */
		sitemap:{
			/**
			 * 사이트 1차 메뉴를 추가/수정한다.
			 *
			 * @param string menu(옵션) 메뉴명
			 */
			menu:function(menu) {
				Admin.configs.sitemap.add("menu",menu);
			},
			/**
			 * 사이트 2차 메뉴를 추가/수정한다.
			 *
			 * @param string page(옵션) 2차 메뉴명
			 */
			page:function(page) {
				Admin.configs.sitemap.add("page",page);
			},
			/**
			 * 사이트 2차메뉴에 그룹을 추가/수정한다.
			 *
			 * @param string group(옵션) 그룹고유값
			 */
			group:function(group) {
				/**
				 * 선택된 사이트 정보를 가져온다.
				 */
				var site = Ext.getCmp("SiteList").getValue().split("@");
				
				var domain = site[0];
				var language = site[1];
				
				var menu = Ext.getCmp("MenuList").getSelection().shift().data.menu;
				var group = group ? group : "";
				
				new Ext.Window({
					id:"SitemapConfigWindow",
					title:(group ? Admin.getText("configs/sitemap/window/group_modify") : Admin.getText("configs/sitemap/window/group_add")),
					width:500,
					modal:true,
					border:false,
					resizeable:false,
					autoScroll:true,
					items:[
						new Ext.form.Panel({
							id:"SitemapConfigForm",
							border:false,
							bodyPadding:"10 10 5 10",
							fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
							items:[
								new Ext.form.Hidden({
									name:"domain",
									value:domain
								}),
								new Ext.form.Hidden({
									name:"language",
									value:language
								}),
								new Ext.form.Hidden({
									name:"menu",
									value:menu,
									allowBlank:true
								}),
								new Ext.form.Hidden({
									name:"group",
									value:group,
									allowBlank:true
								}),
								new Ext.form.TextField({
									name:"title",
									emptyText:Admin.getText("configs/sitemap/form/group_title")
								}),
								new Ext.form.FieldContainer({
									layout:"hbox",
									items:[
										new Ext.form.ComboBox({
											name:"icon_type",
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:[["FontAwesome","fa"],["XEIcon","xi"],["XEIcon2","xi2"],["Icon Image","image"]]
											}),
											allowBlank:true,
											displayField:"display",
											valueField:"value",
											value:"",
											width:140,
											margin:"0 5 0 0",
											emptyText:Admin.getText("configs/sitemap/form/icon_type"),
											listeners:{
												change:function(form,value) {
													if (value) {
														Ext.getCmp("SitemapConfigForm").getForm().findField("icon").enable();
														Ext.getCmp("SitemapConfigForm").getForm().findField("icon").setEmptyText(Admin.getText("configs/sitemap/form/icon_"+value+"_help"));
														if (value != "image") Ext.getCmp("SitemapIconSearchButton").show();
														else Ext.getCmp("SitemapIconSearchButton").hide();
													} else {
														Ext.getCmp("SitemapConfigForm").getForm().findField("icon").disable();
														Ext.getCmp("SitemapConfigForm").getForm().findField("icon").setEmptyText(Admin.getText("configs/sitemap/form/icon_help"));
													}
												}
											}
										}),
										new Ext.form.TextField({
											name:"icon",
											flex:1,
											allowBlank:true,
											disabled:true,
											emptyText:Admin.getText("configs/sitemap/form/icon_help")
										}),
										new Ext.Button({
											id:"SitemapIconSearchButton",
											style:{marginLeft:"5px"},
											hidden:true,
											text:Admin.getText("configs/sitemap/form/icon_search"),
											handler:function() {
												var iconType = Ext.getCmp("SitemapConfigForm").getForm().findField("icon_type").getValue();
												if (iconType == "fa") window.open("https://fontawesome.com/v4.7.0/icons/");
												if (iconType == "xi") window.open("https://xpressengine.github.io/XEIcon/library-1.0.4.html");
												if (iconType == "xi2") window.open("https://xpressengine.github.io/XEIcon/library-2.3.3.html");
											}
										})
									],
									afterBodyEl:'<div class="x-form-help">' + Admin.getText("configs/sitemap/form/group_help") + '</div>'
								})
							],
							buttons:[
								new Ext.Button({
									text:Admin.getText("button/confirm"),
									handler:function() {
										Ext.getCmp("SitemapConfigForm").getForm().submit({
											url:ENV.getProcessUrl("admin","@saveGroup"),
											submitEmptyText:false,
											waitTitle:Admin.getText("action/wait"),
											waitMsg:Admin.getText("action/saving"),
											success:function(form,action) {
												Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
													var group = action.result.group;
													Ext.getCmp("PageList").selected = "^"+group;
													Ext.getCmp("PageList").getStore().reload();
													Ext.getCmp("SitemapConfigWindow").close();
												}});
											},
											failure:function(form,action) {
												if (action.result) {
													if (action.result.message) {
														Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													} else {
														Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													}
												} else {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											}
										});
									}
								}),
								new Ext.Button({
									text:Admin.getText("button/cancel"),
									handler:function() {
										Ext.getCmp("SitemapConfigWindow").close();
									}
								})
							]
						})
					],
					listeners:{
						show:function() {
							if (group) {
								Ext.getCmp("SitemapConfigForm").getForm().load({
									url:ENV.getProcessUrl("admin","@getMenu"),
									params:{domain:domain,language:language,menu:menu,page:"^"+group},
									waitTitle:Admin.getText("action/wait"),
									waitMsg:Admin.getText("action/loading"),
									success:function(form,action) {
									},
									failure:function(form,action) {
										if (action.result && action.result.message) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
										Ext.getCmp("SitemapConfigWindow").close();
									}
								});
							}
						}
					}
				}).show();
			},
			/**
			 * 사이트 1차 메뉴를 다른 사이트로 부터 불러온다.
			 */
			loadMenu:function() {
				Admin.configs.sitemap.load("menu");
			},
			/**
			 * 사이트 2차 메뉴를 다른 사이트 또는 다른 메뉴로 부터 불러온다.
			 */
			loadPage:function() {
				Admin.configs.sitemap.load("page");
			},
			/**
			 * 사이트 메뉴를 추가/수정한다.
			 *
			 * @param string mode 추가할 메뉴종류 (menu : 1차 메뉴, page : 2차 메뉴)
			 * @param string menu(옵션) 메뉴명
			 */
			add:function(mode,code) {
				/**
				 * 선택된 사이트 정보를 가져온다.
				 */
				var site = Ext.getCmp("SiteList").getValue().split("@");
				
				var domain = site[0];
				var language = site[1];
				
				var menu = "";
				var page = "";
				if (mode == "menu" && code) {
					menu = code;
				} else if (mode == "page") {
					menu = Ext.getCmp("MenuList").getSelection().shift().data.menu;
					page = code ? code : "";
				}
				var url = Ext.getCmp("SiteList").getRawValue().match(/\(([^\(\)]+)\)$/).pop();
				
				if (mode == "page") url+= menu+"/";
				
				new Ext.Window({
					id:"SitemapConfigWindow",
					title:(code ? Admin.getText("configs/sitemap/window/modify") : Admin.getText("configs/sitemap/window/add")),
					width:800,
					modal:true,
					border:false,
					resizeable:false,
					autoScroll:true,
					items:[
						new Ext.form.Panel({
							id:"SitemapConfigForm",
							border:false,
							bodyPadding:"10 10 5 10",
							fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
							items:[
								new Ext.form.Hidden({
									name:"domain",
									value:domain
								}),
								new Ext.form.Hidden({
									name:"language",
									value:language
								}),
								new Ext.form.Hidden({
									name:"mode",
									value:mode
								}),
								new Ext.form.Hidden({
									name:"oMenu",
									value:menu,
									allowBlank:true
								}),
								new Ext.form.Hidden({
									name:"oPage",
									value:page,
									allowBlank:true
								}),
								new Ext.form.FieldSet({
									title:Admin.getText("configs/sitemap/form/default"),
									items:[
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/menu"),
											layout:"hbox",
											disabled:(mode == "page"),
											hidden:(mode == "page"),
											items:[
												new Ext.form.DisplayField({
													value:url
												}),
												new Ext.form.TextField({
													name:"menu",
													flex:1
												})
											],
											afterBodyEl:'<div class="x-form-help">'+Admin.getText("configs/sitemap/form/menu_help")+'</div>'
										}),
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/page"),
											layout:"hbox",
											disabled:(mode == "menu"),
											hidden:(mode == "menu"),
											items:[
												new Ext.form.DisplayField({
													value:url
												}),
												new Ext.form.TextField({
													name:"page",
													flex:1
												})
											],
											afterBodyEl:'<div class="x-form-help">'+Admin.getText("configs/sitemap/form/page_help")+'</div>'
										}),
										new Ext.form.TextField({
											fieldLabel:(mode == "menu" ? Admin.getText("configs/sitemap/form/menu_title") : Admin.getText("configs/sitemap/form/page_title")),
											name:"title"
										}),
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/icon"),
											layout:"hbox",
											items:[
												new Ext.form.ComboBox({
													name:"icon_type",
													store:new Ext.data.ArrayStore({
														fields:["display","value"],
														data:[["FontAwesome","fa"],["XEIcon","xi"],["XEIcon2","xi2"],["Icon Image","image"]]
													}),
													allowBlank:true,
													displayField:"display",
													valueField:"value",
													value:"",
													width:140,
													margin:"0 5 0 0",
													emptyText:Admin.getText("configs/sitemap/form/icon_type"),
													listeners:{
														change:function(form,value) {
															if (value) {
																Ext.getCmp("SitemapConfigForm").getForm().findField("icon").enable();
																Ext.getCmp("SitemapConfigForm").getForm().findField("icon").setEmptyText(Admin.getText("configs/sitemap/form/icon_"+value+"_help"));
																if (value != "image") Ext.getCmp("SitemapIconSearchButton").show();
																else Ext.getCmp("SitemapIconSearchButton").hide();
															} else {
																Ext.getCmp("SitemapConfigForm").getForm().findField("icon").disable();
																Ext.getCmp("SitemapConfigForm").getForm().findField("icon").setEmptyText(Admin.getText("configs/sitemap/form/icon_help"));
															}
														}
													}
												}),
												new Ext.form.TextField({
													name:"icon",
													flex:1,
													allowBlank:true,
													disabled:true,
													emptyText:Admin.getText("configs/sitemap/form/icon_help")
												}),
												new Ext.Button({
													id:"SitemapIconSearchButton",
													style:{marginLeft:"5px"},
													hidden:true,
													text:Admin.getText("configs/sitemap/form/icon_search"),
													handler:function() {
														var iconType = Ext.getCmp("SitemapConfigForm").getForm().findField("icon_type").getValue();
														if (iconType == "fa") window.open("https://fontawesome.com/v4.7.0/icons/");
														if (iconType == "xi") window.open("https://xpressengine.github.io/XEIcon/library-1.0.4.html");
														if (iconType == "xi2") window.open("https://xpressengine.github.io/XEIcon/library-2.3.3.html");
													}
												})
											]
										}),
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/type"),
											name:"type",
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:(function(mode) {
													var datas = [];
													for (var type in Admin.getText("configs/sitemap/type")) {
														if (mode == "menu" && type == "MODULE") continue;
														
														datas.push([Admin.getText("configs/sitemap/type/"+type),type]);
													}
													return datas;
												})(mode)
											}),
											displayField:"display",
											valueField:"value",
											emptyText:Admin.getText("configs/sitemap/form/type_help"),
											listeners:{
												change:function(form,value) {
													Ext.getCmp("SitemapConfigContext-MODULE").hide().disable();
													Ext.getCmp("SitemapConfigContext-EXTERNAL").hide().disable();
													Ext.getCmp("SitemapConfigContext-PAGE").hide().disable();
													Ext.getCmp("SitemapConfigContext-WIDGET").hide().disable();
													Ext.getCmp("SitemapConfigContext-LINK").hide().disable();
													
													if (value != "EMPTY" && value != "HTML") Ext.getCmp("SitemapConfigContext-"+value).show().enable();
													
													if (value == "LINK") {
														Ext.getCmp("SitemapConfigDesign").setDisabled(true).setHidden(true);
														Ext.getCmp("SitemapConfigHeader").setDisabled(true).setHidden(true);
														Ext.getCmp("SitemapConfigFooter").setDisabled(true).setHidden(true);
													} else {
														Ext.getCmp("SitemapConfigDesign").setDisabled(false).setHidden(false);
														Ext.getCmp("SitemapConfigHeader").setDisabled(false).setHidden(false);
														Ext.getCmp("SitemapConfigFooter").setDisabled(false).setHidden(false);
														
														if (value == "PAGE") {
															Ext.getCmp("SitemapConfigForm").getForm().findField("subpage").getStore().load();
															Ext.getCmp("SitemapConfigForm").getForm().findField("layout").setDisabled(true).setHidden(true);
															
															Ext.getCmp("SitemapConfigHeader").setDisabled(true).setHidden(true);
															Ext.getCmp("SitemapConfigFooter").setDisabled(true).setHidden(true);
														} else {
															Ext.getCmp("SitemapConfigForm").getForm().findField("layout").setDisabled(false).setHidden(false);
															
															Ext.getCmp("SitemapConfigHeader").setDisabled(false).setHidden(false);
															Ext.getCmp("SitemapConfigFooter").setDisabled(false).setHidden(false);
														}
													}
												}
											}
										}),
										new Ext.form.Checkbox({
											fieldLabel:Admin.getText("configs/sitemap/form/is_footer"),
											name:"is_footer",
											boxLabel:Admin.getText("configs/sitemap/form/is_footer_help")
										}),
										new Ext.form.Checkbox({
											fieldLabel:Admin.getText("configs/sitemap/form/is_hide"),
											name:"is_hide",
											boxLabel:Admin.getText("configs/sitemap/form/is_hide_help")
										}),
										Admin.permissionField(Admin.getText("configs/sitemap/form/permission"),"permission","true",true,Admin.getText("configs/sitemap/form/permission_help")),
									]
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigDesign",
									title:Admin.getText("configs/sitemap/form/design"),
									items:[
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/layout"),
											name:"layout",
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													url:ENV.getProcessUrl("admin","@getSiteTempletLayouts"),
													extraParams:{domain:domain,language:language},
													reader:{type:"json"}
												},
												autoLoad:true,
												remoteSort:false,
												fields:["layout","description"]
											}),
											displayField:"description",
											valueField:"layout"
										}),
										new Ext.form.TextField({
											fieldLabel:Admin.getText("configs/sitemap/form/description"),
											name:"description",
											allowBlank:true
										}),
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/image"),
											layout:"hbox",
											items:[
												new Ext.form.FileUploadField({
													name:"image",
													allowBlank:true,
													accept:"image/*",
													flex:1,
													emptyText:Admin.getText("configs/sitemap/form/image_help"),
													buttonText:Admin.getText("configs/sitemap/form/image_select")
												}),
												new Ext.form.Checkbox({
													name:"image_delete",
													boxLabel:Admin.getText("configs/sitemap/form/image_delete"),
													style:{marginLeft:"5px"},
													hidden:true,
													disabled:true,
													listeners:{
														change:function(form,checked) {
															form.getForm().findField("image").setDisabled(checked);
														}
													}
												})
											]
										})
									]
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigHeader",
									title:Admin.getText("configs/sitemap/form/header"),
									items:[
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/header_type"),
											name:"header_type",
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:(function() {
													var datas = [];
													for (var type in Admin.getText("configs/sitemap/header_type")) {
														datas.push([Admin.getText("configs/sitemap/header_type/"+type),type]);
													}
													
													return datas;
												})()
											}),
											displayField:"display",
											valueField:"value",
											value:"NONE",
											listeners:{
												change:function(form,value) {
													Ext.getCmp("SitemapConfigHeader-EXTERNAL").disable().hide();
													Ext.getCmp("SitemapConfigHeader-TEXT").disable().hide();
													
													if (value != "NONE") {
														Ext.getCmp("SitemapConfigHeader-"+value).enable().show();
														Ext.getCmp("SitemapConfigHeader-"+value).reset();
													}
												}
											},
											afterBodyEl:'<div class="x-form-help">' + Admin.getText("configs/sitemap/form/header_help") + '</div>'
										}),
										new Ext.form.ComboBox({
											id:"SitemapConfigHeader-EXTERNAL",
											fieldLabel:Admin.getText("configs/sitemap/form/header_external"),
											name:"header_external",
											hidden:true,
											disabled:true,
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													url:ENV.getProcessUrl("admin","@getExternals"),
													reader:{type:"json"}
												},
												autoLoad:true,
												remoteSort:false,
												sorters:[{property:"path",direction:"ASC"}],
												fields:["path","display"]
											}),
											displayField:"display",
											valueField:"path",
											afterBodyEl:'<div class="x-form-help">' + Admin.getText("configs/sitemap/form/header_external_help") + '</div>'
										}),
										Admin.wysiwygField(Admin.getText("configs/sitemap/form/content"),"header_text",{id:"SitemapConfigHeader-TEXT",hidden:true,disabled:true})
									]
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigFooter",
									title:Admin.getText("configs/sitemap/form/footer"),
									items:[
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/footer_type"),
											name:"footer_type",
											store:new Ext.data.ArrayStore({
												fields:["display","value"],
												data:(function() {
													var datas = [];
													for (var type in Admin.getText("configs/sitemap/footer_type")) {
														datas.push([Admin.getText("configs/sitemap/footer_type/"+type),type]);
													}
													
													return datas;
												})()
											}),
											displayField:"display",
											valueField:"value",
											value:"NONE",
											listeners:{
												change:function(form,value) {
													Ext.getCmp("SitemapConfigFooter-EXTERNAL").disable().hide();
													Ext.getCmp("SitemapConfigFooter-TEXT").disable().hide();
													
													if (value != "NONE") {
														Ext.getCmp("SitemapConfigFooter-"+value).enable().show();
														Ext.getCmp("SitemapConfigFooter-"+value).reset();
													}
												}
											},
											afterBodyEl:'<div class="x-form-help">' + Admin.getText("configs/sitemap/form/footer_help") + '</div>'
										}),
										new Ext.form.ComboBox({
											id:"SitemapConfigFooter-EXTERNAL",
											fieldLabel:Admin.getText("configs/sitemap/form/footer_external"),
											name:"footer_external",
											hidden:true,
											disabled:true,
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													url:ENV.getProcessUrl("admin","@getExternals"),
													reader:{type:"json"}
												},
												autoLoad:true,
												remoteSort:false,
												sorters:[{property:"path",direction:"ASC"}],
												fields:["path","display"]
											}),
											displayField:"display",
											valueField:"path",
											afterBodyEl:'<div class="x-form-help"> ' + Admin.getText("configs/sitemap/form/footer_external_help") + '</div>'
										}),
										Admin.wysiwygField(Admin.getText("configs/sitemap/form/content"),"footer_text",{id:"SitemapConfigFooter-TEXT",hidden:true,disabled:true})
									]
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigContext-MODULE",
									title:Admin.getText("configs/sitemap/form/context"),
									items:[
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/module"),
											layout:"hbox",
											items:[
												new Ext.form.ComboBox({
													name:"target",
													store:new Ext.data.JsonStore({
														proxy:{
															type:"ajax",
															url:ENV.getProcessUrl("admin","@getContextModules"),
															reader:{type:"json"}
														},
														autoLoad:true,
														remoteSort:false,
														sorters:[{property:"module",direction:"ASC"}],
														fields:["module","title"]
													}),
													displayField:"title",
													valueField:"module",
													flex:1,
													listeners:{
														change:function(form,value) {
															Ext.getCmp("SitemapConfigForm").getForm().findField("context").getStore().getProxy().setExtraParam("target",value);
															Ext.getCmp("SitemapConfigForm").getForm().findField("context").getStore().load();
														}
													}
												})
											]
										}),
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/context"),
											layout:"hbox",
											items:[
												new Ext.form.ComboBox({
													name:"context",
													disabled:true,
													_configs:{},
													store:new Ext.data.JsonStore({
														proxy:{
															type:"ajax",
															simpleSortMode:true,
															url:ENV.getProcessUrl("admin","@getModuleContexts"),
															extraParams:{target:""},
															reader:{type:"json"}
														},
														remoteSort:false,
														sorters:[{property:"module",direction:"ASC"}],
														fields:["context","title"],
														listeners:{
															load:function(store,records,success,e) {
																Ext.getCmp("SitemapConfigForm").getForm().findField("context").reset();
																
																if (success == true) {
																	Ext.getCmp("SitemapConfigForm").getForm().findField("context").enable();
																} else {
																	if (e.getError()) {
																		Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																	} else {
																		Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																	}
																	Ext.getCmp("SitemapConfigForm").getForm().findField("target").reset();
																	Ext.getCmp("SitemapConfigForm").getForm().findField("context").disable();
																}
															}
														}
													}),
													displayField:"title",
													valueField:"context",
													queryMode:"local",
													editable:true,
													anyMatch:true,
													forceSelection:true,
													emptyText:"컨텍스트명 일부를 입력후 검색된 컨텍스트를 선택하거나 우측의 화살표를 눌러 전체 컨텍스트목록을 확인할 수 있습니다.",
													flex:1,
													listeners:{
														change:function(form,value) {
															Ext.getCmp("SitemapConfigContextConfigs").hide();
															Ext.getCmp("SitemapConfigContextConfigs").removeAll();
															
															if (value) {
																$.ajax({
																	type:"POST",
																	url:ENV.getProcessUrl("admin","@getModuleContextConfigs"),
																	data:{domain:domain,language:language,menu:menu,page:form.getForm().findField("page").getValue(),target:form.getStore().getProxy().extraParams.target,context:value},
																	dataType:"json",
																	success:function(result) {
																		if (result.success == true) {
																			Ext.getCmp("SitemapConfigContextConfigs").hide();
																			Ext.getCmp("SitemapConfigContextConfigs").removeAll();
																			
																			for (var i=0, loop=result.configs.length;i<loop;i++) {
																				if (result.configs[i].type == "templet") {
																					Ext.getCmp("SitemapConfigContextConfigs").add(Admin.templetField(result.configs[i].title,"@"+result.configs[i].name,"module",result.configs[i].target,result.configs[i].use_default));
																					form.getForm().findField("@"+result.configs[i].name).setValue(result.configs[i].value);
																				}
																				
																				if (result.configs[i].type == "select") {
																					Ext.getCmp("SitemapConfigContextConfigs").add(
																						new Ext.form.ComboBox({
																							fieldLabel:result.configs[i].title,
																							name:"@"+result.configs[i].name,
																							store:new Ext.data.ArrayStore({
																								fields:["value","display"],
																								data:result.configs[i].data
																							}),
																							displayField:"display",
																							valueField:"value",
																							value:form._configs[result.configs[i].name] ? form._configs[result.configs[i].name] : result.configs[i].value
																						})
																					);
																				}
																			}
																			
																			if (Ext.getCmp("SitemapConfigContextConfigs").items.length > 0) {
																				Ext.getCmp("SitemapConfigContextConfigs").show();
																			}
																		}
																	}
																});
															}
														}
													}
												})
											]
										}),
										new Ext.form.FieldContainer({
											id:"SitemapConfigContextConfigs",
											layout:{type:"vbox",align:"stretch"},
											style:{marginBottom:"0px"},
											items:[]
										})
									],
									listeners:{
										afterlayout:function() {
											Ext.getCmp("SitemapConfigWindow").center();
										}
									}
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigContext-EXTERNAL",
									title:Admin.getText("configs/sitemap/form/context"),
									items:[
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/external"),
											name:"external",
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													url:ENV.getProcessUrl("admin","@getExternals"),
													reader:{type:"json"}
												},
												autoLoad:true,
												remoteSort:false,
												sorters:[{property:"path",direction:"ASC"}],
												fields:["path","display"]
											}),
											displayField:"display",
											valueField:"path",
											afterBodyEl:'<div class="x-form-help">'+Admin.getText("configs/sitemap/form/external_help")+'</div>'
										})
									]
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigContext-PAGE",
									title:Admin.getText("configs/sitemap/form/context"),
									items:[
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/subpage"),
											layout:"hbox",
											items:[
												new Ext.form.ComboBox({
													name:"subpage",
													store:new Ext.data.JsonStore({
														proxy:{
															type:"ajax",
															url:ENV.getProcessUrl("admin","@getSitemap"),
															extraParams:{domain:domain,language:language,menu:menu,mode:"subpage"},
															reader:{type:"json"}
														},
														remoteSort:false,
														fields:["page","title"],
														listeners:{
															load:function(store,records,success,e) {
																if (success == true) {
																	if (store.getCount() == 0) {
																		Ext.getCmp("SitemapConfigForm").getForm().findField("subpage_auto_bind").setValue(true);
																	}
																} else {
																	if (e.getError()) {
																		Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																	}
																}
															}
														}
													}),
													disabled:code ? false : true,
													displayField:"title",
													valueField:"page",
													flex:1,
													style:{marginRight:"5px"}
												}),
												new Ext.form.Checkbox({
													name:"subpage_auto_bind",
													boxLabel:Admin.getText("configs/sitemap/form/subpage_auto_bind"),
													checked:code ? false : true,
													listeners:{
														change:function(form,value) {
															if (!code || (value == false && Ext.getCmp("SitemapConfigForm").getForm().findField("subpage").getStore().getCount() == 0)) {
																Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("NOT_ALLOWED_SELECT_SUBPAGE"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																form.setValue(true);
																return;
															}
															Ext.getCmp("SitemapConfigForm").getForm().findField("subpage").setDisabled(value);
														}
													}
												})
											],
											afterBodyEl:'<div class="x-form-help">'+Admin.getText("configs/sitemap/form/subpage_help")+'</div>'
										})
									],
									listeners:{
										afterlayout:function() {
											Ext.getCmp("SitemapConfigWindow").center();
										}
									}
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigContext-WIDGET",
									title:Admin.getText("configs/sitemap/form/context"),
									items:[
										new Ext.form.TextArea({
											fieldLabel:Admin.getText("configs/sitemap/form/widget"),
											name:"widget",
											value:"[]",
											afterBodyEl:'<div class="x-form-help">'+Admin.getText("configs/sitemap/form/widget_help")+'</div>'
										})
									],
									listeners:{
										afterlayout:function() {
											Ext.getCmp("SitemapConfigWindow").center();
										}
									}
								}),
								new Ext.form.FieldSet({
									id:"SitemapConfigContext-LINK",
									title:Admin.getText("configs/sitemap/form/context"),
									items:[
										new Ext.form.FieldContainer({
											fieldLabel:Admin.getText("configs/sitemap/form/link"),
											layout:"hbox",
											items:[
												new Ext.form.TextField({
													name:"link_url",
													flex:1,
													style:{marginRight:"5px"}
												}),
												new Ext.form.ComboBox({
													name:"link_target",
													store:new Ext.data.ArrayStore({
														fields:["display","value"],
														data:[[Admin.getText("configs/sitemap/form/link_target")._self,"_self"],[Admin.getText("configs/sitemap/form/link_target")._blank,"_blank"]]
													}),
													displayField:"display",
													valueField:"value",
													value:"_self",
													width:120
												})
											]
										})
									],
									listeners:{
										afterlayout:function() {
											Ext.getCmp("SitemapConfigWindow").center();
										}
									}
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:Admin.getText("button/confirm"),
							handler:function() {
								Ext.getCmp("SitemapConfigForm").getForm().submit({
									url:ENV.getProcessUrl("admin","@saveSitemap"),
									submitEmptyText:false,
									waitTitle:Admin.getText("action/wait"),
									waitMsg:Admin.getText("action/saving"),
									success:function(form,action) {
										Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											if (mode == "menu") {
												Ext.getCmp("MenuList").selected = form.findField("menu").getValue();
												Ext.getCmp("MenuList").getStore().reload();
											}
											
											if (mode == "page") {
												Ext.getCmp("PageList").selected = form.findField("page").getValue();
												Ext.getCmp("PageList").getStore().reload();
											}
											
											Ext.getCmp("SitemapConfigWindow").close();
										}});
									},
									failure:function(form,action) {
										if (action.result) {
											if (action.result.message) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									}
								});
							}
						}),
						new Ext.Button({
							text:Admin.getText("button/cancel"),
							handler:function() {
								Ext.getCmp("SitemapConfigWindow").close();
							}
						})
					],
					listeners:{
						show:function() {
							if (code) {
								Ext.getCmp("SitemapConfigForm").getForm().load({
									url:ENV.getProcessUrl("admin","@getMenu"),
									params:{domain:domain,language:language,menu:menu,page:page},
									waitTitle:Admin.getText("action/wait"),
									waitMsg:Admin.getText("action/loading"),
									success:function(form,action) {
										form.findField("context")._configs = action.result.data._configs ? action.result.data._configs : {};
										
										if (action.result.data.type == "MODULE") {
											form.findField("context").getStore().getProxy().setExtraParam("target",action.result.data.target);
											form.findField("context").getStore().load(function() {
												form.findField("context").setValue(action.result.data._context);
											});
										}
										
										if (action.result.data.image > 0) {
											form.findField("image_delete").setDisabled(false).setHidden(false);
										}
										
										Ext.getCmp("SitemapConfigWindow").center();
									},
									failure:function(form,action) {
										if (action.result && action.result.message) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
										Ext.getCmp("SitemapConfigWindow").close();
									}
								});
							} else {
								Ext.getCmp("SitemapConfigContext-MODULE").hide().disable();
								Ext.getCmp("SitemapConfigContext-EXTERNAL").hide().disable();
								Ext.getCmp("SitemapConfigContext-PAGE").hide().disable();
								Ext.getCmp("SitemapConfigContext-WIDGET").hide().disable();
								Ext.getCmp("SitemapConfigContext-LINK").hide().disable();
							}
						}
					}
				}).show();
			},
			/**
			 * 사이트맵의 메뉴 또는 페이지를 삭제한다.
			 *
			 * @param string menu 메뉴명
			 * @param string page 페이지명 (없을경우 메뉴를 삭제한다.)
			 */
			delete:function(menu,page) {
				/**
				 * 선택된 사이트 정보를 가져온다.
				 */
				var site = Ext.getCmp("SiteList").getValue().split("@");
				
				var domain = site[0];
				var language = site[1];
				
				var message = "";
				if (page) {
					if (page.indexOf("$") === 0 || page.indexOf("^") === 0) message = "선택한 그룹을 삭제하시겠습니까?";
					else message = "선택한 페이지를 삭제하시겠습니까?";
				} else message = "선택한 메뉴를 삭제하시겠습니까?<br>메뉴를 삭제할 경우 하위에 포함된 페이지도 함께 삭제됩니다.";
				
				Ext.Msg.show({title:Admin.getText("alert/info"),msg:message,buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "ok") {
						var params = {domain:domain,language:language,menu:menu}
						if (page !== undefined && page) params.page = page;
						
						Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
						$.send(ENV.getProcessUrl("admin","@deleteSitemap"),params,function(result) {
							if (result.success == true) {
								Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
									Ext.getCmp("MenuList").getStore().load(function() {
										var index = Ext.getCmp("MenuList").getStore().findExact("menu",menu);
										if (index !== -1) {
											Ext.getCmp("MenuList").getSelectionModel().select(index);
										}
									});
								}});
							}
						});
					}
				}});
			},
			/**
			 * 사이트맵을 복사할 대상으로 부터 복사한다.
			 *
			 * @param string mode 복사대상 (menu or page)
			 */
			load:function(mode) {
				var site = Ext.getCmp("SiteList").getValue().split("@");
				var domain = site[0];
				var language = site[1];
				
				new Ext.Window({
					id:"LoadMenuWindow",
					title:Admin.getText("configs/sitemap/load_"+mode),
					width:600,
					modal:true,
					border:false,
					resizeable:false,
					autoScroll:true,
					items:[
						new Ext.form.Panel({
							id:"LoadMenuForm",
							border:false,
							bodyPadding:10,
							fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
							items:[
								new Ext.form.FieldSet({
									title:Admin.getText("configs/sitemap/form/load_target"),
									items:[
										new Ext.form.Hidden({
											name:"mode",
											value:mode
										}),
										new Ext.form.Hidden({
											name:"domain",
											value:domain
										}),
										new Ext.form.Hidden({
											name:"language",
											value:language
										}),
										new Ext.form.Hidden({
											name:"menu",
											disabled:(mode == "menu"),
											value:(mode == "menu" ? "" : Ext.getCmp("MenuList").getSelectionModel().getSelection().pop().get("menu"))
										}),
										new Ext.form.Hidden({
											name:"oDomain"
										}),
										new Ext.form.Hidden({
											name:"oLanguage"
										}),
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/load_site"),
											name:"site",
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													simpleSortMode:true,
													url:ENV.getProcessUrl("admin","@getSites"),
													reader:{type:"json"}
												},
												remoteSort:false,
												sorters:[{property:"sort",direction:"ASC"}],
												fields:["display","value","domain","language"]
											}),
											displayField:"display",
											valueField:"value",
											listeners:{
												select:function(form,record) {
													Ext.getCmp("LoadMenuForm").getForm().findField("oDomain").setValue(record.data.domain);
													Ext.getCmp("LoadMenuForm").getForm().findField("oLanguage").setValue(record.data.language);
													
													Ext.getCmp("LoadMenuForm").getForm().findField("oMenu").getStore().getProxy().setExtraParam("domain",record.data.domain);
													Ext.getCmp("LoadMenuForm").getForm().findField("oMenu").getStore().getProxy().setExtraParam("language",record.data.language);
													Ext.getCmp("LoadMenuForm").getForm().findField("oMenu").getStore().load();
												}
											}
										}),
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/load_menu"),
											name:"oMenu",
											disabled:true,
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													url:ENV.getProcessUrl("admin","@getSitemap"),
													extraParams:{domain:"",language:""},
													reader:{type:"json"}
												},
												remoteSort:false,
												fields:["domain","language","menu","title"],
												listeners:{
													load:function(store) {
														Ext.getCmp("LoadMenuForm").getForm().findField("oMenu").setDisabled(store.getCount() == 0);
													}
												}
											}),
											displayField:"title",
											valueField:"menu",
											listeners:{
												select:function(form,record) {
													if (mode == "page") {
														Ext.getCmp("LoadMenuForm").getForm().findField("oPage").getStore().getProxy().setExtraParam("domain",record.data.domain);
														Ext.getCmp("LoadMenuForm").getForm().findField("oPage").getStore().getProxy().setExtraParam("language",record.data.language);
														Ext.getCmp("LoadMenuForm").getForm().findField("oPage").getStore().getProxy().setExtraParam("menu",record.data.menu);
														Ext.getCmp("LoadMenuForm").getForm().findField("oPage").getStore().load();
													}
												}
											}
										}),
										new Ext.form.ComboBox({
											fieldLabel:Admin.getText("configs/sitemap/form/load_page"),
											name:"oPage",
											hidden:(mode == "menu"),
											disabled:true,
											store:new Ext.data.JsonStore({
												proxy:{
													type:"ajax",
													url:ENV.getProcessUrl("admin","@getSitemap"),
													extraParams:{domain:"",language:"",menu:""},
													reader:{type:"json"}
												},
												remoteSort:false,
												fields:["page","title"],
												listeners:{
													load:function(store) {
														Ext.getCmp("LoadMenuForm").getForm().findField("oPage").setDisabled(store.getCount() == 0);
													}
												}
											}),
											displayField:"title",
											valueField:"page"
										})
									]
								}),
								new Ext.form.FieldSet({
									title:Admin.getText("configs/sitemap/form/load_options"),
									hidden:(mode == "page"),
									disabled:(mode == "page"),
									items:[
										new Ext.form.Checkbox({
											name:"is_include",
											boxLabel:Admin.getText("configs/sitemap/form/include_pages")
										})
									]
								})
							]
						})
					],
					buttons:[
						new Ext.Button({
							text:Admin.getText("button/confirm"),
							handler:function() {
								Ext.getCmp("LoadMenuForm").getForm().submit({
									url:ENV.getProcessUrl("admin","@copySitemap"),
									submitEmptyText:false,
									waitTitle:Admin.getText("action/wait"),
									waitMsg:Admin.getText("action/saving"),
									success:function(form,action) {
										Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
											if (mode == "menu") {
												Ext.getCmp("MenuList").selected = form.findField("menu").getValue();
												Ext.getCmp("MenuList").getStore().reload();
											}
											
											if (mode == "page") {
												Ext.getCmp("PageList").selected = form.findField("page").getValue();
												Ext.getCmp("PageList").getStore().reload();
											}
											
											Ext.getCmp("LoadMenuWindow").close();
										}});
									},
									failure:function(form,action) {
										if (action.result) {
											if (action.result.message) {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											} else {
												Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
											}
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									}
								});
							}
						}),
						new Ext.Button({
							text:Admin.getText("button/cancel"),
							handler:function() {
								Ext.getCmp("LoadMenuWindow").close();
							}
						})
					]
				}).show();
			}
		}
	},
	/**
	 * 데이터베이스
	 */
	database:{
		drop:function(table) {
			var tables = [];
			
			if (table === undefined) {
				var selected = Ext.getCmp("TableList").getSelectionModel().getSelection();
				if (selected.length == 0) {
					Ext.Msg.show({title:Admin.getText("alert/info"),msg:"삭제할 테이블을 먼저 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
				}
				
				for (var i=0, loop=selected.length;i<loop;i++) {
					tables[i] = selected[i].get("name");
				}
			} else {
				tables[0] = table;
			}
			
			if (tables.length == 0) return;
			
			Ext.Msg.show({title:Admin.getText("alert/info"),msg:(tables.length == 1 ? tables[0] + " 테이블을 삭제하시겠습니까?" : "아래의 테이블을 삭제하시겠습니까?<br><br>"+tables.join("<br>")),buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "ok") {
					Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
					$.send(ENV.getProcessUrl("admin","@dropTable"),{tables:tables.join(",")},function(result) {
						if (result.success == true) {
							Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								if (Ext.getCmp("TableList")) Ext.getCmp("TableList").getStore().reload();
							}});
						}
					});
				}
			}});
		}
	},
	module:{
		addConfigPanel:function(target,configPanel) {
			panel.add(new Ext.Panel({
				title:Admin.getText("module/list/window/config"),
				border:false,
				autoScroll:true,
				items:[configPanel],
				buttons:[
					new Ext.Button({
						text:Admin.getText("button/confirm"),
						handler:function() {
							Ext.getCmp("ModuleConfigForm").getForm().submit({
								url:ENV.getProcessUrl("admin","@installModule"),
								params:{target:target},
								submitEmptyText:false,
								waitTitle:Admin.getText("action/wait"),
								waitMsg:Admin.getText("module/list/installing"),
								success:function(form,action) {
									Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("module/list/installed"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
								},
								failure:function(form,action) {
									if (action.result && action.result.message) {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("INVALID_FORM_DATA"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								}
							});
						}
					})
				],
				listeners:{
					render:function() {
						Ext.getCmp("ModuleConfigForm").getForm().load({
							url:ENV.getProcessUrl("admin","@getModuleConfigs"),
							params:{target:target},
							waitTitle:Admin.getText("action/wait"),
							waitMsg:Admin.getText("action/loading"),
							success:function(form,action) {
							},
							failure:function(form,action) {
								if (action.result && action.result.message) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_LOAD_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						});
					}
				}
			}));
		}
	},
	/**
	 * ExtJS 그리드에서 선택한 ROW의 정렬순서를 변경한다.
	 *
	 * @param Grid grid ExtJS 그리드 객체
	 * @param string field 정렬기준 필드명
	 * @param string dir 변경방향 (up : 위로, down : 아래로)
	 */
	gridSort:function(grid,field,dir) {
		var checked = grid.getSelectionModel().getSelection();
		if (checked.length == 0) return;
		var selected = [];
		
		for (var i=0, loop=checked.length;i<loop;i++) {
			checked[i] = checked[i].id;
		}
		
		for (var i=0, loop=grid.getStore().getCount();i<loop;i++) {
			if ($.inArray(grid.getStore().getAt(i).id,checked) > -1) {
				selected.push(grid.getStore().getAt(i));
			}
		}
		
		var lowFixedCount = highFixedCount = 0;
		for (var i=0, loop=grid.getStore().getCount();i<loop;i++) {
			if (grid.getStore().getAt(i).get(field) < 0) lowFixedCount++;
			if (grid.getStore().getAt(i).get(field) >= 10000) highFixedCount++;
		}
		
		if (dir == "up") {
			var firstSort = selected[0].get(field);
			if (firstSort <= 0 || firstSort >= 100000) return;
			
			for (var i=0, loop=selected.length;i<loop;i++) {
				var sort = parseInt(selected[i].get(field));
				if (sort > 0 && sort < 10000) {
					grid.getStore().getAt(lowFixedCount + sort).set(field,sort-1);
					grid.getStore().getAt(lowFixedCount + sort - 1).set(field,sort);
				} else {
					continue;
				}
			}
		} else {
			var lastSort = selected[selected.length - 1].get(field);
			if (lastSort < 0 || lowFixedCount + lastSort >= grid.getStore().getCount() - highFixedCount - 1 || lastSort >= 10000) return;
			
			for (var i=selected.length-1;i>=0;i--) {
				var sort = parseInt(selected[i].get(field));
				if (sort >= 0 && lowFixedCount + sort < grid.getStore().getCount() - highFixedCount - 1 && sort < 10000) {
					grid.getStore().getAt(lowFixedCount + sort).set(field,sort+1);
					grid.getStore().getAt(lowFixedCount + sort + 1).set(field,sort);
				} else {
					continue;
				}
			}
		}
	},
	/**
	 * ExtJS Store 데이터를 가져온다.
	 *
	 * @param Grid grid ExtJS 그리드 객체
	 * @param string[] fields 가져올 필드 (없을 경우 전체필드)
	 * @return object[] data
	 */
	grid:function(grid,fields) {
		var fields = fields == undefined ? [] : fields;
		
		var datas = [];
		for (var i=0, loop=grid.getStore().getCount();i<loop;i++) {
			var data = {};
			var oData = grid.getStore().getAt(i).data;
			for (var field in oData) {
				if (fields.length == 0 || $.inArray(field,fields) > -1) data[field] = typeof oData[field] == "string" ? $.trim(oData[field]) : oData[field];
			}
			datas.push(data);
		}
		
		return datas;
	},
	/**
	 * 그리드를 출력한다.
	 *
	 * @param Grid grid ExtJS 그리드 객체
	 */
	gridPrint:function(grid,title) {
		new Ext.Window({
			id:grid.getId()+"PrintWindow",
			title:"인쇄 미리보기",
			width:980,
			height:500,
			modal:true,
			autoScroll:false,
			border:false,
			layout:"fit",
			items:[
				new Ext.form.Panel({
					html:'<iframe id="'+grid.getId()+'PrintFrame" style="width:100%; height:100%;" frameborder="0"></iframe>'
				})
			],
			buttons:[
				new Ext.Button({
					iconCls:"xi xi-print",
					text:"인쇄",
					handler:function() {
						document.getElementById(grid.getId()+"PrintFrame").contentWindow.focus();
						document.getElementById(grid.getId()+"PrintFrame").contentWindow.print();
					}
				}),
				new Ext.Button({
					text:"취소",
					handler:function() {
						Ext.getCmp(grid.getId()+"PrintWindow").close();
					}
				})
			],
			listeners:{
				show:function(window) {
					var content = document.getElementById(grid.getId()+"PrintFrame").contentDocument;
					content.open();
					content.write('<!DOCTYPE HTML>');
					content.write('<html>');
					content.write('<head>');
					content.write('<title>'+(title ? title : grid.getTitle())+'</title>');
					content.write('<meta charset="utf-8">');
					
					for (var i=0;i<document.styleSheets.length;i++) {
						content.write(Ext.String.format('<link rel="stylesheet" href="{0}" type="text/css">',document.styleSheets[i].href));
					}
					
					var agent = navigator.userAgent.toLowerCase();
					if ((navigator.appName == 'Netscape' && agent.indexOf('trident') != -1) || (agent.indexOf("msie") != -1)) {
					} else {
						content.write('<style>body {zoom:85%;}</style>');
					}
					content.write('<style>.x-panel {overflow: visible !important; border:1px solid #e9e9e9 !important; padding-bottom:32px !important;}</style>');
					content.write('<style>.x-panel-body {overflow: visible !important;}</style>');
					content.write('<style>.x-column-header.x-box-item {position:static !important; display:inline-block; box-sizing:border-box; z-index:0;}</style>');
					
					content.write('</head>');
					content.write('<body></body>');
					content.write('</html>');
					content.close();
					
					var $body = $("body",$(content));
					var $dom = $(grid.getEl().dom).clone();
					
					$dom.css("height","auto");
					$(".x-grid-body",$dom).css("height","auto");
					$(".x-grid-view",$dom).css("height","auto");
					$(".x-docked-bottom",$dom).remove();
					$(".x-domscroller-spacer",$dom).remove();
					
					var zoom = 100 / $dom.width();
					
					$dom.css("width","calc(100% - 2px)");
					$("*[style*=width]",$dom).each(function() {
						$(this).css("width",($(this).width() * zoom)+"%");
					});
					
					$(".x-grid-item",$dom).width("100%");
					$(".x-grid-item-container",$dom).width("100%");
					
					$body.append($dom);
				}
			}
		}).show();
	},
	/**
	 * 그리드 내용을 엑셀로 변환한다.
	 *
	 * @param Grid grid ExtJS 그리드 객체
	 */
	gridExcel:function(grid,title) {
		var cells = [];
		var datas = [];
		var columns = grid.getColumns();
		
		for (var i=0, loop=grid.getStore().getCount();i<loop;i++) {
			var data = {};
			var oData = grid.getStore().getAt(i);
			
			for (var column in columns) {
				if (columns[column].dataIndex) {
					var cell = {};
					
					cell.title = columns[column].text;
					cell.dataIndex = columns[column].dataIndex;
					cell.align = columns[column].align;
					
					if (i == 0) cells.push(cell);
					
					data[cell.dataIndex] = typeof columns[column].renderer == "function" ? columns[column].renderer(oData.data[cell.dataIndex],{},oData) : oData.data[cell.dataIndex];
				}
			}
			
			datas.push(data);
		}
		
		new Ext.Window({
			id:"ModuleAdminExcelProgressWindow",
			title:"엑셀 변환중 ...",
			width:500,
			modal:true,
			bodyPadding:5,
			closable:false,
			items:[
				new Ext.ProgressBar({
					id:"ModuleAdminExcelProgressBar"
				})
			],
			listeners:{
				show:function() {
					Ext.getCmp("ModuleAdminExcelProgressBar").updateProgress(0,"데이터 준비중입니다. 잠시만 기다려주십시오.");
					
					$.ajax({
						url:ENV.getProcessUrl("admin","getExcel"),
						method:"POST",
						timeout:0,
						data:{cells:JSON.stringify(cells),datas:JSON.stringify(datas)},
						xhr:function() {
							var xhr = $.ajaxSettings.xhr();
							
							xhr.addEventListener("progress",function(e) {
								if (e.lengthComputable) {
									Ext.getCmp("ModuleAdminExcelProgressBar").updateProgress(e.loaded/e.total,Ext.util.Format.number(e.loaded - 1,"0,000")+" / "+Ext.util.Format.number(e.total,"0,000")+" ("+(e.loaded / e.total * 100).toFixed(2)+"%)",true);
								}
							});
			
							return xhr;
						},
						success:function(result,b,xhr) {
							var hash = xhr.getResponseHeader("X-Excel-File");
							if (hash && hash.length == 32) {
								Ext.getCmp("ModuleAdminExcelProgressBar").updateProgress(1,"변환완료. 곧 다운로드가 시작됩니다.",true);
								setTimeout(function() {
									Ext.getCmp("ModuleAdminExcelProgressWindow").close();
									downloadFrame.location.replace(ENV.getProcessUrl("admin","downloadExcel")+"?hash="+hash+"&title="+encodeURIComponent(title));
								},1000);
							} else {
								if (result.message) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:"엑셀변환 중 에러가 발생하였거나, 엑셀로 변환할 데이터가 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
								Ext.getCmp("ModuleAdminExcelProgressWindow").close();
							}
						},
						error:function() {
							Ext.Msg.show({title:Admin.getText("alert/error"),msg:"엑셀변환 중 에러가 발생하였습니다. 잠시후 다시 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							Ext.getCmp("ModuleAdminExcelProgressWindow").close();
						}
					});
				}
			}
		}).show();
	},
	/**
	 * ExtJS Store 를 저장한다.
	 *
	 * @param Grid grid ExtJS 그리드 객체
	 * @param string url 저장주소
	 * @param int timer 대기시간 (마이크로타임)
	 */
	savingGrid:{},
	gridSave:function(grid,url,timer,callback) {
		if (Admin.savingGrid[grid.getId()]) {
			clearTimeout(Admin.savingGrid[grid.getId()]);
			delete Admin.savingGrid[grid.getId()];
		}
		
		Admin.savingGrid[grid.getId()] = setTimeout(Admin.saveStore,timer,grid.getStore(),url,callback);
	},
	/**
	 * ExtJS Store 를 저장한다.
	 *
	 * @param Store store ExtJS store 객체
	 * @param string url 저장주소
	 */
	saveStore:function(store,url,callback) {
		var updated = store.getUpdatedRecords();
		for (var i=0, loop=updated.length;i<loop;i++) {
			for (var key in updated[i].data) {
				updated[i].data[key] = typeof updated[i].data[key] == "string" ? $.trim(updated[i].data[key]) : updated[i].data[key];
			}
			updated[i] = updated[i].data;
		}
		
		$.send(url,{updated:JSON.stringify(updated)},function(result) {
			if (result.success == true) {
				store.commitChanges();
				if (typeof callback == "function") {
					callback(store);
				}
			} else {
				Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("DATA_SAVE_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
			}
		});
	},
	/**
	 * 템플릿 필드를 추가한다.
	 *
	 * @param string label 라벨명
	 * @param string name 필드명
	 * @param string type 템플릿을 불러올 대상의 종류 (core, module, plugin, widget)
	 * @param string target 템플릿을 불러올 대상 (모듈명, plugin/플러그인명, widget/위젯명)
	 * @return object Ext.form.Combobox
	 */
	templetField:function(label,name,type,target,use_default,url,params,fields) {
		var url = url ? url : ENV.getProcessUrl("admin","@getTempletConfigs");
		var fields = fields ? fields : [];
		var params = params ? params : {};
		
		return new Ext.form.FieldContainer({
			layout:{type:"vbox",align:"stretch"},
			style:{marginBottom:"0px"},
			items:[
				new Ext.form.FieldContainer({
					fieldLabel:label,
					layout:"hbox",
					items:[
						new Ext.form.ComboBox({
							name:name,
							type:type,
							target:target,
							url:url,
							params:fields,
							store:new Ext.data.JsonStore({
								proxy:{
									type:"ajax",
									simpleSortMode:true,
									url:ENV.getProcessUrl("admin","@getTemplets"),
									reader:{type:"json",root:"lists",totalProperty:"totalCount"}
								},
								remoteSort:false,
								sorters:[{property:"sort",direction:"ASC"},{property:"name",direction:"ASC"}],
								pageSize:0,
								fields:["title","templet"]
							}),
							flex:1,
							editable:false,
							displayField:"title",
							valueField:"templet",
							value:use_default !== false ? "#" : null,
							listeners:{
								render:function(form) {
									params.type = form.type;
									params.target = form.target;
									params.use_default = use_default !== false ? true : false;
									
									form.getStore().getProxy().setExtraParams(params);
									form.getStore().load();
									
									form.fireEvent("change",form,form.getValue());
								},
								change:function(form,value) {
									if (Ext.getCmp(form.getName()+"-configs")) form.ownerCt.ownerCt.remove(Ext.getCmp(form.getName()+"-configs"));
									if (value == "#") return;
									
									var params = form.getStore().getProxy().extraParams;
									if (form.params.length > 0) {
										for (var i=0, loop=form.params.length;i<loop;i++) {
											params[form.params[i]] = form.getForm().findField(form.params[i]).getValue();
										}
									}
									
									params.type = form.type;
									params.target = form.target;
									params.name = form.getName();
									params.templet = value;
									
									if (form.getPanel().getId() == "SiteConfigForm") {
										params.domain = form.getForm().findField("domain").getValue();
										params.language = form.getForm().findField("language").getValue();
									}
									
									if (form.getPanel().getId() == "ModuleConfigForm") {
										params.position = "module";
									}
									
									if (form.getPanel().getId() == "SitemapConfigForm") {
										params.domain = form.getForm().findField("domain").getValue();
										params.language = form.getForm().findField("language").getValue();
										params.menu = form.getForm().findField("menu").getValue();
										params.page = form.getForm().findField("page").getValue();
										params.module = form.getForm().findField("target").getValue();
										params.position = "sitemap";
									}
									
									$.send(form.url,params,function(result) {
										if (result.success == true) {
											var configs = result.configs;
											if (configs == null) return;
											
											var container = new Ext.form.FieldSet({
												id:form.getName()+"-configs",
												title:"템플릿 세부설정",
												style:{marginLeft:(form.getPanel().fieldDefaults.labelWidth + 5)+"px",marginBottom:"10px"}
											});
											
											var preset = form.getName()+"_configs_";
											for (var config in configs) {
												var item = configs[config];
												
												var options = {
													fieldLabel:item.title,
													name:preset+config,
													allowBlank:true
												};
												
												if (item.help) options.afterBodyEl = '<div class="x-form-help">'+item.help+'</div>';
												if (item.value) options.value = item.value;
												
												if (item.type == "string") {
													container.add(
														new Ext.form.TextField(options)
													);
												}
												
												if (item.type == "int") {
													container.add(
														new Ext.form.NumberField(options)
													);
												}
												
												if (item.type == "color") {
													options.preview = true;
													container.add(
														new Ext.ux.ColorField(options)
													);
												}
												
												if (item.type == "textarea") {
													container.add(
														new Ext.form.TextArea(options)
													);
												}
												
												if (item.type == "select") {
													options.store = new Ext.data.ArrayStore({
														fields:["display","value"],
														data:item.options
													});
													options.displayField = "display";
													options.valueField = "value";
													container.add(
														new Ext.form.ComboBox(options)
													);
												}
											}
											
											if (container.items.length == 0) {
												form.ownerCt.ownerCt.style = {marginBottom:"0px"};
											} else {
												form.ownerCt.ownerCt.add(container);
											}
											form.ownerCt.ownerCt.updateLayout();
										}
									});
								}
							}
						}),
						new Ext.form.Checkbox({
							boxLabel:Admin.getText("configs/sites/form/apply_all_site"),
							name:"templet_all",
							hidden:true,
							disabled:true,
							style:{marginLeft:"5px"},
							listeners:{
								render:function(form) {
									if (form.getPanel().getId() == "SiteConfigForm") {
										form.show();
										form.setDisabled(false);
									}
								}
							}
						})
					]
				})
			]
		});
	},
	imageField:function(label,name,width,height) {
		return new Ext.form.FieldContainer({
			fieldLabel:label,
			items:[
				new Ext.form.Hidden({
					name:name,
					listeners:{
						render:function(form) {
							var $container = $("#"+form.ownerCt.items.items[1].getId());
							form.getPanel().on("beforeaction",function(form,action) {
								if (action.type == "submit") {
									var image = $(".photo-editor",$container).cropit("export");
									form.findField(name).setRawValue(image);
								}
							})
						},
						change:function(form,value) {
							var $container = $("#"+form.ownerCt.items.items[1].getId());
							$(".photo-editor",$container).cropit("imageSrc",value);
						}
					}
				}),
				new Ext.Panel({
					border:false,
					html:(function(width,height) {
						var html = [
							'<div data-role="image" style="width:'+(width + 22)+'px;">',
							'	<div class="photo-editor" style="width:'+(width + 2)+'px; height:'+(height + 42)+'px;">',
							'		<input type="file" class="cropit-image-input">',
							'		<div class="cropit-image-preview-container">',
							'			<div class="cropit-image-preview"></div>',
							'		</div>',
							'		<div class="cropit-image-zoom-container">',
							'			<span class="cropit-image-zoom-out"><i class="fa fa-picture-o"></i></span>',
							'			<input type="range" class="cropit-image-zoom-input">',
							'			<span class="cropit-image-zoom-in"><i class="fa fa-picture-o"></i></span>',
							'		</div>',
							'	</div>',
							'	<button type="button" data-action="select"><i class="xi xi-book"></i><span>이미지 선택</span></button>',
							'	<button type="button" data-action="reset"><i class="xi xi-trash"></i><span>이미지 초기화</span></button>',
							'</div>'
						];
						
						return html.join("");
					})(width,height),
					listeners:{
						render:function(panel) {
							var $container = $("#"+panel.getId());
							$("button[data-action]",$container).on("click",function() {
								if ($(this).attr("data-action") == "select") {
									$("input.cropit-image-input",$container).click();
								} else if ($(this).attr("data-action") == "reset") {
									$(".photo-editor",$container).cropit("resetImage");
								}
							});
							
							$(".photo-editor",$container).cropit({
								exportZoom:2,
								imageBackground:true,
								imageBackgroundBorderWidth:20,
								imageState:{
//									src:$(".photo-editor",$form).attr("data-path")
								}
							});
							
							panel.updateLayout();
						}
					}
				})
			]
		});
	},
	tagField:function(label,name,value,searchUrl,params) {
		if (typeof label == "string") {
			var params = typeof params != "object" ? {} : params;
			var searchUrl = searchUrl ? searchUrl : "";
			params.fieldLabel = label;
			params.items = [
				new Ext.form.Hidden({
					name:name,
					value:value ? value : "",
					allowBlank:true,
					listeners:{
						change:function(form,value) {
							var panel = form.ownerCt.items.items[1];
							Admin.tagField(panel,$("div[data-role=tags]",$("#"+panel.getId())));
						}
					}
				}),
				new Ext.Panel({
					html:'<div data-role="tags" data-search="'+searchUrl+'"></div>',
					border:false,
					listeners:{
						render:function(panel) {
							Admin.tagField(panel,$("div[data-role=tags]",$("#"+panel.getId())));
						}
					}
				})
			];
			return new Ext.form.FieldContainer(params);
		} else {
			var panel = label;
			var $container = name;
			
			if ($container.is("div[data-role=tags]") == true) {
				$container.empty();
				var tags = panel.ownerCt.items.items[0].getValue().length > 0 ? panel.ownerCt.items.items[0].getValue().split(",") : [];
				
				for (var i=0, loop=tags.length;i<loop;i++) {
					var $tag = $("<div>").attr("data-role","tag").attr("data-tag",tags[0]);
					$tag.append($("<span>").text(tags[i]));
					$tag.append($("<button>").attr("type","button").append($("<i>").addClass("mi mi-close")));
					$container.append($tag);
					
					Admin.tagField(panel,$tag);
				}
				
				var $insert = $("<div>").attr("data-role","tag");
				$insert.append($("<input>").attr("type","text"));
				$container.append($insert);
				Admin.tagField(panel,$insert);
			} else if ($container.is("div[data-role=tag]") == true) {
				if ($("input",$container).length == 0) {
					$container.attr("data-tag",$("span",$container).text());
					$("span",$container).on("click",function() {
						var tag = $(this).text();
						
						$(this).parents("div[data-role=tags]").children().has("input").remove();
						
						var $insert = $("<div>").attr("data-role","tag");
						$insert.append($("<input>").attr("type","text").data("last",tag).val(tag));
						$container.replaceWith($insert);
						Admin.tagField(panel,$insert);
						$("input",$insert).select();
					});
					
					$("button",$container).on("click",function() {
						var $parent = $container.parents("div[data-role=tags]");
						$container.remove();
						
						var tags = [];
						var $tags = $("div[data-role=tag][data-tag]",$parent);
						$tags.each(function() {
							tags.push($(this).attr("data-tag"));
						});
						panel.ownerCt.items.items[0].setRawValue(tags.join(","));
						panel.updateLayout();
					});
				} else {
					var $input = $("input",$container);
					$input.on("keydown",function(e) {
						if (e.keyCode == 32 || e.keyCode == 222 || e.keyCode == 191 || e.keyCode == 220 || e.keyCode == 186 || e.keyCode == 187) {
							e.preventDefault();
							return;
						}
						
						if ((e.keyCode == 188 || e.keyCode == 190) && e.shiftKey == true) {
							e.preventDefault();
							return;
						}
						
						if (e.keyCode == 188) {
							var tag = $input.val().replace(/(#| )/,"");
							
							if (tag.length > 0) {
								var $tag = $("<div>").attr("data-role","tag");
								$tag.append($("<span>").html(tag));
								$tag.append($("<button>").attr("type","button").append($("<i>").addClass("mi mi-close")));
								Admin.tagField(panel,$tag);
								$container.replaceWith($tag);
								
								var $insert = $("<div>").attr("data-role","tag");
								$insert.append($("<input>").attr("type","text"));
								$tag.parents("div[data-role=tags]").append($insert);
								Admin.tagField(panel,$insert);
								$("input",$insert).focus();
							
								var $parent = $tag.parents("div[data-role=tags]");
							} else {
								var $parent = $container.parents("div[data-role=tags]");
								
								if ($container.next().length > 0) {
									$container.remove();
									
									var $insert = $("<div>").attr("data-role","tag");
									$insert.append($("<input>").attr("type","text").data("last",tag).val(tag));
									$parent.append($insert);
									Admin.tagField(panel,$insert);
									e.preventDefault();
									$("input",$insert).focus();
								}
							}
							
							var tags = [];
							var $tags = $("div[data-role=tag][data-tag]",$parent);
							$tags.each(function() {
								tags.push($(this).attr("data-tag"));
							});
							
							panel.ownerCt.items.items[0].setRawValue(tags.join(","));
							panel.updateLayout();
							
							e.preventDefault();
							return;
						}
						
						if (e.keyCode == 51 && e.shiftKey == true) {
							e.preventDefault();
							return;
						}
						
						if (e.keyCode == 8) {
							if ($(this).val().length == 0 && $container.prev("div[data-role=tag]").length > 0) {
								var $prev = $container.prev("div[data-role=tag]");
								var tag = $("span",$prev).text();
								
								$container.remove();
								
								var $insert = $("<div>").attr("data-role","tag");
								$insert.append($("<input>").attr("type","text").data("last",tag).val(tag));
								$prev.replaceWith($insert);
								Admin.tagField(panel,$insert);
								e.preventDefault();
								$("input",$insert).focus();
							}
						}
						
						if (e.keyCode == 13) {
							e.preventDefault();
						}
						
						if (e.keyCode == 9) {
							var tag = $input.val().replace(/(#| )/,"");
							
							if (tag.length > 0) {
								var $tag = $("<div>").attr("data-role","tag");
								$tag.append($("<span>").html(tag));
								$tag.append($("<button>").attr("type","button").append($("<i>").addClass("mi mi-close")));
								Admin.tagField(panel,$tag);
								$container.replaceWith($tag);
								
								var $insert = $("<div>").attr("data-role","tag");
								$insert.append($("<input>").attr("type","text"));
								$tag.parents("div[data-role=tags]").append($insert);
								Admin.tagField(panel,$insert);
								$("input",$insert).focus();
							
								var $parent = $tag.parents("div[data-role=tags]");
							} else {
								var $parent = $container.parents("div[data-role=tags]");
								
								if ($container.next().length > 0) {
									$container.remove();
									
									var $insert = $("<div>").attr("data-role","tag");
									$insert.append($("<input>").attr("type","text").data("last",tag).val(tag));
									$parent.append($insert);
									Admin.tagField(panel,$insert);
									e.preventDefault();
									$("input",$insert).focus();
								}
							}
							
							var tags = [];
							var $tags = $("div[data-role=tag][data-tag]",$parent);
							$tags.each(function() {
								tags.push($(this).attr("data-tag"));
							});
							
							panel.ownerCt.items.items[0].setRawValue(tags.join(","));
							panel.updateLayout();
							
							if (tag.length > 0) e.preventDefault();
						}
					});
					
					$input.on("focus",function(e) {
						panel.ownerCt.items.items[0].getPanel().addCls("x-form-tags");
					});
					
					$input.on("blur",function(e) {
						setTimeout(function($input) {
							panel.ownerCt.items.items[0].getPanel().removeCls("x-form-tags");
							var tag = $input.val().replace(/(#| )/,"");
							
							if (tag.length > 0) {
								var $tag = $("<div>").attr("data-role","tag");
								$tag.append($("<span>").html(tag));
								$tag.append($("<button>").attr("type","button").append($("<i>").addClass("mi mi-close")));
								Admin.tagField(panel,$tag);
								$container.replaceWith($tag);
								
								var $insert = $("<div>").attr("data-role","tag");
								$insert.append($("<input>").attr("type","text"));
								$tag.parents("div[data-role=tags]").append($insert);
								Admin.tagField(panel,$insert);
							
								var $parent = $tag.parents("div[data-role=tags]");
							} else {
								var $parent = $container.parents("div[data-role=tags]");
								
								if ($container.next().length > 0) {
									$container.remove();
									
									var $insert = $("<div>").attr("data-role","tag");
									$insert.append($("<input>").attr("type","text").data("last",tag).val(tag));
									$parent.append($insert);
									Admin.tagField(panel,$insert);
									e.preventDefault();
								}
							}
							
							var tags = [];
							var $tags = $("div[data-role=tag][data-tag]",$parent);
							$tags.each(function() {
								tags.push($(this).attr("data-tag"));
							});
							
							panel.ownerCt.items.items[0].setRawValue(tags.join(","));
							panel.updateLayout();
						},100,$input);
					});
					
					if ($input.parents("div[data-role=tags]").attr("data-search")) $input.keyword($input.parents("div[data-role=tags]").attr("data-search"));
				}
			}
		}
	},
	/**
	 * 검색필드를 추가한다.
	 *
	 * @param int width 넓이
	 * @param string placeHolder placeHolder
	 * @param function 검색함수
	 */
	searchField:function(id,width,placeHolder,handler) {
		return new Ext.form.FieldContainer({
			width:width,
			layout:"hbox",
			items:[
				new Ext.form.TextField({
					id:id,
					flex:1,
					enableKeyEvents:true,
					emptyText:placeHolder,
					listeners:{
						keypress:function(form,e) {
							if (e.keyCode == 13) {
								handler(form.getValue());
								e.preventDefault();
							}
						}
					}
				}),
				new Ext.Button({
					iconCls:"mi mi-search",
					handler:function(button) {
						var keyword = button.ownerCt.items.items[0].getValue();
						handler(keyword);
					}
				})
			]
		});
	},
	/**
	 * 위지윅 필드를 추가한다.
	 *
	 * @param string label 라벨명
	 * @param string name 필드명
	 * @param object options 필드속성
	 */
	wysiwygField:function(label,name,options) {
		var options = typeof options == "object" ? options : {};
		options.name = name;
		options.fieldLabel = (label ? label : "");
		options.width = options.width ? options.width : "100%";
		options.lastHeight = 0;
		options.resizer = function(id) {
			if (Ext.getCmp(id)) {
				if (Ext.getCmp(id).isVisible() == true) {
					if (Ext.getCmp(id).lastHeight != Ext.getCmp(id).getHeight()) {
						Ext.getCmp(id).lastHeight = Ext.getCmp(id).getHeight();
						Ext.getCmp(id).getPanel().updateLayout();
					}
				}
			
				Ext.getCmp(id).getHeight();
				setTimeout(Ext.getCmp(id).resizer,500,id);
			}
		};
		options.cls = "x-form-wysiwyg";
		options.listeners = options.listeners ? options.listeners : {};
		options.listeners.render = function(form) {
			var $textarea = $("textarea",$("#"+form.getId()));
			$textarea.data("panel",form.getPanel());
			
			$textarea.on("froalaEditor.image.beforeUpload",function(e,editor,images) {
				$textarea.data("panel").getRoot().mask("파일을 업로드중입니다...");
			});
			
			$textarea.on("froalaEditor.image.uploaded",function(e,editor,response) {
				var result = JSON.parse(response);
				if (result.idx) {
					var form = Ext.getCmp(editor.$oel.attr("id").replace("-inputEl","-files"));
					var files = form.getValue().length > 0 ? form.getValue().split(",") : [];
					files.push(result.idx);
					form.setValue(files.join(","));
				}
				$textarea.data("panel").getRoot().unmask();
			});
			
			$textarea.on("froalaEditor.file.beforeUpload",function(e,editor,images) {
				$textarea.data("panel").getRoot().mask("파일을 업로드중입니다...");
			});
			
			$textarea.on("froalaEditor.file.uploaded",function(e,editor,response) {
				var result = JSON.parse(response);
				if (result.idx) {
					var form = Ext.getCmp(editor.$oel.attr("id").replace("-inputEl","-files"));
					var files = form.getValue().length > 0 ? form.getValue().split(",") : [];
					files.push(result.idx);
					
					form.setValue(files.join(","));
				}
				$textarea.data("panel").getRoot().unmask();
			});
			
			$textarea.on("froalaEditor.file.inserted",function(e,editor,$file,response) {
				if (response) {
					var result = typeof response == "object" ? response : JSON.parse(response);
					$file.remove();
				}
			});
			
			$textarea.froalaEditor({
				key:"pFOFSAGLUd1AVKg1SN==",
				toolbarButtons:["html","|","bold","italic","underline","strikeThrough","align","|","paragraphFormat","fontSize","color","|","insertImage","insertFile","insertVideo","insertLink","insertTable"],
				fontSize:["8","9","10","11","12","14","18","24"],
				heightMin:300,
				zIndex:1000000,
				imageDefaultWidth:0,
				imageUploadURL:ENV.getProcessUrl("attachment","wysiwyg"),
				imageUploadParams:{module:"admin",target:form.getName("name")},
				fileUploadURL:ENV.getProcessUrl("attachment","wysiwyg"),
				fileUploadParams:{module:"admin",target:form.getName("name")},
				imageEditButtons:["imageAlign","imageLink","linkOpen","linkEdit","linkRemove","imageDisplay","imageStyle","imageAlt","imageSize"],
				paragraphFormat:{N:"Normal",H1:"Heading 1",H2:"Heading 2",H3:"Heading 3"},
				toolbarSticky:false,
				pluginsEnabled:["align","codeView","colors","file","fontSize","image","lineBreaker","link","lists","paragraphFormat","insertCode","table","url","video"]
			});
			
			setTimeout(function(form) {
				if (!form || !form.ownerCt) return;
				
				var index = form.ownerCt.items.indexOf(form);
				form.ownerCt.insert(index+1,
					new Ext.Panel({
						id:form.getId()+"-lists",
						border:0,
						style:{paddingLeft:(label ? (form.labelWidth + 5)+"px" : "0px")},
						html:'<div data-module="attachment" class="wysiwyg"><ul data-role="files"></ul>',
						listeners:{
							render:function(panel) {
								var form = Ext.getCmp(panel.getId().replace(/-lists$/,"-files"));
								if (form.getValue()) form.fireEvent("change",form,form.getValue());
							}
						}
					})
				);
			},500,form);
			
			form.ownerCt.add(
				new Ext.form.Hidden({
					id:form.getId()+"-delete-files",
					name:form.getName()+"_delete_files",
					allowBlank:true
				})
			);
			
			form.ownerCt.add(
				new Ext.form.Hidden({
					id:form.getId()+"-files",
					name:form.getName()+"_files",
					allowBlank:true,
					listeners:{
						change:function(form,value) {
							var $lists = $("ul[data-role=files]",$("#"+form.getId().replace(/-files$/,"-lists")));
							if ($lists.length == 0) return;
							$lists.empty();
							
							if (value) {
								$.send(ENV.getProcessUrl("admin","@getWysiwygFiles"),{idx:value},function(result) {
									if (result.success == true) {
										for (var i=0, loop=result.lists.length;i<loop;i++) {
											var file = result.lists[i];
											var $file = $("<li>");
											$file.append($("<i>").addClass("icon").attr("data-type",file.type).attr("data-extension",file.extension));
											$file.append($("<a>").attr("href",file.path).attr("download",file.name).append($("<i>").html("("+iModule.getFileSize(file.size)+")")).append(file.name));
											
											var $insert = $("<button>").attr("type","button").attr("data-wysiwyg",form.getId().replace(/-files$/,"")).data("file",file).html('<i class="mi mi-upload"></i>');
											$insert.on("click",function() {
												var file = $(this).data("file");
												var $wysiwyg = $("textarea",$("#"+$(this).attr("data-wysiwyg")));
												
												if (file.type == "image") {
													$wysiwyg.froalaEditor("image.insert",file.path,false,{"idx":file.idx});
												} else {
													$wysiwyg.froalaEditor("file.insert",file.path,file.name,{idx:file.idx});
												}
											});
											
											$file.append($insert);
											
											var $delete = $("<button>").attr("type","button").attr("data-wysiwyg",form.getId().replace(/-files$/,"")).data("file",file).html('<i class="mi mi-trash"></i>');
											$delete.on("click",function() {
												var file = $(this).data("file");
												var $file = $(this).parents("li");
												var $wysiwyg = $("textarea",$("#"+$(this).attr("data-wysiwyg")));
												
												Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/deleteFile").replace("{FILE}",file.name),buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
													var delete_files = Ext.getCmp(form.getId().replace(/-files$/,"-delete-files")).getValue().length > 0 ? Ext.getCmp(form.getId().replace(/-files$/,"-delete-files")).getValue().split(",") : [];
													delete_files.push(file.idx);
													Ext.getCmp(form.getId().replace(/-files$/,"-delete-files")).setValue(delete_files.join(","));
													$file.remove();
													
													
													Ext.getCmp(form.getId().replace(/-files$/,"-lists")).updateLayout();
												}});
											});
											$file.append($delete);
											$lists.append($file);
										}
										
										$lists.height("auto");
										Ext.getCmp(form.getId().replace(/-files$/,"-lists")).updateLayout();
									}
								});
							}
							
							$lists.height("auto");
							Ext.getCmp(form.getId().replace(/-files$/,"-lists")).updateLayout();
						}
					}
				})
			);
			
			var parent = form.ownerCt;
			while (parent != null) {
				if (parent.is("fieldset") == true) {
					parent.addCls("x-form-wysiwyg");
				}
				
				parent = parent.ownerCt;
			}
			setTimeout(form.resizer,500,form.getId());
		};
		
		options.listeners.change = function(form,value) {
			var $textarea = $("textarea",$("#"+form.getId()));
			$textarea.froalaEditor("html.set",value);
		};
		
		return new Ext.form.TextArea(options);
	},
	/**
	 * 멀티파일업로더 필드를 추가한다.
	 *
	 * @param string label 라벨명
	 * @param string name 필드명
	 * @param object options 필드속성
	 * @param object[] files 기존파일객체
	 */
	uploadField:function(label,name,options,files) {
		var options = typeof options == "object" ? options : {};
		options.id = options.id ? options.id : null;
		options.name = name;
		options.fieldLabel = (label ? label : "");
		options.layout = {type:"vbox",align:"stretch"};
		options.items = [
			new Ext.form.FieldContainer({
				layout:"hbox",
				style:{marginBottom:0},
				items:[
					new Ext.Button({
						iconCls:options.iconCls ? options.iconCls : "xi xi-upload",
						text:options.buttonText ? options.buttonText : "파일선택",
						handler:function(button) {
							var $input = $("input[type=file]",$(button.ownerCt.el.dom));
							$input.trigger("click");
						}
					}),
					new Ext.form.Hidden({
						name:options.name,
						disabled:true,
						listeners:{
							change:function(form,value) {
								var files = JSON.parse(value)
								for (var i=0, loop=files.length;i<loop;i++) {
									form.ownerCt.print(files[i]);
								}
							},
							afterRender:function(form) {
								if (files !== undefined) form.setValue(JSON.stringify(files));
							}
						}
					}),
					new Ext.form.DisplayField({
						value:'<span style="display:none;"><input type="file" name="'+name+'_input" accept="'+(options.accept ? options.accept : "*/*")+'" multiple></span>업로드할 파일을 선택(다중선택가능)하면 업로드가 시작됩니다',
						fieldStyle:{textAlign:"right",fontSize:"11px",color:"#666"},
						flex:1,
						listeners:{
							render:function(form) {
								var button = form.ownerCt.items.items[0];
								var $input = $("input[type=file]",$(form.el.dom));
								$input.data("parent",form.ownerCt.getId());
								$input.data("button",button);
								$input.data("files",[]);
								$input.data("total",0);
								$input.data("uploaded",0);
								$input.data("queue",[]);
								
								$input.on("change",function(e) {
									$input.data("button").setDisabled(true);
									
									var files = [];
									for (var i=0, loop=e.target.files.length;i<loop;i++) {
										var file = e.target.files[i];
										files.push(file);
									}
									
									$input.val("");
									
									var drafts = [];
									for (var i=0, loop=files.length;i<loop;i++) {
										var draft = {};
										draft.name = files[i].name;
										draft.size = files[i].size;
										draft.type = files[i].type;
										
										drafts.push(draft);
									}
									
									$.send(ENV.getProcessUrl("attachment","draft"),{module:(options.module ? options.module : "admin"),target:(options.target ? options.target : ""),files:JSON.stringify(drafts)},function(result) {
										if (result.success == true) {
											for (var i=0, loop=result.files.length;i<loop;i++) {
												if (result.files[i].code != null) {
													files[i].idx = result.files[i].idx;
													files[i].code = result.files[i].code;
													files[i].mime = result.files[i].mime;
													files[i].uploaded = result.files[i].uploaded;
													files[i].extension = result.files[i].extension;
													files[i].status = result.files[i].status;
													
													$input.data("total",$input.data("total") + files[i].size);
													if (files[i].status == "COMPLETE") $input.data("uploaded",$input.data("uploaded") + files[i].size);
													else $input.data("queue").push(files[i]);
													
													Ext.getCmp($input.data("parent")).print(result.files[i],files[i]);
												}
											}
										}
										
										$input.data("button").setDisabled(false);
										Ext.getCmp($input.data("parent")).start();
									});
								})
							}
						}
					})
				],
				print:function(file,oFile) {
					var parent = this.ownerCt;
					for (var i=0, loop=parent.items.items.length;i<loop;i++) {
						if (file === null || parent.items.items[i].idx == file.idx) {
							return;
						}
					}
					
					parent.add(new Ext.form.FieldContainer({
						idx:file.idx,
						file:file,
						layout:"hbox",
						style:{marginTop:"5px",marginBottom:0},
						items:[
							new Ext.form.DisplayField({
								layout:"hbox",
								fieldStyle:{paddingTop:0,minHeight:"24px",marginRight:"5px"},
								flex:1,
								value:'<div style="width:100%; height:24px; position:relative;"><div style="display:block; width:100%; height:20px; line-height:20px; position:absolute; top:2px; left:0; text-overflow:ellipsis; white-space:nowrap; overflow:hidden; box-sizing:border-box; padding-left:24px; background:url('+file.icon+') no-repeat 0 50%; background-size:contain;"><span style="float:right; margin-left:5px; color:#666;">('+iModule.getFileSize(file.size)+')</span><a href="'+file.download+'" style="text-decoration:none; color:#2196F3;" download="'+file.name+'">'+file.name+'</a></div></div>'
							}),
							new Ext.ProgressBar({
								width:100,
								hidden:file.status != "WAIT",
								value:file.status != "WAIT" ? 1 : null
							}),
							new Ext.Button({
								width:24,
								iconCls:"mi mi-trash",
								cls:"x-btn-danger",
								style:{width:"24px",height:"24px",paddingTop:"2px",paddingBottom:"2px"},
								handler:function(button) {
									var item = button.ownerCt;
									Ext.Msg.show({title:Admin.getText("alert/info"),msg:"선택한 파일을 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.INFO,fn:function(button) {
										item.ownerCt.remove(item);
									}});
								}
							}),
							new Ext.form.Hidden({
								name:options.name+"[]",
								value:file.idx
							})
						]
					}));
				},
				update:function(idx,status) {
					var parent = this.ownerCt;
					for (var i=0, loop=parent.items.items.length;i<loop;i++) {
						if (parent.items.items[i].idx == idx) {
							if (status == parent.items.items[i].file.size) {
								parent.items.items[i].items.items[1].hide();
							} else {
								parent.items.items[i].items.items[1].updateProgress(status/parent.items.items[i].file.size,Ext.util.Format.number(status/parent.items.items[i].file.size * 100,"0.00")+"%");
							}
							return;
						}
					}
				},
				start:function() {
					var $input = $("input[type=file]",$(this.el.dom));
					if ($input.data("uploading") != null) return;
					if ($input.data("queue").length == 0) return this.complete();
					
					$input.data("uploading",$input.data("queue").shift());
					this.upload();
				},
				upload:function() {
					var $input = $("input[type=file]",$(this.el.dom));
					if ($input.data("uploading") == null) return this.start();
					
					var file = $input.data("uploading");
					this.update(file.idx,file.uploaded);
					
					var chunkSize = 2 * 1000 * 1000;
					file.chunk = file.size > file.uploaded + chunkSize ? file.uploaded + chunkSize : file.size;
					
					$.ajax({
						url:ENV.getProcessUrl("attachment","upload")+"?code="+encodeURIComponent(file.code),
						method:"POST",
						contentType:file.mime,
						headers:{
							"Content-Range":"bytes " + file.uploaded + "-" + (file.chunk - 1) + "/" + file.size
						},
						xhr:function() {
							var xhr = $.ajaxSettings.xhr();
			
							if (xhr.upload) {
								xhr.upload.addEventListener("progress",function(e) {
									if (e.lengthComputable) {
										Ext.getCmp($input.data("parent")).update(file.idx,file.uploaded + e.loaded);
									}
								},false);
							}
			
							return xhr;
						},
						processData:false,
						data:file.slice(file.uploaded,file.chunk)
					}).done(function(result) {
						if (result.success == true) {
							file.failCount = 0;
							
							if (file.chunk == file.size) {
								Ext.getCmp($input.data("parent")).print(result.file);
								$input.data("uploaded",$input.data("uploaded") + file.size);
								$input.data("uploading",null);
								Ext.getCmp($input.data("parent")).start();
							} else {
								file.uploaded = result.uploaded;
								Ext.getCmp($input.data("parent")).upload();
							}
						} else {
							if (file.failCount < 3) {
								file.failCount++;
								Ext.getCmp($input.data("parent")).upload();
							} else {
								file.status = "FAIL";
							}
						}
					}).fail(function() {
						if (file.failCount < 3) {
							file.failCount++;
							Ext.getCmp($input.data("parent")).upload();
						}
					});
				},
				complete:function() {
					
				}
			})
		];
		
		return new Ext.form.FieldContainer(options);
	},
	/**
	 * 권한을 설정하는 필드셋을 정의한다.
	 *
	 * @param string label 라벨명
	 * @param string name 필드명
	 * @param string value 권한코드값
	 * @param boolean is_guest 손님권한 포함여부
	 * return FieldContainer field 권한필드
	 */
	permissionField:function(label,name,value,is_guest,help) {
		var selectorValue = "etc";
		var presets = [];
		var permissions = Admin.getText("permission/preset");
		for (var code in permissions) {
			if (is_guest === false && code == "true") continue;
			presets.push([permissions[code],code]);
			if (code == value) selectorValue = code;
		}
		presets.push([Admin.getText("permission/etc"),"etc"]);
		
		return new Ext.form.FieldContainer({
			fieldLabel:label,
			layout:"hbox",
			items:[
				new Ext.form.ComboBox({
					name:name+"_selector",
					store:new Ext.data.ArrayStore({
						fields:["display","value"],
						data:presets
					}),
					displayField:"display",
					valueField:"value",
					value:selectorValue,
					width:160,
					listeners:{
						change:function(form,value) {
							var formId = form.el.up("div[role=form]").id.replace("-body","");
							var form = Ext.getCmp(formId).getForm();
							
							if (value == "etc") {
								form.findField(name).focus();
							} else {
								form.findField(name).setValue(value);
							}
						}
					}
				}),
				new Ext.form.TextField({
					name:name,
					value:value,
					flex:1,
					margin:"0 5 0 5",
					listeners:{
						change:function(form,value) {
							var formId = form.el.up("div[role=form]").id.replace("-body","");
							var form = Ext.getCmp(formId).getForm();
							
							if (form.findField(name+"_selector").getStore().findExact("value",value) == -1) {
								form.findField(name+"_selector").setValue("etc");
							} else {
								form.findField(name+"_selector").setValue(value);
							}
						},
						enable:function(form) {
							form.ownerCt.enable();
						},
						disable:function(form) {
							form.ownerCt.disable();
						}
					}
				}),
				new Ext.Button({
					iconCls:"mi mi-question",
					handler:function() {
						
					}
				})
			],
			afterBodyEl:help ? '<div class="x-form-help">' + help + '</div>' : null
		});
	},
	/**
	 * 언어별 설정값을 입력받는 필드셋을 정의한다.
	 *
	 * @param string id 필드셋 고유값
	 * @param string label 필드라벨
	 * @param string code 언어코드 필드명
	 * @param string field 설정값 필드명
	 * return FieldSet field 권한필드
	 */
	languageFieldSet:function(id,label,code,field) {
		return new Ext.form.FieldSet({
			id:id,
			title:Admin.getText("text/language_setting"),
			collapsible:true,
			collapsed:true,
			codeName:code,
			fieldName:field,
			items:[
				new Ext.form.FieldContainer({
					layout:"hbox",
					fieldDefaults:{labelAlign:"left"},
					margin:"0 0 0 0",
					items:[
						new Ext.form.DisplayField({
							fieldLabel:Admin.getText("text/language_code"),
							width:180,
							margin:"0 5 0 0"
						}),
						new Ext.form.DisplayField({
							fieldLabel:label,
							flex:1,
							margin:"0 0 0 0"
						})
					]
				}),
				new Ext.form.FieldContainer({
					layout:"hbox",
					items:[
						new Ext.form.FieldContainer({
							layout:"hbox",
							width:180,
							style:{marginRight:"5px"},
							items:[
								new Ext.form.TextField({
									name:code+"[]",
									flex:1,
									style:{marginRight:"5px"},
									length:2,
									maxLength:2,
									validator:function(value) {
										if (value.length > 0 && value.search(/^[a-z]{2}$/) == -1) return Admin.getErrorText("INVALID_LANGUAGE_CODE");
										return true;
									}
								}),
								new Ext.Button({
									text:Admin.getText("button/language_search"),
									handler:function() {
										window.open("http://www.mcanerin.com/en/articles/meta-language.asp");
									}
								})
							]
						}),
						new Ext.form.TextField({
							name:field+"[]",
							flex:1,
							style:{marginRight:"5px"},
							listeners:{
								focus:function(form) {
									if (form.ownerCt.items.items[0].items.items[0].getValue().length > 0) {
										form.allowBlank = false;
									} else {
										form.allowBlank = true;
									}
								}
							}
						}),
						new Ext.Button({
							iconCls:"mi mi-plus",
							count:1,
							style:{marginRight:"5px"},
							handler:function(button) {
								Admin.addLanguageField(id);
							}
						}),
						new Ext.Button({
							iconCls:"mi mi-minus",
							count:1,
							style:{marginRight:"5px"},
							handler:function(button) {
								button.ownerCt.items.items[0].items.items[0].reset();
								button.ownerCt.items.items[1].reset();
							}
						})
					]
				})
			]
		})
	},
	/**
	 * 언어별 설정값을 입력받는 필드셋을 추가한다.
	 *
	 * @param string id 추가할 언어 필드셋 고유값
	 * @param string code 언어코드 필드명
	 * @param string field 설정값 필드명
	 */
	addLanguageField:function(id) {
		new Ext.getCmp(id).add(
			new Ext.form.FieldContainer({
				layout:"hbox",
				items:[
					new Ext.form.FieldContainer({
						layout:"hbox",
						width:180,
						style:{marginRight:"5px"},
						items:[
							new Ext.form.TextField({
								name:Ext.getCmp(id).codeName+"[]",
								flex:1,
								style:{marginRight:"5px"},
								validator:function(value) {
									if (value.search(/^[a-z]{2}$/) == -1) return Admin.getErrorText("INVALID_LANGUAGE_CODE");
									return true;
								}
							}),
							new Ext.Button({
								text:Admin.getText("button/language_search"),
								handler:function() {
									window.open("http://www.mcanerin.com/en/articles/meta-language.asp");
								}
							})
						]
					}),
					new Ext.form.TextField({
						name:Ext.getCmp(id).fieldName+"[]",
						flex:1,
						style:{marginRight:"5px"},
						allowBlank:false
					}),
					new Ext.Button({
						iconCls:"mi mi-minus",
						count:1,
						style:{marginRight:"5px"},
						handler:function(button) {
							button.ownerCt.destroy();
						}
					})
				]
			})
		);
	},
	/**
	 * 언어별 설정값을 입력받는 필드셋의 값을 확인한다.
	 *
	 * @param string id 언어 필드셋 고유값
	 * @param string languages 값
	 */
	parseLanguageFieldValue:function(id,languages) {
		var index = 0;
		for (var code in languages) {
			Admin.setLanguageFieldValue(id,index,code,languages[code]);
			index++;
		}
	},
	/**
	 * 언어별 설정값을 입력받는 필드셋에 값을 설정한다.
	 *
	 * @param string id 언어 필드셋 고유값
	 * @param string index 값을 설정할 라인수
	 * @param string code 언어코드
	 * @param string value 값
	 */
	setLanguageFieldValue:function(id,index,code,value) {
		while (Ext.getCmp(id).items.length < index + 2) {
			Admin.addLanguageField(id);
		}
		
		var languages = Ext.getCmp(id).items.items[index+1];
		languages.items.items[0].items.items[0].setValue(code);
		languages.items.items[1].setValue(value);
		
		Ext.getCmp(id).expand();
	}
};

$(document).ready(function() {
	Admin.current.init();
	
	$("button[data-action]",$("header")).on("click",function(e) {
		var $button = $(this);
		var action = $button.attr("data-action");
		
		if (action == "push") {
			var $push = $("div[data-role=push]");
			if ($push.is(":visible") == true) {
				$button.parent().removeClass("opened");
				$push.hide();
				return;
			}
			
			var $lists = $("ul",$push);
			$lists.empty();
			$lists.append($("<li>").addClass("loading").append($("<i>").addClass("mi mi-loading")));
			
			$button.parent().addClass("opened");
			$push.show();
			
			Push.getRecently(20,function(result) {
				$lists.empty();
				
				if (result.success == true) {
					var news = [];
					var previous = [];
					for (var i=0, loop=result.lists.length;i<loop;i++) {
						if (result.lists[i].is_checked == false) {
							news.push(result.lists[i]);
						} else {
							previous.push(result.lists[i]);
						}
					}
					
					if (news.length > 0) {
						$lists.append($("<li>").addClass("title").html(Push.getText("text/new")));
						for (var i=0, loop=news.length;i<loop;i++) {
							var item = news[i];
							var $button = $("<button>").attr("type","button").data("item",item);
							if (item.is_readed == false) $button.addClass("unread");
							
							var $icon = $("<i>").addClass("icon");
							$icon.css("backgroundImage","url(" + item.icon + ")");
							$button.append($icon);
							
							var $text = $("<div>").addClass("text");
							$text.append(item.message);
							$text.append($("<time>").html(moment(item.reg_date * 1000).locale($("html").attr("lang")).fromNow()));
							$button.append($text);
							
							$button.on("click",function(e) {
								var item = $(this).data("item");
								Push.view(item.module,item.type,item.idx,$(this));
							});
							
							$lists.append($("<li>").append($button));
						}
					}
					
					if (previous.length > 0) {
						$lists.append($("<li>").addClass("title").html(Push.getText("text/previous")));
						for (var i=0, loop=previous.length;i<loop;i++) {
							var item = previous[i];
							var $button = $("<button>").attr("type","button").data("item",item);
							if (item.is_readed == false) $button.addClass("unread");
							
							var $icon = $("<i>").addClass("icon");
							$icon.css("backgroundImage","url(" + item.icon + ")");
							$button.append($icon);
							
							var $text = $("<div>").addClass("text");
							$text.append(item.message);
							$text.append($("<time>").html(moment(item.reg_date * 1000).locale($("html").attr("lang")).fromNow()));
							$button.append($text);
							
							$button.on("click",function(e) {
								var item = $(this).data("item");
								Push.view(item.module,item.type,item.idx,$(this));
							});
							
							$lists.append($("<li>").append($button));
						}
					}
				} else {
					$lists.append($("<li>").addClass("message").html(result.message));
					return false;
				}
			});
			
			$push.on("click",function(e) {
				e.stopImmediatePropagation();
			});
			
			e.stopImmediatePropagation();
		}
		
		if (action == "logout") {
			$.send(ENV.getProcessUrl("member","logout"),function(result) {
				if (result.success == true) {
					location.replace(ENV.DIR + "/admin/");
				}
			});
		}
	});
	
	$(document).on("click",function() {
		var $push = $("div[data-role=push]");
		var $button = $("button[data-action=push]",$("header"));
		
		if ($push.is(":visible") == true) {
			$button.parent().removeClass("opened");
			$push.hide();
		}
	});
});