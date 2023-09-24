<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <PageTitle :title="$t('fiatdepositdetail')" />
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <div class="flex mt-5 sm:mt-0 ml-auto">
                <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2" @click="onPrint">
                    <PrinterIcon class="w-4 h-4 mr-2" /> Print
                </button>
                <Dropdown class="w-1/2 sm:w-auto">
                    <DropdownToggle class="btn btn-outline-secondary w-full sm:w-auto">
                        <FileTextIcon class="w-4 h-4 mr-2" /> Export
                        <ChevronDownIcon class="w-4 h-4 ml-auto sm:ml-2" />
                    </DropdownToggle>
                    <DropdownMenu class="w-40">
                        <DropdownContent>
                            <DropdownItem @click="onExportCsv">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export CSV
                            </DropdownItem>
                            <DropdownItem @click="onExportJson">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export JSON
                            </DropdownItem>
                            <DropdownItem @click="onExportXlsx">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export XLSX
                            </DropdownItem>
                            <DropdownItem @click="onExportHtml">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export HTML
                            </DropdownItem>
                        </DropdownContent>
                    </DropdownMenu>
                </Dropdown>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="tabulator" ref="tableRef" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
</template>
  
<script setup>
import { onMounted, ref } from "vue";
import PageTitle from '@/components/core/PageTitle.vue';
import { useDepositsStore } from '../../stores/deposits';
import { computed } from "@vue/reactivity";
import { useRoute } from "vue-router";

const route = useRoute();

const depositStore = useDepositsStore();

const depositId = computed(() => route.params.id);

const depositList = ref([]);


onMounted(async () => {
    depositList.value = depositStore.getFiatDeposit(depositId.value);
});
</script>
  
