<template>
  <div class="grid lg:grid-cols-2 gap-6">

    <div>
      <TextField name="title" label="Title" placeholder="" />
    </div>

    <div>
      <TextField name="package" label="Package" placeholder="" />
    </div>

    <div>
      <TextField name="photo" label="Photo" placeholder="" type="file" />
    </div>
 
  </div>
</template>

<script setup>
import { ref } from 'vue';
import TextField from '@/components/forms/TextField.vue';
import SelectField from '@/components/forms/SelectField.vue';


</script>