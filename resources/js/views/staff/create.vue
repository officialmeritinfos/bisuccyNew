<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
      <PageTitle :title="$t('createStaff')" />
  </div>

  <Form :validation-schema="validationSchema" @submit="handleSubmit">
      <div class="grid grid-cols-12">
          <div class="col-span-12 lg:col-span-6">
              <div class="intro-y box p-5 mt-5">
                  <CreateForm />
                  <div class="mt-6 flex justify-end">
                      <PrimaryButton type="submit" :text="$t('submit')" custom-class="w-24"/>
                  </div>
              </div>
          </div>
      </div>
  </Form>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import PageTitle from "@/components/core/PageTitle.vue";
import CreateForm from './forms/create.vue';
import PrimaryButton from "@/components/core/PrimaryButton.vue";
import { Form } from 'vee-validate';
import * as Yup from 'yup';
import { useRouter } from "vue-router";
import { useStaffStore } from "@/stores/staff";
import { useGlobalStore } from "@/stores/global";

// Import the stores
const staffStore = useStaffStore();
const globalStore = useGlobalStore();

const router = useRouter();


const actionType = ref(null); // Use this to control approval and rejection. 1 for approve, 0 for reject.
const approvalPin = computed(() => globalStore.approvalPin);
const postPayload = ref(null);

const handleSubmit = async (values) => {
  postPayload.value = values
  startSubmissionProcess()
}

// Actions
const startSubmissionProcess = async () => {
  actionType.value = 1;
  await globalStore.showApprovalPinModal(true);
};

const completeSubmissionProcess = async () => {
  await staffStore.createStaff({
    ...postPayload.value,
    pin: approvalPin.value
  }).then(async () => {
    await globalStore.clearApprovalPin();
    router.push({ name: "staff" });
  })

};

watch(() => approvalPin.value, () => {
  // Watch to see if Approval Pin is received, then proceed to perform necessary action.
  if (approvalPin.value && actionType.value === 1) {
    completeSubmissionProcess();
  }
});

const validationSchema = Yup.object().shape({
  name: Yup.string().required().nullable().label("Name"),
  email: Yup.string().email().required().nullable().label("Email"),
  password: Yup.string().required().nullable().label("Password"),
  role: Yup.number().required().nullable().label("Role").typeError('Invalid value'),
});



</script>