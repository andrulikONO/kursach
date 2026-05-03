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
import { LISTING_KIND_OPTIONS } from '../lib/catalog'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Тип объявления' },
  includeAll: { type: Boolean, default: true },
  emptyLabel: { type: String, default: 'Покупка и продажа' }
})

defineEmits(['update:modelValue'])

const selectOptions = computed(() => {
  const items = LISTING_KIND_OPTIONS.map((o) => ({ value: o.value, label: o.label }))
  if (props.includeAll) {
    return [{ value: '', label: props.emptyLabel }, ...items]
  }
  return items
})
</script>
