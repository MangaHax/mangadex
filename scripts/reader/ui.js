'use strict'

/* global Renderer */

const utils = {
  empty: (node) => {
    while (node.firstChild) {
      node.removeChild(node.firstChild)
    }
  }
}

class UI {
  constructor(reader) {
    this.reader = reader
  }

  get isSinglePage()   { return this.renderingMode === UI.RENDERING_MODE.SINGLE }
  get isDoublePage()   { return this.renderingMode === UI.RENDERING_MODE.DOUBLE }
  get isLongStrip()    { return this.renderingMode === UI.RENDERING_MODE.LONG }
  get isNoResize()     { return this.displayFit === UI.DISPLAY_FIT.NO_RESIZE }
  get isFitHeight()    { return this.displayFit === UI.DISPLAY_FIT.FIT_HEIGHT }
  get isFitWidth()     { return this.displayFit === UI.DISPLAY_FIT.FIT_WIDTH }
  get isFitBoth()      { return this.displayFit === UI.DISPLAY_FIT.FIT_BOTH }
  get isDirectionLTR() { return this.direction === UI.DIRECTION.LTR }
  get isDirectionRTL() { return this.direction === UI.DIRECTION.RTL }
  get renderedPages() {
    if (this.renderer == null) {
      return 0
    } else if (this.isLongStrip) {
      return 1
    } else {
      return this.renderer.renderedPages
    }
  }

  initializeContainer(userIsGuest = false) {
    this.container = document.querySelector('div[role="main"]')
    this.container.classList.remove('container')
    this.container.classList.add('reader', 'row', 'flex-column', 'flex-lg-row', 'no-gutters')
    this.imageContainer = this.container.querySelector('.reader-images')
    document.querySelector('footer').classList.add('d-none')
    document.body.style.removeProperty('margin-bottom')
    if (userIsGuest) {
      const reportBtn = this.container.querySelector('#report-button')
      reportBtn.dataset.toggle = ''
      reportBtn.href = '/login'
      reportBtn.firstElementChild.classList.replace('fa-flag', 'fa-sign-in-alt')
    }
  }

  setRenderer(mode = this.reader.settings.renderingMode, useHistory = true) {
    if (this.renderingMode !== mode) {
      if (this.renderer != null) {
        this.renderer.destroy()
      }
      this.renderingMode = mode
      switch(mode) {
        case UI.RENDERING_MODE.LONG:
          this.renderer = new Renderer.LongStrip(this.reader)
          break
        case UI.RENDERING_MODE.DOUBLE:
          this.renderer = new Renderer.DoublePage(this.reader)
          break
        case UI.RENDERING_MODE.SINGLE:
        default:
          this.renderer = new Renderer.SinglePage(this.reader)
          break
        case UI.RENDERING_MODE.ALERT:
          this.renderer = new Renderer.Alert(this.reader)
          break
        case UI.RENDERING_MODE.RECS:
          this.renderer = new Renderer.Recommendations(this.reader)
          break
      }
      this.container.dataset.renderer = this.renderer.name
      if (useHistory) {
        this.pushHistory(this.reader.currentPage)
      }
    }
  }

  setDirection(direction) {
    this.direction = direction
    this.container.dataset.direction = UI.DIRECTION.LTR === direction ? 'ltr' : 'rtl'
  }

  setDisplayFit(fit) {
    this.displayFit = fit
    this.container.dataset.display = UI.DISPLAY_FIT_STR[fit]
    this.container.classList.toggle('fit-horizontal', this.isFitBoth || this.isFitWidth)
    this.container.classList.toggle('fit-vertical',   this.isFitBoth || this.isFitHeight)
  }

  onChapterChange(chapter) {
    if (this.renderer) {
      this.renderer.initialize()
    }
    this.container.classList.toggle('native-long-strip', chapter.manga.isLongStrip)
    this.setRenderer(chapter.manga.isLongStrip ? UI.RENDERING_MODE.LONG : this.reader.settings.renderer, false)
    this.setDisplayFit(chapter.manga.isLongStrip ? UI.DISPLAY_FIT.FIT_WIDTH : this.reader.settings.displayFit)
    this.resetPageBar(chapter.totalPages)
    // TODO?: it's assumed that the input is already escaped; when not, change innerHTML to textContent
    this.updateTitles(chapter)
    this.updateChapterDropdown(chapter, !this.reader.settings.showDropdownTitles)
    this.updatePageDropdown(chapter.totalPages, this.reader.currentPage)
    this.updateGroupList(chapter)
    this.updateCommentsButton(chapter)
    this.updateChapterLinks(chapter)
  }

  updateSetting(key, value) {
    switch (key) {
      case 'direction':
        this.setDirection(value)
        this.updateChapterLinks()
        this.setRenderer()
        if (this.reader.currentPage) {
          this.render(this.reader.currentPage)
        }
        break
      case 'renderingMode':
        this.setRenderer(value)
        if (this.reader.currentPage) {
          this.render(this.reader.currentPage)
        }
        break
      case 'displayFit':
        this.setDisplayFit(value)
        break
      case 'containerWidth':
        if (!value) {
          value = null
        }
        this.imageContainer.style.maxWidth = value ? `${value}px` : null
        break
      case 'showDropdownTitles':
        this.updateChapterDropdown(this.reader.chapter, !value)
        break
      case 'hideHeader':
        this.container.classList.toggle('hide-header', value)
        document.querySelector('nav.navbar').classList.toggle('d-none', value)
        document.querySelector('#fullscreen-button').classList.toggle('active', value)
        break
      case 'hideSidebar':
        this.container.classList.toggle('hide-sidebar', value)
        break
      case 'hidePagebar':
        this.container.classList.toggle('hide-page-bar', value)
        break
    }
    Array.from(this.container.querySelectorAll(`#modal-settings input[data-setting="${key}"]`)).forEach(n => { n.value = value })
    Array.from(this.container.querySelectorAll(`#modal-settings select[data-setting="${key}"]`)).forEach(n => { n.value = value })
    Array.from(this.container.querySelectorAll(`#modal-settings button[data-setting="${key}"]`)).forEach(n => { n.classList.toggle('active', n.dataset.value == value) })
  }

  updateTitles(chapter) {
    const manga = chapter.manga
    document.title = `${manga.title} - ${manga.getChapterTitle(chapter.id)} - MangaDex`
    const mangaFlag = this.container.querySelector('.reader-controls-title .lang-flag')
    mangaFlag.parentElement.replaceChild(UI.flagImg(manga.langCode, manga.langName), mangaFlag)
    const mangaLink = this.container.querySelector('.manga-link')
    mangaLink.href = manga.url
    mangaLink.title = manga.title
    mangaLink.innerHTML = manga.title
    this.container.querySelector('.chapter-title').innerHTML = chapter.title
    this.container.querySelector('.chapter-tag-h').classList.toggle('d-none', !manga.isHentai)
    this.container.querySelector('.chapter-tag-end').classList.toggle('d-none', !chapter.isLastChapter)
    this.container.querySelector('.chapter-tag-doujinshi').classList.toggle('d-none', !manga.isDoujinshi)
  }

  updateChapterDropdown(chapter, hideTitles) {
    if (chapter) {
      const manga = chapter.manga
      const chapters = this.container.querySelector('#jump-chapter')
      utils.empty(chapters)
      for (let ch of manga.chapterList.slice().reverse()) {
        const option = chapters.appendChild(document.createElement('option'))
        option.value = ch.id
        option.selected = ch.id === chapter.id
        option.appendChild(document.createTextNode(manga.getChapterTitle(ch.id, hideTitles)))
      }
    }
  }

  updatePageDropdown(totalPages, currentPage) {
    const pages = this.container.querySelector('#jump-page')
    utils.empty(pages)
    for (let i = 1; i <= totalPages; ++i) {
      const option = pages.appendChild(document.createElement('option'))
      option.value = i
      option.selected = currentPage === i
      option.appendChild(document.createTextNode(i))
    }
  }

  updateGroupList(chapter) {
    const groups = this.container.querySelector('.reader-controls-groups ul')
    utils.empty(groups)
    for (let g of chapter.manga.getGroupsOfChapter(chapter.id)) {
      const li = groups.appendChild(document.createElement('li'))
      const flag = li.appendChild(UI.flagImg(g.lang_code, g.lang_code))
      flag.classList.add('mr-1')
      const link = li.appendChild(document.createElement(g.id == chapter.id ? 'b' : 'a'))
      link.innerHTML = [g.group_name, g.group_name_2, g.group_name_3].filter(n => n).join(' | ')
      link.dataset.chapter = g.id
      link.href = `/chapter/${g.id}`
    }
  }

  updateCommentsButton(chapter) {
    this.container.querySelector('#comment-button').href = this.pageURL(chapter.id) + '/comments'
    this.container.querySelector('.comment-amount').textContent = chapter.comments || ''
  }

  updateChapterLinks(chapter) {
    if (chapter) {
      const update = (toLeft) => {
        if (this.direction != null) {
          let id = (toLeft === this.isDirectionLTR) ? chapter.prevChapterId : chapter.nextChapterId
          return (a) => {
            a.dataset.chapter = id
            a.href = this.pageURL(id)
            a.title = chapter.manga.getChapterTitle(id) || 'Back to manga'
          }
        }
      }
      Array.from(this.container.querySelectorAll('a.chapter-link-left')).forEach(update(true))
      Array.from(this.container.querySelectorAll('a.chapter-link-right')).forEach(update(false))
    }
  }

  updatePageLinks(chapter, pg) {
    if (chapter && typeof pg === 'number') {
      const pgStr = `${pg}${this.renderedPages===2 ? ` - ${pg + 1}` : ''}`
      this.container.querySelector('.reader-controls-pages .current-page').textContent = pgStr
      this.container.querySelector('.reader-controls-pages .total-pages').textContent = chapter.totalPages
      this.container.querySelector('.reader-controls-pages .page-link-left').href = this.pageLeftURL(chapter.id, 1)
      this.container.querySelector('.reader-controls-pages .page-link-right').href = this.pageRightURL(chapter.id, 1)
      this.container.querySelector('#jump-page').value = pg
    }
  }

  pageURL(id, pg) {
    if (id != null && id > 0) {
      if (pg != null) {
        if (pg === 0) {
          return this.pageURL(this.reader.chapter.prevChapterId, -1)
        } else if (pg > this.reader.chapter.totalPages) {
          return this.pageURL(this.reader.chapter.nextChapterId)
        }
        return this.isTestReader ? `/?page=chapter_test&id=${id}&p=${pg}` : `/chapter/${id}/${pg}`
      }
      return this.isTestReader ? `/?page=chapter_test&id=${id}` : `/chapter/${id}`
    }
    return this.manga.url
  }
  pageLeftURL(id, pages = this.isDoublePage ? 2 : 1) {
    return this.pageURL(id, Math.min(this.reader.currentPage + (this.isDirectionLTR ? -pages : pages)), 0)
  }
  pageRightURL(id, pages = this.isDoublePage ? 2 : 1) {
    return this.pageURL(id, Math.min(this.reader.currentPage + (this.isDirectionLTR ? pages : -pages)), 0)
  }

  resetPageBar(totalPages) {
    if (totalPages) {
      const notches = this.container.querySelector('.reader-page-bar .notches')
      utils.empty(notches)
      for (let i = 1; i <= totalPages; ++i) {
        const notch = notches.appendChild(document.createElement('div'))
        notch.classList.add('notch', 'col')
        notch.style.order = i
        notch.dataset.page = i
        notch.title = `Page ${i}`
        // notch.classList.toggle('trail', i <= pg-this.renderedPages)
        // notch.classList.toggle('thumb', i > pg-this.renderedPages && i <= pg)
      }
      this.updatePageBar(totalPages, 1)
    }
  }

  updatePageBar(totalPages, pg) {
    if (totalPages && typeof pg === 'number') {
      const trail = this.container.querySelector('.reader-page-bar .trail')
      const thumb = this.container.querySelector('.reader-page-bar .thumb')
      const notchSize = 100 / Math.max(totalPages, 1)
      trail.style.width = Math.min(pg * notchSize, 100) + '%'
      thumb.style.width = (100 / pg * Math.max(this.reader.renderedPages, 1)) + '%'
      trail.style.right = this.reader.isDirectionLTR ? null : 0
      thumb.style.float = this.reader.isDirectionLTR ? 'right' : 'left'
    }
  }

  render(chapter, pg) {
    if (!pg || chapter == null || this.ui.renderer == null) {
      return Promise.reject()
    }
    return this.renderer.render(pg).then(() => {
      this.updatePageLinks(chapter, this.reader.currentPage)
      this.updatePageBar(chapter, this.reader.currentPage + this.renderedPages - 1)
    }).catch((err) => this.renderError(err))
  }

  renderError(err) {
    console.error('Render error', err)
    this.setRenderer(UI.RENDERING_MODE.ALERT)
    return this.renderer.render({ type: 'danger', 'message': err.message, 'err': err })
  }


  static flagImg (langCode = 'jp', langName = 'Unknown') {
    const flag = document.createElement('img')
    flag.classList.add('lang-flag')
    flag.src = `https://mangadex.org/images/flags/${langCode}.png`
    flag.alt = langName
    flag.title = langName
    return flag
  }
}
UI.RENDERING_MODE = {
  SINGLE: 1,
  DOUBLE: 2,
  LONG:   3,
  ALERT:  4,
  RECS:   5,
}
UI.DIRECTION = {
  LTR: 1,
  RTL: 2,
}
UI.DISPLAY_FIT = {
  FIT_BOTH:   1,
  FIT_WIDTH:  2,
  FIT_HEIGHT: 3,
  NO_RESIZE:  4,
}
UI.DISPLAY_FIT_STR = {
  1: 'fit-both',
  2: 'fit-width',
  3: 'fit-height',
  4: 'no-resize',
}



if (typeof exports === 'object' && typeof module === 'object') {
  module.exports = UI
} else if (typeof define === 'function' && define.amd) {
  define([], function () {
    return UI
  })
} else if (typeof exports === 'object') {
  exports.UI = UI
} else {
  (typeof window !== 'undefined' ? window : this).UI = UI
}