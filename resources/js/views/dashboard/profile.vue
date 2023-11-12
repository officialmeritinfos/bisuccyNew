<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <PageTitle :title="$t('profile')" />
  </div>
  <!-- BEGIN: HTML Table Data -->
  <div class="intro-y mt-5">
    <div class="grid grid-cols-12 gap-6">
      <!-- Profile Info -->
      <div class="col-span-12 box intro-y lg:col-span-6">
        <div class="flex items-center px-5 py-5 border-b sm:py-3 border-slate-200/60 dark:border-darkmode-400">
          <h2 class="mr-auto text-base font-medium">{{$t('profile information')}}</h2>
        </div>
        <div class="p-5">
          <div class="relative flex items-center">
            <div class="ml-4 mr-auto"><span class="font-medium">Name</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{adminProfile?.name}} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Email Address</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{adminProfile?.email}} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Phone</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{adminProfile?.phone}} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Reference</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{adminProfile?.reference}} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Pin Set</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{adminProfile?.setPin}} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Two Factor</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{adminProfile?.twoFactor}} </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Password change -->
      <div class="col-span-12 box intro-y lg:col-span-6">
        <div class="flex items-center px-5 py-5 border-b sm:py-3 border-slate-200/60 dark:border-darkmode-400">
          <h2 class="mr-auto text-base font-medium">{{$t('change password')}}</h2>
        </div>
        <div class="p-5">
          <Form :validation-schema="validationSchema" @submit="handleSubmit">
            <CreateForm />
            <div class="mt-6 flex justify-end">
                <PrimaryButton type="submit" :text="$t('submit')" custom-class="w-24"/>
            </div>
          </Form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import PageTitle from "@/components/core/PageTitle.vue";
import CreateForm from './forms/password.vue';
import PrimaryButton from "@/components/core/PrimaryButton.vue";
import { useDashboardStore } from '../../stores/dashboard';
import { Form } from 'vee-validate';
import * as Yup from 'yup';
import { useGlobalStore } from "../../stores/global";

const dashboardStore = useDashboardStore();
const globalStore = useGlobalStore();

const adminProfile = computed(() => dashboardStore.adminDetails)

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
  await dashboardStore.changePassword({
    ...postPayload.value,
    pin: approvalPin.value
  }).then(async () => {
    await globalStore.clearApprovalPin();
    await dashboardStore.getAdminDetails()
  })

};

watch(() => approvalPin.value, () => {
  // Watch to see if Approval Pin is received, then proceed to perform necessary action.
  if (approvalPin.value && actionType.value === 1) {
    completeSubmissionProcess();
  }
});

const validationSchema = Yup.object().shape({
  old_password: Yup.string().required().nullable().label("Old Password"),
  new_password: Yup.string().required().nullable().label("New Password"),
  confirm_password: Yup.string().required().nullable().label("Confirm Password"),
});


</script>
