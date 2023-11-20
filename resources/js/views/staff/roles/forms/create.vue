<template>
  <div class="grid lg:grid-cols-2 gap-6">

    <div class="col-span-2">
      <TextField name="name" label="Name" placeholder="" />
    </div>

    <div class="col-span-2">
      <SelectField name="users" label="Can control user accounts" placeholder="" :options="permissionOptions"/>
    </div> 

    <div class="col-span-2">
      <SelectField name="staff" label="Has access to staff" placeholder="" :options="permissionOptions"/>
    </div> 

    <div class="col-span-2">
      <SelectField name="account" label="Accounting role" placeholder="" :options="permissionOptions"/>
    </div> 

    <div class="col-span-2">
      <SelectField name="fundUser" label="Can fund and debit user" placeholder="" :options="permissionOptions"/>
    </div> 

    <div class="col-span-2">
      <SelectField name="signal" label="Signal room permissions" placeholder="" :options="permissionOptions"/>
    </div> 
 
  </div>
</template>

<script setup>
import { ref } from "vue";
import TextField from '@/components/forms/TextField.vue';
import SelectField from '@/components/forms/SelectField.vue';

const permissionOptions = ref([
  {
    label: 'No',
    value: 0
  },
  {
    label: 'Yes',
    value: 1
  }
]);

</script>