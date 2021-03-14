<?php

/** Template vars:
 * user: the currently logged in user
 * manga: the manga object
 * page: the current page name
 * mode: the 2nd url parameter
 * parser: the bbcode parser object from header.req.php
 * missing_chapters: list of missing chapters
 * manga_tab_html: html content for the tab to display below the mangainfo table
 */

$followed_manga_ids_array = $templateVar['user']->get_followed_manga_ids();

//$genres = (new Genres())->toArray();
$grouped_genres = new Grouped_Genres();
$relation_types = new Relation_Types();

$alt_names_array = $templateVar['manga']->get_manga_alt_names();
$manga_genres = $templateVar['manga']->get_manga_genres();
$manga_relations = $templateVar['manga']->get_related_manga();
$links_array = ($templateVar['manga']->manga_links) ? json_decode($templateVar['manga']->manga_links) : [];
?>

<div class="card mb-3">
	<h6 class="card-header d-flex align-items-center py-2">
		<?= display_fa_icon('book') ?>
		<span class="mx-1"><?= $templateVar['manga']->manga_name ?></span>
		<?= display_lang_flag_v3($templateVar['manga']) ?>
		<?= display_labels($templateVar['manga']->manga_hentai) ?>
		<?= display_rss_link($templateVar['user'], 'manga_id', $templateVar['manga']->manga_id) ?>
	</h6>
	<div class="card-body p-0">
		<div class="row edit">
			<div class="col-xl-3 col-lg-4 col-md-5">
				<a href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/covers/" title="See covers">
					<img class="rounded" width="100%" src="<?= LOCAL_SERVER_URL . "/images/manga/{$templateVar['manga']->manga_id}.{$templateVar['manga']->manga_image}?" . @filemtime(ABS_DATA_BASEPATH . "/manga/{$templateVar['manga']->manga_id}.{$templateVar['manga']->manga_image}") ?>" />
				</a>
			</div>
			<div class="col-xl-9 col-lg-8 col-md-7">
				<div class="row m-0 py-1 px-0">
					<div class="col-lg-3 col-xl-2 strong">Title ID:</div>
					<div class="col-lg-9 col-xl-10"><?= display_fa_icon('hashtag') ?> <?= $templateVar['manga']->manga_id ?></div>
				</div>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Alt name(s):</div>
					<div class="col-lg-9 col-xl-10">
						<ul class="list-inline m-0">
							<?php foreach ($alt_names_array as $alt_name)
								print "<li class='list-inline-item'>" . display_fa_icon('book') . " $alt_name</li>"; ?>
						</ul>
					</div>
				</div>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Author:</div>
					<div class="col-lg-9 col-xl-10">
						<?php
						$authors = array_map('trim', explode(',', $templateVar['manga']->manga_author));
						$i = 0;
						foreach ($authors AS $author) :
							?>
							<a href="/search?author=<?= $author ?>" title="Other manga by this author"><?= $author ?></a><?= (++$i != count($authors)) ? ', ' : '' ?>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Artist:</div>
					<div class="col-lg-9 col-xl-10">
						<?php
						$artists = array_map('trim', explode(',', $templateVar['manga']->manga_artist));
						$i = 0;
						foreach ($artists AS $artist) :
							?>
							<a href="/search?artist=<?= $artist ?>" title="Other manga by this artist"><?= $artist ?></a><?= (++$i != count($artists)) ? ', ' : '' ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ($templateVar['manga']->manga_demo_id) { ?>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Demographic:</div>
					<div class="col-lg-9 col-xl-10"><span class="badge badge-secondary"><a class="genre" href="/search?demo_id=<?= $templateVar['manga']->manga_demo_id ?>" title="Search for <?= MANGA_DEMO[$templateVar['manga']->manga_demo_id] ?> titles"><?= MANGA_DEMO[$templateVar['manga']->manga_demo_id] ?></a></span></div>
				</div>
				<?php } ?>
				<?php
					$all_genres = $grouped_genres->toGenreArray();
					$manga_genres_grouped = [];
					foreach ($manga_genres as $manga_id) {
						$manga_genres_grouped[$all_genres[$manga_id]['group']][$manga_id] = $all_genres[$manga_id]['name'];
					}
					ksort($manga_genres_grouped);
					foreach($manga_genres_grouped as $group => $genres) {
						$color = $group === 'Content' ? 'badge-warning' : 'badge-secondary';

						echo "<div class='row m-0 py-1 px-0 border-top'>
								<div class='col-lg-3 col-xl-2 strong'>$group:</div>
									<div class='col-lg-9 col-xl-10'>";
						asort($genres);
						foreach($genres as $manga_id => $label) {
							echo "<a class='badge $color' href='/genre/{$manga_id}'>$label</a> ";
						}
						echo "</div></div>";
					}
				?>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Rating:</div>
					<div class="col-lg-9 col-xl-10">
						<ul class="list-inline m-0">
							<li class="list-inline-item"><span class="text-primary"><?= display_fa_icon('star', 'Bayesian rating') ?> <?= $templateVar['manga']->manga_bayesian ?></span> </li>
							<li class="list-inline-item small"><?= display_fa_icon('star', 'Mean rating') ?> <?= $templateVar['manga']->manga_rating ?></li>
							<li class="list-inline-item small"><?= display_fa_icon('user', 'Users') ?> <?= number_format(count($templateVar['manga']->get_user_ratings()), 0) ?></li>
							<li class="list-inline-item"><button type="button" class="btn btn-secondary btn-xs" id="histogram_toggle"><?= display_fa_icon('chart-bar') ?></button></li>
						</ul>
						<div id="histogram_div" class="display-none"><canvas id="ratings_histogram" data-json="<?= json_encode($templateVar['manga']->get_user_ratings()) ?>"></canvas></div>
					</div>
				</div>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Pub. status:</div>
					<div class="col-lg-9 col-xl-10"><?= STATUS_ARRAY[$templateVar['manga']->manga_status_id] ?></div>
				</div>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Stats:</div>
					<div class="col-lg-9 col-xl-10">
						<ul class="list-inline m-0">
							<li class="list-inline-item text-info"><?= display_fa_icon('eye', 'Views') ?> <?= number_format($templateVar['manga']->manga_views) ?></li>
							<li class="list-inline-item text-success"><?= display_fa_icon('bookmark', 'Follows') ?> <?= number_format($templateVar['manga']->manga_follows) ?></li>
							<li class="list-inline-item"><?= display_fa_icon('file', 'Total chapters', '', 'far') ?> <?= number_format($templateVar['manga']->get_total_chapters($templateVar['user']->default_lang_ids)) ?></li>
						</ul>
					</div>
				</div>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Description:</div>
					<div class="col-lg-9 col-xl-10"><?php $templateVar['parser']->parse($templateVar['manga']->manga_description); print nl2br($templateVar['parser']->getAsHtml()); ?></div>
				</div>
				<?= display_manga_relations($manga_relations) ?>
				<?= display_manga_ext_links($links_array) ?>

				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Reading progress:</div>
					<div class="reading_progress col-lg-9 col-xl-10">
						<ul class="list-inline m-0">
							<li class="list-inline-item">
								Volume <span id="current_volume"><?= $followed_manga_ids_array[$templateVar['manga']->manga_id]['volume'] ?? 0 ?></span>/<?= $templateVar['manga']->manga_last_volume ?: '?' ?>
								<button <?= isset($followed_manga_ids_array[$templateVar['manga']->manga_id]) ? '' : "disabled title='You need to follow this title to use this function.'" ?> type="button" class="btn btn-success btn-xs ml-1" id="increment_volume" data-title-id="<?= $templateVar['manga']->manga_id ?>"><?= display_fa_icon('plus-circle') ?></button>
							</li>
							<li class="list-inline-item">
								Chapter <span id="current_chapter"><?= $followed_manga_ids_array[$templateVar['manga']->manga_id]['chapter'] ?? 0 ?></span>/<?= $templateVar['manga']->manga_last_chapter ?: '?' ?>
								<button <?= isset($followed_manga_ids_array[$templateVar['manga']->manga_id]) ? '' : "disabled title='You need to follow this title to use this function.'" ?> type="button" class="btn btn-success btn-xs ml-1" id="increment_chapter" data-title-id="<?= $templateVar['manga']->manga_id ?>"><?= display_fa_icon('plus-circle') ?></button>
							</li>
							<li class="list-inline-item"><button <?= isset($followed_manga_ids_array[$templateVar['manga']->manga_id]) ? '' : "disabled title='You need to follow this title to use this function.'" ?> type="button" class="btn btn-info btn-xs ml-1" id="edit_progress"><?= display_fa_icon('pencil-alt') ?></button></li>
						</ul>
					</div>
					<div class="reading_progress display-none col-lg-9 col-xl-10">
						<form class="form-inline" id="edit_progress_form" method="post" data-title-id="<?= $templateVar['manga']->manga_id ?>">
							<ul class="list-inline m-0">
								<li class="list-inline-item"><input style="width: 60px;" type="text" class="form-control" id="volume" name="volume" value="<?= $followed_manga_ids_array[$templateVar['manga']->manga_id]['volume'] ?? '' ?>">/<?= $templateVar['manga']->manga_last_volume ?: '?' ?></li>
								<li class="list-inline-item"><input style="width: 60px;" type="text" class="form-control" id="chapter" name="chapter" value="<?= $followed_manga_ids_array[$templateVar['manga']->manga_id]['chapter'] ?? '' ?>">/<?= $templateVar['manga']->manga_last_chapter ?: '?' ?></li>
								<li class="list-inline-item"><button type="submit" class="btn btn-success" id="edit_progress_button"><?= display_fa_icon('save') ?></button></li>
								<li class="list-inline-item"><button type="button" class="btn btn-warning" id="cancel_edit_progress"><?= display_fa_icon('undo') ?></button></li>
							</ul>
						</form>
					</div>
				</div>

				<?php if (validate_level($templateVar['user'], 'gmod') && $templateVar['manga']->manga_mod_notes) { ?>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Mod notes:</div>
					<div class="col-lg-9 col-xl-10" style="color: orangered"><?php $templateVar['parser']->parse($templateVar['manga']->manga_mod_notes); print nl2br($templateVar['parser']->getAsHtml()); ?></div>
				</div>
				<?php } ?>

				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Actions:</div>
					<div class="col-lg-9 col-xl-10">
						<?= display_upload_button($templateVar['user']) ?>
						<?= display_follow_button($templateVar['user'], $followed_manga_ids_array, $templateVar['manga']->manga_id) ?>
						<?= display_manga_rating_button($templateVar['user']->user_id, $templateVar['manga']->get_user_rating($templateVar['user']->user_id), $templateVar['manga']->manga_id) ?>
						<?= display_edit_manga($templateVar['user'], $templateVar['manga']) ?>
						<?php if (validate_level($templateVar['user'], 'member')) : ?>
							<button type="button" class="btn btn-warning float-right mr-1" data-toggle="modal" data-target="#manga_report_modal"><?= display_fa_icon('flag') ?> <span class="d-none d-xl-inline">Report</span></button>
						<?php else : ?>
							<button type="button" title="You must be logged in to send a report" class="btn btn-warning float-right mr-1" disabled><?= display_fa_icon('flag') ?> <span class="d-none d-xl-inline">Report</span></button>
						<?php endif; ?>
					</div>
				</div>
				<?php if (validate_level($templateVar['user'], 'gmod')) : ?>
				<div class="row m-0 py-1 px-0 border-top">
					<div class="col-lg-3 col-xl-2 strong">Mod:</div>
					<div class="col-lg-9 col-xl-10">
						<?= display_lock_manga($templateVar['user'], $templateVar['manga']) ?>
						<?= display_delete_manga($templateVar['user']) ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if (validate_level($templateVar['user'], 'contributor') && !$templateVar['user']->has_active_restriction(USER_RESTRICTION_EDIT_TITLES)) { ?>
		<form class="edit display-none" id="manga_edit_form" method="post" enctype="multipart/form-data">
			<table class="table table-sm ">
				<tr>
					<th width="150px">Name:</th>
					<td><input type="text" class="form-control" id="manga_name" name="manga_name" value="<?= $templateVar['manga']->manga_name ?>" required></td>
				</tr>
				<tr>
					<th>Alt name(s):</th>
					<td><textarea class="form-control" id="manga_alt_names" name="manga_alt_names" rows="5"><?= implode("\n", $alt_names_array) ?></textarea></td>
				</tr>
				<tr>
					<th>Author:</th>
					<td><input type="text" class="form-control" id="manga_author" name="manga_author" value="<?= $templateVar['manga']->manga_author ?>"></td>
				</tr>
				<tr>
					<th>Artist:</th>
					<td><input type="text" class="form-control" id="manga_artist" name="manga_artist" value="<?= $templateVar['manga']->manga_artist ?>"></td>
				</tr>
				<tr>
					<th width="100px">Original language:</th>
					<td>
						<select class="form-control selectpicker" id="manga_lang_id" name="manga_lang_id">
							<?php
							foreach (ORIG_LANG_ARRAY as $key => $language) {
								$selected = ($key == $templateVar['manga']->manga_lang_id) ? "selected" : "";
								print "<option $selected value='$key'>$language</option>";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Status:</th>
					<td>
						<select class="form-control selectpicker" id="manga_status_id" name="manga_status_id">
							<?php
							foreach (STATUS_ARRAY as $key => $status) {
								$selected = ($key == $templateVar['manga']->manga_status_id) ? "selected" : "";
								print "<option $selected value='$key'>$status</option>";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Demographic:</th>
					<td>
						<select class="form-control selectpicker" id="manga_demo_id" name="manga_demo_id">
							<?php
							foreach (MANGA_DEMO as $key => $demo) {
								$selected = ($key == $templateVar['manga']->manga_demo_id) ? "selected" : "";
								print "<option $selected value='$key'>$demo</option>";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th>Tags:</th>
					<td><?= display_genres_dropdown($grouped_genres->toGroupedArray(), $manga_genres) ?></td>
				</tr>
				<tr>
					<th>Hentai:</th>
					<td>
						<div class="custom-control custom-checkbox form-check">
							<input type="checkbox" class="custom-control-input" id="manga_hentai" name="manga_hentai" value="1" <?= $templateVar['manga']->manga_hentai ? "checked" : "" ?>>
							<label class="custom-control-label" for="manga_hentai">&nbsp;</label>
						</div>
					</td>
				</tr>
				<tr>
					<th>Related:</th>
					<td>
						<div id="relation_entries">
						<?php
						foreach ($manga_relations as $related_manga)
							print display_edit_manga_relation_entry($related_manga);
						?>
						</div>
						<button class="btn btn-primary" id="add_relation_button"><?= display_fa_icon('link') ?> Add relation</button>
					</td>
				</tr>
				<tr>
					<th>Description:</th>
					<td><textarea class="form-control" rows="11" id="manga_description" name="manga_description" placeholder="Optional"><?= $templateVar['manga']->manga_description ?></textarea></td>
				</tr>
				<tr>
					<th>External links:</th>
					<td>
						<div id="links">
						<?php
						foreach ($links_array as $type => $link_id)
							print display_edit_manga_ext_link($type, $link_id);
						?>
						</div>
						<button class="btn btn-primary" id="add_link_button"><?= display_fa_icon('link') ?> Add link</button>
					</td>
				</tr>
				<tr>
					<th>Image:</th>
					<td>
						<div class="input-group">
                            <input type="text" class="form-control" placeholder="Leave blank if no change to image. Minimum aspect ratio: 1:1.5, highest quality preferred. Max 1MB" disabled name="old_file">
							<span class="input-group-append">
								<span class="btn btn-secondary btn-file">
									<?= display_fa_icon('folder-open', 'Browse', 'far') ?> <span class="span-1280">Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_IMG_EXT) ?>">
								</span>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<th>Last chapter:</th>
					<td class="pb-0">
						<div class="container p-0">
							<div class="row">
								<div class="col col-12 col-md-6">
									<div class="input-group mb-1">
										<div class="input-group-prepend">
											<span class="input-group-text">Vol.</span>
										</div>
										<input type="text" id="manga_last_volume" name="manga_last_volume" class="form-control" value="<?= $templateVar['manga']->manga_last_volume ?>" placeholder="None" title="Volume number of the final full chapter as marked by the publisher if possible">
									</div>
								</div>
								<div class="col col-12 col-md-6">
									<div class="input-group mb-1">
										<div class="input-group-prepend">
											<span class="input-group-text">Ch.</span>
										</div>
										<input type="text" id="manga_last_chapter" name="manga_last_chapter" class="form-control" value="<?= $templateVar['manga']->manga_last_chapter ?>" title="Chapter number of the final full chapter as marked by the publisher if possible">
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
				<?php if (validate_level($templateVar['user'], 'gmod')) { ?>
				<tr>
					<th>Mod notes:</th>
					<td><textarea class="form-control" rows="3" id="manga_mod_notes" name="manga_mod_notes" placeholder="Optional"><?= $templateVar['manga']->manga_mod_notes ?></textarea></td>
				</tr>
				<?php } ?>
				<tr>
					<th>Actions:</th>
					<td>
						<button type="submit" class="btn btn-success" id="manga_edit_button"><?= display_fa_icon('pencil-alt') ?> Save</button>
						<button class="btn btn-warning" id="cancel_edit_button"><?= display_fa_icon('times') ?> Cancel</button>
					</td>
				</tr>
			</table>
		</form>
		<?php } ?>
	</div>
</div>

<?php if (validate_level($templateVar['user'], 'member')) : ?>
<!-- Modal -->
<div class="modal fade" id="manga_report_modal" tabindex="-1" role="dialog" aria-labelledby="manga_report_label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="manga_report_label"><?= display_fa_icon('flag')?> Report title</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" id="manga_report_form">
					<textarea class="form-control mb-3" rows="10" name="report_text" id="report_text" placeholder="Please be as descriptive as possible in your report. If possible, include any recommended fixes." required></textarea>
					<div class="text-center">
						<button type="submit" class="btn btn-warning" id="manga_report_button"><?= display_fa_icon('flag')?> Report</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Nav tabs -->
<ul class="edit nav nav-tabs" role="tablist">
	<li class="nav-item">
		<a class="nav-link <?= ($templateVar['mode'] == 'chapters') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/chapters/"><?= display_fa_icon('file', '', '', 'far') ?> <span class="d-none d-md-inline">Chapters</span></a>
	</li>

	<?php if ($templateVar['missing_chapters']) : ?>
	<li class="nav-item">
		<a class="nav-link <?= ($templateVar['mode'] == 'summary') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/summary/"><?= display_fa_icon('info-circle') ?> <span class="d-none d-md-inline">Missing</span></a>
	</li>
	<?php endif; ?>

	<li class="nav-item">
		<a class="nav-link <?= ($templateVar['mode'] == 'comments') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/comments/"><?= display_fa_icon('comments') ?> <span class="d-none d-md-inline">Comments</span> <?= display_count_comments($templateVar['manga']->thread_posts) ?></a>
	</li>

	<li class="nav-item">
		<a class="nav-link <?= ($templateVar['mode'] == 'covers') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/covers/"><?= display_fa_icon('image') ?> <span class="d-none d-md-inline">Covers</span></a>
	</li>

	<?php if (validate_level($templateVar['user'], 'pr')) : ?>
	<li class="nav-item ml-auto">
		<a class="nav-link text-success <?= ($templateVar['mode'] == 'deleted') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/deleted/"><?= display_fa_icon('trash') ?> <span class="d-none d-md-inline">Bin</span></a>
	</li>
	<?php endif; ?>

	<?php if (validate_level($templateVar['user'], 'gmod')) : ?>
	<li class="nav-item">
		<a class="nav-link text-success <?= ($templateVar['mode'] == 'mod_chapters') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/mod_chapters/"><?= display_fa_icon('edit') ?> <span class="d-none d-md-inline">Mod</span></a>
	</li>
	
	<li class="nav-item">
		<a class="nav-link text-danger <?= ($templateVar['mode'] == 'admin_history') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/admin_history/"><?= display_fa_icon('history') ?> <span class="d-none d-md-inline">History</span></a>
	</li>

	<li class="nav-item">
		<a class="text-danger nav-link <?= ($templateVar['mode'] == 'admin') ? 'active' : '' ?>" href="/title/<?= $templateVar['manga']->manga_id ?>/<?= slugify($templateVar['manga']->manga_name) ?>/admin/"><?= display_fa_icon('user-md') ?> <span class="d-none d-md-inline">Admin</span></a>
	</li>
	<?php endif; ?>
</ul>

<!-- Tab panes -->
<div class="edit tab-content">

	<?= $templateVar['manga_tab_html'] ?>

</div>

<?= $templateVar['post_history_modal_html'] ?>