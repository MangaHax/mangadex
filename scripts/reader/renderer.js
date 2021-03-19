import { formatDistance } from 'date-fns'
import Utils from './utils'
import ReaderView from './reader-view'
import Chapter from './resource/Chapter'
import Manga from './resource/Manga'

export default class AbstractRenderer {
  constructor(container, model, view) {
    this.el = container
    this.model = model
    this.view = view
    this._initialized = false
  }

  get chapter() { return this.model.chapter }

  initialize() {
    // console.log('initialize',this.name)
    this._initialized = true
    this.clearImageContainer()
    this.renderedPages = 0
    this._pageStateHandler = (page) => {
      //console.info('pagestatehandler', this.name, page)
      this.pageStateHandler(page)
    }
    this.model.on('pageloading', this._pageStateHandler)
    this.model.on('pageload', this._pageStateHandler)
    this.model.on('pageerror', this._pageStateHandler)
  }

  destroy() {
    if (!this._initialized) {
      return
    }
    // console.log('destroy',this.name)
    this._initialized = false
    this.clearImageContainer()
    this.model.off('pageloading', this._pageStateHandler)
    this.model.off('pageload', this._pageStateHandler)
    this.model.off('pageerror', this._pageStateHandler)
  }

  reinitialize() {
    if (this._initialized) {
      this.destroy()
    }
    this.initialize()
  }

  createAndAppendWrapper(page) {
    return this.el.appendChild(this.updateWrapper(this.createWrapper(), page))
  }

  createWrapper() {
    const wrapper = document.createElement('div')
    const classes = [
      'reader-image-wrapper',
      'col-auto',
      'my-auto',
      'justify-content-center',
      'align-items-center',
      'noselect', 'nodrag',
      'row', 'no-gutters',
    ]
    wrapper.classList.add(...classes)
    wrapper.dataset.state = 0
    return wrapper
  }

  updateWrapper(wrapper, page = {}) {
    //console.log('update wrapper to', page.number, page)
    if (page.state !== parseInt(wrapper.dataset.state)) {
      while (wrapper.firstChild) {
        wrapper.removeChild(wrapper.firstChild)
      }
      switch (page.state) {
        case 1: wrapper.appendChild(this.createPageLoading()); break;
        case 2: wrapper.appendChild(this.createPageLoaded()); break;
        case 3: wrapper.appendChild(this.createPageError()); break;
      }
    }
    wrapper.style.order = page.number || 0
    wrapper.dataset.page = page.number || 0
    wrapper.dataset.state = page.state || 0
    switch (page.state) {
      case 1: wrapper.querySelector('.loading-page-number').textContent = page.number; break;
      case 2: wrapper.firstChild.src = page.image.src; break;
      case 3: wrapper.querySelector('.alert .message').textContent = page.error.message; break;
    }
    return wrapper
  }

  createPageLoading() {
    const container = document.createElement('div')
    container.classList.add('m-5', 'd-flex', 'align-items-center', 'justify-content-center')
    container.style.color = '#fff'
    container.style.textShadow = '0 0 7px rgba(0,0,0,0.5)'
    const spinner = container.appendChild(document.createElement('span'))
    spinner.classList.add('fas', 'fa-circle-notch', 'fa-spin', 'position-absolute')
    spinner.style.opacity = '0.5'
    spinner.style.fontSize = '7em'
    const pgNum = container.appendChild(document.createElement('span'))
    pgNum.classList.add('loading-page-number')
    pgNum.style.fontSize = '2em'
    return container
  }

  createPageLoaded() {
    const container = document.createElement('img')
    container.draggable = false
    container.classList.add('noselect', 'nodrag', 'cursor-pointer')
    return container
  }

  createPageError() {
    const container = Alert.container('', 'danger')
    const tapMsg = container.appendChild(document.createElement('div'))
    tapMsg.innerHTML = "Tap to reload."
    container.addEventListener('click', evt => {
      evt.preventDefault()
      evt.stopPropagation()
      const page = this.model.getPageWithoutLoading(parseInt(container.parentElement.dataset.page))
      page.reload(true).catch(console.error)
    })
    return container
  }

  createMangaError(chapter) {
    const container = Alert.container('', 'danger')
    const tapMsg = container.appendChild(document.createElement('div'))
    tapMsg.innerHTML = "Tap to reload."
    container.addEventListener('click', evt => {
      evt.preventDefault()
      evt.stopPropagation()
      container.parentElement.removeChild(container)
      this.model.isLoading = true
      chapter.loadManga(true)
        .then(chapter => {
          this.model.isLoading = false
          this.model.setChapter(chapter.id)
            .then(() => { this.view.moveToPage(1, false) })
        })
        .catch(err => {
          this.model.isLoading = false
          this.model.trigger('readererror', [err])
        })
    })
    return container
  }

  clearImageContainer() {
    while (this.el && this.el.firstChild) {
      this.el.removeChild(this.el.firstChild)
    }
  }

  render() {
    throw new Error("Not implemented")
  }

  pageStateHandler() {
    throw new Error("Not implemented")
  }
}

export class SinglePage extends AbstractRenderer {
  get name() { return 'single-page' }

  initialize() {
    super.initialize()
    this.renderedPages = 1
    this.pageToRender = null
    this.pageWrapper = this.createAndAppendWrapper()
  }

  pageStateHandler(page) {
    if (this.pageToRender === page) {
      this.updateWrapper(this.pageWrapper, page)
    }
  }

  render(pg) {
    const page = this.model.getPageWithoutLoading(pg)
    this.pageToRender = page
    this.updateWrapper(this.pageWrapper, page)
    return page.load().catch(p => Promise.resolve(p))
  }
}

export class DoublePage extends AbstractRenderer {
  get name() { return 'double-page' }

  get renderedPages() { return this.pagesToRender.length }
  set renderedPages(v) { }

  isPageTurnForwards() { return this.previousPage < this.model.currentPage }
  isSinglePageBackwards() { return this.previousPage === this.model.currentPage + 1 }
  isImageTooWide(img) {
    return img && img.naturalWidth > img.naturalHeight && img.naturalWidth > this.el.offsetWidth / 2
  }

  initialize() {
    super.initialize()
    this.pageWrapperLoading = this.createAndAppendWrapper({ state: 1, number: '', })
    this.previousPage = 0
    this.pageWrappers = [this.createAndAppendWrapper(), this.createAndAppendWrapper()]
    this.pagesToRender = []
    this.setLoading(true)
  }

  pageStateHandler(page) {
    if (this.pagesToRender.includes(page)) {
      this.checkRender()
    }
  }

  render(pg) {
    this.pagesToRender = [pg, pg + 1]
      .map(p => this.model.getPageWithoutLoading(p))
      .filter(p => p)

    this.checkRender()

    return Promise.all(
      this.pagesToRender.map(page =>
        page.load().catch(p => Promise.resolve(p))
      )
    )
  }

  checkRender() {
    const pagesDone = this.pagesToRender.every(p => p.isDone)
    if (pagesDone) {
      if (this.pagesToRender.length > 1 && this.pagesToRender.some(p => this.isImageTooWide(p.image))) {
        if (this.isPageTurnForwards() || this.isSinglePageBackwards()) {
          this.pagesToRender.pop()
        } else {
          this.pagesToRender.shift()
          this.model.setCurrentPage(this.pagesToRender[0].number)
        }
      }
      this.updateWrapper(this.pageWrappers[0], this.pagesToRender[0])
      this.updateWrapper(this.pageWrappers[1], this.pagesToRender[1])
      this.previousPage = this.model.currentPage
    }
    this.setLoading(!pagesDone)
  }

  setLoading(state) {
    this.pageWrapperLoading.classList.toggle('d-none', !state)
    for (let wrapper of this.pageWrappers) {
      wrapper.classList.toggle('d-none', state)
    }
  }
}

export class LongStrip extends AbstractRenderer {
  get name() { return 'long-strip' }
  get renderedPages() { return this._renderedPageSet.length }
  set renderedPages(v) { }
  get lastRenderedPage() { return this._renderedPageSet[this._renderedPageSet.length - 1] }

  initialize() {
    super.initialize()
    this._pageWrapperMap = new Map()
    this._renderedPageSet = []
    this._scrollY = -1

    this.observer = new MutationObserver((mutationsList) => {
      // this is horrible
      if (this._scrollY === -1 && this.model.currentPage !== 1) {
        this._scrollY = -2
        requestAnimationFrame(() => {
          this.getPageWrapper(this.model.currentPage).scrollIntoView(true)
          requestAnimationFrame(() => {
            this._scrollY = window.pageYOffset || -1
            // console.log('did it',this._scrollY)
            if (this._scrollY !== -1) {
              ReaderView.scroll(0, -document.querySelector('nav.navbar').offsetHeight + 1)
              this.observer.disconnect()
            }
          })
        })
        //this.scrollToPage(this.model.currentPage)
      }
    })
    this.observer.observe(this.el, { childList: true, subtree: true })

    for (let page of this.model.getAllPages()) {
      this._pageWrapperMap.set(page.number, this.createAndAppendWrapper(page))
      if (page.isDone) {
        this._renderedPageSet.push(page.number)
      }
    }
    Utils.stableSort(this._renderedPageSet)
    this.renderEndBlock()

    this.render(this.model.currentPage)

    this.addScrollHandler()
    this._currentPageHandler = (pg) => {
      this.render(pg + 1)
        .then(() => {
          this.render(pg - 1)
        })
    }
    this.model.on('currentpagechange', this._currentPageHandler)
  }

  destroy() {
    if (!this._initialized) {
      return
    }
    super.destroy()
    this.observer.disconnect()
    //this.el.scrollIntoView(true)
    this._pageWrapperMap.clear()
    this.removeScrollHandler()
    window.scrollTo(0, 0)
    this.model.off('currentpagechange', this._currentPageHandler)
  }

  pageStateHandler(page) {
    this.updateWrapper(this.getPageWrapper(page.number), page)
    if (page.isDone || page.loading) {
      if (!this.isRendered(page.number)) {
        this._renderedPageSet.push(page.number)
        Utils.stableSort(this._renderedPageSet)
      }
      if (this.isChapterFullyRendered) {
        this.showEndBlock()
      }
      if (this._scrollY >= 0) {
        this.updateCurrentPage()
      } //else {
      // this._scrollY = -2
      // requestAnimationFrame(() => {
      //   this.getPageWrapper(this.model.currentPage).scrollIntoView(true)
      //   requestAnimationFrame(() => {
      //     ReaderView.scroll(0, -document.querySelector('nav.navbar').offsetHeight + 1)
      //     requestAnimationFrame(() => {
      //       this._scrollY = window.pageYOffset || -1
      //       console.log('did it',this._scrollY)
      //     })
      //   })
      // })
      //this.scrollToPage(this.model.currentPage)
      // }
    }
  }

  getPageWrapper(pg) {
    if (!this._pageWrapperMap.has(pg)) {
      throw new Error("No wrapper for page ", pg)
    }
    return this._pageWrapperMap.get(pg)
  }

  isRendered(pg) {
    return this._renderedPageSet.includes(pg)
  }

  get isChapterFullyRendered() {
    return this.renderedPages === this.model.totalPages
  }

  render(pg) {
    if (!this.isChapterFullyRendered && !this.isRendered(pg)) {
      return this.model.getPage(pg)
        .catch(p => Promise.resolve(p))
    }
    return Promise.resolve()
  }

  renderEndBlock() {
    this._endBlock = this.createAndAppendWrapper({
      number: this.model.totalPages + 1,
      chapter: this.model.chapter.id,
    })
    this._endBlock.textContent = 'End of chapter / Go to next'
    this._endBlock.classList.add('reader-image-block', 'py-3', 'd-none')
    this._endBlock.addEventListener('click', (evt) => {
      evt.stopPropagation()
      this.view.moveToChapter(this.model.chapter.nextChapterId)
    }, { once: true })
    if (this.isChapterFullyRendered) {
      this.showEndBlock()
    }
  }

  showEndBlock() {
    this._endBlock.classList.remove('d-none')
  }

  updateCurrentPage() {
    if (this.renderedPages > 0 && !this._updating) {
      this._updating = true
      if (ReaderView.isScrolledToTop) {
        this.model.setCurrentPage(this._renderedPageSet[0])
        this.view.replaceHistory()
      } else if (ReaderView.isScrolledToBottom) {
        this.model.setCurrentPage(this.lastRenderedPage)
        this.view.replaceHistory()
      } else {
        const scrollY = Math.floor(window.pageYOffset)
        for (let i = this._renderedPageSet.length - 1; i >= 0; --i) {
          const pg = this._renderedPageSet[i]
          const wrapper = this.getPageWrapper(pg)
          if (scrollY >= wrapper.offsetTop) {
            if (this.model.setCurrentPage(pg)) {
              this.view.replaceHistory()
            }
            break
          }
        }
      }
      this._updating = false
    }
  }

  scrollToPage(pg) {
    // requestAnimationFrame(() => {
    const wrapper = this.getPageWrapper(pg)
    if (this.isRendered(pg) && wrapper) {
      // console.log('scrolling to', pg, wrapper.offsetTop + 1)
      //window.scrollTo(window.pageXOffset, wrapper.offsetTop + 1)
      wrapper.scrollIntoView(true)
      if (!ReaderView.isScrolledToBottom) {
        ReaderView.scroll(0, -document.querySelector('nav.navbar').offsetHeight + 1)
      }
      // requestAnimationFrame(() => {

      // })
    }
    // })
  }

  addScrollHandler() {
    if (!this._scrollHandler) {
      const update = () => {
        if (this.model.chapter) {
          this.updateCurrentPage()
        }
      }
      if (Modernizr.requestanimationframe) {
        let wait = false
        this._scrollHandler = () => {
          if (!wait) {
            wait = true
            requestAnimationFrame(() => {
              update()
              wait = false
            })
          }
        }
      } else {
        this._scrollHandler = () => {
          update()
        }
      }
      window.addEventListener('scroll', this._scrollHandler)
    }
  }

  removeScrollHandler() {
    if (this._scrollHandler) {
      window.removeEventListener('scroll', this._scrollHandler)
      this._scrollHandler = null
    }
  }
}



export class Alert extends AbstractRenderer {
  get name() { return 'alert' }

  pageStateHandler() { }

  renderChapterButtons(data) {
    const chBtnContainer = this.el.appendChild(document.createElement('div'))
    chBtnContainer.classList.add('row', 'm-auto', 'justify-content-center', 'directional')
    const buttons = [
      { text: 'Previous chapter', id: data.prevChapterId, order: 1 },
      { text: 'Next chapter', id: data.nextChapterId, order: 2 },
    ]
    const classes = ['col-auto', 'hover', 'text-dark']
    for (let btn of buttons) {
      const link = chBtnContainer.appendChild(Alert.container(btn.text, 'dark', 'a'))
      link.setAttribute('href', this.view.pageURL(btn.id))
      link.dataset.action = 'chapter'
      link.dataset.chapter = btn.id
      link.classList.add(...classes)
      link.classList.replace('m-auto', 'm-1')
      link.style.order = btn.order
    }
  }

  render(data) {
    this.clearImageContainer()
    if (typeof data !== 'object') {
      return Promise.reject({ message: "Data is not an object", data: data, revert: true })
    }
    if (data.isExternal) {
      this.el.appendChild(Alert.container(`This chapter can be read for free on the official publisher's website.<br>Feel free to write your comments about it here on MangaDex!`, 'info'))
      const link = Alert.container(`${Alert.icon('external-link-alt', 'Website')} <strong>Read the chapter</strong>`, 'success', 'a', false)
      link.target = '_blank'
      link.rel = 'noopener noreferrer'
      link.href = data.pages
      this.el.appendChild(link)
      this.renderChapterButtons(data)
    } else if (data.isDelayed) {
      const now = new Date()
      const release = new Date(data.timestamp * 1000)
      const relativeDate = release > now ? formatDistance(release, now, { addSuffix: true }) : 'within a few minutes'
      this.el.appendChild(Alert.container(`Due to the group's delay policy, this chapter will be available ${relativeDate}.`, 'danger'))
      this.el.appendChild(Alert.container(`You might be able to read it on the group's <a target='_blank' rel='noopener noreferrer' href='${data.groupWebsite}'>${Alert.icon('external-link-alt', 'Website')} <strong>website</strong></a>.`, 'info'))
      this.renderChapterButtons(data)
    } else if (data.isSpoilerNet) {
      const alert = document.createElement('div')
      alert.classList.add('alert', `alert-warning`, 'text-center', 'm-auto')
      alert.attributes.role = 'alert'
      alert.innerHTML = `<h3>${Alert.icon('warning')} Spoiler Warning</h3><div class="my-3"><div>There seems to be a gap between chapters (${Chapter.getResource(data.prevChapterId).fullTitle} &rarr; ${Chapter.getResource(data.chapterId).fullTitle}).</div><div>This may be an attempt to troll you into reading a chapter early.</div>`
      const button = document.createElement('button')
      button.classList.add('btn', 'btn-secondary')
      button.type = 'button'
      button.textContent = "I understand, I'm fine with spoilers!"
      button.addEventListener('click', (evt) => {
        evt.stopPropagation()
        this.view.moveToChapter(data.chapterId, 1, true, true)
      })
      alert.appendChild(button)
      this.el.appendChild(alert)
    } else if (data.isNotFound) {
      this.el.appendChild(Alert.container(`Data not found${data.message ? ': ' + data.message : '.'}`, 'danger'))
    } else if (data.isDeleted) {
      this.el.appendChild(Alert.container(`This chapter has been deleted.`, 'danger'))
    } else if (data.isRestricted) {
      this.el.appendChild(Alert.container(`This chapter is unavailable.`, 'danger'))
    } else if (data.isMangaFailed) {
      const alert = this.createMangaError(data)
      alert.querySelector('.message').textContent = "The manga data failed to load."
      alert.draggable = false
      alert.classList.add('noselect', 'nodrag', 'cursor-pointer')
      this.el.appendChild(alert)
    } else if (data.status === 'unavailable') {
      this.el.appendChild(Alert.container(`This chapter is unavailable.`, 'danger'))
    } else if (data != null) {
      const isError = data instanceof Error || data.error instanceof Error
      const type = data.type || isError ? 'danger' : undefined
      const msg = data.message || data.error && data.error.message || data.error && data.error.status || data
      this.el.appendChild(Alert.container(msg, type))
      // if (isError) {
      //   this.el.appendChild(Alert.container('dark', data.stack || data.error.stack))
      // }
    }
    return Promise.resolve()
  }

  static icon(type, title) {
    type = Alert.iconTypes[type] || type
    return `<span class='fas fa-${type} fa-fw' aria-hidden='true'${title ? ` title=${title}` : ''}></span> `
  }

  static get iconTypes() {
    return {
      success: 'check-circle',
      danger: 'times',
      info: 'info',
      warning: 'exclamation-triangle',
    }
  }

  static container(message = '', type = 'dark', element = 'div', icon = true) {
    const div = document.createElement(element)
    div.classList.add('alert', `alert-${type}`, 'text-center', 'm-auto')
    div.attributes.role = 'alert'
    if (icon && Alert.iconTypes[type]) {
      div.innerHTML = Alert.icon(type)
    }
    const span = div.appendChild(document.createElement('span'))
    span.classList.add('message')
    span.innerHTML = message
    return div
  }
}

export class Recommendations extends AbstractRenderer {
  get name() { return 'recommendations' }

  render() {
    if (!this.model.recommendations) {
      this.el.innerHTML = ''
      return this.el.appendChild(Alert.container("No recommendations found. You must be logged in and have followed some titles.", "danger"))
    }
    let recStr = ''
    for (let [manga, chapters] of this.model.recommendations.unreadChaptersGroupedByManga) {
      if (chapters.length > 0) {
        const chapter = chapters[chapters.length - 1]
        const more = chapters.length >= 2 ? ` <em class="ml-1">(+${chapters.length - 1} more)</em>` : ''
        const relativeDate = formatDistance(new Date(chapter.timestamp * 1000), new Date(), { addSuffix: true })
        recStr += `
        <div class="col-xl-3 col-md-4 col-sm-6 border-bottom p-2 text-left">
          <div class="rounded sm_md_logo float-left mr-2">
            <a href="${manga.url}">
              <img class="rounded max-width" src="${manga.coverThumb}">
            </a>
          </div>
          <div>
            <div class="text-truncate py-0 mb-1 border-bottom">
              <span class="fas fa-book fa-fw" aria-hidden="true" title=""></span>
              <a class="manga_title" title="${manga.title}" href="${manga.url}">${manga.title}</a>
            </div>
            <p class="py-0 mb-1 row no-gutters align-items-center flex-nowrap">
              <div class="col-auto">
                <span class="col-auto px-0">
                  <span class="rounded flag flag-${chapter.language}"></span>
                </span>
                <a class="" href="${chapter.url}" data-chapter="${chapter.id}">${chapter.fullTitle}</a>
                ${more}
              </div>
            </p>
            <p class="text-truncate py-0 mb-1"><span class="far fa-clock fa-fw " aria-hidden="true" title=""></span> ${relativeDate}</span></p>
          </div>
        </div>`
      }
    }
    this.el.innerHTML = `<h2 class="text-left">Recommendations</h2><h3 class="text-left">Unread Follows</h3><div class="row no-gutters">${recStr}</div>`
    const handler = (evt) => {
      const chapter = evt.target.dataset.chapter || evt.currentTarget.dataset.chapter
      if (chapter) {
        evt.preventDefault()
        evt.stopPropagation()
        this.view.moveToChapter(parseInt(chapter), 1)
        // this.model.setRenderer(this.model.settings.renderingMode)
      }
    }
    this.el.querySelectorAll('a').forEach(c => c.addEventListener('click', handler, true))
    return Promise.resolve()
  }

  pageStateHandler() { }

  getChapterTitle(ch, numOnly) {
    let title = ''
    if (ch.volume) title += `Vol. ${ch.volume} `
    if (ch.chapter) title += `Ch. ${ch.chapter} `
    if (ch.title && !numOnly) title += `${ch.title}`
    if (!title) title = 'Oneshot'
    return title.trim()
  }
}
