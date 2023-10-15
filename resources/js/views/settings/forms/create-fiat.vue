<template>
  <div class="grid lg:grid-cols-2 gap-6">

    <div>
      <TextField name="name" label="Name" placeholder="" />
    </div>

    <div>
      <TextField name="code" label="Code" placeholder="" />
    </div>

    <div>
      <TextField name="usdRate" label="USD Rate" placeholder="" type="number" />
    </div>

    <div>
      <TextField name="ngnRate" label="NGN Rate" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="buyRate" label="Buy Rate" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="sellRate" label="Sell Rate" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="symbol" label="Symbol" placeholder="" />
    </div>

    <div>
      <TextField name="country" label="Country" placeholder="" />
    </div>

    <div>
      <TextField name="settlementPeriod" label="Settlement Period" placeholder="" />
    </div>

    <div>
      <TextField name="verifiedLimit" label="Verified Limit" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="unverifiedLimit" label="Unverified Limit" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="withdrawalFee" label="Withdrawal Fee" placeholder="" type="number"/>
    </div>

    <div>
      <TextField name="minAllowed" label="Mininum Allowed" placeholder="" type="number"/>
    </div>

    <div>
      <SelectField name="canWithdraw" label="Can Withdraw" placeholder="" :options="canWithdrawOptions"/>
    </div>

    <div>
      <SelectField name="status" label="Status" placeholder="" :options="statusOptions"/>
    </div>  
  </div>
</template>

<script setup>
import { ref } from 'vue';
import TextField from '@/components/forms/TextField.vue';
import SelectField from '@/components/forms/SelectField.vue';

const canWithdrawOptions = ref([
  {
    label: 'Yes',
    value: 1
  },
  {
    label: 'No',
    value: 0
  }
])

const statusOptions = ref([
  {
    label: 'Active',
    value: 1
  },
  {
    label: 'Inactive',
    value: 0
  }
])

</script>