import Utils from './utils.js'
import natsort from 'natsort'

export default class Resource {
  get resourceType() { return 'resource' }
  get resourceFormat() { return 'json' }

  constructor(data = {}) {
    this.initialize(data)
  }

  initialize(data = {}) {
    this._data = data
  }

  get status() { return this._data.status }

  load(opts = {}, force = false) {
    const id = opts.id != null ? opts.id : ''
    const type = this.resourceType
    return new Promise((resolve, reject) => {
      opts.type = type

      if (!(type in Resource.cache)) {
        Resource.cache[type] = {}
      }
      if (!force && id in Resource.cache[type]) {
        return resolve(Resource.cache[type][id])
      }
      const baseURL = opts.baseURL || window.location
      delete opts.baseURL
      const url = new URL(this.constructor.API_URL(), baseURL)
      for (let key in opts) {
        url.searchParams.append(key, opts[key])
      }
      return resolve(fetch(url, {
        credentials: 'same-origin',
      }).catch(err => {
        console.error(err)
        this.response = err
        this._data.message = err.message
        throw err
      }).then(res => {
        this.response = res
        if (!res.ok) {
          console.error('Fetch not ok:', type, id, res)
          return { id }
        }
        if (type === 'follows') {
          return res.text()
        } else {
          return res.json().catch(err => {
            console.error('JSON parsing error:', err)
            return { id }
          })
        }
      }).then(json => {
        Resource.cache[type][id] = this
        this.initialize(json)
        if (!this.response.ok) {
          console.error('Response status:', this.response.status, this.response.statusText)
          console.error('Resource status:', json.status)
        }
        return this
      }))
    }).catch(err => {
      console.error('Error:', err)
      throw err
    })
  }

  static create(opts, force) {
    const resource = new this()
    return resource.load(opts, force)
  }
}
Resource.cache = {}

var xx = 0
export class Manga extends Resource {
  get resourceType() {
    return xx++ ? 'manga' : 'mangaa'
  }
  // static API_URL(id = '') { return `/api/manga/${id}` }
  static API_URL() { return `/api/` }

  initialize(data) {
    super.initialize(data.manga)
    this.chapters = Object.entries(data.chapter || {}).map(([id, ch]) => { ch.id = parseInt(id); return ch })
    this.chapterList = []
  }

  get id() { return this._data.id }
  get title() { return this._data.title || '' }
  get langCode() { return this._data.lang_flag }
  get langName() { return this._data.lang_name }
  get lastChapter() { return this._data.last_chapter }
  get isLongStrip() { return this._data.genres && this._data.genres.includes(36) }
  get isDoujinshi() { return this._data.genres && this._data.genres.includes(7) }
  get isHentai() { return !!this._data.hentai }
  get url() {
    const title = this.title.toLowerCase().replace(/&[0-9a-z]+;/gi, '').replace(/[^0-9a-z]/gi, ' ').split(' ').filter(t => t).join('-')
    return `/title/${this.id}/${title}`
  }
  get coverUrl() { return `/images/manga/${this.id}.jpg` }
  get coverThumbUrl() { return `/images/manga/${this.id}.thumb.jpg` }

  getChapterData(id) {
    return this.chapters.find(c => c.id === id)
  }

  getChapterTitle(id, noTitle = false) {
    const ch = this.getChapterData(id)
    if (!ch) {
      return ''
    } else {
      let title = ''
      if (ch.volume)
        title += `Vol. ${ch.volume} `
      if (ch.chapter)
        title += `Ch. ${ch.chapter} `
      if (ch.title && !noTitle)
        title += `${ch.title}`
      if (!title)
        title = 'Oneshot'
      return title.trim()
    }
  }

  getChapterName(id) {
    const ch = this.getChapterData(id)
    if (!ch) {
      return ''
    } else {
      if (ch.title)
        return ch.title
      if (ch.volume && ch.chapter)
        return `Vol. ${ch.volume} Ch. ${ch.chapter}`
      if (ch.chapter)
        return `Ch. ${ch.chapter}`
      if (ch.volume)
        return `Vol. ${ch.volume}`
      return 'Oneshot'
    }
  }

  makeChapterList(lang, [g1 = 0, g2 = 0, g3 = 0]) {
    this.chapterList = []
    const sameLang = this.chapters.filter(c => c.lang_code === lang)
    Manga.sortChapters(sameLang)
    let best = null
    for (let ch of sameLang) {
      if (!best) {
        best = ch
      } else {
        if (!ch.chapter && (!ch.volume || ch.volume === "0") || (best.chapter !== ch.chapter || best.volume !== ch.volume)) {
          this.chapterList.push(best)
          best = ch
        } else if (ch.group_id === g1 && ch.group_id_2 === g2 && ch.group_id_3 === g3) {
          best = ch
        }
      }
    }
    if (best) {
      this.chapterList.push(best)
    }
    return this.chapterList
  }

  getAltChapters(id) {
    const cur = this.getChapterData(id)
    if (!cur) {
      return []
    } else {
      const isNonNumbered = (cur.volume === "" || cur.volume === "0") && cur.chapter === ""
      return this.chapters
        .filter(c =>
          c.lang_code === cur.lang_code
          && c.volume === cur.volume && c.chapter === cur.chapter
          && (!isNonNumbered || cur.title === c.title)
        ).map(c => new Chapter(c))
    }
  }

  getPrevChapterId(id) {
    const index = this.chapterList.findIndex(c => c.id === id)
    if (index <= 0) {
      return -1
    } else {
      return this.chapterList[index - 1].id
    }
  }

  getNextChapterId(id) {
    const index = this.chapterList.findIndex(c => c.id === id)
    if (index === -1 || index === this.chapterList.length - 1) {
      return 0
    } else {
      return this.chapterList[index + 1].id
    }
  }

  areChaptersSequential(id1, id2) {
    const c1 = this.getChapterData(id1)
    const c2 = this.getChapterData(id2)
    if (!c1 || !c2) {
      return true
    }
    const c1Chapter = parseFloat(c1.chapter)
    const c2Chapter = parseFloat(c2.chapter)
    const c1Volume = parseFloat(c1.volume)
    const c2Volume = parseFloat(c2.volume)
    if (isNaN(c1Chapter) || isNaN(c2Chapter)) {
      return true
    } else if (c1Chapter === c2Chapter && c1Volume === c2Volume) {
      return true
    } else if (Math.abs(c1Chapter - c2Chapter).toFixed(1) <= 1.1) {
      return true
    } else if ((c1Chapter <= 1 && Math.floor(c1Volume - c2Volume) <= 1) || (c2Chapter <= 1 && Math.floor(c2Volume - c1Volume) <= 1)) {
      return true
    } else {
      return false
    }
  }

  /*areChaptersSequential(c1Chapter, c1Volume, c2Chapter, c2Volume) {
    c1Chapter = parseFloat(c1Chapter)
    c2Chapter = parseFloat(c2Chapter)
    c1Volume = parseFloat(c1Volume)
    c2Volume = parseFloat(c2Volume)
    if (isNaN(c1Chapter) || isNaN(c2Chapter)) {
      return true
    } else if (Math.abs(c1Chapter - c2Chapter).toFixed(1) <= 1.1) {
      return true
    } else if ((c1Chapter === 1 || c2Chapter === 1) && Math.abs(c1Volume - c2Volume) <= 1) {
      return true
    }
    return false
  }*/

  static sortChapters(chapters) {
    const sorter = natsort({ desc: false, insensitive: true })
    // sort by volume desc, so that vol null > vol number where ch are equal
    Utils.stableSort(chapters, (a, b) => sorter(b.volume, a.volume))
    // sort by first group
    Utils.stableSort(chapters, (a, b) => sorter(a.group_id, b.group_id))
    // sort by chapter number
    Utils.stableSort(chapters, (a, b) => sorter(a.chapter, b.chapter))
    // add ghost prev vol numbers
    let pv = '0'
    chapters.forEach(c => {
      c.__prev_vol = pv
      if (c.volume) {
        pv = c.volume
      }
    })
    // sort by vol or prev vol
    Utils.stableSort(chapters, (a, b) => sorter(a.volume || a.__prev_vol, b.volume || b.__prev_vol))
    // remove ghost vols
    chapters.forEach(c => { delete c.__prev_vol })
  }

  static create(opts, force) {
    return super.create(opts, force).then(manga => {
      manga._data.id = opts.id
      return manga
    })
  }
}

export class Chapter extends Resource {
  get resourceType() { return 'chapter' }
  // static API_URL(id = '') { return `/api/chapter/${id}` }
  static API_URL() { return `/api/` }

  get id() { return this._data.id }
  get mangaId() { return this._data.manga_id }
  get title() { return this._data.title }
  get chapter() { return this._data.chapter }
  get volume() { return this._data.volume }
  get comments() { return this._data.comments }
  get isLastChapter() { return this.manga && this.manga.lastChapter && this.manga.lastChapter !== "0" && this.manga.lastChapter === this.chapter }
  get langCode() { return this._data.lang_code }
  get langName() { return this._data.lang_name }
  get totalPages() { return this._data.page_array ? this._data.page_array.length : 0 }
  get groupIds() { return [this._data.group_id, this._data.group_id_2, this._data.group_id_3].filter(n => n) }
  get groupNames() { return [this._data.group_name, this._data.group_name_2, this._data.group_name_3].filter(n => n) }
  get groupWebsite() { return this._data.group_website }
  get timestamp() { return this._data.timestamp }
  get prevChapterId() { return this.manga.getPrevChapterId(this.id) }
  get nextChapterId() { return this.manga.getNextChapterId(this.id) }
  get url() { return `/chapter/${this.id}` }
  get externalUrl() { return this._data.external || '' }
  get fullTitle() {
    let title = ''
    if (this.volume) title += `Vol. ${this.volume} `
    if (this.chapter) title += `Ch. ${this.chapter} `
    if (this.title) title += `${this.title}`
    if (!title) title = 'Oneshot'
    return title.trim()
  }
  get isMangaFailed() { try { return !this.manga.response.ok } catch (err) { return true } }
  get isNotFound() { try { return this.response.status == 404 } catch (err) { return false } }
  get isDelayed() { try { return this.response.status == 409 } catch (err) { return false } }
  get isDeleted() { try { return this.response.status == 410 } catch (err) { return false } }
  get isRestricted() { try { return this.response.status == 451 } catch (err) { return false } }
  get isExternal() { return this._data.status === 'external' }
  get message() { return this._data.message }
  get isNetworkServer() {
    if (!this._isNetworkServer) {
      this._isNetworkServer = /mangadex\.network/.test(this._data.server || '')
    }
    return this._isNetworkServer
  }

  get pageArray() { return this._data.page_array || [] }
  getPage(pg) {
    return pg >= 1 && pg <= this.totalPages ? this._data.page_array[pg - 1] : ''
  }
  get pagesFullURL() { return this.pageArray.map((pg, i) => this.imageURL(i + 1)) }

  imageURL(pg) {
    return this._data.server + this._data.hash + '/' + this.getPage(pg)
  }

  makeMangaChapterList() {
    this.manga.makeChapterList(this.langCode, this.groupIds)
  }

  loadManga(force) {
    if (!this._data.manga_id) {
      console.warn('No manga id for chapter', this.id)
      return Promise.resolve(this)
    }
    return Manga.create({ id: this._data.manga_id }, force).then(manga => {
      this.manga = manga
      if (!manga.response.ok) {
        return Promise.reject(this)
      } else {
        this.makeMangaChapterList()
        return Promise.resolve(this)
      }
    })
  }

  static create(opts, force) {
    return super.create(opts, force)
      .catch(ch => ch.mangaId ? Promise.resolve(ch) : Promise.reject(ch))
      .then(ch => {
        if (ch._data.page_array) {
          ch._data.page_array = ch._data.page_array.filter(p => !!p)
        }
        return Promise.resolve(ch)
      })
      .then(ch => ch.loadManga(force))
      .catch(ch => ch.manga && !ch.isMangaFailed ? Promise.resolve(ch) : Promise.reject(ch))
  }
}

export class Follows extends Resource {
  get resourceType() { return 'follows' }
  get resourceFormat() { return 'text' }
  static API_URL() { return '/follows/' }

  get unreadChapters() {
    return this.chapters.filter(c => c.id && !c._data.read)
  }

  get unreadManga() {
    return this.unreadChapters.reduce((acc, cur) => {
      acc[cur.manga.id] = cur.manga
      return acc
    }, {})
  }

  static create(opts, force) {
    return super.create(opts, force).then(follows => {
      const rows = follows._data.match(/col-md-3 [\s\S]*?chapter-list-group/gim)
      if (!rows) {
        return []
      }
      const mangaCache = {}
      let mangaTitle = ''
      follows.chapters = rows.map(row => {
        const none = ['', '']
        mangaTitle = (row.match(/manga_title[\s\S]*?title='([\s\S]*?)'/) || none)[1].trim() || ''
        if (mangaTitle) { console.log(mangaTitle) }
        const mangaId = parseInt((row.match(/data-manga-id="(\d*?)"/) || none)[1])
        if (!(mangaId in mangaCache)) {
          mangaCache[mangaId] = new Manga()
          mangaCache[mangaId].initialize({
            manga: {
              id: mangaId || 0,
              title: mangaTitle,
            }
          })
        }
        const manga = mangaCache[mangaId]
        const chapter = new Chapter()
        chapter.initialize({
          id: parseInt((row.match(/data-id="(\d*?)"/) || none)[1]) || null,
          title: (row.match(/data-title="([\s\S]*?)"/) || none)[1],
          chapter: parseFloat((row.match(/data-chapter="([\d\.]*?)"/) || none)[1]) || null,
          volume: parseFloat((row.match(/data-volume="([\d\.]*?)"/) || none)[1]) || null,
          timestamp: parseInt((row.match(/data-timestamp="(.*?)"/) || none)[1]) * 1000 || null,
          lang_code: (row.match(/flag-(..)/) || none)[1],
          read: /chapter_mark_unread_button/.test(row),
        })
        chapter.manga = manga
        manga.chapters.push(chapter)
        return chapter
      })
      return follows
    })
  }
}

// export default { Manga, Chapter, Follows }