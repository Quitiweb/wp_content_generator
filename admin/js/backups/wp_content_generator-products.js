(function( $ ) {
	'use strict';

	$(function() {

		$('#wp_content_generatorListProductsTbl').DataTable();
		var is_sending = false;

		$('#wp_content_generatorGenProductForm').submit(function (e) {
			if (is_sending) {
				return false; // Don't let someone submit the form while it is in-progress...
			}
			e.preventDefault(); // Prevent the default form submit
			$('.remaining_products').val($('.wp_content_generator-product_count').val());
			var $this = $(this); // Cache this
			// call ajax here
			//$('.remaining_notification').html('').html('<p>Products generator initialized. Waiting for the first response...</p>');
			$('.dcsLoader').show();
			wp_content_generator_generateProductsLoop($this);
		});

		function handleFormError () {
			is_sending = false; // Reset the is_sending var so they can try again...
			$('.wp_content_generator-error-msg').html('Something went wrong. Please try again').fadeIn('fast').delay(1000).fadeOut('slow');
		}

		function wp_content_generator_generateProductsLoop($that){
			var $this = $that;
			var url = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
			$.ajax({
				url: url,
				type: 'post',
				dataType: 'JSON', // Set this so we don't need to decode the response...
				data: $this.serialize(), // One-liner form data prep...
				beforeSend: function () {
					is_sending = true;
					$('.wp_content_generatorGenerateProducts').val('Generating products.');
					// You could do an animation here...
				},
				error: handleFormError,
				success: function (data) {
					$('.wp_content_generatorGenerateProducts').val('Generate products.');
					if (data.status === 'success' && data.remaining_products>0) {
						$('.remaining_products').val(data.remaining_products);
						var totalOfProducts = $('.wp_content_generator-product_count').val();
						
						// loader
						var wp_content_generatorcompletedPer = Math.round(( (totalOfProducts - data.remaining_products ) * 100 ) /totalOfProducts);
						$('.wp_content_generatorLoaderPer').text(wp_content_generatorcompletedPer+'%');
						var addedClass = 'p'+wp_content_generatorcompletedPer;
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass(addedClass);
						// loader

						//$('.remaining_notification').html('').html('<p>'+data.remaining_products+' products are remaining out of '+totalOfProducts+'</p>');
						wp_content_generator_generateProductsLoop($this);
					}else if (data.status === 'success' && data.remaining_products==0){
						
						// loader
						$('.wp_content_generatorLoaderPer').text('100%');
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass('p100');
						$('.dcsLoader').hide();
						$('.wp_content_generatorLoaderPer').text('0%');
						$('.wp_content_generatorLoaderWrpper').attr('class','wp_content_generatorLoaderWrpper c100 blue');
						$('.wp_content_generatorLoaderWrpper').addClass('p0');
						// loader

						$('.wp_content_generator-success-msg').html('Products generated successfully.').fadeIn('fast').delay(1000).fadeOut('slow');
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
