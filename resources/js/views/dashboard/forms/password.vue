<template>
  <div class="grid gap-6">
    <div>
      <TextField type="password" name="old_password" label="Old Password" placeholder="" />
    </div>

    <div>
      <TextField type="password" name="new_password" label="New Password" placeholder="" />
    </div>

    <div>
      <TextField type="password" name="confirm_password" label="Confirm Password" placeholder="" />
    </div>
 
  </div>
</template>

<script setup>
import TextField from '@/components/forms/TextField.vue';

</script>