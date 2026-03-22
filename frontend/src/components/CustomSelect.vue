<template>
  <div class="custom-select" v-click-outside="close">
    <div 
      class="select-trigger" 
      :class="{ active: isOpen }"
      @click="toggle"
      tabindex="0"
      @keydown.enter="toggle"
      @keydown.space.prevent="toggle"
    >
      <span :class="{ 'select-placeholder': !modelValue }">
        {{ selectedLabel }}
      </span>
      <span class="select-arrow" :class="{ rotated: isOpen }">▼</span>
    </div>

    <div v-if="isOpen" class="select-dropdown">
      <div 
        v-for="option in options" 
        :key="option.value"
        class="select-option"
        :class="{ selected: modelValue === option.value }"
        @click="selectOption(option.value)"
      >
        {{ option.label }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Array, required: true },
  placeholder: { type: String, default: 'Выберите...' },
  required: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

const selectedLabel = computed(() => {
  const option = props.options.find(opt => opt.value === props.modelValue)
  return option ? option.label : props.placeholder
})

function toggle() {
  isOpen.value = !isOpen.value
}

function close() {
  isOpen.value = false
}

function selectOption(value) {
  emit('update:modelValue', value)
  isOpen.value = false
}

// Директива для закрытия при клике вне
const vClickOutside = {
  mounted: (el, binding) => {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted: (el) => {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
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
  color: var(--text);
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
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #1a1f35;
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
  z-index: 1000;
  max-height: 240px;
  overflow-y: auto;
  animation: slideDown 0.2s ease;
}

.select-option {
  padding: 10px 12px;
  cursor: pointer;
  transition: background 0.15s ease;
  color: var(--text);
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
  background: rgba(124, 92, 255, 0.3);
}

.select-option.selected {
  background: rgba(124, 92, 255, 0.4);
  color: var(--primary-2);
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
</style>