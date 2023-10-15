<template>
  <label> {{ label }} </label>
  <div class="form-switch mt-2">
    <div class="inline-flex justify-center items-center gap-2">
      <p v-if="prefix"> {{ prefix }} </p>
      <input :id="name" :name="name" type="checkbox" class="form-check-input" v-bind="$attrs" />
      <p v-if="suffix"> {{ suffix }} </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, default: '' },
  name: { type: String, default: '' },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
})
</script>