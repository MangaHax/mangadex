export default class Resource {
  constructor(data = {}, responseCode = -1, responseStatus = null) {
    this._response = {
      code: responseCode,
      status: responseStatus,
    }
    Resource.addResource(this)
  }

  static addResource(resource) {
    const id = resource.id
    const type = resource.constructor.resourceType
    if (id && type) {
      if (!(type in Resource.cache)) {
        Resource.cache[type] = {}
      }
      Resource.cache[type][id] = resource
    }
  }

  static getResource(id) {
    try { return Resource.cache[this.resourceType][id] }
    catch (e) { return null }
  }

  static findResources(filterFn) {
    try { return Object.values(Resource.cache[this.resourceType]).filter(filterFn) }
    catch (e) { return [] }
  }

  static fromJSON(data, responseCode = -1, responseStatus = null, cache = true) {
    const resource = new this(data, responseCode, responseStatus)
    if (cache) {
      Resource.addResource(resource)
    }
    return resource
  }

  static async load(resourceURL, params = {}) {
    try {
      const url = new URL(process.env.API_URL + resourceURL)
      for (let key in params) {
        if (params[key] != null) {
          url.searchParams.append(key, params[key])
        }
      }
      const res = await fetch(url, {
        credentials: 'include',
      })
      if (res.status >= 500) {
        throw new Error("Error while loading a resource. The server may be busy at the moment.")
      }
      return await res.json()
    } catch (e) {
      console.error("Resource loading error:", e)
      throw e
    }
  }

}

Resource.cache = {}