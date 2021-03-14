<!-- reader controls -->
<div class="container reader-controls-container p-0">
    <div class="reader-controls-wrapper bg-reader-controls row no-gutters flex-nowrap" style="z-index:1">
        <div id="reader-controls-collapser" class="d-none d-lg-flex col-auto justify-content-center align-items-center cursor-pointer">
            <span class="fas fa-caret-right fa-fw arrow-link" aria-hidden="true" title="Collapse menu"></span>
        </div>
        <div class="reader-controls col row no-gutters flex-column flex-nowrap">
            <div class="reader-controls-title col-auto text-center p-2">
                <div style="font-size:1.25em">
                    <span class="rounded flag"></span>
                    <a class="manga-link" data-action="url"></a>
                    <span class="chapter-tag-h badge badge-danger d-none">H</span>
                    <span class="chapter-tag-doujinshi badge badge-primary d-none" style="background-color:#735ea5">Dj</span>
                </div>
                <div class="d-none d-lg-block"><span class="chapter-title" data-chapter-id=""></span> <span class="chapter-tag-end badge badge-primary d-none">END</span></div>
            </div>
            <div class="reader-controls-chapters col-auto row no-gutters align-items-center">
                <a class="chapter-link-left col-auto arrow-link" title="" href="" data-action="chapter" data-chapter="">
                    <span class="fas fa-angle-left fa-fw" aria-hidden="true"></span>
                </a>
                <div class="col py-2">
                    <select class="form-control col" id="jump-chapter" name="jump-chapter">
                    </select>
                </div>
                <a class="chapter-link-right col-auto arrow-link" title="" href="" data-action="chapter" data-chapter="">
                    <span class="fas fa-angle-right fa-fw" aria-hidden="true"></span>
                </a>
                <div class="col-auto py-2 pr-2 d-lg-none">
                    <select class="form-control" id="jump-page" name="jump-page">
                    </select>
                </div>
            </div>
            <div class="reader-controls-groups col-auto row no-gutters">
                <ul class="col list-unstyled p-2 m-0 chapter-link">
                </ul>
            </div>
            <div class="reader-controls-unsupported col-auto row no-gutters p-2 text-danger d-none"></div>
            <div class="reader-controls-actions col-auto row no-gutters p-1">
                <div class="col row no-gutters" style="min-width:120px;">
                    <a title="Reader settings" class="btn btn-secondary col m-1" role="button" id="settings-button" data-toggle="modal" data-target="#modal-settings">
                        <span class="fas fa-cog fa-fw"></span><span class="d-none d-lg-inline"> Settings</span>
                    </a>
                    <div class="w-100 d-none d-lg-block"></div>
                    <a title="Hide header" class="btn btn-secondary col m-1" role="button" id="hide-header-button">
                        <span class="far fa-window-maximize fa-fw"></span>
                    </a>
                    <!-- <a title="Fullscreen" class="btn btn-secondary col m-1" role="button" id="fullscreen-button">
                      <span class="fa fa-arrows-alt fa-fw"></span>
                    </a> -->
                    <a title="Comment" data-action="url" class="btn btn-secondary col m-1" role="button" id="comment-button">
                        <strong class="comment-amount" style="font-size:0.9em"></strong> <span class="far fa-comments fa-fw"></span>
                    </a>
                    <a title="Report" class="btn btn-secondary col m-1" role="button" id="report-button" data-toggle="modal" data-target="#modal-report">
                        <span class="fas fa-flag fa-fw"></span>
                    </a>
                </div>
            </div>
            <div class="reader-controls-mode col-auto d-lg-flex d-none flex-column align-items-start" style="flex:0 1 auto; overflow:hidden;">
                <div class="reader-controls-mode-display-fit w-100 cursor-pointer pt-2 px-2">
                    <kbd>^f</kbd>
                    <span class="fas fa-compress fa-fw" aria-hidden="true" title="Display fit"></span>
                    <span class="show-no-resize">No resize</span>
                    <span class="show-fit-both">Fit to container</span>
                    <span class="show-fit-height">Fit height</span>
                    <span class="show-fit-width">Fit width</span>
                </div>
                <div class="reader-controls-mode-rendering w-100 cursor-pointer px-2">
                    <kbd>&nbsp;g</kbd>
                    <span class="fas fa-book fa-fw" aria-hidden="true" title="Reader mode"></span>
                    <span class="show-single-page">Single page</span>
                    <span class="show-double-page">Double page</span>
                    <span class="show-long-strip">Long strip <span class="show-native-long-strip">(native)</span></span>
                    <span class="show-recommendations">Recommendations</span>
                    <span class="show-alert">Alert</span>
                </div>
                <div class="reader-controls-mode-direction w-100 cursor-pointer pb-2 px-2">
                    <kbd>&nbsp;h</kbd>
                    <!-- <span class="fas fa-exchange-alt fa-fw" aria-hidden="true" title="Direction"></span>
                    <span class="direction-ltr">Left to right</span>
                    <span class="direction-rtl">Right to left</span> -->
                    <span class="show-direction-ltr">
            <span class="fas fa-long-arrow-alt-right fa-fw" aria-hidden="true" title="Direction"></span> Left to right
          </span>
                    <span class="show-direction-rtl">
            <span class="fas fa-long-arrow-alt-left fa-fw" aria-hidden="true" title="Direction"></span> Right to left
          </span>
                </div>
            </div>
            <div class="reader-controls-footer col-auto mt-auto d-none d-lg-flex justify-content-center" style="flex:0 1 auto; overflow:hidden;">
                <div class="text-muted text-center text-truncate row flex-wrap justify-content-center p-2 no-gutters">
                    <span class="col-auto">©2020</span>
                    <a href="/" class="col-auto mx-2">MangaDex</a>
                    <a href="https://path.net/" target="_blank" title="Provider of DDoS mitigation services" class="col-auto mx-2">Path Network</a>
                    <a href="https://sdbx.moe/" target="_blank" title="Seedbox provider" class="col-auto">sdbx.moe</a>
                </div>
            </div>
            <div class="reader-controls-pages col-auto d-none d-lg-flex row no-gutters align-items-center">
                <a class="page-link-left col-auto arrow-link" href="" data-action="page" data-direction="left" data-by="1">
                    <span class="fas fa-angle-left fa-fw" aria-hidden="true" title="Turn page left"></span>
                </a>
                <div class="col text-center reader-controls-page-text cursor-pointer">
                    Page <span class="current-page">0</span> / <span class="total-pages">0</span>
                </div>
                <div class="col text-center reader-controls-page-recommendations">
                    Recommendations
                </div>
                <a class="page-link-right col-auto arrow-link" href="" data-action="page" data-direction="right" data-by="1">
                    <span class="fas fa-angle-right fa-fw" aria-hidden="true" title="Turn page right"></span>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- reader main -->
<div class="reader-main col row no-gutters flex-column flex-nowrap noselect" style="flex:1">
    <noscript>
        <div class="alert alert-danger text-center">
            JavaScript is required for this reader to work.
        </div>
    </noscript>
    <div class="reader-goto-top d-flex d-lg-none justify-content-center align-items-center fade cursor-pointer">
        <span class="fas fa-angle-up"></span>
    </div>
    <div class="reader-images col-auto row no-gutters flex-nowrap m-auto text-center cursor-pointer directional"></div>
    <div class="reader-load-icon">
        <span class="fas fa-circle-notch fa-spin" aria-hidden="true"></span>
    </div>
    <div class="reader-page-bar col-auto d-none d-lg-flex directional">
        <div class="track cursor-pointer row no-gutters">
            <div class="trail position-absolute h-100 noevents">
                <div class="thumb h-100"></div>
            </div>
            <div class="notches row no-gutters h-100 w-100 directional"></div>
            <div class="notch-display col-auto m-auto px-3 py-1 noevents"></div>
        </div>
    </div>
</div>
<!-- report modal -->
<div class="modal" id="modal-report" tabindex="-1" role="dialog" aria-labelledby="modal-report-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-report-label"><span class='fas fa-flag fa-fw' aria-hidden='true' title=''></span> Report chapter</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="chapter-report-form" method="post" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="type_id" class="col-sm-3 col-form-label">Reasons</label>
                        <div class="col-sm-9">
                            <select required title="Select a reason" class="form-control" name="type_id">
                                <?php
                                $chapter_reasons = array_filter($templateVar['report_reasons'], function($reason) { return REPORT_TYPES[$reason['type_id']] === 'Chapter'; });
                                foreach ($chapter_reasons as $reason): ?>
                                    <option value="<?= $reason['id'] ?>"><?= $reason['text'] ?><?= $reason['is_info_required'] ? ' *' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="info" class="col-sm-3 col-form-label">Explanation</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="info" placeholder="Required for reasons marked with *" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-lg-offset-3 col-sm-9"></div>
                    </div>
                    <div class="alert-container"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <span class='fas fa-undo fa-fw' aria-hidden='true' title=''></span> Close
                    </button>
                    <button type="submit" class="btn btn-warning loading-container" id="chapter-report-submit">
                        <span class="d-not-loading"><span class='fas fa-flag fa-fw'></span> Submit report</span>
                        <span class="d-loading"><span class='fas fa-spinner fa-pulse fa-fw'></span> Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- settings modal -->
<!-- FIXME: bootstrap 3 legacy -->
<div class="modal" id="modal-settings" tabindex="-1" role="dialog" aria-labelledby="modal-settings-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-settings-label"><span class='fas fa-cog fa-fw' aria-hidden='true' title=''></span> Reader settings</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- <div id="alert-storage-warning" class="alert alert-warning text-center d-none" role="alert">
                  <span class='fas fa-exclamation-triangle fa-fw' aria-hidden='true' title=''></span> These reader settings cannot be permanently saved on this browser due to the unavailability of <a href="https://en.wikipedia.org/wiki/Web_storage">Web Storage</a>.
                </div> -->
                <div class="container">
                    <div class="form-group row">
                        <div class="col">
                            <div class="custom-control custom-checkbox form-check">
                                <input type="checkbox" id="showAdvanced" data-setting="showAdvancedSettings" data-value="0" class="custom-control-input">
                                <label for="showAdvanced" class="custom-control-label"> Display advanced (*) settings
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h5><span class='fas fa-book-open fa-fw' aria-hidden='true' title=''></span> Display settings</h5>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Fit display to</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="displayFit" class="btn btn-default btn-secondary col px-2">Container</button>
                                <button type="button" data-value="2" data-setting="displayFit" class="btn btn-default btn-secondary col px-2">Width</button>
                                <button type="button" data-value="3" data-setting="displayFit" class="btn btn-default btn-secondary col px-2">Height</button>
                                <button type="button" data-value="4" data-setting="displayFit" class="btn btn-default btn-secondary col px-2">No resize</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row advanced">
                        <label class="col-sm-4 col-form-label">Maximum container width</label>
                        <div class="col px-0 my-auto input-group">
                            <input data-setting="containerWidth" class="form-control" type="number" min="0" step="50" placeholder="Leave empty for 100%">
                            <div class="input-group-append">
                                <span class="input-group-text">pixels</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Page rendering</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="renderingMode" class="btn btn-default btn-secondary col px-2">Single</button>
                                <button type="button" data-value="2" data-setting="renderingMode" class="btn btn-default btn-secondary col px-2">Double</button>
                                <button type="button" data-value="3" data-setting="renderingMode" class="btn btn-default btn-secondary col px-2">Long strip</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Direction</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="direction" class="btn btn-default btn-secondary col px-2">Left to right</button>
                                <button type="button" data-value="2" data-setting="direction" class="btn btn-default btn-secondary col px-2">Right to left</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h5><span class='fas fa-columns fa-fw' aria-hidden='true' title=''></span> Layout settings</h5>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Header</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="0" data-setting="hideHeader" class="btn btn-default btn-secondary col px-2">Visible</button>
                                <button type="button" data-value="1" data-setting="hideHeader" class="btn btn-default btn-secondary col px-2">Hidden</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Sidebar</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="0" data-setting="hideSidebar" class="btn btn-default btn-secondary col px-2">Visible</button>
                                <button type="button" data-value="1" data-setting="hideSidebar" class="btn btn-default btn-secondary col px-2">Hidden</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Page bar</label>
                        <div class="col d-none d-lg-block">
                            <div class="row">
                                <button type="button" data-value="0" data-setting="hidePagebar" class="btn btn-default btn-secondary col px-2">Visible</button>
                                <button type="button" data-value="1" data-setting="hidePagebar" class="btn btn-default btn-secondary col px-2">Hidden</button>
                            </div>
                        </div>
                        <div class="col d-lg-none">
                            <div class="row">
                                <button type="button" disabled class="btn btn-default btn-secondary col px-2">Hidden on the mobile layout</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row advanced">
                        <label class="col-sm-4 col-form-label">Chapter dropdown titles</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="showDropdownTitles" class="btn btn-default btn-secondary col px-2">Visible</button>
                                <button type="button" data-value="0" data-setting="showDropdownTitles" class="btn btn-default btn-secondary col px-2">Hidden</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h5><span class='fas fa-hand-pointer fa-fw' aria-hidden='true' title=''></span> Input settings</h5>
                    <div class="row form-group advanced">
                        <label class="col-sm-4 col-form-label">Tap/click target area</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="tapTargetArea" class="btn btn-default btn-secondary col px-2">Entire container</button>
                                <button type="button" data-value="0" data-setting="tapTargetArea" class="btn btn-default btn-secondary col px-2">Images only</button>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-sm-4 col-form-label">Turn page by tapping/clicking</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="pageTapTurn" class="btn btn-default btn-secondary col px-2">Directional turn</button>
                                <button type="button" data-value="2" data-setting="pageTapTurn" class="btn btn-default btn-secondary col px-2">Always turn forward</button>
                                <button type="button" data-value="0" data-setting="pageTapTurn" class="btn btn-default btn-secondary col px-2">Disabled</button>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group advanced">
                        <label class="col-sm-4 col-form-label">Turn page by vertical scrolling</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="pageWheelTurn" class="btn btn-default btn-secondary col px-2">Mouse wheel + keys</button>
                                <button type="button" data-value="2" data-setting="pageWheelTurn" class="btn btn-default btn-secondary col px-2">Mouse wheel</button>
                                <button type="button" data-value="0" data-setting="pageWheelTurn" class="btn btn-default btn-secondary col px-2">Disabled</button>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-sm-4 col-form-label">Keyboard scrolling method</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="2" data-setting="scrollingMethod" class="btn btn-default btn-secondary col px-2">Browser native</button>
                                <button type="button" data-value="0" data-setting="scrollingMethod" class="btn btn-default btn-secondary col px-2">Browser native + WASD</button>
                                <button type="button" data-value="1" data-setting="scrollingMethod" class="btn btn-default btn-secondary col px-2">Screen portion</button>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group advanced">
                        <label class="col-sm-4 col-form-label">Touchscreen swipe direction</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="0" data-setting="swipeDirection" class="btn btn-default btn-secondary col px-2">Normal</button>
                                <button type="button" data-value="1" data-setting="swipeDirection" class="btn btn-default btn-secondary col px-2">Inverted</button>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-sm-4 col-form-label">Touchscreen swipe sensitivity</label>
                        <div class="col px-0 my-auto">
                            <select class="form-control" data-setting="swipeSensitivity">
                                <option value="0">Off</option>
                                <option value="1">Very low</option>
                                <option value="2">Low</option>
                                <option value="3">Normal</option>
                                <option value="4">High</option>
                                <option value="5">Very high</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <h5><span class='fas fa-folder-open fa-fw' aria-hidden='true' title=''></span> Other settings</h5>
                    <div class="row form-group">
                        <label class="col-sm-4 col-form-label">Preload images (0 to <span class="preload-max-value">5</span>)</label>
                        <div class="col px-0 my-auto">
                            <input data-setting="preloadPages" class="form-control" type="number" min="0" max="5" placeholder="The amount of images (default: 3)">
                        </div>
                    </div>
                    <div class="row form-group advanced">
                        <label class="col-sm-4 col-form-label">Preload this entire chapter</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" id="preload-all" class="btn btn-default btn-secondary col px-2" disabled>Logged in users only</button>
                            </div>
                        </div>
                    </div>
                    <!--<div class="row form-group">
                        <label class="col-sm-4 col-form-label">Warn about chapter gaps</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="gapWarning" class="btn btn-default btn-secondary col px-2">Enabled</button>
                                <button type="button" data-value="0" data-setting="gapWarning" class="btn btn-default btn-secondary col px-2">Disabled</button>
                            </div>
                        </div>
                    </div>-->
                    <div class="row form-group">
                        <label class="col-sm-4 col-form-label">Image server</label>
                        <div class="col px-0 my-auto">
                            <select class="form-control" data-setting="imageServer">
                                <option value="0">Automatic</option>
                                <option value="na">NA/EU 1</option>
                                <option value="na2">NA/EU 2</option>
                                <!--<option value="eu">Europe</option>
                                <option value="eu2">Europe 2</option>-->
                                <!--<option value="row">Rest of the world</option>-->
                            </select>
                        </div>
                    </div>
                  <div class="row form-group">
                    <label class="col-sm-4 col-form-label">Data saver <a href="/thread/252554"><span class="fas fa-info-circle fa-fw" title="More information"></span></a></label>
                      <div class="col">
                          <div class="row">
                            <button type="button" data-value="0" data-setting="dataSaver" class="btn btn-default btn-secondary col px-2">Original images</button>
                            <button type="button" data-value="1" data-setting="dataSaver" class="btn btn-default btn-secondary col px-2">Compressed images</button>
                          </div>
                      </div>
                  </div>
                    <div class="row form-group advanced">
                        <label class="col-sm-4 col-form-label">[BETA] Recommendations</label>
                        <div class="col">
                            <div class="row">
                                <button type="button" data-value="1" data-setting="betaRecommendations" class="btn btn-default btn-secondary col px-2">Enabled</button>
                                <button type="button" data-value="0" data-setting="betaRecommendations" class="btn btn-default btn-secondary col px-2">Disabled</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <h4><span class='fas fa-keyboard fa-fw' aria-hidden='true' title=''></span> Keyboard shortcuts</h4>
                        <p>
                            <kbd>^</kbd> = shift key
                            <br>
                            <kbd>^f</kbd> = shift + f</p>
                        <ul class="list-unstyled container">
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>f</kbd>
                                </div>
                                <div class="col pl-2">Toggle between fit display to container and width</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>^f</kbd>
                                </div>
                                <div class="col pl-2">Toggle between fit display to height and no resize</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>g</kbd>
                                    <kbd>^g</kbd>
                                </div>
                                <div class="col pl-2">Toggle between the page rendering options</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>h</kbd>
                                </div>
                                <div class="col pl-2">Toggle between the reader directions</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>r</kbd>
                                </div>
                                <div class="col pl-2">Toggle header visibility</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>t</kbd>
                                </div>
                                <div class="col pl-2">Toggle side bar visibility</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>y</kbd>
                                </div>
                                <div class="col pl-2">Toggle page bar visibility</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>^r</kbd>
                                    <kbd>^t</kbd>
                                    <kbd>^y</kbd>
                                </div>
                                <div class="col pl-2">Show/hide all header, side bar and page bar</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>^m</kbd>
                                </div>
                                <div class="col pl-2">Exit to the manga's main page</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>^k</kbd>
                                </div>
                                <div class="col pl-2">Exit to the chapter's comments</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>^q</kbd>
                                    <kbd>^e</kbd>
                                </div>
                                <div class="col pl-2">Go to the next/previous chapter depending on the direction</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>&larr;&uarr;&darr;&rarr;</kbd>
                                    <kbd>wasd</kbd>
                                </div>
                                <div class="col pl-2">Scroll the screen and turn pages</div>
                            </li>
                            <li class="row no-gutters">
                                <div class="col-2 text-right">
                                    <kbd>^&larr;</kbd>
                                    <kbd>^&rarr;</kbd>
                                    <kbd>^a</kbd>
                                    <kbd>^d</kbd>
                                </div>
                                <div class="col pl-2">Shift by a single page in double page mode</div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-secondary" data-dismiss="modal"><span class='fas fa-undo fa-fw' aria-hidden='true' title=''></span> Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
