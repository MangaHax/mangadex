import Cookies from 'js-cookie'

class Store {
    static save(key, val) { }
    static load(key) { }
    static clear(key) { }
}

export class StoreLocalStorage extends Store {
    static save(key, val) {
        localStorage.setItem(`reader.${key}`, val)
    }
    static load(key) {
        return localStorage.getItem(`reader.${key}`)
    }
    static clear(key) {
        localStorage.removeItem(`reader.${key}`)
    }
    static clearAllExcept(retainedKeys) {
        retainedKeys = retainedKeys.map(key => `reader.${key}`)
        for (let [key, value] of Object.entries(localStorage)) {
            if (key.startsWith('reader.') && retainedKeys.indexOf(key) === -1) {
                localStorage.removeItem(key)
            }
        }
    }
}

export class StoreCookie extends Store {
    static get cookies() {
        return document.cookie.split(';').reduce((a, c) => {
            const [k, v] = c.split('=')
            a[k.trim()] = v.trim()
            return a
        }, {})
    }
    static save(key, val) {
        Cookies.set(`r-${key}`, val)
    }
    static load(key) {
        return Coookies.get(`r-${key}`)
    }
    static clear(key) {
        Coookies.remove(`r-${key}`)
    }
    static clearAllExcept(retainedKeys) {
        retainedKeys = retainedKeys.map(key => `r-${key}`)
        for (let [key, value] of Object.entries(Cookies.get())) {
            if (key.startsWith('reader.') && retainedKeys.indexOf(key) === -1) {
                Coookies.remove(key)
            }
        }
    }
}
