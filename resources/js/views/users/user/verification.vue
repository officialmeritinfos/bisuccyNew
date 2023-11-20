<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <PageTitle :title="$t('userVerification')" />
  </div>
  <!-- BEGIN: HTML Table Data -->
  <div class="intro-y mt-5">
    <div class="flex justify-end mt-6">

    </div>

    <div class="grid grid-cols-12 gap-6">
      <!-- Verification Info -->
      <div class="col-span-12 box intro-y mt-6">
        <div class="flex items-center px-5 py-5 border-b sm:py-3 border-slate-200/60 dark:border-darkmode-400">
          <h2 class="mr-auto text-base font-medium">{{ $t('profile information') }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-6 p-5">
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">ID Type</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ verificationData?.idType }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">ID Number</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ verificationData?.idNumber }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">ID Expiry</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ verificationData?.expiryDate }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Date Created</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ $h.formatDateFromUnix(verificationData?.dateCreated, 'DD/MM/YYYY') }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Approved By</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ verificationData?.approvedBy }} </div>
            </div>
          </div>
          <div class="relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Note</span>
              <div class="mr-5 text-slate-500 sm:mr-5"> {{ verificationData?.note }} </div>
            </div>
          </div>
          <div class="md:col-span-2 relative flex items-center mt-5">
            <div class="ml-4 mr-auto"><span class="font-medium">Image</span>
              <div class="mr-5 text-slate-500 sm:mr-5">
                <img :src="verificationData?.image" class="w-full h-auto" />
              </div>
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
const verificationData = ref(null);

const route = useRoute();

const router = useRouter();

const userId = route.params.id;


const goToBanks = () => {
  router.push({ name: "userBanks", params: { id: userId } });
};

const goToReferrals = () => {
  router.push({ name: "userReferrals", params: { id: userId } });
};

onMounted(async () => {
  verificationData.value = await userStore.getUserVerification(userId);
});
</script>
