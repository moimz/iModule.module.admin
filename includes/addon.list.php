<script>
var panel = new Ext.grid.Panel({
	id:"AddonList",
	border:false,
	tbar:[
		new Ext.Button({
			text:Admin.getLanguage("addon/list/updateSize"),
			handler:function() {
			}
		})
	],
	store:new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:ENV.getProcessUrl("admin","@getAddonList"),
			reader:{type:"json"}
		},
		remoteSort:false,
		sorters:[{property:"title",direction:"ASC"}],
		autoLoad:true,
		pageSize:0,
		groupField:"installed",
		groupDir:"DESC",
		fields:["id","addon","title","version","description","hash",{name:"db_size",type:"int"},"active","installed","installed_hash"],
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
	columns:[{
		text:Admin.getLanguage("addon/list/columns/title"),
		width:150,
		summaryType:"count",
		dataIndex:"title",
		sortable:true,
		summaryRenderer:function(value) {
			return value+" addon"+(value > 1 ? "s" : "");
		}
	},{
		text:Admin.getLanguage("addon/list/columns/version"),
		width:65,
		align:"center",
		dataIndex:"version"
	},{
		text:Admin.getLanguage("addon/list/columns/description"),
		minWidth:150,
		flex:1,
		sortable:true,
		dataIndex:"description",
	},{
		text:Admin.getLanguage("addon/list/columns/author"),
		width:90,
		sortable:true,
		dataIndex:"author",
	},{
		text:Admin.getLanguage("addon/list/columns/status"),
		width:100,
		dataIndex:"hash",
		align:"center",
		renderer:function(value,p,record) {
			if (record.data.installed == "FALSE") {
				p.style = "color:#666;";
				return Admin.getLanguage("addon/list/columns/need_install");
			} else if (record.data.installed_hash != value) {
				p.style = "color:red;";
				return Admin.getLanguage("addon/list/columns/need_update");
			} else {
				p.style = "color:blue;";
				return Admin.getLanguage("addon/list/columns/updated");
			}
		}
	},{
		text:Admin.getLanguage("addon/list/columns/active"),
		dataIndex:"active",
		width:100,
		align:"center",
		renderer:function(value,p) {
			if (value == "TRUE") p.style = "color:blue;";
			else p.style = "color:red;";
			return Admin.getLanguage("addon/list/active/"+value);
		}
	},{
		text:Admin.getLanguage("addon/list/columns/db_size"),
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
	}],
	selModel:new Ext.selection.RowModel(),
	features:[{
		ftype:"groupingsummary",
		groupHeaderTpl:'<tpl if="name == \'TRUE\'">'+Admin.getLanguage("addon/list/columns/installed")+'<tpl elseif="name == \'FALSE\'">'+Admin.getLanguage("addon/list/columns/not_installed")+'</tpl>',
		hideGroupedHeader:false,
		enableGroupingMenu:false
	}],
	listeners:{
		itemdblclick:function(grid,record) {
			var type = record.data.installed == "FALSE" ? "install" : (record.data.hash != record.data.installed_hash ? "update" : "config");
			
			new Ext.Window({
				id:"AddonConfigWindow",
				title:Admin.getLanguage("addon/list/window/"+type),
				width:600,
				height:400,
				modal:true,
				border:false,
				resizeable:false,
				autoScroll:true,
				items:[
					new Ext.form.Panel({
						id:"AddonConfigForm",
						border:false,
						bodyPadding:10,
						fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:true},
						items:[
							new Ext.form.Hidden({
								name:"target",
								value:record.data.addon
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:Admin.getLanguage("addon/list/window/"+type),
						handler:function() {
							Ext.getCmp("AddonConfigForm").getForm().submit({
								url:ENV.getProcessUrl("admin","@installAddon"),
								submitEmptyText:false,
								waitTitle:Admin.getLanguage("wait"),
								waitMsg:Admin.getLanguage("addon/list/installing"),
								success:function(form,action) {
									Ext.Msg.show({title:Admin.getLanguage("alert/info"),msg:Admin.getLanguage("addon/list/installComplete"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
										Ext.getCmp("AddonConfigWindow").close();
										Ext.getCmp("AddonList").getStore().reload();
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
							Ext.getCmp("AddonConfigWindow").close();
						}
					})
				]
			}).show();
		}
	}
});
</script>