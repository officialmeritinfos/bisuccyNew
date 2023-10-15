<template>
  <div class="flex items-center mr-auto">
    <Field :id="name" :name="name" type="checkbox" @input="fieldChanged" v-model="inputValue" :unchecked-value="false" :value="true" class="form-check-input border mr-2" />
    <label :for="name" class="cursor-pointer select-none">{{ label }}</label>
  </div>
  <p class="field-error">
    <ErrorMessage :name="name" />
  </p>
</template>

<script setup>
import { Field, ErrorMessage } from 'vee-validate';
import { ref } from 'vue';
const props = defineProps({
  label: { type: String, default: '' },
  name: { type: String, default: '', required: true },
  value: { type: Boolean, default: false }
})

const inputValue = ref(props.value)

const emit = defineEmits(['changed'])


const fieldChanged = () => {
  emit('changed', inputValue.value)
}


</script>