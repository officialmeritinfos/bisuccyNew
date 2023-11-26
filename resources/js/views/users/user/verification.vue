<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <PageTitle :title="$t('userVerification')" />
  </div>
  <!-- BEGIN: HTML Table Data -->
  <div class="intro-y mt-5">
    <div class="flex justify-end mt-6">
      <ApproveButton @click="startApprovalProcess" />
      <RejectButton @click="startRejectionProcess" />
      
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
import { ref, computed, watch, onMounted } from "vue";
import PageTitle from "@/components/core/PageTitle.vue";
import ApproveButton from "@/components/core/ApproveButton.vue";
import RejectButton from "@/components/core/RejectButton.vue";
import { useUserStore } from "@/stores/user";
import { useGlobalStore } from "@/stores/global";
import { useRoute, useRouter } from "vue-router";

// Import the stores
const userStore = useUserStore();
const globalStore = useGlobalStore();
const verificationData = ref(null);
const actionType = ref(null); // Use this to control approval and rejection. 1 for approve, 0 for reject.
const approvalPin = computed(() => globalStore.approvalPin);

const route = useRoute();
const router = useRouter();

const userId = route.params.id;


// Actions
const startApprovalProcess = async () => {
    actionType.value = 1;
    await globalStore.showApprovalPinModal(true);
};

const completeApprovalProcess = async () => {
    await userStore.approveUserVerification({
      docId: verificationData.value.id,
        pin: approvalPin.value
    });
    globalStore.clearApprovalPin();
};


const startRejectionProcess = async () => {
    actionType.value = 0;
    await globalStore.showApprovalPinModal(true);
};

const completeRejectionProcess = async () => {
    await userStore.rejectUserVerification({
      docId: verificationData.value.id,
        pin: approvalPin.value
    });
    globalStore.clearApprovalPin();
};

watch(() => approvalPin.value, () => {
    // Watch to see if Approval Pin is received, then proceed to perform necessary action.
    if (approvalPin.value && actionType.value === 1) {
        completeApprovalProcess();
    }

    if (approvalPin.value && actionType.value === 0) {
        completeRejectionProcess();
    }
});

onMounted(async () => {
  verificationData.value = await userStore.getUserVerification(userId);
});
</script>
