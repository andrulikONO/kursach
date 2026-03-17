<template>
  <div class="card">
    <div class="card__body" style="display: grid; gap: 12px">
      <div class="split">
        <div>
          <div class="title">Фильтры</div>
          <div class="muted" style="font-size: 13px">Поиск, тип, цена</div>
        </div>
        <button class="btn" type="button" @click="reset">Сброс</button>
      </div>

      <label style="display: grid; gap: 6px">
        <span class="muted">Поиск</span>
        <input class="input" :value="model.q" @input="set('q', $event.target.value)" placeholder="например: iPhone, кроссовки..." />
      </label>

      <TypeSelect :model-value="model.type" @update:modelValue="set('type', $event)" />

      <div class="row">
        <label style="display: grid; gap: 6px">
          <span class="muted">Цена от</span>
          <input class="input" inputmode="numeric" :value="model.minPrice" @input="set('minPrice', $event.target.value)" placeholder="0" />
        </label>

        <label style="display: grid; gap: 6px">
          <span class="muted">Цена до</span>
          <input class="input" inputmode="numeric" :value="model.maxPrice" @input="set('maxPrice', $event.target.value)" placeholder="100000" />
        </label>
      </div>

      <button class="btn btn--primary" type="button" @click="$emit('apply', normalized)">Применить</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import TypeSelect from './TypeSelect.vue'

const props = defineProps({
  model: {
    type: Object,
    required: true
  }
})
const emit = defineEmits(['update:model', 'apply'])

function set(key, value) {
  emit('update:model', { ...props.model, [key]: value })
}

function reset() {
  emit('update:model', { q: '', type: '', minPrice: '', maxPrice: '' })
  emit('apply', { q: '', type: '', minPrice: '', maxPrice: '' })
}

const normalized = computed(() => ({
  q: (props.model.q || '').trim(),
  type: props.model.type || '',
  minPrice: props.model.minPrice === '' ? '' : Number(props.model.minPrice),
  maxPrice: props.model.maxPrice === '' ? '' : Number(props.model.maxPrice)
}))
</script>

