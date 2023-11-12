<template>
  <div class="grid gap-6">

    <div>
      <TextField name="bank" label="Bank" placeholder="" />
    </div>

    <div>
      <TextField type="number" name="accountNumber" label="Account Number" placeholder="" />
    </div>

    <div>
      <TextField name="accountName" label="Account Name" placeholder="" />
    </div>
 
  </div>
</template>

<script setup>
import TextField from '@/components/forms/TextField.vue';

</script>