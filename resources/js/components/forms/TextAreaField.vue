<template>
  <label :for="name" class="form-label">{{ label }}</label>
  <Field as="textarea" rows="5" :id="name" :name="name" :type="type" class="form-control border-gray-200 rounded w-full resize-none" :placeholder="placeholder"
    :aria-describedby="name" v-bind="$attrs" />
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
  placeholder: { type: String, default: '' }
})
</script>