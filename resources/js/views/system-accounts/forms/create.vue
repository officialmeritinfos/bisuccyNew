<template>
  <div class="grid gap-6">

    <div>
      <TextField name="address" label="Address" placeholder="" />
    </div>

    <div>
      <TextField type="number" name="amount" label="Amount" placeholder="" />
    </div>

    <div>
      <TextField name="memo" label="Memo" placeholder="" />
    </div>
 
  </div>
</template>

<script setup>
import TextField from '@/components/forms/TextField.vue';

</script>