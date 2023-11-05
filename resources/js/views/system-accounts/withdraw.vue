<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <PageTitle :title="$t('withdraw')" />
  </div>

  <Form :validation-schema="validationSchema" @submit="handleSubmit">
    <div class="grid grid-cols-12">
      <div class="col-span-12 lg:col-span-6">
        <div class="intro-y box p-5 mt-5">
          <CreateForm />
          <div class="mt-6 flex justify-end">
            <PrimaryButton type="submit" :text="$t('submit')" custom-class="w-24" />
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
import { useRouter, useRoute } from "vue-router";
import { useSystemAccountsStore } from "@/stores/systemAccounts";
import { useGlobalStore } from "../../stores/global";

// Import the stores
const systemAccountsStore = useSystemAccountsStore();
const globalStore = useGlobalStore();

const router = useRouter();
const pageRoute = useRoute();

const actionType = ref(null); // Use this to control approval and rejection. 1 for approve, 0 for reject.
const approvalPin = computed(() => globalStore.approvalPin);
const postPayload = ref(null);

const handleSubmit = async (values) => {
  postPayload.value = values
  startWithdrawalProcess()
}

// Actions
const startWithdrawalProcess = async () => {
  actionType.value = 1;
  await globalStore.showApprovalPinModal(true);
};

const completeWithdrawalProcess = async () => {

  try {
    await systemAccountsStore.withdrawFromSystemAccount({
      ...postPayload.value,
      asset: 1,
      id: pageRoute.params.id,
      pin: approvalPin.value
    })
    await globalStore.clearApprovalPin();
    router.push({ name: "systemAccounts" });

  } catch (error) {
    console.error(error)
  }

};

watch(() => approvalPin.value, () => {
  // Watch to see if Approval Pin is received, then proceed to perform necessary action.
  if (approvalPin.value && actionType.value === 1) {
    completeWithdrawalProcess();
  }
});



const validationSchema = Yup.object().shape({
  address: Yup.string().required().nullable().label("Address"),
  amount: Yup.number().required().nullable().label("Amount"),
  memo: Yup.string().nullable().label("Memo"),
});



</script>