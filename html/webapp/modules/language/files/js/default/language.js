var clsLanguage = Class.create();
var languageCls = Array();

clsLanguage.prototype = {
	initialize: function(id) {
		this.id = id;
	},
	switchLanguage: function(permalink, select_lang) {
		if(permalink == '') permalink = '?';
		var url = _nc_base_url + '/' + permalink + 'lang=' + select_lang;
		window.location = url;
	}
}
