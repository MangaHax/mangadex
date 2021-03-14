export default class ChipInput {
  constructor(root) {
    if (!root.dataset.initialized) {
      this.input = root
      this.inputName = this.input.name
      this.input.removeAttribute('name')
      this.wrapper = document.createElement('div')
      this.chipWrapper = document.createElement('div')
      this.values = []

      root.parentElement.replaceChild(this.wrapper, this.input)
      this.wrapper.appendChild(this.input)
      this.wrapper.appendChild(this.chipWrapper)
      this.wrapper.classList.add('chip-input-wrapper')
      this.chipWrapper.classList.add('chip-array')

      this.initialize()
      this.input.dataset.initialized = true

      this.insertDefaults()
    }
  }

  initialize() {
    this.input.value = ''
    this.input.addEventListener('change', evt => {
      this.addValue(evt.target.value)
      evt.target.value = ''
    })
  }

  getText(value) {
    return value
  }

  addValue(value, chipWrapper) {
    if (!this.input.dataset.noTrim || this.input.dataset.noTrim === "0") {
      value = value.trim()
    }
    if (!this.values.find(v => v.value == value)) {
      this.values.push(value)
      return this.addChip(chipWrapper || this.chipWrapper, value)
    }
    return null
  }

  removeValue(value) {
    const index = this.values.findIndex(n => n == value)
    if (index > -1) {
      this.removeChip(value)
      this.values.splice(index, 1)
    }
  }

  getChip(value) {
    return this.chipWrapper.querySelector(`.chip[data-value="${value}"]`)
  }

  addChip(chipWrapper, value) {
    const wrapper = chipWrapper.appendChild(document.createElement('span'))
    wrapper.classList.add('chip')
    wrapper.dataset.value = value
    const badge = wrapper.appendChild(document.createElement('span'))
    badge.textContent = this.getText(value)
    badge.classList.add('badge', 'badge-pill')
    const remove = badge.appendChild(document.createElement('span'))
    remove.classList.add('remove', 'fas', 'fa-times-circle', 'text-light')
    const input = wrapper.appendChild(document.createElement('input'))
    input.type = 'hidden'
    input.value = value
    input.name = this.inputName
    remove.addEventListener('click', evt => {
      this.removeValue(evt.target.closest('.chip').dataset.value)
    })
    return wrapper
  }

  removeChip(value) {
    const chip = this.getChip(value)
    if (chip) {
      chip.remove()
    } else {
      console.error(`Chip ${value} not found`)
    }
  }

  insertDefaults() {
    const sep = this.input.dataset.separator ? new RegExp(this.input.dataset.separator) : ','
    for (let value of this.input.dataset.defaults.split(sep)) {
      if (value) {
        this.addValue(value)
      }
    }
  }

  static initialize(classes = '.chip-input') {
    for (let node of document.querySelectorAll('select'+classes)) {
      new ChipInputSelect(node)
    }
    for (let node of document.querySelectorAll('input[type="text"]'+classes)) {
      new ChipInputText(node)
    }
  }
}

class ChipInputText extends ChipInput {
}

class ChipInputSelect extends ChipInput {
  initialize() {
    this.input.value = ''
    this.input.addEventListener('change', evt => {
      evt.stopPropagation()
      evt.preventDefault()
      if (evt.target.value) {
        this.addValue(evt.target.value)
      }
    })
    if (this.input.dataset.grouped) {
      const optgroupLabels = Array.from(this.input.querySelectorAll('optgroup')).map(o => o.label)
      for (let optgroup of optgroupLabels) {
        const wrapper = document.createElement('div')
        wrapper.classList.add('chip-group', 'd-none')
        wrapper.dataset.label = optgroup
        const label = wrapper.appendChild(document.createElement('span'))
        label.classList.add('label')
        label.textContent = optgroup
        const chips = wrapper.appendChild(document.createElement('div'))
        chips.classList.add('chip-array')
        this.chipWrapper.appendChild(wrapper)
      }
    }
  }

  getText(value) {
    const option = this.getOption(value)
    return option.textContent
    //const optgroup = this.getOptgroupLabel(value)
    //return (optgroup ? optgroup+': ' : '') + option.textContent
  }

  getOption(value) {
    return this.input.querySelector(`option[value="${value}"]`)
  }

  getOptgroup(value) {
    return this.getOption(value).closest('optgroup')
  }

  getChipGroup(label) {
    return this.chipWrapper.querySelector(`.chip-group[data-label="${label}"]`)
  }

  addValue(value) {
    const wrapper = this.input.dataset.grouped ? this.getChipGroup(this.getOptgroup(value).label).querySelector('.chip-array') : this.chipWrapper
    const chip = super.addValue(value, wrapper)
    if (chip) {
      const option = this.getOption(value)
      option.disabled = true
      if (this.input.dataset.selectBehaviour === 'check') {
        option.innerHTML = "&check; " + option.innerHTML
      } else if (this.input.dataset.selectBehaviour === 'hide') {
        option.classList.add('d-none')
      }
      if (option.dataset.chipClasses) {
        chip.querySelector('.badge').classList.add(...option.dataset.chipClasses.split(' '))
      }
      if (this.input.dataset.grouped) {
        this.getChipGroup(this.getOptgroup(value).label).classList.remove('d-none')
      }
    }
  }

  removeValue(value) {
    super.removeValue(value)
    const option = this.getOption(value)
    option.disabled = false
    if (this.input.dataset.selectBehaviour === 'check') {
      option.innerHTML = option.innerHTML.slice(2)
    } else if (this.input.dataset.selectBehaviour === 'hide') {
      option.classList.remove('d-none')
    }
    if (this.input.dataset.grouped) {
      const optgroup = this.getOptgroup(value)
      const chipGroup = this.getChipGroup(optgroup.label)
      chipGroup.classList.toggle('d-none', chipGroup.querySelector('.chip') == null)
    }
  }
}
