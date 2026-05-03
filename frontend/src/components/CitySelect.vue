<template>
  <label style="display: grid; gap: 6px">
    <span class="muted">{{ label }}</span>
    <CustomSelect
      :model-value="modelValue"
      :options="selectOptions"
      :placeholder="emptyLabel"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </label>
</template>

<script setup>
import { computed } from 'vue'
import CustomSelect from './CustomSelect.vue'
import { RUSSIAN_CITIES } from '../lib/cities'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Город' },
  emptyLabel: { type: String, default: 'Все города' }
})

defineEmits(['update:modelValue'])

const selectOptions = computed(() => [
  { value: '', label: props.emptyLabel },
  ...RUSSIAN_CITIES.map((city) => ({ value: city, label: city }))
])
</script>
