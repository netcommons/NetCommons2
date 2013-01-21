(function ($) {
	$.fn.nc_jqm_alert = function(msg) {
		$( "<div class='ui-loader ui-overlay-shadow ui-body-e ui-corner-all'><h1>" + msg + "</h1></div>" )
			.css({ "display": "block", "opacity": 0.96, "top": $(window).scrollTop() + 100 })
			.appendTo( $.mobile.activePage )
			.delay( 800 )
			.fadeOut( 400, function() {
				$( this ).remove();
			});
	};
})(jQuery);