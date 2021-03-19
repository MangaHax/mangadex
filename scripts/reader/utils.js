export default class Utils {
  static range (start, end, step = 1) {
    const r = []
    for (let i = start; i < end; i += step) {
      r.push(i)
    }
    return r
  }

  static clamp (val, min, max) {
    return Math.max(min, Math.min(val, max))
  }

  static emptyNode (node) {
    while (node && node.firstChild) {
      node.removeChild(node.firstChild)
    }
  }

  static scrollBy () {
    window.scrollBy.apply(null, arguments)
  }

  static stableSort (array, cmp) {
    // https://medium.com/@fsufitch/is-javascript-array-sort-stable-46b90822543f
    cmp = !!cmp ? cmp : (a, b) => {
      if (a < b) return -1
      if (a > b) return 1
      return 0
    }
    const stabilized = array.map((el, index) => [el, index])
    const stableCmp = (a, b) => {
      const order = cmp(a[0], b[0])
      return order != 0 ? order : a[1] - b[1]
    }
    stabilized.sort(stableCmp)
    for (let i = 0; i < array.length; ++i) {
      array[i] = stabilized[i][0]
    }
    return array
  }


  static htmlTextDecodeHack(str) {
    const textarea = document.createElement('textarea')
    textarea.innerHTML = str
    return textarea.value
  }
}


try {
  window.scrollBy({ top: 0, behavior: "smooth" })
} catch(e) {
  Utils.scrollBy = function() {
    const arg = arguments[0]
    switch(typeof arg) {
      case 'object':
        return window.scrollBy(arg.top || 0, arg.left || 0)
      case 'number':
        return window.scrollBy.apply(null, arguments)
    }
  }
}


if (typeof exports === 'object' && typeof module === 'object') {
  module.exports = Utils
}