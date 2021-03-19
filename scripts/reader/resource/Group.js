import Resource from './Resource'
export default class Group extends Resource {
    static get resourceType() { return 'group' }

    constructor(data = {}, responseCode = -1, responseStatus = null) {
        super(data, responseCode, responseStatus)
        this.id = data.id
        this.name = data.name
    }

    static async load(id, cache = true) {
        const json = await super.load(`group/${id}`, {})
        return this.fromJSON(json.data, json.code, json.status, cache)
    }
}
