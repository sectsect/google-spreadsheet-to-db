jQuery(function() {
	// Extend <select>
	jQuery("#google_ss2db_dataformat").select2();

	// Acorddiion Panel
	jQuery("dl.acorddion dt span").not(".ss2db_delete").on("click", function() {
		jQuery(this).parent().next().slideToggle();
		jQuery(this).closest('dl').toggleClass("opened");
	});

	// Delete the Data form DB
	jQuery(".acorddion .ss2db_delete").on("click", function() {
		var theid = jQuery(this).closest('dl').attr('data-id');
		swal({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!',
			showLoaderOnConfirm: true,
			preConfirm: function() {
				return new Promise(function(resolve) {
					jQuery.ajax( {
						url        : google_ss2db_data.plugin_dir_url + 'includes/delete.php',
						dataType   : "json",
						data       : {id : theid, nonce : google_ss2db_data.nonce},
						type       : "post",
						beforeSend : function() {}
					} ).done(function(data) {
						swal('Deleted!', data.message, data.status);
					} ).fail(function(XMLHttpRequest, textStatus, errorThrown) {
						swal('Oops...', 'Something went wrong with Ajax !', 'error');
					} ).always(function(data) {
						if (data.res == 1) {
							var ele = jQuery(".acorddion[data-id='" + data.id + "']");
							jQuery.ajaxSetup( { cache: true } );
							jQuery.when(
								ele.stop(true, true).animate( {
									"height" : "0",
									"margin" : "0"
								}, 300)
							).done(function() {
								setTimeout(jQuery.proxy(function() {
									ele.remove();
								}, this), 600);
							});
							jQuery.ajaxSetup( { cache: false } );
						}
					});
				});
			},
			allowOutsideClick: false
		});
	});
});
