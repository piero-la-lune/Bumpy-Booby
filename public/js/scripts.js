
function highlighter(e, f) {
	if ($("html").hasClass("lt9")) {
		// doesn't work with IE8 and below
		return true;
	}
	hljs.highlightBlock(f);
	var text = e.html().replace(/^\n+|\n+$/g, '');
	if (e.hasClass("blank")) { e.html(text); }
	else {
		var nb = text.split(/\n/).length;
		var html = "<table><td class=\"td-nb\">";
		for (var i = 1; i < nb+1; i++) {
			html += "<span unselectable=\"on\">"+i+"</span>\n";
		}
		e.html(html+"</td><td class=\"td-code\">"+text+"</td></tr></table>");
	}
}

function update_position(scrollto) {
	if ($("html").hasClass("lt8")) { return true; }
	$(".div-affix").each(function() {
		var elm = $(this),
			container = elm.parent(),
			top = $(window).scrollTop(),
			offset = elm.parent().offset(),
			classes = "aligned-fixed aligned-bottom";
		if (elm.height()+24 >= $(window).height()) {
			if (container.hasClass("aligned-fixed")) {
				if (scrollto) {
					container.removeClass(classes).addClass("aligned-bottom");
					$(window).scrollTop(elm.offset().top);
				}
				else {
					container.removeClass(classes);
				}
			}

		}
		else if (top + 12 <= offset.top) {
			container.removeClass(classes);
		}
		else if (top + 12 + elm.height() >= $("body").height()) {
			container.removeClass(classes).addClass("aligned-bottom");
		}
		else {
			container.removeClass(classes).addClass("aligned-fixed");
		}
	});
}

(function($) {
	$.fn.pie = function() {
		return this.each(function() {
			var pie = $(this);
			if (!pie.get(0).getContext) { return true; }
			var ctx = pie.get(0).getContext("2d"),
				pie_width = parseInt(pie.attr("width"), 10),
				pie_height = parseInt(pie.attr("height"), 10),
				pie_radius = pie_height/2-12,
				pie_radius2 = pie_radius/2,
				pie_hover = false,
				pie_data = pies_data[pie.data("data")];
			function pie_get_mouse_position(e) {
				var x = Math.floor((e.pageX-pie.offset().left));
				var y = Math.floor((e.pageY-pie.offset().top));
				var fromCenterX = x-pie_width/2;
				var fromCenterY = y-pie_height/2;
				var fromCenter = Math.sqrt(Math.pow(Math.abs(fromCenterX), 2) +
					Math.pow(Math.abs(fromCenterY), 2));
				if (fromCenter <= pie_radius && fromCenter >= pie_radius2-12) {
					var angle = Math.atan2(fromCenterY, fromCenterX);
					if (angle < 0) angle = 2 * Math.PI + angle; // normalize
					for (var i=0; i<pie_data.length; i++) {
						if (angle <= pie_data[i].end) {
							return i;
						}
					}
				}
				return false;
			}
			function pie_draw_arc(start, end, color, radius, border) {
				ctx.fillStyle = color;
				ctx.beginPath();
				ctx.moveTo(pie_width/2, pie_height/2);
				ctx.arc(pie_width/2, pie_height/2, radius, start, end, false);
				ctx.lineTo(pie_width/2, pie_height/2);
				ctx.fill();
				if (border) { ctx.stroke(); }
			}
			function pie_draw() {
				ctx.clearRect(0, 0, pie_width, pie_height);
				ctx.lineWidth = 2;
				ctx.lineCap = "round";
				ctx.strokeStyle = $("body").css("background-color");
				for (var i=0; i<pie_data.length; i++) {
					var arc = pie_data[i];
					pie_draw_arc(
						arc.start,
						arc.end,
						arc.color,
						pie_radius,
						true
					);
				}
				pie_draw_arc(
					0,
					2*Math.PI,
					$("body").css("background-color"),
					pie_radius-pie_radius2,
					false
				);
			}
			pie.mousemove(function(e) {
				var new_hover = pie_get_mouse_position(e);
				if (new_hover !== pie_hover) {
					if (pie_hover !== false) {
						pie_draw();
						pie.css("cursor", "auto");
					}
					if (new_hover !== false) {
						var arc = pie_data[new_hover];
						ctx.globalCompositeOperation = "destination-over";
						pie_draw_arc(
							arc.start,
							arc.end,
							arc.color,
							pie_radius+6,
							true
						);
						ctx.globalCompositeOperation = "source-over";
						pie.css("cursor", "pointer");
						pie_draw_arc(
							0,
							2*Math.PI,
							arc.color,
							pie_radius-pie_radius2-2,
							false
						);
						ctx.font = "900 32px "+$("body").css("font-family");
						ctx.fillStyle = "#fff";
						ctx.textAlign = "center";
						ctx.textBaseline = "middle";
						ctx.fillText(arc.nb, pie_width/2, pie_height/2, 50);
					}
					pie_hover = new_hover;
				}
			});
			pie.click(function(e) {
				var position = pie_get_mouse_position(e);
				if (position !== false) {
					window.location.href = pie_data[position].url;
				}
			});
			pie_draw();
			pie.closest(".div-pie-statuses").show();
		});
	};
})(jQuery);


$(document).ready(function(){

	$(".box-settings .top").click(function() {
		$(this)
			.find("i")
				.toggleClass("icon-chevron-down")
				.toggleClass("icon-chevron-up").end()
			.closest(".box-settings").find(".inner-form")
				.slideToggle();
	});
	$(".a-help-markdown").click(function() {
		$(this).closest(".box").find(".div-help-markdown").toggle();
	});

	$(".alert").click(function() { $(this).slideUp(); });

	$(".main-right-open").click(function() {
		$(".main-right").toggleClass("open");
	});
	$(".a-menu").click(function() {
		$(".main-right").toggleClass("open");
	});

// Pie chart
	$(".pie-statuses").pie();

// Markdown preview
	$(document).on("click", ".btn-preview", function() {
		var btn = $(this),
			form = $(this).closest("form");
		$.ajax({
			type: "POST",
			url: ajax,
			data: {
				action: "markdown",
				text: form.find("textarea").val()
			}
		}).done(function(ans) {
			ans = jQuery.parseJSON(ans);
			if (ans.success) {
				form
					.find("textarea").hide().end()
					.find(".preview").html(ans.text).show().end()
					.find("pre code").each(function(i,e) {
						highlighter($(this), e);
					});
				btn
					.removeClass("btn-preview")
					.addClass("btn-edit")
					.text(verb_edit);
				update_position(true);
			}
			else {
				alert(ans.text);
			}
		});
		return false;
	});
	$(document).on("click", ".btn-edit", function() {
		$(this)
			.removeClass("btn-edit")
			.addClass("btn-preview")
			.text(verb_preview)
			.closest("form")
				.find(".preview").hide().end()
				.find("textarea").show();
		update_position(true);
		return false;
	});

// Form validation
	$("form").submit(function() {
		if ($(this).find("input[name=\"issue_labels\"]").length) {
			var val = "";
			$(this).find(".label.selected").each(function() {
				val += $(this).data("id")+",";
			});
			$(this).find("input[name=\"issue_labels\"]").val(val);
		}
	});

// Sort
	$(".box-sort-filter form").submit(function(){
		var sel = $(this).find("select"),
			val = "",
			arr = [];
		if (sel.eq(0).val() == "id") { val = "id_"; }
		else { val = "mod_"; }
		if (sel.eq(1).val() == "desc") { val = val+"desc"; }
		else { val = val+"asc"; }
		$(this).find("input[name=\"sort\"]").val(val);

		$(this).find(".btn-status.selected").each(function() {
			arr.push($(this).data("id"));
		});
		$(this).find("input[name=\"statuses\"]").val(arr.join(","));

		val = "all";
		var ok1 = $(this).find(".btn-open").hasClass("selected");
		var ok2 = $(this).find(".btn-closed").hasClass("selected");
		if (ok1 && !ok2) {
			val = "open";
		}
		else if (ok2 && !ok1) {
			val = "closed";
		}
		$(this).find("input[name=\"open\"]").val(val);
	});
	$(".box-sort-filter .btn-open,\
		.box-sort-filter .btn-closed,\
		.box-sort-filter .btn-status"
	).not(".disabled").click(function() {
		$(this).toggleClass("selected").toggleClass("unselected");
	});

// Issue creation / update
	$(".p-edit-labels .label").click(function() {
		$(this).toggleClass("selected").toggleClass("unselected");
	});
	$(".select-status").change(function() {
		if ($(this).find("option:selected").data("match")) {
			$(".select-users").show();
		}
		else {
			$(".select-users").hide();
		}
	}).change();
	$(".btn-cancel").click(function() {
		$(this).closest(".box")
			.find(".t-display").show().end()
			.find(".i-display").hide();
	});
	$(".a-edit-content").click(function() {
		$(this).closest(".box")
			.find(".t-display.div-left").hide().end()
			.find(".i-display.div-left").show();
	});
	$(".a-edit-details").click(function() {
		$(this).closest(".box")
			.find(".t-display.div-right").hide().end()
			.find(".i-display.div-right").show();
		$(".select-status").change();
	});
	$(".a-remove-issue").click(function() {
		if (confirm(confirm_delete_issue)) {
			$(this).closest("form")
				.find("input[name=\"action\"]").attr("name", "delete_issue").end()
				.submit();
		}
	});
	$(".a-notifications").click(function() {
		$(this).closest("form")
			.find("input[name=\"action\"]").attr("name", "notifications").end()
			.submit();
	});
	$(".btn-reopen").click(function() {
		$(this).closest("form").find("input[name=\"issue_open\"]").val("open");
	});
	$(".btn-close").click(function() {
		$(this).closest("form").find("input[name=\"issue_open\"]").val("closed");
	});
	$(".a-edit").click(function() {
		$(this).closest(".box, form")
			.find(".t-display").toggle().end()
			.find(".i-display").toggle();
	});
	$(".a-remove-comment").click(function() {
		if (confirm(confirm_delete_comment)) {
			$(this).closest(".box").find("form")
				.find("input[name=\"edit_comment\"]").attr("name", "delete_comment").end()
				.submit();
		}
	});

// Uploads
	$(".form-upload").each(function() {
		var form = $(this);
		function upload_callback(ans) {
			form
				.find(".btn-upload")
					.toggleClass("disabled")
					.find("span").toggle().end()
					.find("i").toggle().end().end()
				.find(".bar").css("width", 0);
			if (ans.success) {
				form.find(".uploads").append(ans.text);
			}
			else {
				alert(ans.text);
			}
			form
				.find("input[name=\"token\"]").val(ans.token).end()
				.find("input[name=\"upload\"]").remove().end()
				.find(".btn-upload").append("<input type=\"file\" name=\"upload\" />");
			update_position(true);
		}
		form.on("change", "input[name=\"upload\"]", function() {
			var file = $(this)[0].files;

			if (typeof file != "undefined" && window.XMLHttpRequestUpload && window.FormData) {
				file = file[0];
				xhr = new XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						form.find(".bar").css("width", (evt.loaded/evt.total)*100+"%");
					}
				}, false);
				xhr.addEventListener("load", function(evt) {
					upload_callback(jQuery.parseJSON(evt.target.responseText));
				}, false);

				xhr.open("post", ajax, true);

				var formData = new FormData();
				formData.append("type", "xhr");
				formData.append("token", form.find("input[name=\"token\"]").val());
				formData.append("action", "upload");
				formData.append("upload", file);
				xhr.send(formData);
			}
			else {
				form.submit();
			}
			form
				.find(".btn-upload")
					.toggleClass("disabled")
					.find("span").toggle().end()
					.find("i").toggle();
		});
		form.on("click", ".icon-trash", function() {
			var div = $(this).closest("div");
			$.ajax({
				type: "POST",
				url: ajax,
				data: {
					action: "upload_remove",
					token: token,
					name: div.data("name")
				}
			}).done(function(ans) {
				ans = jQuery.parseJSON(ans);
				if (ans.success) {
					div.remove();
				}
				else {
					alert(ans.text);
				}
				token = ans.token;
			});
		});
		$(form.data("link")).submit(function() {
			var uploads = "";
			form.find(".uploads div").each(function() {
				uploads += $(this).data("name")+",";
			});
			$(this).find("input[name=\"uploads\"]").val(uploads);
		});
	});
	$(".a-remove-upload").click(function() {
		if (!confirm(confirm_delete_upload)) { return false; }
		var p = $(this).closest("p"),
			progress = $(this).closest(".div-list-uploads").find(".progress");
		$.ajax({
			type: "POST",
			url: ajax,
			data: {
				action: "upload_remove_linked",
				token: token,
				name: p.data("name"),
				user: p.data("user")
			}
		}).done(function(ans) {
			ans = jQuery.parseJSON(ans);
			if (ans.success) {
				p.remove();
				progress.find("span").html(ans.space);
				progress.find(".bar").css("width", ans.percent+"%");
			}
			else {
				alert(ans.text);
			}
			token = ans.token;
		});
	});

// Highlights code
	$("pre code").each(function(i,e) { highlighter($(this), e); });

// Blocks with fixed position
	$(window).scroll(function() { update_position(); });
	$(window).resize(function() { update_position(); });
	update_position();

});