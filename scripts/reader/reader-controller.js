import ReaderModel from './reader-model'
import ReaderView from './reader-view'

export default class Reader {
  constructor() {
    this.model = new ReaderModel()
    this.view = new ReaderView(this.model)
  }

  initialize() {
    return new Promise((resolve, reject) => {
      const meta = document.querySelector('meta[name="app"]')
      this.model.appMeta = meta ? meta.dataset : {}
      this.model.loadSettings()
      for (let mode of ['renderingMode', 'displayFit', 'direction']) {
        this.model[mode] = this.model.settings[mode]
      }
      if (typeof window === 'undefined') {
        return reject()
      }
      return resolve()
    }).then(() => {
      this.view.initialize(document.querySelector('div[role="main"]'))
      this.view.addListeners()
      if (this.model.appMeta.page === 'recs') {
        return this.model.moveToRecommendations()
          .then(() => {
            this.view.renderer.render()
          })
      }
      let page = parseInt(this.model.appMeta.page) || 1
      if (page === -1 && this.model.chapter) {
        page = this.model.chapter.totalPages
      }
      return this.model.setChapter(parseInt(this.model.appMeta.chapterId), page)
        .then((chapter) => {
          if (this.model.appMeta.page === 'recs') {
            this.view.moveToRecommendations()
          } else if (!chapter.error) {
            this.model.preload(page)
            if (this.model.isStateReading) {
              return this.view.moveToPage(page, false).then(() => {
                this.view.replaceHistory()
                this.view.updatePage()
              })
            }
          } else {
            this.view.replaceHistory(chapter.id, null)
          }
        })
    }).catch((err) => {
      console.error(err)
    })
  }
}