import { differenceInHours } from 'date-fns'
import Resource from './Resource'
import Chapter from './Chapter'
import Group from './Group'
import Utils from '../utils'
import natsort from 'natsort'


export default class Manga extends Resource {
  static get resourceType() { return 'manga' }

  constructor(data = {}, responseCode = -1, responseStatus = null) {
    super(data, responseCode, responseStatus)
    this.id = data.id
    this.title = data.title
    this.language = data.publication ? data.publication.language : null
    this.isHentai = data.isHentai
    this.lastChapter = data.lastChapter || ''
    this.lastVolume = data.lastVolume || ''
    this.mainCover = data.mainCover
    this.tags = data.tags || []
    this.links = data.links || {}
    this._uniqueChapters = []
    this._chapters = []
    this.chapterListLastLoaded = null
  }

  // derived data
  get isLongStrip() { return this.tags.indexOf(36) !== -1 }
  get isDoujinshi() { return this.tags.indexOf(7) !== -1 }
  get url() {
    const title = this.title.toLowerCase().replace(/&[0-9a-z]+;/gi, '').replace(/[^0-9a-z]/gi, ' ').split(' ').filter(t => t).join('-')
    return `/title/${this.id}/${title}`
  }
  get coverThumb() { return `/images/manga/${this.id}.thumb.jpg` }
  get isChapterListOutdated() { return differenceInHours(this.chapterListLastLoaded, new Date()) >= 1 }

  // methods
  isLastChapter(chapter) {
    return this.lastChapter && (chapter.chapter === this.lastChapter) && (chapter.volume == this.lastVolume)
  }

  getChapter(id) {
    return Chapter.getResource(id)
  }

  get chapterList() {
    return this._chapters
  }

  get uniqueChapterList() {
    return this._uniqueChapters
  }

  updateChapterList(baseChapter, restrictChLang = false) {
    let chapters = Chapter.findResources(c => c.mangaId === this.id)
    if (restrictChLang) {
      chapters = chapters.filter(c => c.language === baseChapter.language)
    }
    const chapterIds = Manga.createSortedChapterIdList(chapters)
    this._chapters = chapterIds.map(id => Chapter.getResource(id))
    this._uniqueChapters = Manga.createUniqueChapterList(baseChapter, this._chapters)
  }

  getNextChapterId(id) {
    const index = this._uniqueChapters.map(c => c.id).indexOf(id)
    return (index >= 0 && index + 1 < this._uniqueChapters.length) ? this._uniqueChapters[index + 1].id : 0
  }

  getPrevChapterId(id) {
    const index = this._uniqueChapters.map(c => c.id).indexOf(id)
    return (index > 0) ? this._uniqueChapters[index - 1].id : -1
  }

  getAltChapters(id) {
    const cur = Chapter.getResource(id)
    return cur ? this.chapterList.filter(ch => cur.isAlternativeOf(ch)) : []
  }

  static createSortedChapterIdList(chapters) {
    chapters = chapters.map(ch => {
      return !ch ? null : {
        id: ch.id,
        timestamp: ch.timestamp,
        chapter: ch.chapter,
        volume: ch.volume,
        group: ch.groupIds[0],
      }
    }).filter(c => c)
    const sorter = natsort({ asc: true, insensitive: true })
    // sort by timestamp desc
    Utils.stableSort(chapters, (a, b) => sorter(b.timestamp, a.timestamp))
    // sort by volume desc, so that vol null > vol number where ch are equal
    Utils.stableSort(chapters, (a, b) => sorter(b.volume, a.volume))
    // sort by group
    Utils.stableSort(chapters, (a, b) => sorter(a.group, b.group))
    // sort by chapter number
    Utils.stableSort(chapters, (a, b) => sorter(a.chapter, b.chapter))
    // add "ghost" prev vol numbers
    let pv = '0'
    for (let c of chapters) {
      c.prevVolume = pv
      pv = c.volume ? c.volume : pv
    }
    // sort by vol or prev vol
    Utils.stableSort(chapters, (a, b) => sorter(a.volume || a.prevVolume, b.volume || b.prevVolume))
    return chapters.map(c => c.id)
  }

  static createUniqueChapterList(baseChapter, chapters) {
    const list = []
    if (chapters.length > 0) {
      let best = chapters[0]
      for (let ch of chapters.slice(1)) {
        if (!ch.isAlternativeOf(best)) {
          list.push(best)
          best = ch
        } else if (best !== baseChapter && ch.hasSameGroupsWith(baseChapter)) {
          best = ch
        }
      }
      list.push(best)
    }
    return list
  }

  static async load(id, params = {}, cache = true) {
    const json = await super.load(`manga/${id}`, params)
    try {
      return this.fromJSON(json.data, json.code, json.status, cache)
    } catch (error) {
      throw this.fromJSON({ id, status: json.message }, json.code, json.status, false)
    }
  }

  static async loadChapterList(id, params = {}, cache = true) {
    const json = await super.load(`manga/${id}/chapters`, params)
    try {
      const { chapters } = this.fromChapterListJSON(id, json, cache)
      return chapters
    } catch (error) {
      return []
    }
  }

  static async loadWithChapterList(id, params = {}, cache = true) {
    const json = await super.load(`manga/${id}`, Object.assign(params, { include: 'chapters' }))
    try {
      const { manga } = this.fromChapterListJSON(id, json, cache)
      return manga
    } catch (error) {
      throw this.fromJSON({ id, status: json.message }, json.code, json.status, false)
    }
  }

  static fromChapterListJSON(id, json, cache) {
    // avoid overwriting group/chapter resources that already exist because these are going to be incomplete
    const groups = json.data.groups.map(g => Group.getResource(g.id) || Group.fromJSON(g))
    const chapters = json.data.chapters.map(c => Chapter.getResource(c.id) || Chapter.fromJSON(c, json.code, json.status, cache))
    const manga = json.data.manga ? this.fromJSON(json.data.manga, json.code, json.status, cache) : this.getResource(id)
    manga.chapterListLastLoaded = new Date()
    //manga.updateChapterList()
    return { groups, chapters, manga }
  }
}