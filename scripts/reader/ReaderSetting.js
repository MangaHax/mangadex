import { StoreCookie, StoreLocalStorage } from './Store'

const SettingStore = Modernizr.localstorage ? StoreLocalStorage : StoreCookie

export default class ReaderSetting {
    constructor(name, def, test, parser) {
        this._name = name
        this._default = def
        this._value = def
        this.test = test || ReaderSetting.getTest(def)
        this.parser = parser || ReaderSetting.getParser(def)
    }

    get name() { return this._name }

    get default() { return this._default }

    get value() { return this._value }
    set value(val) {
        const parsedVal = this.parser(val)
        if (this.test(parsedVal)) {
            this._value = parsedVal
        }
    }

    load() {
        this.value = SettingStore.load(this.name)
    }

    save(val) {
        this.value = val
        if (this.value === this.default) {
            SettingStore.clear(this.name)
        } else {
            SettingStore.save(this.name, this.value)
        }
    }

    clear() {
        SettingStore.clear(this.name)
    }

    static getTest(val) {
        switch (typeof val) {
            case 'number': return (val) => !isNaN(val)
            case 'string': return (val) => typeof val === 'string'
            default: return (val) => true
        }
    }

    static getParser(val) {
        switch (typeof val) {
            case 'number': return (val) => parseFloat(val)
            default: return (val) => val
        }
    }

    static clearAllExcept(retainedKeys) {
        SettingStore.clearAllExcept(retainedKeys)
    }
}