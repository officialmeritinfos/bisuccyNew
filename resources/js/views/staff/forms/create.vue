<template>
  <div class="grid lg:grid-cols-2 gap-6">

    <div class="col-span-2">
      <TextField name="name" label="Name" placeholder="" />
    </div>
 
    <div class="col-span-2">
      <TextField type="email" name="email" label="Email" placeholder="" />
    </div>

    <div class="col-span-2">
      <SelectField name="role" label="Role" labelField="name" valueField="id" placeholder="" :options="roles"/>
    </div> 

    <div class="col-span-2">
      <TextField type="password" name="password" label="Password" placeholder="" />
    </div>
 
  </div>
</template>

<script setup>
import { ref, onMounted} from "vue";
import TextField from '@/components/forms/TextField.vue';
import SelectField from '@/components/forms/SelectField.vue';
import { useStaffStore } from "@/stores/staff";

const roles = ref([]);
const staffStore = useStaffStore();

onMounted(async () => {
  roles.value = await staffStore.getRoles();
});
</script>