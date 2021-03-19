import Cookies from 'js-cookie'
import Resource from './Resource'
import Chapter from './Chapter'
import Manga from './Manga'

export default class Follows extends Resource {
    static get resourceType() { return 'follows' }

    constructor(data = {}, responseCode = -1, responseStatus = null) {
        super(data, responseCode, responseStatus)
        this.chapters = data.chapters.map(c => new Chapter(c)).sort((a, b) => b.timestamp - a.timestamp)
        this.manga = new Map(Object.values(data.manga).map(m => [m.id, new Manga(m)]))
    }

    get unreadChapters() {
        return this.chapters.filter(c => !c.read)
    }

    get unreadChaptersGroupedByManga() {
        return this.unreadChapters.reduce((map, ch) => {
            const manga = this.manga.get(ch.mangaId)
            if (!map.has(manga)) {
                map.set(manga, [])
            }
            map.get(manga).push(ch)
            return map
        }, new Map())
    }

    static async load(params = {}, cache = true) {
        const type = 1 // Reading
        const hentai = Cookies.get('mangadex_h_toggle')
        const json = await super.load(`user/me/followed-updates`, Object.assign({ type, hentai }, params)) // type 1 = Reading
        return this.fromJSON(json.data, json.code, json.status, cache)
    }
}
