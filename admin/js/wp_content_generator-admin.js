!(function (e) {
    "use strict";
    e(function () {
        // Función auxiliar para mostrar mensajes
        function showMessage(type, message) {
            var messageClass = type === 'error' ? 'wp_content_generator-error-msg' : 'wp_content_generator-success-msg';
            
            // Crear o actualizar el contenedor de mensajes
            var messageContainer = e('.' + messageClass);
            if (messageContainer.length === 0) {
                messageContainer = e('<div>').addClass(messageClass).appendTo('body');
            }

            var messageHtml = '<div class="message-container">' +
                            '<span class="message-text">' + message + '</span>' +
                            '<span class="close-message">×</span>' +
                            '</div>';

            messageContainer
                .html(messageHtml)
                .show()
                .delay(3000)
                .fadeOut('slow');
        }

        // Manejador para cerrar mensajes
        e(document).on('click', '.close-message', function() {
            e(this).closest('.message-container').parent().hide();
        });

        // Función del Data Cleaner
		e(document).on("click", ".wp_content_generatorDataCleaner", function (t) {
            t.preventDefault();
            var n,
                a,
                o = e(this).attr("id");
            console.log(o);
            var s = "";

			// Delete posts section
			switch (o) {
                case "wp-admin-bar-wp_content_generatorDeleteUsers":
                    s = "wp_content_generatorDeleteUsers";
                    break;
                case "wp-admin-bar-wp_content_generatorDeletePosts":
                    s = "wp_content_generatorDeletePosts";
                    break;
                case "wp-admin-bar-wp_content_generatorDeleteProducts":
                    s = "wp_content_generatorDeleteProducts";
                    break;
                case "wp-admin-bar-wp_content_generatorDeleteThumbnails":
                    s = "wp_content_generatorDeleteThumbnails";
            }

            (n = s),
                (a = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url),
                e.ajax({
                    url: a,
                    type: "post",
                    dataType: "JSON",
                    data: { action: n, nonce: wp_content_generator_backend_ajax_object.nonce },
                    beforeSend: function () {
                        e("#wpfooter").append('<div class="wp_content_generatorLoading">Loading&#8230;</div>'), e("#wpfooter").show();
                    },
                    error: r,
                    success: function (t) {
                        e(".wp_content_generatorLoading").remove();
                        if ("success" === t.status) {
                            if (t.message) {
                                showMessage('success', t.message);
                            }
                        } else {
                            r();
                        }
                    },
                }),
                console.log(s);
        }),

        // Tabla para el listado de Posts
		e("#wp_content_generatorListPostsTbl").DataTable(),
		// Tabla para el listado Productos
        e("#wp_content_generatorListProductsTbl").DataTable();

        var t = !1;

		function r() {
            t = !1;
            showMessage('error', 'Something went wrong. Please try again');
        }

        // Función JS para la generación de Posts Standard
		e("#wp_content_generatorGenPostForm").submit(function (n) {
            if (t) return !1; // Don't let someone submit the form while it is in-progress...
            n.preventDefault(); // Prevents the default form submit

            e(".remaining_posts").val(e(".wp_content_generator-post_count").val());
            var a = e(this);

            e(".dcsLoader").show(),
                (function n(a) {
                    var o = a,
                        s = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
                    e.ajax({
                        url: s,
                        type: "post",
                        dataType: "JSON",
                        data: o.serialize(),
                        beforeSend: function () {
                            t = !0;
                            e(".wp_content_generatorGeneratePosts").val("Generating posts...");
                            e(".wp_content_generator-info-msg").html("Generando post...").fadeIn('fast');
                        },
                        error: function() {
                            showMessage('error', 'Error de conexión al generar los posts');
                            t = false;
                        },
                        success: function (a) {
                            e(".wp_content_generatorGeneratePosts").val("Generate posts");
                            e(".wp_content_generator-info-msg").fadeOut('fast');

                            if (a.status === 'error' || (a.message && a.message.startsWith('error:'))) {
                                showMessage('error', a.message.replace('error:', ''));
                                t = false;
                                return;
                            }

                            if ("success" === a.status && a.remaining_posts > 0) {
                                e(".remaining_posts").val(a.remaining_posts);
                                var s = e(".wp_content_generator-post_count").val(),
                                    d = Math.round(((s - a.remaining_posts) * 100) / s);
                                e(".wp_content_generatorLoaderPer").text(d + "%"),
                                    e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                    e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
                                    n(o);
                            } else if ("success" === a.status && 0 == a.remaining_posts) {
                                var messageElement = e(".wp_content_generator-success-msg");
                                if (messageElement.length === 0) {
                                    e("body").append('<div class="wp_content_generator-success-msg"></div>');
                                    messageElement = e(".wp_content_generator-success-msg");
                                }
                                if (a.message) {
                                    e(".wp_content_generator-success-msg").html(a.message).fadeIn("fast").delay(1e3).fadeOut("slow");
                                } else {
                                    e(".wp_content_generator-success-msg").html("Posts generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow");
                                }
                                e(".wp_content_generatorLoaderPer").text("100%"),
                                    e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                    e(".wp_content_generatorLoaderWrpper").addClass("p100"),
                                    e(".dcsLoader").hide(),
                                    e(".wp_content_generatorLoaderPer").text("0%"),
                                    e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                    e(".wp_content_generatorLoaderWrpper").addClass("p0"),
                                    (t = !1);
                            } else {
                                r(), (t = !1);
                            }
                        },
                    });
                })(a);
        }),

        // Función JS para la generación de Posts AWS
        e("#wp_content_generatorGenAWSPostForm").submit(function (n) {
            if (t) return !1;
            n.preventDefault();

            var asins = e('.wp_content_generator-post_asin').val().trim();
            if (!asins) {
                showMessage('error', 'Por favor, introduce al menos un ASIN');
                return false;
            }

            // Dividir ASINs y procesar uno por uno
            var asins_array = asins.split(/\s+/).filter(asin => asin.length > 0);
            e(".remaining_posts").val(asins_array.length);
            e(".remaining_asins").val(asins_array.join(" "));

            // Procesar el primer ASIN
            processNextAsin(asins_array);
            return false;
        });

        function processNextAsin(asins_array) {
            console.log('Processing ASINs:', asins_array);

            if (!asins_array || asins_array.length === 0) {
                showMessage('success', '¡Todos los ASINs han sido procesados!');
                e(".dcsLoader").hide();
                return;
            }

            var formData = e("#wp_content_generatorGenAWSPostForm").serialize();
            e.ajax({
                url: wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url,
                type: "POST",
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('API Response:', response);
                    
                    if (response.error) {
                        showMessage('error', "Error: " + response.message);
                        return;
                    }
                    
                    if (response.status === "success") {
                        if (response.is_complete) {
                            showMessage('success', '¡Todos los ASINs han sido procesados!');
                            e(".dcsLoader").hide();
                            return;
                        }

                        e(".wp_content_generator-info-msg")
                            .html("ASIN " + response.current_asin + " procesado correctamente.")
                            .fadeIn("fast");

                        // Actualizar remaining_asins en el formulario
                        e(".remaining_asins").val(response.remaining_asins);
                        processNextAsin(response.remaining_asins.split(/\s+/).filter(Boolean));
                    } else {
                        showMessage('error', response.message || "Error desconocido al procesar ASIN");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    showMessage('error', "Error de conexión al procesar ASINs: " + textStatus);
                }
            });
        }

        // Función para los Tests
		e("#wp_content_generatorTestForm").submit(function (n) {

			console.log('HOLAAAAAAAAAAAAAAAAAAAAA!!!!!');

            if (t) return !1; // Don't let someone submit the form while it is in-progress...
            n.preventDefault(); // Prevents the default form submit

            e(".remaining_posts").val(e(".wp_content_generator-post_count").val());
            var a = e(this);

			e(".dcsLoader").show(),
				(function n(a) {
					var o = a,
						s = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
					e.ajax({
						url: s,
						type: "post",
						dataType: "JSON",
						data: o.serialize(),
						beforeSend: function () {
							t = !0;
							e(".wp_content_generatorGenerateTest").val("Generating posts...");
							e(".wp_content_generator-info-msg").html("Generando post...").fadeIn('fast');
						},
						error: function() {
							showMessage('error', 'Error de conexión al generar los posts');
							t = false;
						},
						success: function (a) {
							e(".wp_content_generatorGenerateTest").val("Generate Test posts");
							e(".wp_content_generator-info-msg").fadeOut('fast');

							if (a.status === 'error' || (a.message && a.message.startsWith('error:'))) {
								showMessage('error', a.message.replace('error:', ''));
								t = false;
								return;
							}

							if ("success" === a.status && a.remaining_posts > 0) {
								console.log('Entramos en el IF');
								e(".remaining_posts").val(a.remaining_posts);
								var s = e(".wp_content_generator-post_count").val(),
									d = Math.round(((s - a.remaining_posts) * 100) / s);
								e(".wp_content_generatorLoaderPer").text(d + "%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
									n(o);
							} else if ("success" === a.status && 0 == a.remaining_posts) {
                                var messageElement = e(".wp_content_generator-success-msg");
                                if (messageElement.length === 0) {
                                    e("body").append('<div class="wp_content_generator-success-msg"></div>');
                                    messageElement = e(".wp_content_generator-success-msg");
                                }
                                if (a.message) {
                                    e(".wp_content_generator-success-msg").html(a.message).fadeIn("fast").delay(1e3).fadeOut("slow");
                                } else {
                                    e(".wp_content_generator-success-msg").html("Posts generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow");
                                }
                                e(".wp_content_generatorLoaderPer").text("100%"),
                                    e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                    e(".wp_content_generatorLoaderWrpper").addClass("p100"),
                                    e(".dcsLoader").hide(),
                                    e(".wp_content_generatorLoaderPer").text("0%"),
                                    e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                    e(".wp_content_generatorLoaderWrpper").addClass("p0"),
                                    (t = !1);
                            } else {
                                r(), (t = !1);
                            }
						},
					});
				})(a);
        }),

		/**
		 *  
		 * Estas funciones van aparte.
		 * Están relacionadas con Generación de Productos y Generación de Usuarios.
		 * Aún no los utilizamos (2024)
		 * 
		*/
		e("#wp_content_generatorGenProductForm").submit(function (n) {
			if (t) return !1;
			n.preventDefault(), e(".remaining_products").val(e(".wp_content_generator-product_count").val());
			var a = e(this);

			e(".dcsLoader").show(),
				(function n(a) {
					var o = a,
						s = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
					e.ajax({
						url: s,
						type: "post",
						dataType: "JSON",
						data: o.serialize(),
						beforeSend: function () {
							(t = !0), e(".wp_content_generatorGenerateProducts").val("Generating products.");
						},
						error: r,
						success: function (a) {
							if ("success" === a.status && a.remaining_products > 0) {
								e(".remaining_products").val(a.remaining_products);
								var s = e(".wp_content_generator-product_count").val(),
									d = Math.round(((s - a.remaining_products) * 100) / s);
								e(".wp_content_generatorLoaderPer").text(d + "%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
									n(o);
							} else if ("success" === a.status && 0 == a.remaining_products) {
                                var messageElement = e(".wp_content_generator-success-msg");
                                if (messageElement.length === 0) {
                                    e("body").append('<div class="wp_content_generator-success-msg"></div>');
                                    messageElement = e(".wp_content_generator-success-msg");
                                }
                                if (a.message) {
                                    e(".wp_content_generator-success-msg").html(a.message).fadeIn("fast").delay(1e3).fadeOut("slow");
                                } else {
                                    e(".wp_content_generator-success-msg").html("Products generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow");
                                }
								e(".wp_content_generatorLoaderPer").text("100%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p100"),
									e(".dcsLoader").hide(),
									e(".wp_content_generatorLoaderPer").text("0%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p0"),
									(t = !1);
                            } else {
                                r(), (t = !1);
                            }
						},
					});
				})(a);
		}),

		e("#wp_content_generatorGenUserForm").submit(function (n) {
			if ((wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url, t)) return !1;
			n.preventDefault(),
				e(".remaining_users").val(e(".wp_content_generator-user_count").val()),
				e(".dcsLoader").show(),

				(function n(a) {
					var o = a,
						s = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;
					e.ajax({
						url: s,
						type: "post",
						dataType: "JSON",
						data: o.serialize(),
						beforeSend: function () {
							(t = !0), e(".wp_content_generatorGenerateUsers").val("Generating users.");
						},
						error: r,
						success: function (a) {
							if ("success" === a.status && a.remaining_users > 0) {
								e(".remaining_users").val(a.remaining_users);
								var s = e(".wp_content_generator-user_count").val(),
									d = Math.round(((s - a.remaining_users) * 100) / s);
								e(".wp_content_generatorLoaderPer").text(d + "%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
									n(o);
							} else if ("success" === a.status && 0 == a.remaining_users) {
                                var messageElement = e(".wp_content_generator-success-msg");
                                if (messageElement.length === 0) {
                                    e("body").append('<div class="wp_content_generator-success-msg"></div>');
                                    messageElement = e(".wp_content_generator-success-msg");
                                }
                                if (a.message) {
                                    e(".wp_content_generator-success-msg").html(a.message).fadeIn("fast").delay(1e3).fadeOut("slow");
                                } else {
                                    e(".wp_content_generator-success-msg").html("Users generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow");
                                }
								e(".wp_content_generatorLoaderPer").text("100%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p100"),
									e(".dcsLoader").hide(),
									e(".wp_content_generatorLoaderPer").text("0%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p0"),
									e(".wp_content_generatorGenerateUsers").val("Generate users."),
									(t = !1);
                            } else {
                                r(), (t = !1);
                            }
						},
					});
				})(e(this));
		});

    });

})(jQuery);