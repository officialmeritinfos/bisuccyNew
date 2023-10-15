<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <PageTitle :title="$t('createFiat')" />
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
import PageTitle from "@/components/core/PageTitle.vue";
import CreateForm from './forms/create-fiat.vue';
import PrimaryButton from "@/components/core/PrimaryButton.vue";
import { Form } from 'vee-validate';
import * as Yup from 'yup';
import { useRouter } from "vue-router";
import { useSettingsStore } from "@/stores/settings";

// Import the stores
const settingsStore = useSettingsStore();

const router = useRouter();

const handleSubmit = async (values) => {
    const response = await settingsStore.createFiat(values)
}

const validationSchema = Yup.object().shape({
    name: Yup.string().required().nullable().label("Name"),
    code: Yup.string().required().nullable().label("Code"),
    usdRate: Yup.number().required().nullable().label("USD Rate"),
    ngnRate: Yup.number().required().nullable().label("NGN Rate"),
    buyRate: Yup.number().required().nullable().label("Buy Rate"),
    sellRate: Yup.number().required().nullable().label("Sell Rate"),
    symbol: Yup.string().required().nullable().label("Symbol"),
    country: Yup.string().required().nullable().label("Country"),
    settlementPeriod: Yup.string().required().nullable().label("Settlement Period"),
    verifiedLimit: Yup.number().required().nullable().label("Verified Limit"),
    unverifiedLimit: Yup.number().required().nullable().label("Unverified Limit"),
    withdrawalFee: Yup.number().required().nullable().label("Withdrawal Fee"),
    minAllowed: Yup.number().required().nullable().label("Min Allowed"),
    canWithdraw: Yup.number().required().nullable().label("Can Withdraw").typeError('Invalid value'),
    status: Yup.number().required().nullable().label("Status").typeError('Invalid value'),
});



</script>