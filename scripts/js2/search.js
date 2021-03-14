const searchForm = document.querySelector('#search_titles_form')

function toggleSearchButton(form, searching) {
  const submit = form.querySelector('button[type="submit"]')
  submit.disabled = searching
  const icon = submit.querySelector('span')
  icon.classList.toggle('fa-search', !searching)
  icon.classList.toggle('fa-pulse', searching)
  icon.classList.toggle('fa-spinner', searching)
}

if (searchForm) {
  searchForm.addEventListener('submit', function(evt) {
    try {
      const map = {}
      map['tags'] = []
      map['demos'] = []
      map['statuses'] = []
      const tagExc = []
      for (let el of this.querySelectorAll('[name="tags_both[]"]')) {
        if (el.dataset.state === '1') {
          el.name = 'tags_inc[]'
        } else if (el.dataset.state === '2') {
          el.name = 'tags_exc[]'
        }
      }
      for (let i = 0; i < this.elements.length; ++i) {
        const el = this.elements[i]
        if (!el.disabled && el.name && (!(el.type === 'radio' || el.type === 'checkbox') || el.checked)) {
          switch (el.name) {
            case 'demo_id[]':   map['demos'].push(el.value); break;
            case 'status_id[]': map['statuses'].push(el.value); break;
            case 'tags_inc[]':  map['tags'].push(el.value); break;
            case 'tags_exc[]':  tagExc.push(el.value); break;
            case 'tags_both[]':
            case '': break;
            default: map[el.name] = el.value; break;
          }
        }
      }
      if (map['demos'].length === 4) { map['demos'] = [] }
      if (map['statuses'].length === 4) { map['statuses'] = [] }
      map['tags'] = map['tags'].concat(tagExc.filter(tag => map['tags'].indexOf(tag) === -1).map(n => '-'+n))
      const url = new URL(this.action || window.location.origin + window.location.pathname)
      for (let key in map) {
        let value = map[key]
        const isArray = Array.isArray(value)
        if (!isArray && value || isArray && value.length > 0) {
          //const urlKey = isArray ? key.slice(0, -2) : key
          if (isArray) {
            value = value.filter((v, i, a) => a.indexOf(v) === i).sort() // unique values
            value = value.map(encodeURIComponent).join(',')
          } else {
            value = encodeURIComponent(value)
          }
          url.searchParams.set(key, value)
        }
      }
      url.searchParams.sort()
      evt.preventDefault()
      toggleSearchButton(this, true)
      window.location.href = decodeURIComponent(url.pathname+url.search)
    } catch (e) {
      console.error(e)
    }
  })

  // bfcache
  window.addEventListener('unload', function (evt) {
    toggleSearchButton(searchForm, false)
  })
}

for (let searchForm of document.querySelectorAll('.quick-search')) {
  searchForm.addEventListener('submit', function(evt) {
    evt.preventDefault()
    toggleSearchButton(this, true)
    const term = encodeURIComponent(this.elements['term'].value)
    switch (this.elements['type'].value) {
      case 'all':    window.location.href = `/quick_search/${term}`; break;
      case 'titles': window.location.href = `/search?title=${term}`; break;
      case 'groups': window.location.href = `/groups/0/1/${term}`; break;
      case 'users':  window.location.href = `/users/0/1/${term}`; break;
    }
  })

  // bfcache
  window.addEventListener('unload', function (evt) {
    toggleSearchButton(searchForm, false)
  })
}

function getTagMode() {
  try { return localStorage.getItem('display.tagmode') || 'dropdowns' }
  catch(e) { return 'dropdowns' }
}

function setTagMode(value) {
  try { return localStorage.setItem('display.tagmode', value) }
  catch(e) { }
}

function updateToggles(value) {
  if (value) {
    for (let toggle of document.querySelectorAll('.tag-display-mode-toggle')) {
      toggle.classList.toggle('active', toggle.dataset.value === value)
    }
    for (let wrapper of document.querySelectorAll('.tag-display-mode-wrapper')) {
      const hide = wrapper.dataset.tagDisplay !== value
      wrapper.classList.toggle('d-none', hide)
      for (let input of wrapper.querySelectorAll('input')) {
        input.disabled = hide
      }
    }
  }
}

for (let toggle of document.querySelectorAll('.tag-display-mode-toggle')) {
  toggle.addEventListener('click', function(evt) {
    evt.preventDefault()
    const value = this.dataset.value
    updateToggles(value)
    setTagMode(value)
  })
}

export function initialize() {
  updateToggles(getTagMode())
}