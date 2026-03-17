<template>
  <label style="display: grid; gap: 6px">
    <span class="muted">{{ label }}</span>
    <select class="select" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">Все типы</option>
      <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
    </select>
  </label>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Тип вещи' },
  types: { type: Array, default: () => ['Одежда', 'Обувь', 'Электроника', 'Дом'] }
})
defineEmits(['update:modelValue'])
</script>

