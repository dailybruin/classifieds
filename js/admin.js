(function($) {
	$(function() {
		// Place your administration-specific JavaScript here
		
		$('button#add_posts').click(function() {
			
			$('#status').html("<img src='"+pluginurl+"img/290.gif'/><p>Adding...</p>")
			
			jQuery.post(ajaxurl,{action:'insert_posts',publish:'add'}, function(response) {
				
				if (response.charAt(response.length-1) == "0") {
					response = response.substring(0,response.length-1);
				}

				response = jQuery.parseJSON(response);

				$('#status').html("Added " + response.added + " ads.");

			});
		});


		$('button#replace_posts').click(function() {

			$('#status').html("<img src='"+pluginurl+"img/290.gif'/><p>Replacing...</p>")
			
			jQuery.post(ajaxurl,{action:'insert_posts',publish:'replace'}, function(response) {
				
				if (response.charAt(response.length-1) == "0") {
					response = response.substring(0,response.length-1);
				}

				response = jQuery.parseJSON(response);

				$('#status').html("<p>Deleted " + response.deleted + " ads. Added " + response.added + " ads.</p>");

			});
		});
		
	});
})(jQuery);