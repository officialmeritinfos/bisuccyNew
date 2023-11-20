<template>
  <PageTitle :title="$t('dashboard')"/>
  <!-- BEGIN: Page Layout -->
  <div class="intro-y box p-5 mt-5">{{$t('welcome')}}, {{ adminProfile?.email }}</div>
  <!-- END: Page Layout -->
  <div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-6">
        <!-- BEGIN: General Report -->
        <div class="col-span-12 mt-8">
          <div class="intro-y flex items-center h-10">
            <h2 class="text-lg font-medium truncate mr-5">{{$t('general report')}}</h2>
            <!-- <a href="" class="ml-auto flex items-center text-primary">
              <RefreshCcwIcon class="w-4 h-4 mr-3" /> Reload Data
            </a> -->
            <!-- <span class="ml-auto text-sm text-gray-500"> (30 days)</span> -->
          </div>
          <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 2xl:col-span-2 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <WalletIcon class="report-box__icon text-primary" />
                    <div class="ml-auto">
                      <!-- <Tippy tag="div" class="report-box__indicator bg-success cursor-pointer"
                        content="33% Higher than last month">
                        {{dashboardData?.purchases}}
                        <ChevronUpIcon class="w-4 h-4 ml-0.5" />
                      </Tippy> -->
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">{{dashboardData?.purchases}}</div>
                  <div class="text-base text-slate-500 mt-1">Purchases</div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 2xl:col-span-2 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <CreditCardIcon class="report-box__icon text-pending" />
                    <div class="ml-auto">
                      <!-- <Tippy tag="div" class="report-box__indicator bg-danger cursor-pointer"
                        content="2% Lower than last month">
                        2%
                        <ChevronDownIcon class="w-4 h-4 ml-0.5" />
                      </Tippy> -->
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">{{dashboardData?.deposits}}</div>
                  <div class="text-base text-slate-500 mt-1">Deposits</div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 2xl:col-span-2 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <CreditCardIcon class="report-box__icon text-pending" />
                    <div class="ml-auto">
                      <!-- <Tippy tag="div" class="report-box__indicator bg-danger cursor-pointer"
                        content="2% Lower than last month">
                        2%
                        <ChevronDownIcon class="w-4 h-4 ml-0.5" />
                      </Tippy> -->
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">{{dashboardData?.swaps}}</div>
                  <div class="text-base text-slate-500 mt-1">Swaps</div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 2xl:col-span-2 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <MonitorIcon class="report-box__icon text-warning" />
                    <div class="ml-auto">
                      <!-- <Tippy tag="div" class="report-box__indicator bg-success cursor-pointer"
                        content="12% Higher than last month">
                        12%
                        <ChevronUpIcon class="w-4 h-4 ml-0.5" />
                      </Tippy> -->
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">{{dashboardData?.withdrawals}}</div>
                  <div class="text-base text-slate-500 mt-1">
                    Withdrawals
                  </div>
                </div>
              </div>
            </div>
            <div class="col-span-12 sm:col-span-6 xl:col-span-3 2xl:col-span-2 intro-y">
              <div class="report-box zoom-in">
                <div class="box p-5">
                  <div class="flex">
                    <UsersIcon class="report-box__icon text-success" />
                    <div class="ml-auto">
                      <!-- <Tippy tag="div" class="report-box__indicator bg-success cursor-pointer"
                        content="22% Higher than last month">
                        22%
                        <ChevronUpIcon class="w-4 h-4 ml-0.5" />
                      </Tippy> -->
                    </div>
                  </div>
                  <div class="text-3xl font-medium leading-8 mt-6">{{dashboardData?.users}}</div>
                  <div class="text-base text-slate-500 mt-1">
                    Users
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- END: General Report -->
      </div>
    </div>
    <div class="col-span-12 mt-10"></div>
    <div class="col-span-12 lg:col-span-6">
      <div class="intro-y box p-5 py-8">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-lg font-semibold leading-6 text-gray-900">Recent Purchases</h1>
            </div>
          </div>
          <div v-if="purchases && purchases.length > 0" class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 lg:pl-8">Date</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Asset</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Crypto Amount</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fiat Amount</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="(item, index) in purchases" :key="index">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8">{{ $h.formatDate(item.dateInitiated, 'DD/MM/YYYY') }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.asset }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.cryptoAmount }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.fiatAmount }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.user }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.status }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-span-12 lg:col-span-6">
      <div class="intro-y box p-5 py-8">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-lg font-semibold leading-6 text-gray-900">Recent Sales</h1>
            </div>
          </div>
          <div v-if="purchases && purchases.length > 0" class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="inline-block min-w-full py-2 align-middle">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead>
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 lg:pl-8">Date</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Asset</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Crypto Amount</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fiat Amount</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="(item, index) in sales" :key="index">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8">{{ $h.formatDate(item.dateInitiated, 'DD/MM/YYYY') }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.asset }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.cryptoAmount }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.fiatAmount }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.user }}</td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.status }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import PageTitle from '@/components/core/PageTitle.vue';
import { useDashboardStore } from '../../stores/dashboard';

const dashboardStore = useDashboardStore();
const adminProfile = computed(() => dashboardStore.adminDetails)
const dashboardData = computed(() => dashboardStore.dashboardData)
const dashboardTransactions = computed(() => dashboardStore.dashboardTransactions)

const purchases = ref([])
const sales = ref([])

onMounted(() => {
  dashboardStore.getDashboardData()
  dashboardStore.getDashboardTransactions()
});

watch(() => dashboardTransactions.value, (newValue) => {
  purchases.value = newValue.purchases
  sales.value = newValue.sales
})
</script>