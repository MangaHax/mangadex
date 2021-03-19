import ReaderView from './reader-view.js'

export default class KeyboardShortcuts {
  static registerDefaults() {
    const defaultScroll = 50
    // kbd shortcuts
    // ^ only when shift is pressed
    // ! only when shift is not pressed
    this.register('turnPageLeft', ['arrowleft', 'left', 'a'], function (evt, view) {
      if (ReaderView.isScrolledToLeft) {
        view.turnPageLeft(evt.shiftKey ? 1 : undefined)
      }
    })
    this.register('turnPageRight', ['arrowright', 'right', 'd'], function (evt, view) {
      if (ReaderView.isScrolledToRight) {
        view.turnPageRight(evt.shiftKey ? 1 : undefined)
      }
    })
    this.register('turnPageUp', ['arrowup', 'up', 'w'], function (evt, view) {
      if (view.model.settings.pageWheelTurn == 1 && ReaderView.isScrolledToTop) {
        view.turnPageBackward(evt.shiftKey ? 1 : undefined)
      }
    })
    this.register('turnPageDown', ['arrowdown', 'down', 's'], function (evt, view) {
      if (view.model.settings.pageWheelTurn == 1 && ReaderView.isScrolledToBottom) {
        view.turnPageForward(evt.shiftKey ? 1 : undefined)
      }
    })
    this.register('scrollLeft', ['arrowleft', 'left', 'a'], function (evt, view) {
      if (view.model.settings.scrollingMethod == 1) {
        ReaderView.scroll(-Math.floor(view.el.clientWidth * 0.9), 0, 'smooth')
      } else if (view.model.settings.scrollingMethod == 0) {
        const key = evt.key.toLowerCase()
        if (key !== 'arrowleft' && key !== 'left') {
          ReaderView.scroll(-defaultScroll, 0)
        }
      }
    })
    this.register('scrollRight', ['arrowright', 'right', 'd'], function (evt, view) {
      if (view.model.settings.scrollingMethod == 1) {
        ReaderView.scroll(Math.floor(view.el.clientWidth * 0.9), 0, 'smooth')
      } else if (view.model.settings.scrollingMethod == 0) {
        const key = evt.key.toLowerCase()
        if (key !== 'arrowright' && key !== 'right') {
          ReaderView.scroll(defaultScroll, 0)
        }
      }
    })
    this.register('scrollUp', ['arrowup', 'up', 'w'], function (evt, view) {
      if (view.model.settings.scrollingMethod == 1) {
        ReaderView.scroll(0, -Math.floor(view.el.clientHeight * 0.9), 'smooth')
      } else if (view.model.settings.scrollingMethod == 0) {
        const key = evt.key.toLowerCase()
        if (key !== 'arrowup' && key !== 'up') {
          ReaderView.scroll(0, -defaultScroll)
        }
      }
    })
    this.register('scrollDown', ['arrowdown', 'down', 's'], function (evt, view) {
      if (view.model.settings.scrollingMethod == 1) {
        ReaderView.scroll(0, Math.floor(view.el.clientHeight * 0.9), 'smooth')
      } else if (view.model.settings.scrollingMethod == 0) {
        const key = evt.key.toLowerCase()
        if (key !== 'arrowdown' && key !== 'down') {
          ReaderView.scroll(0, defaultScroll)
        }
      }
    })
    this.register('turnChapterLeft', ['^q'], function (evt, view) {
      view.moveToChapter(view.model.isDirectionLTR ? view.model.chapter.prevChapterId : view.model.chapter.nextChapterId, 1)
    })
    this.register('turnChapterRight', ['^e'], function (evt, view) {
      view.moveToChapter(view.model.isDirectionRTL ? view.model.chapter.prevChapterId : view.model.chapter.nextChapterId, 1)
    })
    this.register('toggleDisplayFit', ['f'], function (evt, view) {
      view.model.saveSetting('displayFit', view.model.displayFit % 2 + (evt.shiftKey ? 3 : 1))
    })
    this.register('toggleRenderingMode', ['g'], function (evt, view) {
      view.model.saveSetting('renderingMode', ((view.model.renderingMode) + (evt.shiftKey ? -1 : 1)) % 3 || 3)
    })
    this.register('toggleDirection', ['h'], function (evt, view) {
      view.model.saveSetting('direction', view.model.direction % 2 + 1)
    })
    this.register('toggleHeader', ['!r'], function (evt, view) {
      view.model.saveSetting('hideHeader', view.model.settings.hideHeader ? 0 : 1)
    })
    this.register('toggleSidebar', ['!t'], function (evt, view) {
      view.model.saveSetting('hideSidebar', view.model.settings.hideSidebar ? 0 : 1)
    })
    this.register('togglePagebar', ['!y'], function (evt, view) {
      view.model.saveSetting('hidePagebar', view.model.settings.hidePagebar ? 0 : 1)
    })
    this.register('toggleAllBars', ['^r', '^t', '^y'], function (evt, view) {
      let any = view.model.settings.hideSidebar || view.model.settings.hideHeader || view.model.settings.hidePagebar
      view.model.saveSetting('hideSidebar', any ? 0 : 1)
      view.model.saveSetting('hideHeader', any ? 0 : 1)
      view.model.saveSetting('hidePagebar', any ? 0 : 1)
    })
    this.register('exitToManga', ['^m'], function (evt, view) {
      view.exitToURL(view.model.manga.url)
    })
    this.register('exitToComments', ['^k'], function (evt, view) {
      view.exitToURL(`${view.pageURL(view.model.chapter.id)}/comments`)
    })
  }

  static register(action, keys, handler) {
    this._kbdInput = this._kbdInput || {}
    this._kbdHandlers = this._kbdHandlers || {}
    this._kbdHandlers[action] = handler
    for (let key of keys) {
      this._kbdInput[key] = this._kbdInput[key] || []
      this._kbdInput[key].push(action)
    }
  }

  static fire(key, evt, view) {
    if (key in this._kbdInput) {
      for (let action of this._kbdInput[key]) {
        if (action in this._kbdHandlers) {
          this._kbdHandlers[action](evt, view)
        }
      }
    }
  }

  static keydownHandler(evt, view) {
    if (!(evt.altKey || evt.ctrlKey || evt.metaKey || evt.key === 'OS')) {
      const tag = (evt.target || evt.srcElement).tagName
      const key = evt.key.toLowerCase()
      if (!['INPUT','SELECT','TEXTAREA'].includes(tag)) {
        evt.stopPropagation()
        this.fire(key, evt, view)
        this.fire(evt.shiftKey ? '^'+key : '!'+key, evt, view)
      }
    }
  }
}