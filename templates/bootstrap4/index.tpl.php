<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="robots" content="index,follow" />
	<meta name="author" content="MangaDex" />
	<meta name="description" content="<?= $templateVar['og']['description'] ?>" />
	<meta name="keywords" itemprop="keywords" content="<?= $templateVar['og']['keywords'] ?>" />

	<meta name="theme-color" content="#<?= ($templateVar['user']->style == 1) ? 'eeeeee' : '272b30' ?>">

	<meta property="og:site_name" content="<?= TITLE ?>" />
	<meta property="og:title" content="<?= $templateVar['og']['title'] ?>" />
	<meta property="og:image" content="<?= $templateVar['og']['image'] ?>" />
	<meta property="og:url" content="<?= "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" />
	<meta property="og:description" content="<?= $templateVar['og']['description'] ?>" />
	<meta property="og:type" content="website" />
    <meta name="twitter:site" content="@mangadex" />

    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png?1">
	<link rel="icon" type="image/png" sizes="192x192" href="/favicon-192x192.png?1">
    <link rel="manifest" href="/manifest.json" crossOrigin="use-credentials">
    <link rel="search" type="application/opensearchdescription+xml" title="MangaDex Quick Search" href="/opensearch.xml">

	<?= $templateVar['og']['canonical'] ?>
	<title><?= $templateVar['og']['title'] ?></title>

	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-TS59XX9');</script>
	<!-- End Google Tag Manager -->

	<!-- Google fonts -->
	<link href="https://fonts.googleapis.com/css?family=Ubuntu:regular,regularitalic,bold" rel="stylesheet">
    <?php if ($templateVar['page'] == 'drama') { ?>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:regular,regularitalic,bold" rel="stylesheet">
    <?php } ?>

	<!-- Bootstrap core CSS -->
	<link href="/bootstrap/css/bootstrap.css?<?= @filemtime(ABSPATH . '/bootstrap/css/bootstrap.css') ?>" rel="stylesheet" />

	<!-- Bootstrap select CSS -->
	<link href="/bootstrap/css/bootstrap-select.min.css" rel="stylesheet" />

	<!-- OWL CSS -->
	<link href="/scripts/owl/assets/owl.carousel.min.css" rel="stylesheet" />
    <link href="/scripts/owl/assets/owl.theme.default.min.css" rel="stylesheet" />

	<!-- Fontawesone glyphicons -->
	<link href="/fontawesome/css/all.css" rel="stylesheet" />

	<!-- lightbox CSS -->
	<link href="/scripts/lightbox2/css/lightbox.min.css" rel="stylesheet">

	<!-- Custom styles for this template -->
	<link href="/scripts/css/theme.css?<?= @filemtime(ABSPATH . '/scripts/css/theme.css') ?>" rel="stylesheet" />
	<link href="/scripts/css/<?= THEMES[$templateVar['theme_id']] ?>.css?<?= @filemtime(ABSPATH . "/scripts/css/" . THEMES[$templateVar['user']->style] . ".css") ?>" rel="stylesheet" />

	<?php if (in_array($templateVar['page'], ['chapter', 'chapter_test']) && (!isset($_GET['mode']) || $_GET['mode'] == 'chapter') && !$templateVar['user']->reader) { ?>
		<meta name="app" content="MangaDex" data-guest="<?= $templateVar['user']->user_id ? 0 : 1 ?>" data-chapter-id="<?= $_GET['id'] ?? '' ?>" data-page="<?= isset($_GET['p']) ? $_GET['p'] : 1 ?>" />
	<?php }
		if (in_array($templateVar['page'], ['chapter']) && (!isset($_GET['mode']) || $_GET['mode'] == 'chapter') && !$templateVar['user']->reader) { ?>
		<link href="/scripts/css/reader.css?<?= @filemtime(ABSPATH . "/scripts/css/reader.css") ?>" rel="stylesheet" />
	<?php } ?>

    <?php if (defined('DEBUG') && DEBUG): ?>
        <script type="module" src="/dist/js/bundle.dev.js?<?= @filemtime(ABSPATH . "/dist/js/bundle.dev.js") ?>"></script>
    <?php else: ?>
        <script type="module" src="/dist/js/bundle.prod.js?<?= @filemtime(ABSPATH . "/dist/js/bundle.prod.js") ?>"></script>
    <?php endif; ?>
</head>

<body>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TS59XX9"	height="0" width="0" style="display:none; visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<!-- Fixed navbar -->
	<?= parse_template('partials/navbar', $templateVar) ?>

	<div class="container" role="main" id="content">
		<?php if (!$templateVar['user']->activated && $templateVar['user']->user_id)
			print display_alert("warning", "Warning", "Your account is currently unactivated. Please enter your activation code <a href='/activation'>here</a> for access to all of " . TITLE . "'s features."); ?>

        <?php if (is_array($templateVar['announcement']) && count($templateVar['announcement']) > 0 && !$templateVar['user']->read_announcement && !in_array($templateVar['page'], ['chapter', 'list'])) { ?>
            <div id="announcement" class="alert alert-success <?= $templateVar['user']->user_id ? 'alert-dismissible ' : ''?>fade show text-center" role="alert">
            <?php if ($templateVar['user']->user_id) { ?>
                <button id="read_announcement_button" type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php } ?>
            <?php foreach ($templateVar['announcement'] as $idx=>$row) { ?>
                    <strong>Announcement (<?= date('M-d', $row->timestamp) ?>):</strong> <?= $row->thread_name ?> <a title="Go to forum thread" href="/thread/<?= $row->thread_id ?>"><?= display_fa_icon('external-link-alt', 'Forum thread') ?></a>
                    <?php if($idx < count($templateVar['announcement']) - 1){ ?>
                        <hr style="margin-right: -<?= $templateVar['user']->user_id ? 4 : 1.25 ?>rem; margin-left: -1.25rem;" />
                    <?php } ?>
            <?php } ?>
            </div>
		<?php } ?>


		<?php
		    /** Print page content */
		    print $templateVar['page_html'];

            if (validate_level($templateVar['user'], 'admin') && $templateVar['page'] != 'chapter')
                print_r ($templateVar['sql']->debug());
		?>

	</div> <!-- /container -->

	<!-- message_container -->
	<div id="message_container" class="display-none"></div>
	<!-- /container -->

	<!-- Modal -->
	<div class="modal fade" id="homepage_settings_modal" tabindex="-1" role="dialog" aria-labelledby="homepage_settings_label" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="homepage_settings_label"><?= display_fa_icon('cog')?> MangaDex settings</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<form method="post" id="homepage_settings_form">
						<div class="form-group row">
							<label for="language" class="col-lg-3 col-form-label-modal">Site theme:</label>
							<div class="col-lg-9">
								<select class="form-control selectpicker" id="theme_id" name="theme_id">
									<?php
									foreach (THEMES as $key => $theme) {
										$selected = ($templateVar['theme_id'] == $key) ? 'selected' : '';
										print "<option $selected value='$key'>$theme</option>";
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="default_lang_ids" class="col-lg-3 col-form-label-modal">Filter chapter languages:</label>
							<div class="col-lg-9">
								<select multiple class="form-control selectpicker show-tick" data-actions-box="true" data-selected-text-format="count > 5" data-size="10" id="default_lang_ids" name="default_lang_ids[]" title="All langs">
									<?= display_languages_select($templateVar['lang_id_filter_array']) ?>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<label for="display_language" class="col-lg-3 col-form-label-modal">User interface language:</label>
							<div class="col-lg-9">
								<select class="form-control selectpicker" id="display_lang_id" name="display_lang_id" data-size="10">
									<?= display_languages_select([$templateVar['ui_lang']->lang_id]) ?>
								</select>
							</div>
						</div>
						<?php if ($templateVar['user']->hentai_mode) { ?>
						<div class="form-group row">
							<label class="col-lg-3 col-form-label-modal" for="hentai_mode">Hentai:</label>
							<div class="col-lg-9">
								<select class="form-control selectpicker show-tick" id="hentai_mode" name="hentai_mode">
									<option value="0" <?= (!$templateVar['hentai_toggle']) ? 'selected' : '' ?> data-content="<?= $templateVar['hentai_options'][0] ?>">Hide H</option>
									<option value="1" <?= ($templateVar['hentai_toggle'] == 1) ? 'selected' : '' ?> data-content="<?= $templateVar['hentai_options'][1] ?>">All</option>
									<option value="2" <?= ($templateVar['hentai_toggle'] == 2) ? 'selected' : '' ?> data-content="<?= $templateVar['hentai_options'][2] ?>">Only H</option>
								</select>
							</div>
						</div>
						<?php } ?>
						<div class="form-group row">
                            <div class="col-lg-3 text-right">
                                <button type="submit" class="btn btn-secondary" id="homepage_settings_button"><?= display_fa_icon('save') ?> Save</button>
                            </div>
                            <div class="col-lg-9 text-left">

                            </div>
						</div>
					</form>
				</div>

				<div class="modal-footer">
					<?php if (!$templateVar['user']->user_id) {
						print display_alert('info mx-auto', 'Info', "These settings are temporary. Please " . display_fa_icon('pencil-alt') . " <a href='/signup'>make an account</a> to remember these permanently.");
					} else { ?>
						<a class="btn btn-secondary mx-auto" role="button" href="/settings"><?= display_fa_icon('cog') ?> More settings</a>
					<?php } ?>
				</div>
            </div>
		</div>
	</div>

	<?= parse_template('partials/report_modal', $templateVar); ?>

	<footer class="footer">
		<p class="m-0 text-center text-muted">&copy; <?= date('Y') ?> <a href="/" title="<?php print_r($templateVar['memcached']->get($templateVar['ip'])) ?>">MangaDex</a> | <a href="https://path.net/" target="_blank" title="Provider of DDoS mitigation services">Path Network</a> | <a href="https://sdbx.moe/" target="_blank" title="seedbox provider">sdbx.moe</a></p>
	</footer>

	<?php
	if (!isset($templateVar['user']->navigation) || $templateVar['user']->navigation) {
		print parse_template('partials/mobile_menus', $templateVar);
	}
	?>


	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
    <script nomodule src="/dist/js/polyfills.prod.js?<?= @filemtime(ABSPATH . "/dist/js/polyfills.prod.js") ?>"></script>
    <script nomodule src="/dist/js/bundle.prod.js?<?= @filemtime(ABSPATH . "/dist/js/bundle.prod.js") ?>"></script>
    <script src="/scripts/jquery.min.js?<?= @filemtime(ABSPATH . "/scripts/jquery.min.js") ?>"></script>
	<script src="/scripts/jquery.touchSwipe.min.js"></script>
	<script src="/bootstrap/js/popper.min.js"></script>
	<script src="/bootstrap/js/bootstrap.min.js?1"></script>
	<script src="/bootstrap/js/bootstrap-select.min.js?1"></script>
	<script src="/scripts/lightbox2/js/lightbox.js"></script>
	<script src="/scripts/chart.min.js"></script>
    <?php if ($templateVar['page'] == 'home') { ?>
	<script src="/scripts/owl/owl.carousel.js"></script>
	<?php } ?>
    <script>
      if (!('URL' in window) || !('URLSearchParams' in window)) {
        document.head.appendChild(Object.assign(document.createElement("script"), {
          "src": "/dist/js/polyfills.prod.js?<?= @filemtime(ABSPATH . "/dist/js/polyfills.prod.js") ?>", "async": true,
        }))
      }
    </script>
	<?php if (in_array($templateVar['page'], ['chapter', 'chapter_test']) && (!isset($_GET['mode']) || $_GET['mode'] == 'chapter') && !$templateVar['user']->reader) { ?>
		<script src="/scripts/modernizr-custom.js"></script>
	<?php }
	if ($templateVar['page'] == 'chapter' && (!isset($_GET['mode']) || $_GET['mode'] == 'chapter') && !$templateVar['user']->reader) { ?>
		<script async src="/scripts/reader.min.js?<?= @filemtime(ABSPATH . "/scripts/reader.min.js") ?>"></script>
	<?php } ?>

    <script src="/scripts/js/reporting.js"></script>
	<script type="text/javascript">

    <?php if (defined('INCLUDE_JS_REDIRECT') && INCLUDE_JS_REDIRECT) : ?>
	var t='mang';
		t = t+'adex.org';
	var w='www.mangadex.org';
	if (window.location.hostname != t && window.location.hostname != w ) {
		window.location='https://'+t;
	}
    <?php endif; ?>

	var $ = jQuery;

	$(document).on('change', '.btn-file :file', function() {
		var input = $(this),
			numFiles = input.get(0).files ? input.get(0).files.length : 1,
			label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
		input.trigger('fileselect', [numFiles, label]);
	});

	function capitalizeFirstLetter(string) {
		return string.charAt(0).toUpperCase() + string.slice(1);
	}

	function commaMultipleSelect(id) {
		var list = document.getElementById(id);
		var selected = new Array();

		for (i = 0; i < list.options.length; i++) {
			if (list.options[i].selected) {
				 selected.push(list.options[i].value);
			}
		}

		return selected.join(',');
	}

	function commaMultipleCheckbox(name) {
		var list = document.getElementsByName(name);
		var selected = new Array();

		for (i = 0; i < list.length; i++) {
			if (list[i].checked) {
				 selected.push(list[i].value);
			}
		}

		return selected.join(',');
	}

	$(document).ready(function(){
		var query = location.search;

		<?php if (!isset($templateVar['user']->navigation) || $templateVar['user']->navigation) : ?>
		$("#left_swipe_area").swipe({
			swipeRight:function(event, direction, distance, duration, fingerCount) {
				$('#left_modal').modal('toggle');
			},
			threshold: 50
		});
		$("#right_swipe_area").swipe({
			swipeLeft:function(event, direction, distance, duration, fingerCount) {
				$('#right_modal').modal('toggle');
			},
			threshold: 50
		});
		$("#right_modal").swipe({
			swipeRight:function(event, direction, distance, duration, fingerCount) {
				$('#right_modal').modal('toggle');
			},
			threshold: 50
		});
		$("#left_modal").swipe({
			swipeLeft:function(event, direction, distance, duration, fingerCount) {
				$('#left_modal').modal('toggle');
			},
			threshold: 50
		});
		<?php endif; ?>

		$("#read_announcement_button").click(function(event){
			$.ajax({
				url: "/ajax/actions.ajax.php?function=read_announcement",
				type: 'GET',
				success: function (data) {
					$("#announcement").hide();
				},
				cache: false,
				contentType: false,
				processData: false
			});
			event.preventDefault();
		});

		$(".logout").click(function(event){
			$.ajax({
				type: "POST",
				url: "/ajax/actions.ajax.php?function=logout",
				success: function(data) {
					$("#message_container").html(data).show().delay(1500).fadeOut();
					location.reload();
				}
			});
			event.preventDefault();
		});

        function highlightPost(node) {
            if (node) {
                Array.from(document.querySelectorAll('.highlighted')).forEach(function(n) {n.classList.remove('highlighted')});
                node.classList.add('highlighted')
            }
        }

        if (location.hash)
            highlightPost(document.querySelector(location.hash + ' .postbody'));

        $('a.permalink').click(function(event) {
            highlightPost(event.target.closest('.post').querySelector('.postbody'))
        });

		<?= $templateVar['page_scripts'] ?>


	});

	</script>
</body>
</html>