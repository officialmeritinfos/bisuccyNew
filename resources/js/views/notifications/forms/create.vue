<template>
  <div class="grid lg:grid-cols-2 gap-6">

    <div class="col-span-2">
      <TextField name="title" label="Title" placeholder="" />
    </div>

    <div class="col-span-2">
      <TextAreaField name="content" label="Content" placeholder="" type="datetime-local"/>
    </div>

    <div>
      <TextField name="timeToBroadcast" label="Broadcast Time" placeholder="" type="datetime-local"/>
    </div>

    <div>
      <TextField name="type" label="Type" placeholder="" type="number" />
    </div>
 
  </div>
</template>

<script setup>
import { ref } from 'vue';
import TextField from '@/components/forms/TextField.vue';
import TextAreaField from '@/components/forms/TextAreaField.vue';
import SelectField from '@/components/forms/SelectField.vue';


</script>