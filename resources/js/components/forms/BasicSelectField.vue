<template>
  <label :for="name" class="form-label">{{ label }}</label>
  <Field as="select" class="form-control" :name="name" v-model="selectedVal" :validateOnBlur="false"  v-slot="{ field, value, errorMessage }">
      <option></option>   
      <option v-for="option in formattedOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
  </Field>
  <p class="field-error">
    <ErrorMessage :name="name" />
  </p>
</template>

<script setup>
import { Field, ErrorMessage } from 'vee-validate';
import { computed, ref } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  name: { type: String, default: '', required: true },
  placeholder: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  multiple: { type: Boolean, default: false },
  labelField: { type: String, default: 'label' }, 
  valueField: { type: String, default: 'value' },  
})

const selectedVal = ref(props.defaultValue || '')

const formattedOptions = computed(() => props.options.map(option => ({
  label: option[props.labelField],
  value: option[props.valueField]
})));


</script>
