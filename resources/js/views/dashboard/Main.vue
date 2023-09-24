<template>
  <PageTitle :title="$t('dashboard')"/>
  <!-- BEGIN: Page Layout -->
  <div class="intro-y box p-5 mt-5">{{$t('welcome')}}, {{ adminProfile?.email }}</div>
  <!-- END: Page Layout -->
  <div class="grid grid-cols-4 gap-6">
    <div class="col-span-12 2xl:col-span-9">
      <div class="grid grid-cols-12 gap-6">
        <!-- BEGIN: General Report -->
        <div class="col-span-12 mt-8">
          <div class="intro-y flex items-center h-10">
            <h2 class="text-lg font-medium truncate mr-5">{{$t('general report')}}</h2>
            <!-- <a href="" class="ml-auto flex items-center text-primary">
              <RefreshCcwIcon class="w-4 h-4 mr-3" /> Reload Data
            </a> -->
            <span class="ml-auto text-sm text-gray-500"> (30 days)</span>
          </div>
          <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <WalletIcon class="report-box__icon text-primary" />
                    <div class="ml-auto">
                      <Tippy tag="div" class="report-box__indicator bg-success cursor-pointer"
                        content="33% Higher than last month">
                        33%
                        <ChevronUpIcon class="w-4 h-4 ml-0.5" />
                      </Tippy>
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">176,710</div>
                  <div class="text-base text-slate-500 mt-1">Wallets</div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <CreditCardIcon class="report-box__icon text-pending" />
                    <div class="ml-auto">
                      <Tippy tag="div" class="report-box__indicator bg-danger cursor-pointer"
                        content="2% Lower than last month">
                        2%
                        <ChevronDownIcon class="w-4 h-4 ml-0.5" />
                      </Tippy>
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">400,250</div>
                  <div class="text-base text-slate-500 mt-1">Deposits</div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <MonitorIcon class="report-box__icon text-warning" />
                    <div class="ml-auto">
                      <Tippy tag="div" class="report-box__indicator bg-success cursor-pointer"
                        content="12% Higher than last month">
                        12%
                        <ChevronUpIcon class="w-4 h-4 ml-0.5" />
                      </Tippy>
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">150,000</div>
                  <div class="text-base text-slate-500 mt-1">
                    Withdrawals
                  </div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <UsersIcon class="report-box__icon text-success" />
                    <div class="ml-auto">
                      <Tippy tag="div" class="report-box__indicator bg-success cursor-pointer"
                        content="22% Higher than last month">
                        22%
                        <ChevronUpIcon class="w-4 h-4 ml-0.5" />
                      </Tippy>
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">152,100</div>
                  <div class="text-base text-slate-500 mt-1">
                    Users
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import PageTitle from '@/components/core/PageTitle.vue';
import { useDashboardStore } from '../../stores/dashboard';

const dashboardStore = useDashboardStore();
const adminProfile = computed(() => dashboardStore.adminDetails)

</script>