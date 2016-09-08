<?php
/**
 * 이 파일은 iModule 관리자모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 사이트를 추가하거나 관리한다.
 * 
 * @file /modules/admin/panels/configs.sites.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160903
 */
?>
<style>
.groupHeader span.label {float:right; font-size:12px; height:18px; border:1px solid transparent; padding:0px 3px; line-height:18px; margin-left:5px; border-radius:3px;}
.groupHeader span.label.merge {border-color:#f44336; background-color:#f44336; color:#fff;}
.groupHeader span.label.unique {border-color:#2196F3; background-color:#2196F3; color:#fff;}
</style>

<script>
var panel = new Ext.grid.Panel({
	id:"SiteList",
	border:false,
	tbar:[
		new Ext.Button({
			iconCls:"fa fa-plus",
			text:Admin.getLanguage("configs/sites/addSite"),
			handler:function() {
				Admin.configs.sites.add();
			}
		})
	],
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
		groupField:"grouping",
		groupDir:"ASC",
		fields:["grouping","url","domain","language","title","description","templet","favicon","is_ssl","is_default","sort"],
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
		text:Admin.getLanguage("configs/sites/columns").domain+" / "+Admin.getLanguage("configs/sites/columns").language,
		width:280,
		dataIndex:"url",
		summaryType:"count",
		renderer:function(value,p,record) {
			p.style = "background:url("+record.data.favicon+") no-repeat 5px 50%; background-size:22px 22px; padding-left:35px;";
			return value;
		},
		summaryRenderer:function(value) {
			return value+" language"+(value > 1 ? "s" : "");
		}
	},{
		text:Admin.getLanguage("configs/sites/columns").title,
		width:150,
		dataIndex:"title"
	},{
		text:Admin.getLanguage("configs/sites/columns").description,
		minWidth:150,
		flex:1,
		sortable:true,
		dataIndex:"description"
	},{
		text:Admin.getLanguage("configs/sites/columns").templet,
		width:90,
		sortable:true,
		dataIndex:"templet"
	}],
	selModel:new Ext.selection.RowModel(),
	features:[{
		ftype:"groupingsummary",
		groupHeaderTpl:'<div class="groupHeader">{[values.children[0].data.domain]} <tpl if="[values.children[0].data.member] == \'MERGE\'"><span class="label merge">{[Admin.getLanguage("configs/sites/member/"+[values.children[0].data.member])]}</span><tpl else><span class="label unique">{[Admin.getLanguage("configs/sites/member/"+[values.children[0].data.member])]}</span></tpl></div>',
		hideGroupedHeader:false,
		enableGroupingMenu:false
	}],
	listeners:{
		itemdblclick:function(grid,record) {
			Admin.configs.sites.add(record.data.domain,record.data.language);
		}
	}
});
</script>