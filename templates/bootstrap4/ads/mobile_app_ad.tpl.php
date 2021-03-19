<!-- ad template -->
<?php

$banner = $templateVar[array_rand($templateVar)];
?>
<?php if (!isset($_COOKIE["hide_banner"])) : ?>
<div id="affiliate-banner" class="affiliate-banner mb-3">  
  <div class="d-flex position-relative">
    <img id="close-banner" src="/images/banners/close.png" style="right:0;top:0;bottom:0;height:100%;cursor:pointer;" class="position-absolute"><a href="/support/affiliates">
        <img class="w-100" src="/images/banners/affiliatebanner<?= $banner['banner_id'] ?>.<?= $banner['ext'] ?>">
    </a>      
  </div>
  <div class="d-none d-md-block" style="text-align: right;margin-right:20px;">
    This affiliate banner was designed by <?= $banner["is_anonymous"] ? "an anonymous user" : display_user_link_v2($banner) ?>
  </div>
  <div class="d-md-none small" style="text-align: right;margin-right:20px;">
    This affiliate banner was designed by <?= $banner["is_anonymous"] ? "an anonymous user" : display_user_link_v2($banner)  ?>
  </div>
</div>
<script type="text/javascript">
  const closeButton = document.querySelector('#close-banner')
  closeButton.addEventListener('click', evt => {
    document.querySelector("#affiliate-banner").classList.add("d-none")
    document.cookie = "hide_banner=true; max-age=2678400"
  })
</script>
<?php endif; ?>
