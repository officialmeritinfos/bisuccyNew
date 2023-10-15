<template>
  <label :for="name" class="form-label">{{ label }}</label>
  <template v-if="prefix">
    <div class="input-group">
      <div class="input-group-text">{{ prefix }}</div>
      <Field :id="name" :name="name" :type="type" class="form-control" :placeholder="placeholder" :aria-describedby="name"
        v-bind="$attrs" />
    </div>
  </template>
  <template v-else-if="suffix">
    <div class="input-group">
      <Field :id="name" :name="name" :type="type" class="form-control" :placeholder="placeholder" :aria-describedby="name"
        v-bind="$attrs" />
      <div class="input-group-text">{{ suffix }}</div>
    </div>
  </template>
  <template v-else>
    <Field :id="name" :name="name" :type="type" class="form-control w-full" :placeholder="placeholder"
      :aria-describedby="name" v-bind="$attrs" />
  </template>
  <p class="field-error">
    <ErrorMessage :name="name" />
  </p>
</template>

<script setup>
import { Field, ErrorMessage } from 'vee-validate';

defineProps({
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  name: { type: String, default: '', required: true },
  placeholder: { type: String, default: '' },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
})
</script>