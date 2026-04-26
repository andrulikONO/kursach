<template>
  <label style="display: grid; gap: 6px">
    <span class="muted">{{ label }}</span>
    <select class="select" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">{{ emptyLabel }}</option>
      <option v-for="city in cities" :key="city" :value="city">
        {{ city }}
      </option>
    </select>
  </label>
</template>

<script setup>
import { RUSSIAN_CITIES } from '../lib/cities'

defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Город' },
  emptyLabel: { type: String, default: 'Все города' }
})

defineEmits(['update:modelValue'])

const cities = RUSSIAN_CITIES
</script>
