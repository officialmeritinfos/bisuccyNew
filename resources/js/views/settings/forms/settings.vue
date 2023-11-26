<template>
  <div class="grid lg:grid-cols-2 gap-6">

    <div>
      <TextField name="name" label="Name" placeholder="" />
    </div>

    <div>
      <TextField name="email" label="Email" placeholder="" />
    </div>

    <div>
      <TextField name="phone" label="Phone" placeholder="" />
    </div>

    <div>
      <BasicSelectField name="maintenance" label="Maintenance" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="registration" label="Registration" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="emailVerification" label="Email Verification" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="phoneVerification" label="Phone Verification" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="twoFactor" label="Two Factor" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <TextField name="withdrawalCharge" label="Withdrawal Charge" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="depositCharge" label="Deposit Charge" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="sellCharge" label="Sell Charge" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="buyCharge" label="Buy Rate" placeholder="" type="number"/>
    </div>

    <div>
      <BasicSelectField name="canSend" label="Can Send" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="canDeposit" label="Can Deposit" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="canSell" label="Can Sell" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="canBuy" label="Can Buy" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="canSwap" label="Can Swap" placeholder="" :options="dropdownOptions"/>
    </div>

    <div>
      <BasicSelectField name="mainCurrency" label="Main Currency" placeholder="" :options="currencyOptions"/>
    </div>

    <div>
      <TextField name="refBonus" label="Referral Bonus" placeholder="" type="number"/>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import TextField from '@/components/forms/TextField.vue';
import BasicSelectField from '@/components/forms/BasicSelectField.vue';

const dropdownOptions = ref([
  {
    label: 'Yes',
    value: 1
  },
  {
    label: 'No',
    value: 0
  }
])

const currencyOptions = ref([
  {
    label: 'NGN',
    value: 'NGN'
  },
  {
    label: 'USD',
    value: 'USD'
  }
])

// const dropdownOptions = ref([
//   {
//     label: 'Yes',
//     value: true
//   },
//   {
//     label: 'No',
//     value: false
//   }
// ])

</script>