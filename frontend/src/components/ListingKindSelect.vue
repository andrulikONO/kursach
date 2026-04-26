<template>
  <label style="display: grid; gap: 6px">
    <span class="muted">{{ label }}</span>
    <select class="select" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
      <option v-if="includeAll" value="">{{ emptyLabel }}</option>
      <option v-for="item in options" :key="item.value" :value="item.value">
        {{ item.label }}
      </option>
    </select>
  </label>
</template>

<script setup>
import { LISTING_KIND_OPTIONS } from '../lib/catalog'

defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Тип объявления' },
  includeAll: { type: Boolean, default: true },
  emptyLabel: { type: String, default: 'Покупка и продажа' }
})

defineEmits(['update:modelValue'])

const options = LISTING_KIND_OPTIONS
</script>
