<template>
  <label :for="name" class="form-label">{{ label }}</label>
  <Field :name="name" v-model="selectedVal" :validateOnBlur="false"  v-slot="{ field, value, errorMessage }">
    <TomSelect 
      :multiple="multiple"
      v-model="selectedVal"
      :options="{
        placeholder: '- search -',
      }"
      class="relative w-full"
    >
      <option></option>   
      <option v-for="option in formattedOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
    </TomSelect>
    <p class="field-error">{{ errorMessage }}</p>
  </Field>
</template>

<script setup>
import { Field } from 'vee-validate';
import { computed, ref } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  name: { type: String, default: '', required: true },
  placeholder: { type: String, default: '' },
  options: { type: Array, default: () => [] },
  multiple: { type: Boolean, default: false },
  labelField: { type: String, default: 'label' }, 
  valueField: { type: String, default: 'value' }  
})

const selectedVal = ref('')

const formattedOptions = computed(() => props.options.map(option => ({
  label: option[props.labelField],
  value: option[props.valueField]
})));


// const emit = defineEmits(['changed'])

// const fieldChanged = (val) => {
//   console.log(val)
//   emit('changed', val)
// }

</script>
