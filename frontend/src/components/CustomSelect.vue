<template>
  <div class="custom-select" ref="rootEl">
    <div
      ref="triggerEl"
      class="select-trigger"
      :class="{ active: isOpen }"
      @click.stop="toggle"
      tabindex="0"
      @keydown.enter="toggle"
      @keydown.space.prevent="toggle"
    >
      <span :class="{ 'select-placeholder': !modelValue }">
        {{ selectedLabel }}
      </span>
      <span class="select-arrow" :class="{ rotated: isOpen }">▼</span>
    </div>

    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="dropdownEl"
        class="select-dropdown"
        :style="dropdownPos"
        role="listbox"
      >
        <div
          v-for="option in options"
          :key="option.value"
          class="select-option"
          :class="{ selected: modelValue === option.value }"
          role="option"
          @click="selectOption(option.value)"
        >
          {{ option.label }}
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Array, required: true },
  placeholder: { type: String, default: 'Выберите...' },
  required: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

const rootEl = ref(null)
const triggerEl = ref(null)
const dropdownEl = ref(null)
const isOpen = ref(false)
const dropdownPos = ref({})

const selectedLabel = computed(() => {
  const option = props.options.find((opt) => opt.value === props.modelValue)
  return option ? option.label : props.placeholder
})

function syncDropdownPosition() {
  const t = triggerEl.value
  if (!t) return
  const r = t.getBoundingClientRect()
  dropdownPos.value = {
    position: 'fixed',
    top: `${r.bottom + 4}px`,
    left: `${r.left}px`,
    width: `${r.width}px`,
    zIndex: 10000
  }
}

function onDocClick(event) {
  if (!isOpen.value) return
  const root = rootEl.value
  const panel = dropdownEl.value
  if (root?.contains(event.target) || panel?.contains(event.target)) return
  isOpen.value = false
}

function toggle() {
  isOpen.value = !isOpen.value
}

function selectOption(value) {
  emit('update:modelValue', value)
  isOpen.value = false
}

watch(isOpen, (open) => {
  if (open) {
    syncDropdownPosition()
    requestAnimationFrame(syncDropdownPosition)
    window.addEventListener('scroll', syncDropdownPosition, true)
    window.addEventListener('resize', syncDropdownPosition)
    document.addEventListener('click', onDocClick, true)
  } else {
    window.removeEventListener('scroll', syncDropdownPosition, true)
    window.removeEventListener('resize', syncDropdownPosition)
    document.removeEventListener('click', onDocClick, true)
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', syncDropdownPosition, true)
  window.removeEventListener('resize', syncDropdownPosition)
  document.removeEventListener('click', onDocClick, true)
})
</script>

<style scoped>
.custom-select {
  position: relative;
}

.select-trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid var(--border);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: rgba(255, 255, 255, 0.92);
  outline: none;
}

.select-trigger:hover {
  border-color: var(--primary);
}

.select-trigger:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(124, 92, 255, 0.2);
}

.select-trigger.active {
  border-color: var(--primary);
  background: rgba(124, 92, 255, 0.1);
}

.select-arrow {
  font-size: 12px;
  transition: transform 0.2s ease;
  color: var(--muted);
  flex-shrink: 0;
}

.select-arrow.rotated {
  transform: rotate(180deg);
}

.select-dropdown {
  background: #12172a;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
  max-height: min(240px, 50vh);
  overflow-y: auto;
  animation: slideDown 0.2s ease;
  color: rgba(255, 255, 255, 0.92);
}

.select-option {
  padding: 10px 12px;
  cursor: pointer;
  transition: background 0.15s ease;
  color: rgba(255, 255, 255, 0.92);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  font-size: 14px;
}

.select-option:first-child {
  border-radius: 12px 12px 0 0;
}

.select-option:last-child {
  border-bottom: none;
  border-radius: 0 0 12px 12px;
}

.select-option:hover {
  background: rgba(124, 92, 255, 0.35);
}

.select-option.selected {
  background: rgba(124, 92, 255, 0.45);
  color: #9ae8ff;
  font-weight: 500;
}

.select-placeholder {
  color: var(--muted);
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (forced-colors: active) {
  .select-dropdown {
    forced-color-adjust: none;
    background: Canvas;
    color: CanvasText;
    border: 1px solid CanvasText;
  }

  .select-option:hover,
  .select-option.selected {
    background: Highlight;
    color: HighlightText;
  }
}
</style>
