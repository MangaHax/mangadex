function updateCustomCheckbox(checkbox) {
  const isTertiary = checkbox.classList.contains('tertiary')
  const state = (parseInt(checkbox.dataset.state) || 0) % (isTertiary ? 3 : 2)
  checkbox.checked = state !== 0
  checkbox.indeterminate = isTertiary ? (state === 2) : (checkbox.checked && checkbox.classList.contains('indeterminate-mark'))
  checkbox.dataset.state = state
}

for (let checkbox of document.querySelectorAll('input[type=checkbox].custom-control-input')) {
  if (checkbox.checked) {
    checkbox.dataset.state = 1
  }
  updateCustomCheckbox(checkbox)
  checkbox.addEventListener('change', function(evt) {
    evt.preventDefault()
    const state = parseInt(this.dataset.state) || 0
    this.dataset.state = state + 1
    updateCustomCheckbox(this)
  })
}