<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <PageTitle :title="$t('cryptowithdrawals')" />
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
    <!-- BEGIN: Details slide over -->
    <SlideOver :showSlideOver="showwithdrawalDetails" @hide-slide-over="hidewithdrawalDetails">
        <template v-slot:header>
            <h2 class="font-medium text-base mr-auto">
                {{ $t('fiatwithdrawaldetail') }} : {{ withdrawalDetails.amount }}
            </h2>
        </template>
        <template v-slot:body>
            <div class="flex flex-col justify-center gap-y divide-y divida-gray-300">
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">DATE:</dt>
                    <dd>{{ $h.formatDate(withdrawalDetails.date, 'DD/MM/YYYY') }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">USER:</dt>
                    <dd>{{ withdrawalDetails.user }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">STATUS:</dt>
                    <dd>{{ withdrawalDetails.status }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">AMOUNT:</dt>
                    <dd>{{ withdrawalDetails.amount }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">FIAT AMOUNT:</dt>
                    <dd>{{ withdrawalDetails.fiatAmount }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">AMOUNT CREDITED:</dt>
                    <dd>{{ withdrawalDetails.amountCredit }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">ACCOUNT NAME:</dt>
                    <dd>{{ withdrawalDetails.accountName }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">ACCOUNT NUMBER:</dt>
                    <dd>{{ withdrawalDetails.accountNumber }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">BANK:</dt>
                    <dd>{{ withdrawalDetails.bank }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">STATUS:</dt>
                    <dd :class="{
                    'text-warning' : withdrawalDetails.status === 'pending payment' || withdrawalDetails.status === 'pending approval',
                    'text-success' : withdrawalDetails.status === 'completed',
                    'text-danger' : withdrawalDetails.status === 'cancelled'
                    }">{{ withdrawalDetails.status }}</dd>
                </dl>
            </div>
        </template>
    </SlideOver>
    <!-- END: Details slide over -->


</template>
  
<script setup>
import { ref, onMounted, watch, computed } from "vue";
import xlsx from "xlsx";
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import PageTitle from '@/components/core/PageTitle.vue';
import SlideOver from '@/components/core/SlideOver.vue';
import { useWithdrawalStore } from '../../stores/withdrawals';
import { helper as $h } from "@/utils/helper";

// Import the stores
const withdrawalStore = useWithdrawalStore();

// Declare the variables
const cryptoWithdrawalsList = computed(() => withdrawalStore.cryptoWithdrawalsList)
const tableRef = ref();
const tabulator = ref();
const withdrawalDetails = ref({});
const showwithdrawalDetails = ref(false);

const initTabulator = () => {
    tabulator.value = new Tabulator(tableRef.value, {
        reactiveData: true,
        printAsHtml: true,
        printStyled: true,
        pagination: "remote",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No matching records found",
        data: cryptoWithdrawalsList.value,
        columns: [
            {
                formatter: "responsiveCollapse",
                width: 40,
                minWidth: 30,
                hozAlign: "center",
                resizable: false,
                headerSort: false,
            },

            // For HTML table
            {
                title: "DATE",
                minWidth: 200,
                responsive: 0,
                field: "date",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
                formatter:function(cell){
                    return $h.formatDate(cell.getValue(), 'DD/MM/YYYY')
                },
            },
            {
                title: "AMOUNT",
                minWidth: 200,
                responsive: 0,
                field: "amount",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "NAME",
                minWidth: 200,
                responsive: 0,
                field: "name",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "ASSET",
                minWidth: 200,
                field: "asset",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "FIAT EQUIVALENT",
                minWidth: 200,
                field: "fiatEquivalent",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "TRANSACTION ID",
                minWidth: 200,
                field: "txId",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "USER",
                minWidth: 200,
                field: "user",
                vertAlign: "middle",
                hozAlign: "left",
            },
        ],
    });
};

// Redraw table onresize
const reInitOnResizeWindow = () => {
    window.addEventListener("resize", () => {
        tabulator.value.redraw();
        // createIcons({
        //     icons,
        //     "stroke-width": 1.5,
        //     nameAttr: "data-lucide",
        // });
    });
};


// Export
const onExportCsv = () => {
    tabulator.value.download("csv", "data.csv");
};

const onExportJson = () => {
    tabulator.value.download("json", "data.json");
};

const onExportXlsx = () => {
    const win = window;
    win.XLSX = xlsx;
    tabulator.value.download("xlsx", "data.xlsx", {
        sheetName: "Products",
    });
};

const onExportHtml = () => {
    tabulator.value.download("html", "data.html", {
        style: true,
    });
};

// Print
const onPrint = () => {
    tabulator.value.print();
};

const hidewithdrawalDetails = () => {
    showwithdrawalDetails.value = false;
}
watch(
    computed(() => cryptoWithdrawalsList.value),
    () => {
        initTabulator()
    }
);

watch(
    computed(() => withdrawalDetails.value),
    () => { }
);

onMounted(async () => {
    await withdrawalStore.getCryptoWithdrawalList()
    reInitOnResizeWindow();
});
</script>
  
