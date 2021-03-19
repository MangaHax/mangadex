import EventEmitter from 'wolfy87-eventemitter'

export default class ReaderPageModel extends EventEmitter {
    constructor(number, chapterId, url, fallbackURL) {
        super()
        this._number = number
        this._chapter = chapterId
        this._state = ReaderPageModel.STATE_WAIT
        this._error = null
        this._url = url
        this._fallbackURL = fallbackURL
        this._image = new Image()
        this.addImageListeners()
    }

    get number() { return this._number }
    get chapter() { return this._chapter }
    get image() { return this._image }
    get waiting() { return this.state === ReaderPageModel.STATE_WAIT }
    get loading() { return this.state === ReaderPageModel.STATE_LOADING }
    get loaded() { return this.state === ReaderPageModel.STATE_LOADED }
    get hasError() { return this.state === ReaderPageModel.STATE_ERROR }
    get isDone() { return this.loaded || this.hasError }
    get error() { return this._error }
    get state() { return this._state }
    set state(v) {
        this._state = v
        this.trigger('statechange', [this])
    }
    get stateName() {
        switch (this.state) {
            case 0: return 'wait'
            case 1: return 'loading'
            case 2: return 'loaded'
            case 3: return 'error'
        }
    }

    load(breakCache = false) {
        return new Promise((resolve, reject) => {
            if (!breakCache && this.isDone) {
                return resolve(this)
            } else {
                if (!this.loading) {
                    this._error = null

                    this.unload()
                    loadImage(this._url, this._fallbackURL).then(url => {
                          this._image.src = url
                    }).catch(e => {
                        this._error = e
                        this.state = ReaderPageModel.STATE_ERROR
                        reject(this)
                    })

                    this.state = ReaderPageModel.STATE_LOADING
                }
                this.once('statechange', () => {
                    switch (this.state) {
                        case ReaderPageModel.STATE_LOADED: return resolve(this)
                        case ReaderPageModel.STATE_ERROR: return reject(this)
                    }
                })
            }
        })
    }

    unload() {
      try {
        if (this._image.src && this._image.src.startsWith('blob:')) {
          URL.revokeObjectURL(this._image.src)
          this._image.src = ''
        }
      } catch () {}
    }

    addImageListeners() {
        const _errorHandler = () => {
            this._error = new Error(`Image #${this.number} failed to load.`)
            this.state = ReaderPageModel.STATE_ERROR
            this.unload()
        }
        const _loadHandler = () => {
            this.state = ReaderPageModel.STATE_LOADED
            try { this._image.decode() } catch (e) { }
        }
        this._image.addEventListener('error', _errorHandler)
        this._image.addEventListener('load', _loadHandler)
    }

    reload(breakCache = false) {
        return this.load(this.hasError || breakCache)
    }

    static get STATE_WAIT() { return 0 }
    static get STATE_LOADING() { return 1 }
    static get STATE_LOADED() { return 2 }
    static get STATE_ERROR() { return 3 }
}

async function loadImage(primaryURL, fallbackURL) {
    try {
        await caches.delete('mangadex_images')
    } catch () {}

    // Otherwise fetch the image and time how long it takes
    let resp, timing, body
    if (!fallbackURL || primaryURL === fallbackURL || !window.AbortController) {
      // If we only have one URL, load it normally
      ;[resp, timing, body] = await fetchWithTiming(primaryURL, null)
    } else {
      // Otherwise things get weird. We want to race the requests
      // but also get a working response if possible.
      const primaryA = new AbortController()
      const fallbackA = new AbortController()
      // Make a promise for each URL, starting the fallback 5s after the primary
      const primaryP = fetchWithTiming(primaryURL, primaryA.signal).catch(e => {
        return [new Response(null, { status: 599 }), 0, 0]
      })
      const fallbackP = sleep(5000, fallbackA.signal)
        .then(() => {
          return fetchWithTiming(fallbackURL, fallbackA.signal)
        })
        .catch(e => {
          return [new Response(null, { status: 599 }), 0, 0]
        })

      // Get the first response
      ;[resp, timing, body] = await Promise.race([primaryP, fallbackP])

      // If the first response failed, wait for the second
      if (!resp.ok) {
        const results = await Promise.all([primaryP, fallbackP])
        // Find the first good result, if it exists
        ;[resp, timing, body] = results.find(([r]) => r.ok) || results[0]
      }

      // Cancel the other request
      // We need seperate AbortControllers & abort calls since abort also cancels the *response*
      // normalize the URLs so we can actually compare
      if (new URL(resp.url).href === new URL(primaryURL).href) {
        fallbackA.abort()
      } else {
        primaryA.abort()
      }
    }

    // Async report to MD@H server how it went, if applicable
    if (primaryURL && /mangadex\.network/.test(primaryURL)) {
      const success = (new URL(resp.url).href === new URL(primaryURL).href) && resp.ok
      fetch('https://api.mangadex.network/report', {
        method: 'post',
        mode: 'cors',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          url: primaryURL,
          success: success,
          bytes: success ? body.size : 0,
          duration: success ? timing : 0,
          cached: success && resp.headers.get('X-Cache') === 'HIT',
        }),
        keepalive: true, // Keep sending the report even if the user leaves the page as we send it
      })
        .then(res => {
          if (!res.ok) throw res
        })
        .catch(e => null)
    }

    return URL.createObjectURL(body)
  }

  function sleep(ms, signal) {
    return new Promise((resolve) => {
      if (signal.aborted) return resolve()

      const t = setTimeout(resolve, ms)

      signal.addEventListener('abort', () => {
        clearTimeout(t)
        resolve()
      })
    })
  }

  async function fetchWithTiming(url, signal) {
    let resp, body
    const networkImage = /mangadex\.network/.test(url)
    const start = 'performance' in self ? performance.now() : +new Date()
    const hashM = /\/(?:data)\/.*-([0-9a-f]{64})\.[a-z]{3,4}$/.exec(url)

    resp = await fetch(url, {
      mode: 'cors',
      cache: networkImage ? 'no-store' : 'default',
      referrer: 'no-referrer',
      redirect: 'error', // Cross-origin redirects strip required headers, so explicitly error instead
      integrity: hashM ? `sha256-${hex2b64(hashM[1])}` : undefined, // if the image has a hash in it, check that it matches what we got
      signal, // Cancel the fetch if we already got a response
    })
    body = await resp.blob()

    const end = 'performance' in self ? performance.now() : +new Date()
    return [resp, end - start, body]
  }

  function hex2b64(s) {
      let b = ''
      const n = Math.ceil(s.length / 2)
      for (let i = 0, o = 0; i < n; i++, o += 2) {
        b += String.fromCharCode(parseInt(s.substr(o, 2), 16))
      }
      return btoa(b)
  }
