import Resource from './Resource'
import Manga from './Manga'
import Group from './Group'

const VOL_SHORT = 'Vol.'
const CH_SHORT = 'Ch.'
const TITLE_ONESHOT = 'Oneshot'
const TITLE_EMPTY = '(Untitled)'

export default class Chapter extends Resource {
  static get resourceType() { return 'chapter' }

  constructor(data = {}, responseCode = -1, responseStatus = null) {
    super(data, responseCode, responseStatus)
    this.id = data.id
    this.hash = data.hash
    this.mangaId = data.mangaId
    this.chapter = data.chapter
    this.volume = data.volume
    this.title = data.title
    this.timestamp = data.timestamp
    this.language = data.language
    this.threadId = data.threadId || null
    this.comments = data.comments
    this.server = data.server
    this.serverFallback = data.serverFallback
    this.groupIds = (data.groups || []).map(g => g.id || g)
    this.groupWebsite = data.groupWebsite
    this.pages = data.pages || []
    this.read = data.read || false
    this.status = data.status
  }

  // derived data
  get textTitle() {
    return this.title || ((this.volume || this.chapter) ? TITLE_EMPTY : TITLE_ONESHOT)
  }
  get numberTitle() {
    return [
      this.volume ? `${VOL_SHORT} ${this.volume}` : '',
      this.chapter ? `${CH_SHORT} ${this.chapter}` : '',
    ].filter(n => n).join(' ')
  }
  get fullTitle() {
    return [
      this.numberTitle,
      this.title,
    ].filter(n => n).join(' ') || TITLE_ONESHOT
  }
  get manga() { return Manga.getResource(this.mangaId) }
  get groups() { return this.groupIds.map(id => Group.getResource(id)) }
  get totalPages() { return !this.isExternal ? this.pages.length : 1 }
  get url() { return `/chapter/${this.id}` }
  get isNotFound() { return this._response.code === 404 }
  get isDeleted() { return this.status === 'deleted' }
  get isDelayed() { return this.status === 'delayed' }
  get isUnavailable() { return this.status === 'unavailable' }
  get isRestricted() { return this.status === 'restricted' }
  get isExternal() { return this.status === 'external' }
  get isFullyLoaded() { return this.hash != null && this.server != null }
  get isUnnumbered() { return !this.volume && !this.chapter }
  get isLastChapter() { return this.manga && this.manga.isLastChapter(this) }
  get nextChapterId() { if (this.manga) return this.manga.getNextChapterId(this.id) }
  get prevChapterId() { if (this.manga) return this.manga.getPrevChapterId(this.id) }
  get nextChapter() { return Chapter.getResource(this.nextChapterId) }
  get prevChapter() { return Chapter.getResource(this.prevChapterId) }
  get isNetworkServer() {
    if (this._isNetworkServer == null) {
      this._isNetworkServer = /mangadex\.network/.test(this.server || '')
    }
    return this._isNetworkServer
  }

  // methods
  getPage(i) {
    return (i >= 1 && i <= this.totalPages && !this.isExternal) ? this.pages[i - 1] : ''
  }
  getPageUrl(i) {
    return this.server + this.hash + '/' + this.getPage(i)
  }
  getAllPageUrls() {
    function serverOrUndefined(server, hash, pg) {
      if (!server) {
        return server;
      }
      return server + hash + '/' + pg;
    }
    return !this.isExternal ? this.pages.map(pg => [serverOrUndefined(this.server, this.hash, pg), serverOrUndefined(this.serverFallback, this.hash, pg)]) : []
  }

  isAlternativeOf(ch) {
    return ch.chapter == this.chapter
      && (ch.volume == this.volume || ch.volume == '' || this.volume == '')
      && (!this.isUnnumbered || ch.title == this.title)
  }

  isSequentialWith(chapter) {
    if (!chapter) {
      return undefined
    } else if (typeof chapter == 'number') {
      chapter = Chapter.getResource(chapter)
    }
    const ch1 = parseFloat(this.chapter)
    const ch2 = parseFloat(chapter.chapter)
    const vol1 = parseInt(this.volume) || 0
    const vol2 = parseInt(chapter.volume) || 0
    if (isNaN(ch1) || isNaN(ch2) || this.isUnnumbered || chapter.isUnnumbered) {
      return undefined
    } else if (this.isAlternativeOf(chapter)) {
      return undefined
    } else if (Math.abs(ch1 - ch2).toFixed(1) <= 1.1) {
      return true
    } else if ((ch1 === 1 && vol1 === vol2 + 1) || (ch2 === 1 && vol2 === vol1 + 1)) {
      return true
    } else {
      return false
    }
  }

  hasSameGroupsWith(chapter) {
    if (this === chapter) {
      return true
    } else if (this.groups.length !== chapter.groups.length) {
      return false
    }
    for (let i = 0; i < this.groups.length; ++i) {
      if (this.groups[i].id !== chapter.groups[i].id) {
        return false
      }
    }
    return true
  }

  static async load(id, params = {}, cache = true) {
    const json = await super.load(`chapter/${id}`, params)
    try {
      json.data.groups.forEach(g => Group.fromJSON(g))
      return this.fromJSON(json.data, json.code, json.status, cache)
    } catch (error) {
      console.error(error)
      throw this.fromJSON({ id, status: json.message }, json.code, json.status, false)
    }
  }

  static async loadWithManga(id, params = {}, cache = true) {
    const json = await super.load(`chapter/${id}`, Object.assign(params, { include: 'manga' }))
    try {
      json.data.chapter.groups.forEach(g => Group.fromJSON(g))
      const chapter = this.fromJSON(json.data.chapter, json.code, json.status, cache)
      Manga.fromJSON(json.data.manga, json.code, json.status, cache)
      return chapter
    } catch (error) {
      console.error(error)
      throw this.fromJSON({ id, status: json.message }, json.code, json.status, false)
    }
  }

}
