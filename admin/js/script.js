jQuery(function(){
	jQuery("#google_ss2db_dataformat").select2();
	jQuery("dl.acorddion dt").on("click", function() {
		jQuery(this).next().slideToggle();
		jQuery(this).parent().toggleClass("opened");
	});
});
