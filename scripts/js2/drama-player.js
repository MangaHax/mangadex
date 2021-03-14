const vtt = require('vtt.js'), WebVTT = vtt.WebVTT

if (typeof window.VTTCue === 'undefined') {
  window.VTTCue = vtt.VTTCue
}

export default class DramaPlayer {
  constructor(el) {
    this.el = el
    this.captionCues = null

    this.elCaptions = null
    this.elTrack = null
    this.audio = el.querySelector('audio')
    this.elSourceSelect = el.querySelector('.source-select')
    this.elCaptionsDisplay = el.querySelector('.captions-display')

    this.updateSourceSelect()
    this.initializeAudio()
    this.reset()

    this.elSourceSelect.addEventListener('change', evt => {
      this.reset()
    })
  }

  get canPlay() {
    return this.hasTrackSelected && this.audio && !isNaN(this.audio.duration)
  }

  get hasTrackSelected() {
    return this.elTrack !== null
  }

  get hasCaptionsFetched() {
    return this.captionCues !== null
  }

  setCaptions(el) {
    if (this.elCaptions) {
      this.elCaptions.classList.remove('selected')
    }
    this.reset()
    this.captionCues = null
    this.elCaptions = el
    this.elCaptions.classList.add('selected')
    this.elTrack = el.closest('.drama-track')
    this.updateSourceSelect()
    this.updateCaptionsDisplay()
    return this.fetchCaptions(this.elCaptions.dataset.captions)
      .then(() => {
        this.updateAudioSource()
        this.updateCaptionsDisplay()
      })
      .catch((err) => {
        console.error(err)
        const span = this.elCaptionsDisplay.appendChild(document.createElement('span'))
        span.textContent = err.message
        span.classList.add('alert', 'alert-danger')
      })
  }

  fetchCaptions(url) {
    return new Promise((resolve, reject) => {
      fetch(url)
        .then(res => {
          if (res.ok) {
            return res.text()
          } else {
            return reject(new Error("Error fetching captions: "+res.statusText))
          }
        })
        .then(txt => {
          this.captionCues = []
          this.updateCaptionsDisplay()
          const parser = new WebVTT.Parser(window, WebVTT.StringDecoder())
          parser.oncue = cue => this.captionCues.push(cue)
          parser.onparsingerror = (err) => {
            console.error(err)
            return reject(new Error("Error parsing captions: "+err.message))
          }
          parser.onflush = resolve
          parser.parse(txt)
          parser.flush()
        })
    })
  }

  reset() {
    this.updateAudioSource()
    //this.updateTime()
    //this.updatePlayButton()
    //this.updateLoadProgress()
  }

  initializeAudio() {
    //this.audio = new Audio()
    this.audio.autoplay = false
    this.audio.preload = "metadata"
    this.audio.addEventListener('timeupdate', evt => this.updateCaptionsDisplay(this.audio.currentTime))
    //this.audio.addEventListener('timeupdate', evt => this.updateTime())
    //this.audio.addEventListener('pause', evt => this.updatePlayButton())
    //this.audio.addEventListener('load', evt => this.updateLoadProgress())
    //this.audio.addEventListener('loadeddata', evt => this.updateLoadProgress())
    //this.audio.addEventListener('loadend', evt => this.updateLoadProgress())
    //this.audio.addEventListener('progress', evt => this.updateLoadProgress())
    this.audio.addEventListener('error', err => {
      console.error(this.audio.error)
      let msg = "Unknown audio error."
      switch (this.audio.error.code) {
        case 1: msg = "Fetching audio was aborted."; break;
        case 2: msg = "Failed to fetch audio due to network error."; break;
        case 3: msg = "Failed to decode audio."; break;
        case 4: msg = "Audio format not supported by the browser."; break;
      }
      this.elCaptionsDisplay.innerHTML = `<span class="alert alert-danger">Error: ${msg}</span>`
    })
    this.updateAudioSource()
  }

  updateAudioSource() {
    this.audio.pause()
    this.audio.removeAttribute('src')
    this.audio.load()

    if (this.hasTrackSelected) {
      this.audio.src = this.elSourceSelect.value
      this.audio.load()
    }
  }

  play() {
    if (!this.audio.src) {
      if (!this.elSourceSelect.value) {
        return
      }
      this.audio.src = this.elSourceSelect.value
      this.audio.load()
    }
    if (this.audio.paused) {
      this.audio.play()
    } else {
      this.audio.pause()
    }
  }

  seek(time) {
    this.audio.currentTime = time
  }

  updateCaptionsDisplay(currentTime = 0) {
    while (this.elCaptionsDisplay.firstChild) {
      this.elCaptionsDisplay.removeChild(this.elCaptionsDisplay.firstChild)
    }
    //this.elCaptionsDisplay.classList.toggle('d-none', !this.hasCaptionsFetched)
    if (!this.hasTrackSelected) {
      this.elCaptionsDisplay.innerHTML = "Select one of captions to begin."
    } else if (!this.hasCaptionsFetched) {
      this.elCaptionsDisplay.innerHTML = "Loading captions..."
    } else if (this.audio.buffered.length === 0) {
      this.elCaptionsDisplay.innerHTML = "Captions loaded. Choose audio format and press play."
    } else {
      const activeCues = this.captionCues.filter(cue => cue.startTime <= currentTime && cue.endTime >= currentTime)
      for (let cue of activeCues) {
        const div = WebVTT.convertCueToDOMTree(window, cue.text)
        if (div) {
          this.elCaptionsDisplay.appendChild(div)
        }
      }
    }
  }

  updateSourceSelect() {
    while (this.elSourceSelect.firstChild) {
      this.elSourceSelect.removeChild(this.elSourceSelect.firstChild)
    }
    this.elSourceSelect.parentElement.classList.toggle('d-none', !this.hasTrackSelected)
    if (this.elTrack) {
      for (let source of this.elTrack.querySelectorAll('source')) {
        const opt = this.elSourceSelect.appendChild(document.createElement('option'))
        opt.textContent = `${source.dataset.format} (${Math.round(source.dataset.size / 1024 / 1024)} MB)`
        opt.value = source.src
      }
    }
  }

  static initialize(playerClass = '.drama-player') {
    const el = document.querySelector(playerClass)
    if (el) {
      const player = new DramaPlayer(el)
      document.addEventListener('click', function(evt) {
        if (evt.target.classList.contains('drama-captions-link')) {
          evt.preventDefault()
          player.setCaptions(evt.target.closest('.track-captions'))
        }
      })
    }
  }
}