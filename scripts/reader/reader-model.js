import EventEmitter from 'wolfy87-eventemitter'
import Chapter from './resource/Chapter'
import Manga from './resource/Manga'
import Follows from './resource/Follows'
import Utils from './utils.js'
import ReaderPageModel from './ReaderPageModel'
import ReaderSetting from './ReaderSetting'

export default class ReaderModel extends EventEmitter {
  constructor() {
    super()
    this._state = ReaderModel.readerState.READING
    this._isLoading = false
    this._currentPage = 0
    this._chapter = null
    this._renderingMode = 0
    this._displayFit = 0
    this._direction = 0
    this._appMeta = {}
    this._settings = {}
    this._settingsShortcut = {}
    this._pageCache = new Map()
    this._preloadSet = new Set()
  }

  get appMeta() { return this._appMeta }
  set appMeta(val) {
    this._appMeta = val
  }

  get isUserGuest() { return this.appMeta.guest !== '0' }

  get isLoading() { return this._isLoading }
  set isLoading(val) {
    val = !!val
    if (this._isLoading !== val && !this.exiting) {
      this._isLoading = val
      this.trigger('loadingchange', [val])
    }
  }

  get currentPage() { return this._currentPage }
  setCurrentPage(val) {
    if (this._currentPage !== val && !isNaN(val) && !this.exiting) {
      this._currentPage = val
      this.trigger('currentpagechange', [val])
      return true
    }
  }

  get totalPages() { return this.chapter ? this.chapter.totalPages : 0 }

  get state() { return this._state }
  set state(val) {
    if (this._state !== val && !this.exiting) {
      this._state = val
      this.trigger('statechange', [val])
    }
  }

  get isStateReading() { return this._state === ReaderModel.readerState.READING }
  get isStateRecommendations() { return this._state === ReaderModel.readerState.RECS }
  get isStateGap() { return this._state === ReaderModel.readerState.GAP }

  get exiting() { return this._state === ReaderModel.readerState.EXITING }

  exitReader() {
    this.state = ReaderModel.readerState.EXITING
    // bfcache
    window.onunload = () => { this.state = ReaderModel.readerState.READING }
  }

  get renderingMode() { return this._renderingMode }
  set renderingMode(val) {
    if (this._renderingMode !== val && !this.exiting) {
      this._renderingMode = val
      this.trigger('renderingmodechange', [val])
    }
  }

  get displayFit() { return this._displayFit }
  set displayFit(val) {
    if (this._displayFit !== val && !this.exiting) {
      this._displayFit = val
      this.trigger('displayfitchange', [val])
    }
  }

  get direction() { return this._direction }
  set direction(val) {
    if (this._direction !== val && !this.exiting) {
      this._direction = val
      this.trigger('directionchange', [val])
    }
  }

  get isSinglePage() { return this.renderingMode === ReaderModel.renderingModeState.SINGLE }
  get isDoublePage() { return this.renderingMode === ReaderModel.renderingModeState.DOUBLE }
  get isLongStrip() { return this.renderingMode === ReaderModel.renderingModeState.LONG }
  get isNoResize() { return this.displayFit === ReaderModel.displayFitState.NO_RESIZE }
  get isFitHeight() { return this.displayFit === ReaderModel.displayFitState.FIT_HEIGHT }
  get isFitWidth() { return this.displayFit === ReaderModel.displayFitState.FIT_WIDTH }
  get isFitBoth() { return this.displayFit === ReaderModel.displayFitState.FIT_BOTH }
  get isDirectionLTR() { return this.direction === ReaderModel.directionState.LTR }
  get isDirectionRTL() { return this.direction === ReaderModel.directionState.RTL }

  get settings() { return this._settingsShortcut }
  get settingDefaults() {
    return Array.from(Object.values(this._settings)).reduce((acc, setting) => {
      acc[setting.name] = setting.default
      return acc
    }, {})
  }

  saveSetting(key, value) {
    const setting = this._settings[key]
    setting.save(value)
    this._settingsShortcut[setting.name] = setting.value
    if (['renderingMode', 'displayFit', 'direction'].includes(key)) {
      this[key] = setting.value
    }
    this.trigger('settingchange', [setting.name, setting.value])
  }

  loadSettings() {
    const defaults = [
      new ReaderSetting('displayFit', ReaderModel.displayFitState.FIT_WIDTH),
      new ReaderSetting('direction', ReaderModel.directionState.LTR),
      new ReaderSetting('renderingMode', ReaderModel.renderingModeState.SINGLE),
      new ReaderSetting('showAdvancedSettings', 0),
      new ReaderSetting('scrollingMethod', 0),
      new ReaderSetting('swipeDirection', 0),
      new ReaderSetting('swipeSensitivity', 3),
      new ReaderSetting('pageTapTurn', 1),
      new ReaderSetting('pageTurnLongStrip', 1),
      new ReaderSetting('pageWheelTurn', 0),
      new ReaderSetting('showDropdownTitles', 1),
      new ReaderSetting('tapTargetArea', 1),
      new ReaderSetting('hideHeader', 0),
      new ReaderSetting('hideSidebar', 0),
      new ReaderSetting('hidePagebar', 0),
      new ReaderSetting('collapserStyle', 0),
      new ReaderSetting('hideCursor', 0),
      new ReaderSetting('betaRecommendations', 0),
      new ReaderSetting('imageServer', '0'),
      new ReaderSetting('dataSaverV2', 0),
      new ReaderSetting('gapWarning', 1),
      new ReaderSetting('restrictChLang', 1, null, (val) => {
        val = parseInt(val)
        if (this.chapter && this.manga) {
          this.manga.updateChapterList(this.chapter, val)
          this.trigger('chapterlistchange', [])
        }
        return val
      }),
      new ReaderSetting('preloadPages', 10, () => true, (val) => {
        if (isNaN(parseInt(val)))
          return 10
        const clamped = Utils.clamp(parseInt(val), 0, this.preloadMax)
        return !isNaN(clamped) ? clamped : 0
      }),
      new ReaderSetting('containerWidth', null, (val) => {
        return !val || !isNaN(parseInt(val))
      }),
    ]
    for (let setting of defaults) {
      setting.load()
      this._settings[setting.name] = setting
      this._settingsShortcut[setting.name] = setting.value
      this.trigger('settingchange', [setting.name, setting.value])
    }

    this.saveSetting('gapWarning', 1)

    ReaderSetting.clearAllExcept(defaults.map(s => s.name))
  }

  get chapter() { return this._chapter }

  get manga() { return this._chapter ? this._chapter.manga : null }

  getChapterParams() {
    return {
      server: this.settings.imageServer !== '0' ? this.settings.imageServer : null,
      saver: this.settings.dataSaverV2,
    }
  }

  async setChapter(id, pg = 1) {
    if (this.exiting || this.isLoading) {
      throw new Error(`Trying to set chapter while ${this.exiting ? 'exiting' : 'loading'}`)
    } else if (isNaN(id) || id <= 0) {
      throw new Error("Trying to set invalid chapter: " + id)
    }

    try {
      this.isLoading = true
      let chapter = Chapter.getResource(id)
      if (chapter && !chapter.isFullyLoaded) {
        chapter = await Chapter.load(id, this.getChapterParams())
      } else if (!chapter) {
        chapter = await Chapter.loadWithManga(id, this.getChapterParams())
        await Manga.loadChapterList(chapter.manga.id)
      }
      const oldManga = this.manga
      this._chapter = chapter
      this._currentPage = pg
      this.isLoading = false
      chapter.manga.updateChapterList(chapter, this.settings.restrictChLang)
      this._createPageCache(chapter)
      this.state = ReaderModel.readerState.READING
      this.trigger('chapterchange', [chapter])
      if (!oldManga || oldManga.id !== chapter.manga.id) {
        this.trigger('mangachange', [chapter.manga])
      }
      if (chapter.error || chapter.isExternal) {
        throw chapter
      } else if (chapter.totalPages === 0) {
        chapter.error = new Error("Chapter has no pages.")
        throw chapter
      }
      if (chapter.isNetworkServer) {
        this.preload(this.currentPage, Infinity)
      }
      return chapter
    } catch (error) {
      console.error(error)
      this.isLoading = false
      this.state = ReaderModel.readerState.ERROR
      this.trigger('readererror', [error])
      return error
    }
  }

  async reloadChapterList() {
    if (this.manga.isChapterListOutdated) {
      this.isLoading = true
      try {
        await Manga.loadChapterList(this.manga.id)
      } catch (error) { }
      this.isLoading = false
    }
  }

  _createPageCache(chapter) {
    for (let [i, page] of this._pageCache) {
      page.unload()
      page.off()
    }
    this._pageCache.clear()
    this._preloadSet.clear()
    this._preloading = false
    let pgNum = 1
    for (let [url, fallbackURL] of chapter.getAllPageUrls()) {
      const page = new ReaderPageModel(pgNum, chapter.id, url, fallbackURL)
      this._pageCache.set(pgNum, page)
      page.on('statechange', (page) => {
        switch (page.state) {
          case ReaderPageModel.STATE_LOADING: return this.trigger('pageloading', [page])
          case ReaderPageModel.STATE_LOADED: return this.trigger('pageload', [page])
          case ReaderPageModel.STATE_ERROR: return this.trigger('pageerror', [page])
        }
      })
      ++pgNum
    }
  }

  _loadPage(pg, skipCache = false) {
    const page = this._pageCache.get(pg)
    if (!page) {
      return Promise.reject(new Error(`Page ${pg} not in cache`))
    } else {
      return page.load(skipCache)
    }
  }

  getPage(pg) {
    return new Promise((resolve, reject) => {
      if (this.chapter == null || this._pageCache.size === 0) {
        return reject(new Error("Tried to get a page before chapter has loaded"))
      } else if (isNaN(pg) || pg < 1 || pg > this.totalPages) {
        return resolve(null)
        // return reject(new Error("Page not a number or out of bounds"))
      } else if (this._pageCache.get(pg).loaded) {
        return resolve(this._pageCache.get(pg))
      } else {
        return resolve(this._loadPage(pg))
      }
    })
  }

  reloadErrorPages() {
    Array.from(this._pageCache.values()).filter(i => i.hasError).forEach(i => i.reload())
  }

  getPageWithoutLoading(pg) {
    if (!this._pageCache.has(pg)) {
      //throw new Error(`No page ${pg} set.`)
      return null
    }
    return this._pageCache.get(pg)
  }

  get currentPageObject() { return this.getPageWithoutLoading(this.currentPage) }

  get isPageCacheEmpty() { return this._pageCache.size === 0 }

  getAllPages() {
    return Array.from(this._pageCache.values())
  }

  getLoadedPages() {
    return this.getAllPages().filter(i => i.loaded || i.hasError)
  }

  _preloadNextInSet() {
    if (this._preloadSet.size > 0) {
      this._preloading = true
      const pg = [...this._preloadSet][0]
      this._preloadSet.delete(pg)
      this._loadPage(pg)
        .catch((page) => { /*console.warn(`Preload failed for page ${pg}`)*/ })
        .then(() => this._preloadNextInSet())
    } else {
      this._preloading = false
    }
  }

  _preloadArray(pages) {
    if (pages.length > 0) {
      pages
        .filter(pg => !this.getPageWithoutLoading(pg).loaded)
        .forEach(pg => this._preloadSet.add(pg))
      if (!this._preloading) {
        return this._preloadNextInSet()
      }
    }
  }

  get preloadMax() {
    return this.isUserGuest ? PRELOAD_MAX_GUEST : PRELOAD_MAX_USER
  }

  // TODO: preload backwards iff [current, end] already loaded
  preload(start = this.currentPage + 1, amount = this.settings.preloadPages) {
    if (this.isPageCacheEmpty) {
      return
    }
    if (amount == null) {
      amount = 10
    }
    if (amount !== Infinity) {
      amount = Utils.clamp(amount, 0, this.chapter.isNetworkServer ? this.totalPages : this.preloadMax)
    }
    start = Utils.clamp(start, 1, this.totalPages + 1)
    const end = Utils.clamp(start + amount, 1, this.totalPages + 1)
    return this._preloadArray(Utils.range(start, end))
  }

  preloadEverything() {
    return this.preload(1, Infinity) // lol
  }

  moveToPage(pg) {
    if (!this.chapter || isNaN(pg)) {
      return Promise.resolve()
    } else if (pg <= -1) {
      return this.moveToPage(this.totalPages)
    } else if (pg === 0) {
      return Promise.reject({ chapter: this.chapter.prevChapterId, page: -1 })
      // return this.moveToChapter(this.chapter.prevChapterId, -1)
    } else if (pg > this.totalPages) {
      return Promise.reject({ chapter: this.chapter.nextChapterId, page: 1 })
      // return this.moveToChapter(this.chapter.nextChapterId, 1)
    } else {
      if (this.currentPage === pg) {
        this.trigger('currentpagechange', [pg])
      } else {
        this.setCurrentPage(pg)
      }
      return Promise.resolve()
    }
  }

  moveToChapter(id, pg = 1) {
    if (id <= 0) {
      return Promise.reject({ chapter: id })
    } else {
      return this.setChapter(id, pg).then(() => {
        if (this.chapter && this.totalPages > 0 && this.isStateReading) {
          return this.moveToPage(pg)
        } else {
          return Promise.resolve()
        }
      })
    }
  }

  moveToRecommendations() {
    this.isLoading = true
    return Follows.load({}, false)
      .then(recs => {
        this.recommendations = recs
        this.isLoading = false
        this.state = ReaderModel.readerState.RECS
        return Promise.resolve(recs)
      })
      .catch(err => {
        this.recommendations = null
        this.isLoading = false
        this.state = ReaderModel.readerState.ERROR
        this.trigger('readererror', [err])
        return Promise.reject(err)
      })
  }

}

const PRELOAD_MAX_USER = 20
const PRELOAD_MAX_GUEST = 5

ReaderModel.renderingModeState = {
  SINGLE: 1,
  DOUBLE: 2,
  LONG: 3,
  ALERT: 4,
  RECS: 5,
}
ReaderModel.directionState = {
  LTR: 1,
  RTL: 2,
}
ReaderModel.displayFitState = {
  FIT_BOTH: 1,
  FIT_WIDTH: 2,
  FIT_HEIGHT: 3,
  NO_RESIZE: 4,
}
ReaderModel.readerState = {
  ERROR: 0,
  READING: 1,
  RECS: 2,
  EXITING: 3,
  GAP: 4,
}
