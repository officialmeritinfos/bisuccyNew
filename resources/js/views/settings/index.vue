<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
      <PageTitle :title="$t('settings')" />
  </div>

  <Form :validation-schema="validationSchema" :initial-values="settingsData" @submit="handleSubmit">
      <div class="grid grid-cols-12">
          <div class="col-span-12 lg:col-span-6">
              <div class="intro-y box p-5 mt-5">
                  <SettingsForm />
                  <div class="mt-6 flex justify-end">
                      <PrimaryButton type="submit" :text="$t('submit')" custom-class="w-24"/>
                  </div>
              </div>
          </div>
      </div>
  </Form>
</template>

<script setup>
import PageTitle from "@/components/core/PageTitle.vue";
import SettingsForm from './forms/settings.vue';
import PrimaryButton from "@/components/core/PrimaryButton.vue";
import { Form } from 'vee-validate';
import * as Yup from 'yup';
import { useRouter } from "vue-router";
import { useSettingsStore } from "@/stores/settings";
import { onMounted, ref } from "vue";

// Import the stores
const settingsStore = useSettingsStore();

const router = useRouter();
const settingsData = ref(null)

const handleSubmit = async (values) => {
  const response = await settingsStore.updateSettings(values)
  getDataFromServer()
}

const validationSchema = Yup.object().shape({
  name: Yup.string().required().nullable().label("Name"),
  email: Yup.string().required().nullable().label("Email"),
  phone: Yup.number().required().nullable().label("Phone"),
  maintenance: Yup.number().required().nullable().label("Maintenance").typeError('Invalid value'),
  registration: Yup.number().required().nullable().label("Registration").typeError('Invalid value'),
  emailVerification: Yup.number().required().nullable().label("Email Verification").typeError('Invalid value'),
  phoneVerification: Yup.number().required().nullable().label("Phone Verification").typeError('Invalid value'),
  twoFactor: Yup.number().required().nullable().label("Two Factor").typeError('Invalid value'),
  withdrawalCharge: Yup.number().required().nullable().label("Withdrawal Charge"),
  depositCharge: Yup.number().required().nullable().label("Deposit Charge"),
  sellCharge: Yup.number().required().nullable().label("Sell Charge"),
  buyCharge: Yup.number().required().nullable().label("Buy Rate"),
  canSend: Yup.number().required().nullable().label("Can Send").typeError('Invalid value'),
  canDeposit: Yup.number().required().nullable().label("Can Deposit").typeError('Invalid value'),
  canSell: Yup.number().required().nullable().label("Can Sell").typeError('Invalid value'),
  canBuy: Yup.number().required().nullable().label("Can Buy").typeError('Invalid value'),
  canSwap: Yup.number().required().nullable().label("Can Swap").typeError('Invalid value'),
  mainCurrency: Yup.string().required().nullable().label("Main Currency"),
  refBonus: Yup.number().required().nullable().label("Referral Bonus"),
});

const getDataFromServer = async () => {
  settingsData.value = await settingsStore.getSettings()
}


onMounted(async() => {
  await getDataFromServer();
})


</script>