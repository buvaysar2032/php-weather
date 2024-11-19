import { Directive, DirectiveBinding } from 'vue'
import Popover from 'bootstrap/js/dist/popover'

function resolveTriggers(modifiers: DirectiveBinding['modifiers']): Popover.Options['trigger'][] {
  const triggers: Popover.Options['trigger'][] = []
  if (modifiers.manual) {
    triggers.push('manual')
  } else {
    if (modifiers.click) triggers.push('click')
    if (modifiers.hover) triggers.push('hover')
    if (modifiers.focus) triggers.push('focus')
  }
  return triggers
}

function resolvePlacement(modifiers: DirectiveBinding['modifiers']): Popover.Options['placement'] {
  if (modifiers.left) return 'left'
  if (modifiers.right) return 'right'
  if (modifiers.bottom) return 'bottom'
  if (modifiers.top) return 'top'
  return 'auto'
}

const vBsPopover: Directive<HTMLElement, string> = {
  mounted(el, binding) {
    const triggers = resolveTriggers(binding.modifiers)
    const placement = resolvePlacement(binding.modifiers)
    const isHtml = /<("[^"]*"|'[^']*'|[^'">])*>/.test(binding.value)
    el.setAttribute('data-bs-toggle', 'popover')
    el.setAttribute('data-bs-content', binding.value)
    new Popover(el, {
      trigger: triggers.length === 0 ? 'click' : (triggers.join(' ') as Popover.Options['trigger']),
      placement,
      content: binding.value,
      html: isHtml,
    })
  },
  updated(el, binding) {
    if (el.getAttribute('data-bs-content') === binding.value) return
    const title = el.getAttribute('title')
    const instance = Popover.getInstance(el)
    instance?.dispose()
    const triggers = resolveTriggers(binding.modifiers)
    const placement = resolvePlacement(binding.modifiers)
    const isHtml = /<("[^"]*"|'[^']*'|[^'">])*>/.test(binding.value)
    el.setAttribute('data-bs-content', binding.value)
    el.setAttribute('title', title || '')
    new Popover(el, {
      trigger: triggers.length === 0 ? 'click' : (triggers.join(' ') as Popover.Options['trigger']),
      placement,
      content: binding.value,
      html: isHtml,
    })
  },
  beforeUnmount(el) {
    const instance = Popover.getInstance(el)
    instance?.dispose()
  },
}

export default vBsPopover
