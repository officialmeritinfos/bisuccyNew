<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $t('deposits') }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button class="btn btn-primary shadow-md mr-2">Add New Deposit</button>
            <Dropdown class="ml-auto sm:ml-0">
                <DropdownToggle class="btn px-2 box">
                    <span class="w-5 h-5 flex items-center justify-center">
                        <FilterIcon class="w-4 h-4" />
                    </span>
                </DropdownToggle>
                <DropdownMenu class="w-40">
                    <DropdownContent>
                        <DropdownItem>
                            <Clock12Icon class="w-4 h-4 mr-2" /> Last 24 hours
                        </DropdownItem>
                        <DropdownItem>
                            <CalendarIcon class="w-4 h-4 mr-2" /> Last 7 days
                        </DropdownItem>
                    </DropdownContent>
                </DropdownMenu>
            </Dropdown>
        </div>
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
import { ref, reactive, onMounted, watch } from "vue";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import {TabulatorFull as Tabulator} from 'tabulator-tables';
import PageTitle from '@/components/core/PageTitle.vue';
import { useDepositsStore } from '../../stores/deposits';
import { computed } from "@vue/reactivity";

const depositStore = useDepositsStore();

const depositsList = computed(() => depositStore.depositsList)

const tableRef = ref();
const tabulator = ref();
const filter = reactive({
    field: "name",
    type: "like",
    value: "",
});

const imageAssets = import.meta.globEager(
    `@/assets/images/*.{jpg,jpeg,png,svg}`
);
const initTabulator = () => {
    tabulator.value = new Tabulator(tableRef.value, {
        reactiveData:true,
        printAsHtml: true,
        printStyled: true,
        pagination: "remote",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],        
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No matching records found",
        data: depositsList.value,
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
                title: "ASSET",
                minWidth: 200,
                responsive: 0,
                field: "asset",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
                print: false,
                download: true,
            },
            {
                title: "AMOUNT",
                minWidth: 200,
                responsive: 0,
                field: "amount",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
                print: false,
                download: true,
            },
            {
                title: "FIAT EQUIVALENT",
                minWidth: 200,
                field: "fiatEquivalent",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
                print: false,
                download: true,
            },
            {
                title: "USER",
                minWidth: 200,
                field: "user",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
                print: false,
                download: true,
            },
            {
                title: "TRANX ID",
                minWidth: 200,
                field: "txId",
                vertAlign: "middle",
                hozAlign: "left",
                print: false,
                download: true,
                formatter(cell){
                    return `<span class="max-w-sm truncate">${cell.getData().txId}</span>`
                }
            },

            // For print format
            {
                title: "ASSET",
                field: "asset",
                visible: false,
                print: true,
                download: true,
            },
            {
                title: "AMOUNT",
                field: "amount",
                visible: false,
                print: true,
                download: true,
            },
            {
                title: "FIAT EQUIVALENT",
                field: "fiatEquivalent",
                visible: false,
                print: true,
                download: true,
            },
            {
                title: "USER",
                field: "user",
                visible: false,
                print: true,
                download: true,
            },
            {
                title: "TRANX ID",
                field: "txId",
                visible: false,
                print: true,
                download: true,

            }
        ]
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

watch(
    computed(() => depositsList.value),
    () => {
        initTabulator()
    }
);

// Print
const onPrint = () => {
    tabulator.value.print();
};

onMounted(async () => {
    await depositStore.getDepositsList();
    reInitOnResizeWindow();
});
</script>
  
