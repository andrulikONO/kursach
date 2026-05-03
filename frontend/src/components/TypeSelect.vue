<template>
  <label style="display: grid; gap: 6px">
    <span class="muted">{{ label }}</span>
    <CustomSelect
      :model-value="modelValue"
      :options="selectOptions"
      placeholder="Все типы"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </label>
</template>

<script setup>
import { computed } from 'vue'
import CustomSelect from './CustomSelect.vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Тип вещи' },
  types: { type: Array, default: () => ['Одежда', 'Обувь', 'Электроника', 'Дом'] }
})

defineEmits(['update:modelValue'])

const selectOptions = computed(() => [
  { value: '', label: 'Все типы' },
  ...props.types.map((t) => ({ value: t, label: t }))
])
</script>
