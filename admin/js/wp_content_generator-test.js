!(function (e) {

    "use strict";

    e(function () {

        var t = !1;
        
		function r() {
            (t = !1), e(".wp_content_generator-error-msg").html("Something went wrong. Please try again").fadeIn("fast").delay(1e3).fadeOut("slow");
        }

        e("#wp_content_generatorTestForm").submit(function (n) {

			console.log('HOLAAAAAAAAAAAAAAAAAAAAA!!!!!');

            if (t) return !1; // Don't let someone submit the form while it is in-progress...
            n.preventDefault(); // Prevents the default form submit

            e(".remaining_posts").val(e(".wp_content_generator-post_count").val());
            var a = e(this);

			console.log('Valor de .remaining_posts wp_content_generatorTestForm');
			console.log(e(".remaining_posts").val(a.remaining_posts));

            e(".dcsLoader").show();

			(function n(a) {
				var o = a,
					s = wp_content_generator_backend_ajax_object.wp_content_generator_ajax_url;

				e.ajax({
					url: s,
					type: "post",
					dataType: "JSON",
					data: o.serialize(),
					beforeSend: function () {
						(t = !0), e(".wp_content_generatorGenerateTest").val("Generating posts...");
					},
					error: r,
					success: function (a) {
						if ((e(".wp_content_generatorGenerateTest").val("Generate posts"), "success" === a.status && a.remaining_posts > 0)) {
							e(".remaining_posts").val(a.remaining_posts);
							var s = e(".wp_content_generator-post_count").val(),
								d = Math.round(((s - a.remaining_posts) * 100) / s);
							e(".wp_content_generatorLoaderPer").text(d + "%"),
								e(".wp_content_generatorLoaderWrpper").attr("class", "wp_content_generatorLoaderWrpper c100 blue"),
								e(".wp_content_generatorLoaderWrpper").addClass("p" + d),
								n(o);
						} else
							"success" === a.status && 0 == a.remaining_posts
								? (e(".wp_content_generator-success-msg").html("Test Posts generated successfully.").fadeIn("fast").delay(1e3).fadeOut("slow"),
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
        });

    });

})(jQuery);
