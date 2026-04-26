<template>
  <label style="display: grid; gap: 6px">
    <span class="muted">{{ label }}</span>
    <select class="select" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
      <option v-if="includeAll" value="">{{ emptyLabel }}</option>
      <optgroup v-for="group in groups" :key="group.name" :label="group.name">
        <option v-for="item in group.items" :key="item.slug" :value="item.slug">
          {{ item.name }}
        </option>
      </optgroup>
    </select>
  </label>
</template>

<script setup>
import { CATEGORY_GROUPS } from '../lib/catalog'

defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Категория' },
  includeAll: { type: Boolean, default: true },
  emptyLabel: { type: String, default: 'Все категории' }
})

defineEmits(['update:modelValue'])

const groups = CATEGORY_GROUPS
</script>
