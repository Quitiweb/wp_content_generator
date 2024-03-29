(function( $ ) {
	'use strict';

	$(function() {

		// manage data from adminbar links
		$(document).on('click','.wp_content_generatorDataCleaner',function(event){
			event.preventDefault();
			var wp_content_generatorEventID = $(this).attr('id');
			console.log(wp_content_generatorEventID);
			var wp_content_generatorAction = '';
			switch(wp_content_generatorEventID) {
			    case 'wp-admin-bar-wp_content_generatorDeleteUsers':
			        wp_content_generatorAction = 'wp_content_generatorDeleteUsers';
			        break;
			    case 'wp-admin-bar-wp_content_generatorDeletePosts':
			        wp_content_generatorAction = 'wp_content_generatorDeletePosts';
			        break;
			    case 'wp-admin-bar-wp_content_generatorDeleteProducts':
			        wp_content_generatorAction = 'wp_content_generatorDeleteProducts';
			        break;
			    case 'wp-admin-bar-wp_content_generatorDeleteThumbnails':
			        wp_content_generatorAction = 'wp_content_generatorDeleteThumbnails';
			        break;			    
			    default:

			}
			wp_content_generatorAjaxManageData(wp_content_generatorAction);
			console.log(wp_content_generatorAction);
		});

		function wp_content_generatorAjaxManageData(wp_content_generatorAction){
			var url = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
			$.ajax({
				url: url,
				type: 'post',
				dataType: 'JSON', // Set this so we don't need to decode the response...
				data:  ({ action: wp_content_generatorAction}), // One-liner form data prep...
				beforeSend: function () {
					$('#wpfooter').append('<div class="wp_content_generatorLoading">Loading&#8230;</div>');
					$('#wpfooter').show();
					// You could do an animation here...
				},
				error: handleFormError,
				success: function (data) {
					$('.wp_content_generatorLoading').remove();
					if (data.status === 'success') {
						console.log('success');
					}else {
						handleFormError(); // If we don't get the expected response, it's an error...
						//is_sending = false;
					}
				}
			});
		}
		// manage data from adminbar links

		// var data_val = $('#wp_content_generatorGenPostForm').serialize();
		$('#wp_content_generatorListPostsTbl').DataTable();
		var is_sending = false;
		// failure_message = 'Whoops, looks like there was a problem. Please try again later.';

		$('#wp_content_generatorGenPostForm').submit(function (e) {
			if (is_sending) {
				return false; // Don't let someone submit the form while it is in-progress...
			}
			e.preventDefault(); // Prevents the default form submit
			// if ASIN is not blank, we need to count the number of ASINs and loop
			var asins = $('.wp_content_generator-post_asin').val();
			// log
			console.log('asins y -post_asin from wp_content_generatorGenPostForm');
			console.log(asins);
			console.log($('.wp_content_generator-post_asin').val());
			if (asins == '' || asins == null) {
				$('.remaining_posts').val($('.wp_content_generator-post_count').val());
			}else {
				var asins_array = asins.split(" ");
				$('.remaining_posts').val(asins_array.length);
				$('.remaining_asins').val(asins);
			}
			// log
			console.log('remaining_posts');
			console.log($('.remaining_posts').val());

			var $this = $(this); // Cache this
			// call ajax here
			$('.dcsLoader').show();
			// $('.remaining_notification').html('').html('<p>Post generator initialized. Waiting for the first response...</p>');
			wp_content_generator_generatePostsLoop($this);
		});

		function handleFormError () {
			is_sending = false; // Reset the is_sending var so they can try again...
			$('.wp_content_generator-error-msg').html('Something went wrong. Please try again').fadeIn('fast').delay(1000).fadeOut('slow');
			// alert(failure_message);
		}

		function wp_content_generator_generatePostsLoop($that){
			var $this = $that;
			var url = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
			$.ajax({
				url: url,
				type: 'post',
				dataType: 'JSON', // Set this so we don't need to decode the response...
				data: $this.serialize(), // One-liner form data prep...
				beforeSend: function () {
					is_sending = true;
					$('.wp_content_generatorGeneratePosts').val('Generating posts...');
					// You could do an animation here...
				},
				error: handleFormError,
				success: function (data) {
					$('.wp_content_generatorGeneratePosts').val('Generate posts');
					// log
					console.log('data.remaining_posts');
					console.log(data.remaining_posts);
					if (data.status === 'success' && data.remaining_posts>0) {
						var totalOfPosts = 0;
						// if ASIN is not blank, we need to count the number of ASINs and loop
						var asins = $('.wp_content_generator-post_asin').val();
						// log
						console.log('asins y -post_asin');
						console.log(asins);
						console.log($('.wp_content_generator-post_asin').val());
						if (asins == '' || asins == null) {
							totalOfPosts = $('.wp_content_generator-post_count').val();
						}else {
							totalOfPosts = asins.length;
						}
						// log
						console.log('total of posts');
						console.log(totalOfPosts);
						$('.remaining_posts').val(data.remaining_posts);
						$('.remaining_asins').val(data.remaining_asins);
						// loader
						var wp_content_generatorcompletedPer = Math.round(( (totalOfPosts - data.remaining_posts ) * 100 ) /totalOfPosts);
						$('.wp_content_generatorLoaderPer').text(wp_content_generatorcompletedPer+'%');
						var addedClass = 'p'+wp_content_generatorcompletedPer;
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass(addedClass);
						// loader

						//$('.remaining_notification').html('').html('<p>'+data.remaining_posts+' posts are remaining out of '+totalOfPosts+'</p>');
						wp_content_generator_generatePostsLoop($this);
					}else if (data.status === 'success' && data.remaining_posts==0){
						$('.wp_content_generator-success-msg').html('Posts generated successfully.').fadeIn('fast').delay(1000).fadeOut('slow');
						// loader
						$('.wp_content_generatorLoaderPer').text('100%');
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass('p100');
						$('.dcsLoader').hide();
						$('.wp_content_generatorLoaderPer').text('0%');
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass('p0');
						// loader
						//$('.remaining_notification').html('');
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
