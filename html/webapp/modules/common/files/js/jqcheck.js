var clsJqcheck = Class.create();

clsJqcheck.prototype = {
	initialize: function() {
		this.loadedFiles = new Array();
	},
	jqload: function(dir_name, check, next) {
		if(!this.loadedFiles[dir_name]) {
			this.loadedFiles[dir_name] = true;
			commonCls.load(_nc_core_base_url + _nc_index_file_name + "?action=common_download_js&add_block_flag=1&dir_name=" + dir_name + "&vs=" + _nc_js_vs, check, function(){jQuery.noConflict(); if(next) {next();}});
		}
		else {
			jcheck = new Function('return !!(' + check + ')');
			commonCls.wait(jcheck,next);
		}
	}
}
jqcheckCls = new clsJqcheck();
