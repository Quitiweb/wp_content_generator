(function( $ ) {
	'use strict';

	$(function() {

		var is_sending = false;
		
		$('#wp_content_generatorGenUserForm').submit(function (e) {
			var url = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
			if (is_sending) {
				return false; // Don't let someone submit the form while it is in-progress...
			}
			e.preventDefault(); // Prevent the default form submit
			$('.remaining_users').val($('.wp_content_generator-user_count').val());
			$('.dcsLoader').show();
			//$('.remaining_notification').html('').html('<p>User generator initialized. Waiting for the first response...</p>');
			var $this = $(this); // Cache this
			wp_content_generator_generateUsersLoop($this)
		});

		function handleFormError () {
			is_sending = false; // Reset the is_sending var so they can try again...
			$('.wp_content_generator-error-msg').html('Something went wrong. Please try again').fadeIn('fast').delay(1000).fadeOut('slow');
		}

		function wp_content_generator_generateUsersLoop($that){
			var $this = $that;
			var url = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
			$.ajax({
				url: url,
				type: 'post',
				dataType: 'JSON', // Set this so we don't need to decode the response...
				data: $this.serialize(), // One-liner form data prep...
				beforeSend: function () {
					is_sending = true;
					$('.wp_content_generatorGenerateUsers').val('Generating users.');
					// You could do an animation here...
				},
				error: handleFormError,
				success: function (data) {
					if (data.status === 'success' && data.remaining_users>0) {
						$('.remaining_users').val(data.remaining_users);
						var totalOfUsers = $('.wp_content_generator-user_count').val();

						// loader
						var wp_content_generatorcompletedPer = Math.round(( (totalOfUsers - data.remaining_users ) * 100 ) /totalOfUsers);
						$('.wp_content_generatorLoaderPer').text(wp_content_generatorcompletedPer+'%');
						var addedClass = 'p'+wp_content_generatorcompletedPer;
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass(addedClass);
						// loader

						//$('.remaining_notification').html('').html('<p>'+data.remaining_users+' users are remaining out of '+totalOfUsers+'</p>');
						wp_content_generator_generateUsersLoop($this);
					}else if (data.status === 'success' && data.remaining_users==0){
						$('.wp_content_generator-success-msg').html('Users generated successfully.').fadeIn('fast').delay(1000).fadeOut('slow');
						//$('.remaining_notification').html('');
						// loader
						$('.wp_content_generatorLoaderPer').text('100%');
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass('p100');
						$('.dcsLoader').hide();
						$('.wp_content_generatorLoaderPer').text('0%');
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass('p0');
						//loader
						$('.wp_content_generatorGenerateUsers').val('Generate users.');
						is_sending = false;
					}else {
						handleFormError(); // If we don't get the expected response, it's an error...
						is_sending = false;
					}
					
				}
			});
		}

	});

})( jQuery );
