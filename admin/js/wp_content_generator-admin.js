!(function (e) {
    "use strict";
    e(function () {

        e(document).on("click", ".wp_content_generatorDataCleaner", function (t) {
            t.preventDefault();
            var n,
                a,
                o = e(this).attr("id");
            console.log(o);
            var s = "";
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
                        e(".wp_content_generatorLoading").remove(), "success" === t.status ? console.log("success") : r();
                    },
                }),
                console.log(s);
        }),

        e("#wp_content_generatorListPostsTbl").DataTable(),
        e("#wp_content_generatorListProductsTbl").DataTable();
        var t = !1;
        
		function r() {
            (t = !1), e(".wp_content_generator-error-msg").html("Something went wrong. Please try again").fadeIn("fast").delay(1e3).fadeOut("slow");
        }

        e("#wp_content_generatorGenPostForm").submit(function (n) {
            if (t) return !1;
            n.preventDefault(), e(".remaining_posts").val(e(".wp_content_generator-post_count").val());
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
                            (t = !0), e(".wp_content_generatorGeneratePosts").val("Generating posts...");
                        },
                        error: r,
                        success: function (a) {
                            if ((e(".wp_content_generatorGeneratePosts").val("Generate posts"), "success" === a.status && a.remaining_posts > 0)) {
                                e(".remaining_posts").val(a.remaining_posts);
                                var s = e(".wp_content_generator-post_count").val(),
                                    d = Math.round(((s - a.remaining_posts) * 100) / s);
                                e(".wp_content_generatorLoaderPer").text(d + "%"),
                                    e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                    e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
                                    n(o);
                            } else
                                "success" === a.status && 0 == a.remaining_posts
                                    ? (e(".wp_content_generator-success-msg").html("Posts generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow"),
                                      e(".wp_content_generatorLoaderPer").text("100%"),
                                      e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                      e(".wp_content_generatorLoaderWrpper").addClass("p100"),
                                      e(".dcsLoader").hide(),
                                      e(".wp_content_generatorLoaderPer").text("0%"),
                                      e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
                                      e(".wp_content_generatorLoaderWrpper").addClass("p0"),
                                      (t = !1))
                                    : (r(), (t = !1));
                        },
                    });
                })(a);
        }),

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
							if ((e(".wp_content_generatorGenerateProducts").val("Generate products."), "success" === a.status && a.remaining_products > 0)) {
								e(".remaining_products").val(a.remaining_products);
								var s = e(".wp_content_generator-product_count").val(),
									d = Math.round(((s - a.remaining_products) * 100) / s);
								e(".wp_content_generatorLoaderPer").text(d + "%"),
									e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
									e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
									n(o);
							} else
								"success" === a.status && 0 == a.remaining_products
									? (e(".wp_content_generatorLoaderPer").text("100%"),
										e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
										e(".wp_content_generatorLoaderWrpper").addClass("p100"),
										e(".dcsLoader").hide(),
										e(".wp_content_generatorLoaderPer").text("0%"),
										e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
										e(".wp_content_generatorLoaderWrpper").addClass("p0"),
										e(".wp_content_generator-success-msg").html("Products generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow"),
										(t = !1))
									: (r(), (t = !1));
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
							} else
								"success" === a.status && 0 == a.remaining_users
									? (e(".wp_content_generator-success-msg").html("Users generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow"),
										e(".wp_content_generatorLoaderPer").text("100%"),
										e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
										e(".wp_content_generatorLoaderWrpper").addClass("p100"),
										e(".dcsLoader").hide(),
										e(".wp_content_generatorLoaderPer").text("0%"),
										e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
										e(".wp_content_generatorLoaderWrpper").addClass("p0"),
										e(".wp_content_generatorGenerateUsers").val("Generate users."),
										(t = !1))
									: (r(), (t = !1));
						},
					});
				})(e(this));
		});

    });

})(jQuery);
