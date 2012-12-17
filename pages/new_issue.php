<?php

	$form_s = '';
	$form_t = '';
	$token = getToken();

	if (isset($_POST['new_issue'])) {
		$issues = Issues::getInstance();
		$ans = $issues->new_issue($_POST);
		if ($ans === true) {
			header('Location: '.Url::parse(getProject().'/issues/'.$issues->lastissue));
			exit;
		}
		$this->addAlert($ans);
		$form_s = htmlspecialchars($_POST['summary']);
		$form_t = htmlspecialchars($_POST['text']);
	}

	$title = Trad::T_NEW_ISSUE;

	$should_login = '';
	if (!$config['loggedin']
		&& canAccess('signup')
		&& in_array(DEFAULT_GROUP, $config['permissions']['new_issue']))
	{
		$should_login = '<p class="help">'.Trad::A_SHOULD_LOGIN.'</p>';
	}

	$content = '
<h1>'.Trad::T_NEW_ISSUE.'</h1>

<div class="div-relative">
	<div class="box box-new-issue">
		<div class="top">
			<div class="manage"><a href="javascript:;" class="a-help-markdown a-icon-hover"><i class="icon-question-sign"></i></a></div>
			<i class="icon-pencil"></i> '.Trad::F_WRITE.'
		</div>
		<div class="div-help-markdown">'.Trad::HELP_MARKDOWN.'</div>
		<form action="'.Url::parse(getProject().'/issues/new').'" method="post" class="form">
			<input type="text" name="summary" value="'.$form_s.'" placeholder="'.Trad::F_SUMMARY.'" required />
			<textarea name="text" rows="12" placeholder="'.Trad::F_CONTENT.'" required>'.$form_t.'</textarea>
			<div class="preview"></div>
			'.$should_login.'
			<div class="form-actions">
				<button class="btn btn-preview">'.Trad::V_PREVIEW.'</button>
				<button type="submit" class="btn btn-primary">'.Trad::V_SUBMIT.'</button>
			</div>
			<input type="hidden" name="uploads" value="" />
			<input type="hidden" name="token" value="'.$token.'" />
			<input type="hidden" name="new_issue" value="1" />
		</form>
	</div>
	'.Uploader::get_html().'
</div>
	';


	$javascript = '
		'.Uploader::get_javascript().'
		$(".box-new-issue form").submit(function() {
			var val = "";
			for (var prop in array_uploads) {
				if (array_uploads.hasOwnProperty(prop)) { val += array_uploads[prop]+","; }
			}
			$(this).find("input[name=\"uploads\"]").val(val);
		});
		$(".btn-preview").live("click", function() {
			var form = $(this).closest("form");
			var btn = $(this);
			$.ajax({
				type: "POST",
				url: "'.Url::parse('public/ajax').'",
				data: {
					action: "markdown",
					text: form.find("textarea").val()
				}
			}).done(function(ans) {
				var ans = jQuery.parseJSON(ans);
				if (ans.success) {
					form.find("textarea").hide();
					form.find(".preview").html(ans.text).show();
					form.find("pre code").each(function(i,e) { highlighter($(this), e); });
					btn.removeClass("btn-preview").addClass("btn-edit").text("'.Trad::V_EDIT.'");
				}
				else {
					alert(ans.text);
				}
			});
			return false;
		});
		$(".btn-edit").live("click", function() {
			var form = $(this).closest("form");
			form.find(".preview").hide();
			form.find("textarea").show();
			$(this).removeClass("btn-edit").addClass("btn-preview").text("'.Trad::V_PREVIEW.'");
			return false;
		});
	';

?>