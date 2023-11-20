<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <PageTitle :title="$t('userProfile')" />
  </div>
  <!-- BEGIN: HTML Table Data -->
  <div class="intro-y mt-5">
    <div class="flex justify-end mt-6">
      <Dropdown>
        <DropdownToggle class="btn bg-white">
         <span>User Menu </span>
         <ChevronDownIcon class="w-4 h-4 ml-2"/>
        </DropdownToggle>
        <DropdownMenu class="w-48">
          <DropdownContent>
            <DropdownHeader>User Actions</DropdownHeader>
            <DropdownDivider />
            <DropdownItem @click="goToVerification">
              <FileTextIcon class="w-4 h-4 mr-2" />
              Verification
            </DropdownItem>
            <DropdownItem @click="goToDeposits">
              <TrendingDownIcon class="w-4 h-4 mr-2" />
              Deposits
            </DropdownItem>
            <DropdownItem @click="goToWithdrawals">
              <TrendingUpIcon class="w-4 h-4 mr-2" />
              Crypto Withdrawals
            </DropdownItem>
            <DropdownItem @click="goToFiatWithdrawals">
              <BanknoteIcon class="w-4 h-4 mr-2" />
              Fiat Withdrawals
            </DropdownItem>
            <DropdownItem @click="goToPurchases">
              <BriefcaseIcon class="w-4 h-4 mr-2" />
              Purchases
            </DropdownItem>
            <DropdownItem @click="goToSales">
              <BarChart2Icon class="w-4 h-4 mr-2" />
              Sales
            </DropdownItem>
            <DropdownItem @click="goToSwaps">
              <RefreshCcwIcon class="w-4 h-4 mr-2" />
              Swaps
            </DropdownItem>
            <DropdownItem @click="goToSignals">
              <SignalIcon class="w-4 h-4 mr-2" />
              Signal Purchases
            </DropdownItem>
            <DropdownItem @click="goToBanks">
              <LandmarkIcon class="w-4 h-4 mr-2" />
              Bank Accounts
            </DropdownItem>
            <DropdownItem @click="goToReferrals">
              <UsersIcon class="w-4 h-4 mr-2" />
              Referrals
            </DropdownItem>
          </DropdownContent>
        </DropdownMenu>
      </Dropdown>
    </div>

    <div class="grid grid-cols-12 gap-6">
      <!-- Profile Info -->
      <div class="col-span-12 box intro-y mt-6">
        <div class="flex items-center px-5 py-5 border-b sm:py-3 border-slate-200/60 dark:border-darkmode-400">
          <h2 class="mr-auto text-base font-medium">{{ $t('profile information') }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-6 p-5">
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Name</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.name }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Email</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.email }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Email Verified</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.emailVerified }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Account Balance</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.accountBalance }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Account Currency</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.accountCurrency }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Account Verified</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.accountVerified }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Address</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.address }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Can Buy</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.canBuy }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Can Deposit</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.canDeposit }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Can Sell</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.canSell }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Can Send Crypto</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.canSendCrypto }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Can Swap</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.canSwap }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">City</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.city }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Country</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.country }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Enrolled in Signal</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.enrolledInSignal }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">ID</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.id }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Notification</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.notification }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Package Enrolled</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.packageEnrolled }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Phone</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.phone }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Phone Code</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.phoneCode }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Phone Verified</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.phoneVerified }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Photo</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> <img :src="userProfile?.photo" class="h-10 w-10 rounded-full" /> </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Ref Balance</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.refBalance }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Ref By</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.refBy }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Reference</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.reference }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Referral Earning</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.referralEarning }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Registration IP</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.registrationIp }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">State</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.state }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Time Renew Payment</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.timeRenewPayment }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Two Factor</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ userProfile?.twoFactor }} </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import PageTitle from "@/components/core/PageTitle.vue";
import { useUserStore } from "@/stores/user";
import { useRoute, useRouter } from "vue-router";

// Import the stores
const userStore = useUserStore();
const userProfile = ref(null);

const route = useRoute();

const router = useRouter();

const userId = route.params.id;

const goToDeposits = () => {
  router.push({ name: "userDeposits", params: { id: userId } });
};

const goToWithdrawals = () => {
  router.push({ name: "userWithdrawals", params: { id: userId } });
};

const goToSwaps = () => {
  router.push({ name: "userSwaps", params: { id: userId } });
};

const goToPurchases = () => {
  router.push({ name: "userPurchases", params: { id: userId } });
};

const goToSales = () => {
  router.push({ name: "userSales", params: { id: userId } });
};

const goToSignals = () => {
  router.push({ name: "userSignals", params: { id: userId } });
};

const goToFiatWithdrawals = () => {
  router.push({ name: "userFiatWithdrawals", params: { id: userId } });
};

const goToBanks = () => {
  router.push({ name: "userBanks", params: { id: userId } });
};

const goToReferrals = () => {
  router.push({ name: "userReferrals", params: { id: userId } });
};

const goToVerification = () => {
  router.push({ name: "userVerification", params: { id: userId } });
};

onMounted(async () => {
  userProfile.value = await userStore.getUserProfile(userId);
});
</script>
