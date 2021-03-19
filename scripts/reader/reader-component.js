export default class ReaderComponent {
  static create(type, props) {
    const el = typeof type === 'string' ? document.createElement(type) : type
    for (let i in props) {
      el[i] = props[i]
    }
    return el
  }
  static empty(node) {
    while (node && node.firstChild) {
      node.removeChild(node.firstChild)
    }
  }
}

export class Option extends ReaderComponent {
  static render(data) {
    return this.create('option', {
      value: data.value,
      selected: data.selected || false,
      textContent: data.text,
    })
  }
}

export class Flag extends ReaderComponent {
  static render(data, el) {
    const langCode = (data.language || '_unknown').replace(/\W/g, '')
    el = this.create(el || 'span', {
      title: langCode,
    })
    el.className = ''
    el.classList.add('rounded', 'flag', `flag-${langCode}`.trim())
    return el
  }
}

export class Link extends ReaderComponent {
  static render(data, el) {
    return this.create(el || 'a', {
      href: data.url,
      title: data.title,
      innerHTML: data.title,
    })
  }
}

export class ChapterDropdown extends ReaderComponent {
  static render(model, el) {
    this.empty(el)
    for (let ch of model.manga.uniqueChapterList.slice().reverse()) {
      el.appendChild(Option.render({
        value: ch.id,
        selected: ch.id === model.chapter.id,
        text: model.settings.showDropdownTitles ? ch.fullTitle : ch.numberTitle,
      }))
    }
    return el
  }
}

export class PageDropdown extends ReaderComponent {
  static render(model, el) {
    this.empty(el)
    for (let i = 1; i <= model.chapter.totalPages; ++i) {
      el.appendChild(Option.render({
        value: i,
        selected: i === model.currentPage,
        text: i
      }))
    }
    return el
  }
}

export class GroupItem extends ReaderComponent {
  static render(chapter) {
    const li = this.create('li')
    const flag = li.appendChild(Flag.render(chapter))
    flag.classList.add('mr-1')
    const link = li.appendChild(this.create(!chapter.isCurrentChapter ? 'a' : 'strong', {
      innerHTML: chapter.groups.map(g => g.name).join(' | '),
      href: chapter.url
    }))
    link.dataset.action = "chapter"
    link.dataset.chapter = chapter.id
    return li
  }
}