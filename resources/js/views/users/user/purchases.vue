<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <PageTitle :title="$t('userPurchases')" />
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
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export
                                JSON
                            </DropdownItem>
                            <DropdownItem @click="onExportXlsx">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export
                                XLSX
                            </DropdownItem>
                            <DropdownItem @click="onExportHtml">
                                <FileTextIcon class="w-4 h-4 mr-2" /> Export
                                HTML
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
import { ref, onMounted, watch, computed } from "vue";
import xlsx from "xlsx";
import { TabulatorFull as Tabulator } from "tabulator-tables";
import PageTitle from "@/components/core/PageTitle.vue";
import { useUserStore } from "@/stores/user";
import { helper as $h } from "@/utils/helper";
import { useRoute } from "vue-router";

// Import the stores
const userStore = useUserStore();

// Declare the variables
const tableData = ref([]);
const tableRef = ref();
const tabulator = ref();

const route = useRoute();

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
        data: tableData.value,
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
                field: "dateInitiated",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
                formatter: function (cell) {
                    return $h.formatDate(cell.getValue(), 'DD/MM/YYYY')
                },
            },
            {
                title: "ASSET",
                minWidth: 200,
                responsive: 0,
                field: "asset",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "FIAT",
                minWidth: 200,
                responsive: 0,
                field: "fiat",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "CRYPTO AMOUNT",
                minWidth: 200,
                responsive: 0,
                field: "cryptoAmount",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "FIAT AMOUNT",
                minWidth: 200,
                responsive: 0,
                field: "fiatAmount",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "NGN RATE",
                minWidth: 200,
                field: "ngnRate",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "RATE GIVEN",
                minWidth: 200,
                responsive: 1,
                field: "rateGiven",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "REFERENCE",
                minWidth: 200,
                responsive: 1,
                field: "reference",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "USER",
                minWidth: 200,
                responsive: 1,
                field: "user",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "STATUS",
                minWidth: 200,
                responsive: 4,
                field: "status",
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

watch(
    computed(() => tableData.value),
    () => {
        initTabulator();
    }
);

onMounted(async () => {
    tableData.value = await userStore.getUserPurchases(route.params.id);
    reInitOnResizeWindow();
});
</script>
