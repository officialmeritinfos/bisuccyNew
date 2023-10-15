<template>
  <label :for="name" class="form-label">{{ label }}</label>
  <Field :name="name" :validateOnBlur="false"  v-slot="{ field, value, errorMessage }">
    <TomSelect 
      :id="name" 
      v-bind="field" 
      :multiple="multiple"
      v-model="field.value"
      :options="{
        placeholder: '- type to search -',
        valueField: valueField,
        labelField: labelField,
        searchField: labelField,
        load: loadQuery
      }"
      class="relative w-full"
      @change="fieldChanged" />
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
  valueField: { type: String, default: 'value' } ,
  loadQuery: { type: Function, default: null } 
})

const selectedVal = ref('')

const formattedOptions = computed(() => props.options.map(option => ({
  label: option[props.labelField],
  value: option[props.valueField]
})));


const emit = defineEmits(['changed'])

const fieldChanged = (val) => {
  emit('changed', val)
}

// const actualLoadQuery = props.loadQuery || ((query, callback) => {
//   // Your built-in implementation here, for example:
//   var url = 'https://api.github.com/search/repositories?q=' + encodeURIComponent(query);
//   fetch(url)
//     .then(response => response.json())
//     .then(json => {
//       callback(json.items);
//     }).catch(() => {
//       callback();
//     });
// });

</script>
