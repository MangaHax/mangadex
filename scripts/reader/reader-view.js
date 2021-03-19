import EventEmitter from 'wolfy87-eventemitter'
import ReaderModel from './reader-model.js'
import * as ReaderComponent from './reader-component.js'
import KeyboardShortcuts from './keyboard-shortcuts.js'
import * as Renderer from './renderer.js'
import Utils from './utils.js'

const HISTORY_UPDATE_DELAY = 100

export default class ReaderView extends EventEmitter {
  constructor(model) {
    super()
    this.model = model
    this.el = null
    this.imageContainer = null
    this.renderer = null
    this._isRendering = false
    this._lastHistoryUpdate = Date.now()
    this._listeners = {}
  }

  get renderedPages() {
    if (this.renderer == null) {
      return 0
    } else if (this.model.isLongStrip) {
      return 1
    } else {
      return this.renderer.renderedPages
    }
  }

  get isRendering() { return this._isRendering }
  set isRendering(val) {
    this._isRendering = val
    this.onLoadingChange()
  }

  initialize(container) {
    if (!container) {
      throw new Error("Main container missing")
    }
    this.el = container
    this.imageContainer = this.el.querySelector('.reader-images')
    if (!this.imageContainer) {
      throw new Error("Image container (.reader-images) missing")
    }
    this.el.classList.remove('container')
    this.el.classList.add('reader', 'row', 'flex-column', 'no-gutters')
    this.el.classList.add('layout-horizontal')
    const footer = document.querySelector('footer')
    if (footer) { footer.classList.add('d-none') }
    document.body.style.removeProperty('margin-bottom')
    if (this.model.isUserGuest) {
      const reportBtn = this.el.querySelector('#report-button')
      if (reportBtn) {
        reportBtn.dataset.toggle = ''
        reportBtn.href = '/login'
        reportBtn.firstElementChild.classList.replace('fa-flag', 'fa-sign-in-alt')
      }
    }

    for (let [key, value] of Object.entries(this.model.settings)) {
      this.onSettingChange(key, value)
    }
    this.onRenderingModeChange()
    this.onDisplayFitChange()
    this.onDirectionChange()
    this.initializeModelEventHandlers()
  }

  addListeners() {
    this.listenSettingEvents()
    this.listenButtonEvents()
    this.listenInputs()
  }

  initializeModelEventHandlers() {
    this.model.on('loadingchange', () => this.onLoadingChange())
    this.model.on('renderingmodechange', () => this.onRenderingModeChange())
    this.model.on('displayfitchange', () => this.onDisplayFitChange())
    this.model.on('directionchange', () => this.onDirectionChange())
    this.model.on('chapterchange', () => this.onChapterChange())
    this.model.on('chapterlistchange', () => this.onChapterListChange())
    this.model.on('mangachange', () => this.onMangaChange())
    this.model.on('statechange', () => this.onStateChange())
    this.model.on('currentpagechange', () => this.onCurrentPageChange())
    this.model.on('readererror', (error) => this.onReaderError(error))
    this.model.on('pageload', (page) => this.onPageLoad(page))
    this.model.on('pageerror', (page) => this.onPageError(page))
    this.model.on('settingchange', (key, value) => this.onSettingChange(key, value))
  }

  onLoadingChange() {
    this.el.classList.toggle('is-loading', this.model.isLoading || this._isRendering)
  }

  onRenderingModeChange() {
    if (this.renderer != null) {
      this.renderer.destroy()
    }
    const rendererClass = ReaderView.getRendererClass(this.model.renderingMode)
    this.renderer = new rendererClass(this.imageContainer, this.model, this)
    this.el.dataset.renderer = this.renderer.name
    this.toggleListener('go to top scroll', this.model.isLongStrip)
    this.resetGoToTop()
    if (this.model.chapter) {
      this.renderer.initialize()
      if (this.renderer.name === 'long-strip') {
        try {
          this.renderer.scrollToPage(this.model.currentPage)
        } catch (e) { }
      }
    }
  }

  renderPageInNewMode(mode, page) {
    if (this.model.renderingMode !== mode) {
      this.model.renderingMode = mode
    }
    return this.renderPage(page)
  }

  onDisplayFitChange() {
    this.el.dataset.display = ReaderView.getDisplayFitString(this.model.displayFit)
    this.el.classList.toggle('fit-horizontal', this.model.isFitBoth || this.model.isFitWidth)
    this.el.classList.toggle('fit-vertical', this.model.isFitBoth || this.model.isFitHeight)
    this.forceImageLayoutRefresh()
  }

  forceImageLayoutRefresh() {
    // tries to fix a Chrome bug by forcing a refresh on the image positions
    if (Modernizr.requestanimationframe) {
      Array.from(this.el.querySelectorAll('.reader-image-wrapper img')).forEach(i => {
        i.classList.add('m-0')
        requestAnimationFrame(() => i.classList.remove('m-0'))
      })
    }
  }

  onDirectionChange() {
    this.el.dataset.direction = ReaderModel.directionState.LTR === this.model.direction ? 'ltr' : 'rtl'
    if (this.model.chapter) {
      this.updateChapterLinks()
      this.updatePageBar()
    }
  }

  onChapterChange() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    const nativeLongStrip = this.model.manga.isLongStrip
    this.model.renderingMode = nativeLongStrip ? ReaderModel.renderingModeState.LONG : this.model.settings.renderingMode
    this.model.displayFit = nativeLongStrip ? ReaderModel.displayFitState.FIT_WIDTH : this.model.settings.displayFit
    if (this.renderer) {
      this.renderer.reinitialize()
    }
    this.el.dataset.mangaId = this.model.manga.id || 0
    this.el.dataset.chapterId = this.model.chapter.id || 0
    this.el.dataset.totalPages = this.model.totalPages || 0
    this.el.classList.toggle('native-long-strip', nativeLongStrip)
    this.resetPageBar()
    this.updateUI()
    const preloadAllBtn = document.querySelector('#preload-all')
    if (preloadAllBtn) {
      preloadAllBtn.disabled = this.model.isUserGuest
      preloadAllBtn.textContent = this.model.isUserGuest ? 'Logged in users only' : 'Start preloading'
    }
  }

  onChapterListChange() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    this.updateChapterDropdown()
    this.updateGroupList()
    this.updateChapterLinks()
  }

  onMangaChange() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    if (this.model.settings.gapWarning && this.model.chapter.isSequentialWith(this.model.chapter.prevChapter) === false) {
      this.moveToGapCheck(this.model.chapter.id, this.model.chapter.prevChapterId)
    }
  }

  onStateChange() {
    this.toggleListener('page tap', this.model.isStateReading)
    this.toggleListener('page wheel', this.model.isStateReading)
    switch (this.model.state) {
      case ReaderModel.readerState.RECS:
        this.imageContainer.classList.remove('cursor-pointer')
        // this.model.renderingMode = ReaderModel.renderingModeState.RECS
        this.renderPageInNewMode(ReaderModel.renderingModeState.RECS, {})
        break
      case ReaderModel.readerState.GAP:
        this.renderPageInNewMode(ReaderModel.renderingModeState.ALERT, { isSpoilerNet: true, chapterId: this.gapChapterId, prevChapterId: this.gapPrevChapterId })
        break
      case ReaderModel.readerState.EXITING:
        this.el.classList.add('is-loading')
        break
      case ReaderModel.readerState.ERROR:
        this.imageContainer.classList.remove('cursor-pointer')
        break
      case ReaderModel.readerState.READING:
      default:
        this.imageContainer.classList.toggle('cursor-pointer', this.model.settings.tapTargetArea)
        this.model.renderingMode = this.model.settings.renderingMode
        break
    }
  }

  onCurrentPageChange() {
    this.el.dataset.currentPage = this.model.currentPage || 0
    if (this.model.currentPage > 0) {
      this.model.preload(this.model.currentPage + this.renderedPages)
      this.renderPage(this.model.currentPage)
    }
  }

  onPageLoad(page) {
    if (this.model.chapter && page.chapter === this.model.chapter.id) {
      const notch = this.el.querySelector(`.notch[data-page="${page.number}"]`)
      if (notch) {
        notch.classList.add('loaded')
        notch.classList.remove('failed')
      }
    }
  }

  onPageError(page) {
    if (this.model.chapter && page.chapter === this.model.chapter.id) {
      const notch = this.el.querySelector(`.notch[data-page="${page.number}"]`)
      if (notch) {
        notch.classList.add('failed')
      }
    }
  }

  onReaderError(error) {
    this.renderPageInNewMode(ReaderModel.renderingModeState.ALERT, error)
  }

  get swipeThreshold() {
    return screen.availWidth / ((this.model.settings.swipeSensitivity + 1) * 0.9)
  }

  onSettingChange(key, value) {
    let el = null
    switch (key) {
      case 'renderingMode':
        if (this.model.chapter) {
          this.renderPage()
        }
        break
      case 'showAdvancedSettings':
        el = this.el.querySelector('#modal-settings')
        if (el) { el.classList.toggle('show-advanced', !!value) }
        break
      case 'swipeSensitivity':
        if (jQuery) {
          jQuery(this.imageContainer).swipe('option', 'threshold', this.swipeThreshold)
        }
        break
      case 'containerWidth':
        if (!value) {
          value = null
        }
        this.imageContainer.classList.toggle('constrained', !!parseInt(value))
        this.imageContainer.style.maxWidth = parseInt(value) ? `${value}px` : null
        break
      case 'showDropdownTitles':
        if (this.model.chapter) {
          this.updateChapterDropdown()
        }
        break
      case 'tapTargetArea':
        this.imageContainer.classList.toggle('cursor-pointer', !!value)
        break
      case 'hideCursor':
        this.toggleListener('hide cursor', value)
        if (!value) {
          clearTimeout(this._cursorHideDebounce)
          this.el.classList.remove('hide-cursor')
        }
        break
      case 'hideHeader':
        this.el.classList.toggle('hide-header', value)
        el = document.querySelector('nav.navbar')
        if (el) { el.classList.toggle('d-none', value) }
        el = document.querySelector('#hide-header-button')
        if (el) { el.classList.toggle('active', value) }
        break
      case 'hideSidebar':
        this.el.classList.toggle('hide-sidebar', value)
        break
      case 'hidePagebar':
        this.el.classList.toggle('hide-page-bar', value)
        break
      case 'collapserStyle':
        this.el.dataset.collapser = value === 0 ? 'button' : 'bar'
        break
      case 'preloadPages':
        this.el.querySelector('.preload-max-value').textContent = this.model.preloadMax
        const input = this.el.querySelector('input[data-setting="preloadPages"]')
        input.max = this.model.preloadMax
        input.placeholder = `The amount of images (default: ${this.model.settingDefaults.preloadPages})`
        if (this.model.chapter) {
          this.model.preload(this.model.currentPage + this.renderedPages)
        }
        break
      case 'betaRecommendations':
        const recsBtn = document.querySelector('#recommendations-button')
        recsBtn.classList.toggle('d-none', !value)
        break
    }
    Array.from(this.el.querySelectorAll(`select[data-setting="${key}"]`)).forEach((n) => { n.value = value })
    Array.from(this.el.querySelectorAll(`input[data-setting="${key}"]`)).forEach((n) => { n.value = value })
    Array.from(this.el.querySelectorAll(`input[type="checkbox"][data-setting="${key}"]`)).forEach((n) => { n.checked = !!value })
    Array.from(this.el.querySelectorAll(`button[data-setting="${key}"]`)).forEach((n) => { n.classList.toggle('active', n.dataset.value == value) })
  }

  updateUI() {
    this.updateTitles()
    this.updateChapterDropdown()
    this.resetPageDropdown()
    this.updatePageDropdown()
    this.updatePageLinks()
    this.updateGroupList()
    this.updateCommentsButton()
    this.updateChapterLinks()
  }

  updatePage() {
    if (this.model.chapter) {
      this.updatePageBar()
      this.updatePageDropdown()
      this.updatePageLinks()
    }
  }

  updateTitles() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    const chapter = this.model.chapter
    const manga = this.model.manga
    document.title = Utils.htmlTextDecodeHack(`${manga.title} - ${chapter.fullTitle} - MangaDex`)
    ReaderComponent.Flag.render(manga, this.el.querySelector('.reader-controls-title .flag'))
    ReaderComponent.Link.render(manga, this.el.querySelector('.manga-link'))
    this.el.querySelector('.chapter-title').textContent = Utils.htmlTextDecodeHack(chapter.title)
    this.el.querySelector('.chapter-title').dataset.chapterId = chapter.id
    this.el.querySelector('.chapter-tag-h').classList.toggle('d-none', !manga.isHentai)
    this.el.querySelector('.chapter-tag-end').classList.toggle('d-none', !chapter.isLastChapter)
    this.el.querySelector('.chapter-tag-doujinshi').classList.toggle('d-none', !manga.isDoujinshi)
  }

  resetChapterDropdown() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    ReaderComponent.ChapterDropdown.render(this.model, this.el.querySelector('#jump-chapter'))
  }

  updateChapterDropdown() {
    this.resetChapterDropdown()
  }

  resetPageDropdown() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    ReaderComponent.PageDropdown.render(this.model, this.el.querySelector('#jump-page'))
  }

  updatePageDropdown() {
    this.el.querySelector('#jump-page').selectedIndex = this.model.currentPage - 1
  }

  updateGroupList() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    const groups = this.el.querySelector('.reader-controls-groups ul')
    Utils.emptyNode(groups)
    for (let ch of this.model.manga.getAltChapters(this.model.chapter.id)) {
      ch.isCurrentChapter = ch.id == this.model.chapter.id
      groups.appendChild(ReaderComponent.GroupItem.render(ch))
    }
  }

  updateCommentsButton() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    this.el.querySelector('#comment-button').href = `${this.pageURL(this.model.chapter.id)}/comments`
    this.el.querySelector('.comment-amount').textContent = this.model.chapter.comments || ''
  }

  updateChapterLinks() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    const update = (previous) => {
      let id = previous ? this.model.chapter.prevChapterId : this.model.chapter.nextChapterId
      return (a) => {
        a.dataset.chapter = id
        a.href = this.pageURL(id)
        a.title = this.model.chapter.fullTitle || 'Back to manga'
      }
    }
    Array.from(this.el.querySelectorAll('.chapter-link-left')).forEach(update(this.model.isDirectionLTR))
    Array.from(this.el.querySelectorAll('.chapter-link-right')).forEach(update(this.model.isDirectionRTL))
  }

  updatePageLinks() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    const pg = this.model.currentPage
    const pgStr = pg + (this.renderedPages === 2 ? ` - ${pg + 1}` : '')
    const ctrlPages = this.el.querySelector('.reader-controls-pages')
    ctrlPages.querySelector('.current-page').textContent = pgStr
    ctrlPages.querySelector('.total-pages').textContent = this.model.totalPages
    ctrlPages.querySelector('.page-link-left').href = this.pageLeftURL(1)
    ctrlPages.querySelector('.page-link-right').href = this.pageRightURL(1)
    this.el.querySelector('#jump-page').value = pg
  }

  resetPageBar() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    // TODO: make less demanding on chrome
    const notches = this.el.querySelector('.reader-page-bar .notches')
    if (notches) {
      Utils.emptyNode(notches)
      for (let i = 1; i <= this.model.totalPages; ++i) {
        const notch = notches.appendChild(document.createElement('div'))
        notch.classList.add('notch', 'col')
        notch.style.order = i
        notch.dataset.page = i
        // const wrapper = notch.appendChild(document.createElement('div'))
        // const page = wrapper.appendChild(document.createElement('div'))
        // page.textContent = `${i} / ${this.model.totalPages}`
      }
      this.updatePageBar()
    }
  }

  updatePageBar() {
    if (!this.model.chapter || !this.model.manga) {
      return
    }
    const trail = this.el.querySelector('.reader-page-bar .trail')
    const thumb = this.el.querySelector('.reader-page-bar .thumb')
    if (trail && thumb) {
      const total = Math.max(this.model.totalPages, 1)
      const rendered = Math.max(this.renderedPages, 1)
      const pg = Math.max(this.model.currentPage + rendered - 1, 1)
      const notchSize = 100 / total
      trail.style.width = Math.min(pg * notchSize, 100) + '%'
      thumb.style.width = (100 / pg * rendered) + '%'
      trail.style.right = this.model.isDirectionLTR ? null : 0
      thumb.style.float = this.model.isDirectionLTR ? 'right' : 'left'
    }
  }

  renderPage(pg = this.model.currentPage) {
    return new Promise((resolve, reject) => {
      if (!this.renderer) {
        return reject("No renderer")
      } else if (this.isRendering) {
        return reject("Already rendering")
      } else {
        //this.isRendering = true
        this.updatePage()
        return resolve(this.renderer.render(pg))
      }
    }).then(() => {
      //this.isRendering = false
      this.el.dataset.renderedPages = this.renderedPages
      if (!this.model.isLongStrip) {
        this.scrollPageIntoView()
      }
      this.forceImageLayoutRefresh()
      if (this.model.chapter) {
        this.model.preload(this.model.currentPage + this.renderedPages)
        this.updatePage()
      }
    })
      .catch((err) => {
        console.error(err)
        if (err && err.revert === true) {
          this.renderPageInNewMode(this.model.settings.renderingMode, pg)
        }
        // this.isRendering = false
      })
  }

  scrollPageIntoView() {
    const scrollView = () => {
      // this.imageContainer.scrollIntoView(this.model.isFitWidth || this.model.isNoResize)
      this.imageContainer.scrollIntoView(true)
      if (this.model.isFitHeight || this.model.isNoResize) {
        ReaderView.scroll(document.body.scrollWidth * (this.model.isDirectionRTL ? 1 : -1), 0)
      }
      if (this.model.isFitWidth || this.model.isNoResize) {
        const nav = document.querySelector('nav.navbar')
        if (nav) { ReaderView.scroll(0, -nav.offsetHeight) }
      }
    }
    if (Modernizr.requestanimationframe) {
      // use RAF to hopefully avoid scrolling before the image has properly rendered
      window.requestAnimationFrame(() => { scrollView() })
    } else {
      scrollView()
    }
  }

  getHistoryStateObject() {
    return {
      page: this.model.currentPage,
      chapter: this.model.chapter.id,
      state: this.model.state,
    }
  }

  pushHistory(chapter = this.model.chapter.id, page = this.model.currentPage) {
    if (Modernizr.history && this._lastHistoryUpdate + HISTORY_UPDATE_DELAY < Date.now()) {
      const curState = window.history.state
      page = !page ? null : page
      if (!(curState && curState.page == page && curState.chapter == chapter && curState.state == this.model.state)) {
        this._lastHistoryUpdate = Date.now()
        const url = this.pageURL(chapter, page)
        const newState = { chapter, page, state: this.model.state }
        try { window.history.pushState(newState, '', url) }
        catch (e) { console.warn(e) }
      }
    }
  }

  replaceHistory(chapter = this.model.chapter.id, page = this.model.currentPage) {
    if (Modernizr.history && this._lastHistoryUpdate + HISTORY_UPDATE_DELAY < Date.now()) {
      this._lastHistoryUpdate = Date.now()
      const url = this.pageURL(chapter, page)
      const newState = { chapter, page, state: this.model.state }
      try { window.history.replaceState(newState, '', url) }
      catch (e) { console.warn(e) }
    }
  }

  turnPageLeft(pages) {
    this.turnPage(this.model.isDirectionRTL, pages)
  }
  turnPageRight(pages) {
    this.turnPage(this.model.isDirectionLTR, pages)
  }
  turnPageBackward(pages) {
    this.turnPage(false, pages)
  }
  turnPageForward(pages) {
    this.turnPage(true, pages)
  }
  turnPage(forward, pages = this.model.isDoublePage ? 2 : 1) {
    if (this.model.isDoublePage && (
      (forward && this.renderedPages === 1) ||
      (!forward && this.model.currentPage <= 2))) {
      pages = 1
    }
    pages = forward ? pages : -pages
    this.moveToPage(Math.max(this.model.currentPage, 1) + pages)
  }

  moveToPage(pg, useHistory = true) {
    if (pg === 'recs') {
      return this.moveToRecommendations(useHistory)
    } else if (pg === 'gap') {
      this.moveToGapCheck(this.model.chapter.id, this.model.chapter.prevChapterId, useHistory)
      return Promise.resolve()
    } else {
      const oldState = this.model.state
      if (!this.model.isStateReading) {
        this.model.state = ReaderModel.readerState.READING
      }
      return this.model.moveToPage(pg)
        .then(() => {
          if (this.model.isLongStrip) {
            this.renderer.scrollToPage(pg)
          }
          if (useHistory) {
            if (!this.model.isLongStrip) {
              this.pushHistory()
            } else {
              this.replaceHistory()
            }
          }
        })
        .catch((err) => {
          if (err && err.chapter != null) {
            return this.moveToChapter(err.chapter, err.page, useHistory, oldState === ReaderModel.readerState.GAP)
          } else {
            console.error(err)
          }
          return Promise.resolve()
        })
    }
  }

  async moveToChapter(id, pg = 1, useHistory = true, skipGapCheck = this.model.isStateGap) {
    if (this.model.isLoading || isNaN(id) || this.model.exiting) {
      return
    }
    if (id === -1) {
      // going backwards from the first chapter
      // this.exitToURL(this.model.manga.url)
    } else if (id === 0) {
      // reload chapter list data to see if there has been an update
      await this.model.reloadChapterList()
      if (this.model.chapter.nextChapterId !== 0) {
        return this.moveToChapter(this.model.chapter.nextChapterId, 1, useHistory)
      } else if (this.model.settings.betaRecommendations) {
        this.moveToRecommendations()
      } else {
        this.exitToURL(this.model.manga.url)
      }
    } else {
      if (!skipGapCheck && this.model.settings.gapWarning && id === this.model.chapter.nextChapterId) {
        if (this.model.chapter.isSequentialWith(id) === false) {
          this.moveToGapCheck(id, this.model.chapter.id, useHistory)
          return
        }
      }
      try {
        await this.model.moveToChapter(id, pg)
        if (this.model.chapter && !this.model.chapter.error && this.model.isStateReading) {
          return this.moveToPage(pg, useHistory)
        } else if (useHistory) {
          this.pushHistory()
        }
      } catch (e) { }
    }
  }

  moveToGapCheck(chapterId, prevChapterId, useHistory = true) {
    const gotoLastPage = (this.model.isDoublePage && this.renderedPages === 2 && this.model.currentPage === this.model.totalPages - 1)
    this.gapChapterId = chapterId
    this.gapPrevChapterId = prevChapterId
    if (gotoLastPage) {
      this.model.setCurrentPage(this.model.totalPages)
    }
    this.model.state = ReaderModel.readerState.GAP
    if (useHistory) {
      this.pushHistory(chapterId, 'gap')
    }
  }

  async moveToRecommendations(useHistory = true) {
    await this.model.moveToRecommendations()
    this.model.setCurrentPage(this.model.totalPages + 1)
    if (useHistory) {
      this.pushHistory(undefined, 'recs')
    }
  }

  exitToURL(url) {
    if (!this.model.exiting) {
      this.model.exitReader()
      window.location = url
    }
  }

  pageURL(id, pg) {
    if (id != null && id > 0 || typeof id === 'string') {
      if (pg != null && (!this.model.chapter.isExternal || typeof pg === 'string')) {
        if (pg === 0) {
          return this.pageURL(this.model.chapter.prevChapterId, -1)
        } else if (pg > this.model.totalPages) {
          return this.pageURL(this.model.chapter.nextChapterId)
        }
        return `/chapter/${id}/${pg}`
      }
      return `/chapter/${id}`
    }
    return this.model.manga.url
  }

  pageLeftURL(pages = this.model.isDoublePage ? 2 : 1) {
    return this.pageURL(this.model.chapter.id, Math.min(this.model.currentPage + (this.model.isDirectionLTR ? -pages : pages)), 0)
  }

  pageRightURL(pages = this.model.isDoublePage ? 2 : 1) {
    return this.pageURL(this.model.chapter.id, Math.min(this.model.currentPage + (this.model.isDirectionLTR ? pages : -pages)), 0)
  }

  addListener(action, el, type, handler, useCapture = false) {
    if (typeof el === 'string') {
      const elStr = el
      el = this.el.querySelector(elStr)
      if (!el) {
        throw new Error(`Element "${elStr}" not found`)
      }
    }
    this._listeners[action] = { el, type, handler, useCapture, active: false }
    this.toggleListener(action, true)
  }

  toggleListener(action, on) {
    const ln = this._listeners[action]
    if (ln && ln.active && !on) {
      ln.el.removeEventListener(ln.type, ln.handler, ln.useCapture)
      ln.active = false
    } else if (ln && !ln.active && on) {
      ln.el.addEventListener(ln.type, ln.handler, ln.useCapture)
      ln.active = true
    }
  }

  listenSettingEvents() {
    const settingInputs = [
      ['#modal-settings input[type="checkbox"][data-setting]', 'change'],
      ['#modal-settings input[data-setting]', 'keyup'],
      ['#modal-settings input[data-setting]', 'change'],
      ['#modal-settings select[data-setting]', 'change'],
      ['#modal-settings button[data-setting]', 'click'],
    ]
    const saveSettingValue = (evt) => {
      if (evt.target.type === 'checkbox') {
        this.model.saveSetting(evt.target.dataset.setting, evt.target.checked ? 1 : 0)
      } else {
        const value = (evt.target.dataset.value != null ? evt.target.dataset.value : evt.target.value)
        this.model.saveSetting(evt.target.dataset.setting, value)
      }
    }
    for (let [input, evt] of settingInputs) {
      Array.from(this.el.querySelectorAll(input)).forEach(c => c.addEventListener(evt, saveSettingValue))
    }
  }

  listenButtonEvents() {
    // various buttons
    this.el.querySelector('.reader-controls-mode-display-fit').addEventListener('click', () => {
      this.model.saveSetting('displayFit', this.model.displayFit % 4 + 1)
    })
    this.el.querySelector('.reader-controls-mode-rendering').addEventListener('click', () => {
      this.model.saveSetting('renderingMode', this.model.renderingMode % 3 + 1)
    })
    this.el.querySelector('.reader-controls-mode-direction').addEventListener('click', () => {
      this.model.saveSetting('direction', this.model.direction % 2 + 1)
    })
    this.el.querySelector('#hide-header-button').addEventListener('click', () => {
      this.model.saveSetting('hideHeader', !this.model.settings.hideHeader ? 1 : 0)
    })
    this.el.querySelector('#recommendations-button').addEventListener('click', () => {
      this.moveToRecommendations()
    })
    this.el.querySelectorAll('.reader-controls-collapser').forEach(n => n.addEventListener('click', () => {
      this.model.saveSetting('hideSidebar', !this.model.settings.hideSidebar ? 1 : 0)
    }))
    // action links
    this.el.addEventListener('click', (evt) => {
      if (!evt.ctrlKey && !evt.metaKey) {
        let target = evt.target
        while (target && !(target.nodeName === 'A' && target.dataset.action)) {
          target = target.parentElement
        }
        if (target) {
          const data = target.dataset
          evt.preventDefault()
          switch (data.action) {
            case 'page':
              if (data.direction && data.direction === 'left') {
                this.turnPageLeft(parseInt(data.by))
              } else if (data.direction && data.direction === 'right') {
                this.turnPageRight(parseInt(data.by))
              } else if (data.to) {
                this.moveToPage(parseInt(data.to))
              }
              break
            case 'chapter':
              return this.moveToChapter(parseInt(data.chapter))
            case 'url':
              return this.exitToURL(target.href)
          }
        }
      }
    })//)
    // preload all
    this.addListener('preload all button', '#preload-all', 'click', (evt) => {
      evt.target.disabled = true
      evt.target.textContent = 'Preloading...'
      this.model.on('pageload', () => {
        const loaded = this.model.getLoadedPages().length
        evt.target.textContent = `Preloading... ${Math.round(loaded / this.model.totalPages * 100)}%`
        if (loaded === this.model.totalPages) {
          evt.target.textContent = `Preloading done`
          return true // unbinds handler
        }
      })
      this.model.preloadEverything()
    })
    // report form submit
    this.addListener('report submit', '#chapter-report-form', 'submit', (evt) => {
      evt.preventDefault()
      const btn = evt.target.querySelector('button[type=submit]')
      btn.classList.add('is-loading')
      const alert = evt.target.querySelector('.alert-container')
      Utils.emptyNode(alert)
      fetch(`/ajax/actions.ajax.php?function=chapter_report&id=${this.model.chapter.id}&server=${encodeURIComponent(this.model.chapter.server)}`, {
        method: 'POST',
        body: new FormData(evt.target),
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).then(res => {
        if (res.ok) {
          return res.text()
        } else {
          throw new Error(res.statusText)
        }
      }).then((res) => {
        alert.innerHTML = res ? res : Renderer.Alert.container("This chapter has been reported.", 'success').outerHTML
        return Promise.resolve()
      }).catch((err) => {
        alert.innerHTML = Renderer.Alert.container("Something weird went wrong. Details in the Console (F12), hopefully.", 'danger').outerHTML
        console.error(err)
        return Promise.resolve()
      }).then(() => {
        btn.classList.remove('is-loading')
      })
    })
    // pagebar
    const notchDisplay = this.el.querySelector('.reader-page-bar .notch-display')
    this.addListener('page bar hover', '.reader-page-bar .notches', 'mouseover', (evt) => {
      if (evt.target.dataset.page) {
        notchDisplay.textContent = `${evt.target.dataset.page} / ${this.model.totalPages}`
      } else {
        notchDisplay.textContent = ''
      }
    })
  }

  listenInputs() {
    // history
    if (Modernizr.history) {
      window.onpopstate = (evt) => {
        if (evt.state != null) {
          if (evt.state.state !== this.model.state) {
            this.model.state = evt.state.state
          }
          if (evt.state.chapter === this.model.chapter.id && evt.state.page !== null) {
            this.moveToPage(evt.state.page, false)
          } else {
            this.moveToChapter(evt.state.chapter, evt.state.page, false, true)
          }
        }
      }
    }
    // track tap
    this.addListener('track tap', '.reader-page-bar .track', 'click', (evt) => {
      evt.stopPropagation()
      const page = parseInt(evt.target.dataset.page || evt.currentTarget.dataset.page)
      if (page) {
        return this.moveToPage(this.isDoublePage ? Math.max(page, 1) : page)
      }
    })
    // window resize
    let resizeDebounce = null
    this.addListener('window resize', window, 'resize', (evt) => {
      clearTimeout(resizeDebounce)
      resizeDebounce = setTimeout(() => this.forceImageLayoutRefresh(), 100)
    })
    // // fullscreen
    // this.addListener('fullscreen', '#fullscreen-button', 'click', (evt) => {
    //   const requestFullscreen = Modernizr.prefixed('requestFullscreen', this.el) || Modernizr.prefixed('requestFullScreen', this.el)
    //   requestFullscreen.call(this.el)
    //   this.el.style.overflowY = 'scroll'
    // })
    // page tap
    const getImageContainerWidth = () => {
      try {
        return this.imageContainer.scrollWidth - parseFloat(window.getComputedStyle(this.imageContainer).paddingRight)
      } catch (err) {
        return this.imageContainer.scrollWidth
      }
    }
    let swiped = false
    this.addListener('page tap', this.imageContainer, 'click', (evt) => {
      evt.preventDefault()
      if (this.model.isLongStrip && this.model.settings.pageTurnLongStrip === 0) {
        return
      }
      if (swiped ||
        (this.model.settings.tapTargetArea !== 1 && evt.target.nodeName.toLowerCase() !== 'img')) {
        swiped = false
        return
      }
      switch (this.model.settings.pageTapTurn) {
        case 1:
          evt.stopPropagation()
          const imgCW = getImageContainerWidth()
          const elW = document.body.clientWidth - document.body.scrollWidth + imgCW
          const isLeft = (evt.clientX < (elW / 2))
          return isLeft ? this.turnPageLeft() : this.turnPageRight()
        case 2:
          evt.stopPropagation()
          return this.turnPageForward()
      }
    })
    // page swipe
    if (jQuery) {
      jQuery(this.imageContainer).swipe({
        swipeRight: (evt) => {
          if (this.model.settings.swipeSensitivity > 0) {
            const inverted = this.model.settings.swipeDirection === 1
            inverted ? this.turnPageRight() : this.turnPageLeft()
            swiped = true
          }
        },
        swipeLeft: (evt) => {
          if (this.model.settings.swipeSensitivity > 0) {
            const inverted = this.model.settings.swipeDirection === 1
            inverted ? this.turnPageLeft() : this.turnPageRight()
            swiped = true
          }
        },
        preventDefaultEvents: false,
        cancelThreshold: 10,
        threshold: this.swipeThreshold
      })
    }
    // page wheel
    let wheelTicks = 0
    const tickThreshold = 6
    this.addListener('page wheel', this.imageContainer, 'wheel', (evt) => {
      if (this.model.settings.pageWheelTurn !== 0) {
        if ((evt.deltaY > 0 && ReaderView.isScrolledToBottom) ||
          (evt.deltaY < 0 && ReaderView.isScrolledToTop)) {
          evt.preventDefault()
          wheelTicks++
          if (!this.model.isLongStrip || (wheelTicks >= tickThreshold)) {
            wheelTicks = 0
            this.turnPage(evt.deltaY > 0)
          }
        } else {
          wheelTicks = 0
        }
      }
    })
    // single page wheel
    this.addListener('single page wheel', '.reader-controls-pages', 'wheel', (evt) => {
      if (this.model.settings.pageWheelTurn !== 0) {
        if (evt.deltaY > 0) {
          this.turnPageForward(1)
        } else {
          this.turnPageBackward(1)
        }
      }
    })
    // chapter dropdown
    this.addListener('jump chapter', '#jump-chapter', 'change', (evt) => {
      const newChapterId = parseInt(evt.target.value)
      if (!this.model.chapter || this.model.chapter.id !== newChapterId) {
        this.moveToChapter(newChapterId, 1)
        evt.target.blur()
      }
    })
    // page dropdown
    this.addListener('jump page', '#jump-page', 'change', (evt) => {
      this.moveToPage(parseInt(evt.target.value))
      evt.target.blur()
    })
    // page prompt
    this.addListener('page prompt', '.reader-controls-page-text', 'click', (evt) => {
      const pg = parseInt(prompt('Move to page number:'))
      this.moveToPage(pg)
    })
    // go to top
    const isOverThreshold = (turn, threshold) => (Math.abs(window.scrollY - turn) > threshold)
    this.addListener('go to top scroll', window, 'scroll', (evt) => {
      const gotoTop = this.el.querySelector('.reader-goto-top')
      const wasScrollDown = (gotoTop.dataset.scroll < window.scrollY)
      if (wasScrollDown === (gotoTop.dataset.turn > window.scrollY)) {
        gotoTop.dataset.turn = gotoTop.dataset.scroll
      }
      if (!wasScrollDown && !gotoTop.classList.contains('show') && isOverThreshold(gotoTop.dataset.turn, gotoTop.dataset.threshold)) {
        gotoTop.classList.add('show')
      } else if (wasScrollDown && gotoTop.classList.contains('show')) {
        gotoTop.classList.remove('show')
      }
      gotoTop.dataset.scroll = window.scrollY
    })
    this.resetGoToTop()
    this.addListener('go to top click', '.reader-goto-top', 'click', () => {
      window.scrollTo(0, 0)
      this.resetGoToTop()
    })
    // hide cursor over images
    this._cursorHideDebounce = null
    this.addListener('hide cursor', '.reader-images', 'mousemove', (evt) => {
      clearTimeout(this._cursorHideDebounce)
      this.el.classList.remove('hide-cursor')
      this._cursorHideDebounce = setTimeout(() => this.el.classList.add('hide-cursor'), 2000)
    })
    this.toggleListener('hide cursor', this.model.settings.hideCursor)
    // kbd shortcuts
    KeyboardShortcuts.registerDefaults()
    document.addEventListener('keydown', (evt) => {
      KeyboardShortcuts.keydownHandler(evt, this)
    })
  }

  resetGoToTop() {
    this.toggleListener('go to top scroll', this.model.isLongStrip)
    const gotoTop = this.el.querySelector('.reader-goto-top')
    gotoTop.dataset.scroll = 0
    gotoTop.dataset.turn = 0
    gotoTop.dataset.threshold = 100
    gotoTop.classList.remove('show')
  }

  static scroll(left = 50, top = 50, behavior = 'auto') {
    Utils.scrollBy({ behavior, left, top })
  }

  static get isTestReader() {
    try { return window.location.href.includes('chapter_test') }
    catch (err) { return false }
  }

  static getRendererClass(mode) {
    const STATE = ReaderModel.renderingModeState
    switch (mode) {
      case STATE.ALERT: return Renderer.Alert
      case STATE.RECS: return Renderer.Recommendations
      case STATE.LONG: return Renderer.LongStrip
      case STATE.DOUBLE: return Renderer.DoublePage
      case STATE.SINGLE:
      default: return Renderer.SinglePage
    }
  }

  static getDisplayFitString(fit) {
    const STATE = ReaderModel.displayFitState
    switch (fit) {
      case STATE.FIT_BOTH: return 'fit-both'
      case STATE.FIT_WIDTH: return 'fit-width'
      case STATE.FIT_HEIGHT: return 'fit-height'
      case STATE.NO_RESIZE: return 'no-resize'
      default: return 'fit-unknown'
    }
  }

  static get isScrolledToLeft() {
    return window.pageXOffset === 0
  }
  static get isScrolledToRight() {
    return (window.innerWidth + Math.ceil(window.pageXOffset + 1)) >= document.body.scrollWidth
  }
  static get isScrolledToTop() {
    return window.pageYOffset === 0
  }
  static get isScrolledToBottom() {
    return (window.innerHeight + Math.ceil(window.pageYOffset + 1)) >= document.body.scrollHeight
  }
}
