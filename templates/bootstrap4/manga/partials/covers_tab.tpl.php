<div class="row m-0">
	<?php foreach ($templateVar['covers_data'] as $cover) : ?>
	<div id="volume_<?= $cover['volume'] ?>" class="col-6 col-md-4 col-lg-3 col-xl-2 px-2 pt-3">
		<a href="<?= LOCAL_SERVER_URL ?>/images/covers/<?= $templateVar['manga']->manga_id ?>v<?= $cover['volume'] ?>.<?= $cover['img'] ?>?<?= @filemtime(ABS_DATA_BASEPATH . "/covers/{$templateVar['manga']->manga_id}v{$cover['volume']}.{$cover['img']}") ?>" data-lightbox="<?= $templateVar['manga']->manga_name ?>" data-title="Volume <?= $cover['volume']?> (Uploader: <?= $cover['username']?>)">
			<img class="rounded" width="100%" src="<?= LOCAL_SERVER_URL ?>/images/covers/<?= $templateVar['manga']->manga_id ?>v<?= $cover['volume'] ?>.250.jpg?<?= @filemtime(ABS_DATA_BASEPATH . "/covers/{$templateVar['manga']->manga_id}v{$cover['volume']}.250.jpg") ?>" title="Volume <?= $cover['volume']?> (Uploader: <?= $cover['username']?>)" alt="Volume <?= $cover['volume']?>" />
		</a>
		<?php if (validate_level($templateVar['user'], 'gmod')) : ?>
		<div style="position: absolute; top: 25px; left: 15px; ">
			<a href="#" class="manga_cover_delete" data-manga-id="<?= $templateVar['manga']->manga_id ?>" data-volume="<?= $cover['volume'] ?>"><?= display_fa_icon('trash', '', 'fa-lg') ?></a>
		</div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
	
	<div class="col-6 col-md-4 col-lg-3 col-xl-2 px-2 pt-3">	
		<div style="display: flex; align-items:center; justify-content:center; height: 100%; width: 100%">
			<?php if (!validate_level($templateVar['user'], 'gmod') && $templateVar['manga']->manga_locked) : ?>
			<a href="#" title="Title is locked. Contact staff to upload."><?= display_fa_icon('upload', '', 'fa-3x text-warning')?></a>
			<?php elseif (validate_level($templateVar['user'], 'member')) : ?>
			<a href="#" title="Upload cover" data-toggle="modal" data-target="#manga_cover_upload_modal"><?= display_fa_icon('upload', '', 'fa-3x')?></a>
			<?php else : ?>
			<a href="/login" title="Upload cover"><?= display_fa_icon('upload', '', 'fa-3x')?></a>
			<?php endif; ?>
		</div>	
	</div>

</div>

<!-- Modal -->
<div class="modal fade" id="manga_cover_upload_modal" tabindex="-1" role="dialog" aria-labelledby="manga_cover_upload_label" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="manga_cover_upload_label"><?= display_fa_icon('image')?> Upload volume cover</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<form method="post" id="manga_cover_upload_form" enctype="multipart/form-data">
					<div class="form-group row">
						<label for="username" class="col-lg-3 col-form-label-modal">Volume:</label>
						<div class="col-lg-9">
							<input maxlength="5" type="text" class="form-control" id="volume" name="volume" placeholder="Decimals allowed" required>
						</div>
					</div>
					<div class="form-group row">
						<label for="file" class="col-lg-3 col-form-label-modal">Cover:</label>
						<div class="col-lg-9">
							<div class="input-group">
								<input type="text" class="form-control" placeholder="Recommended resolution: ~1000px width. Max filesize: 2 MB." disabled name="old_file">
								<span class="input-group-append">
									<span class="btn btn-secondary btn-file">
										<?= display_fa_icon('folder-open', '', '', 'far') ?> <span>Browse</span> <input type="file" name="file" id="file" accept=".<?= IMPLODE(",.", ALLOWED_IMG_EXT) ?>">
									</span>
								</span>
							</div>
						</div>
					</div>
					<div class="text-center">
						<button type="submit" class="btn btn-secondary" id="upload_cover_button"><?= display_fa_icon('save') ?> Save</button>
					</div>


				</form>
			</div>
			
			<div class="modal-footer">
				<?= display_alert('warning mx-auto', 'Warning', "Please make sure you have entered the correct volume before uploading! Best covers usually come from ebookjapan or bookwalker. Report this title to replace existing covers."); ?>
			</div>
		</div>
	</div>
</div>