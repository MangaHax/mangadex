/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./scripts/js2/chip-input.js":
/*!***********************************!*\
  !*** ./scripts/js2/chip-input.js ***!
  \***********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return ChipInput; });
class ChipInput {
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
    super.initialize()
    if (this.input.dataset.grouped) {
      for (let optgroup of this.input.querySelectorAll('optgroup')) {
        const wrapper = document.createElement('div')
        wrapper.classList.add('chip-group', 'd-none')
        wrapper.dataset.label = optgroup.label
        const label = wrapper.appendChild(document.createElement('label'))
        label.textContent = optgroup.label
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


/***/ }),

/***/ "./scripts/js2/index.js":
/*!******************************!*\
  !*** ./scripts/js2/index.js ***!
  \******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _chip_input_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./chip-input.js */ "./scripts/js2/chip-input.js");


_chip_input_js__WEBPACK_IMPORTED_MODULE_0__["default"].initialize('.chip-input')

/***/ }),

/***/ 0:
/*!************************************!*\
  !*** multi ./scripts/js2/index.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! ./scripts/js2/index.js */"./scripts/js2/index.js");


/***/ })

/******/ });
//# sourceMappingURL=bundle.dev.js.map