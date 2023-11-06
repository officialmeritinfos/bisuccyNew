<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <PageTitle :title="$t('systemAccountWithdrawals')">
            <!-- <PrimaryButton :text="$t('createSystemAccount')" @click="goToCreate"/> -->
        </PageTitle>
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

    <!-- BEGIN: Details slide over -->

    <SlideOver :showSlideOver="showSystemAccountDetails" @hide-slide-over="hideSystemAccountDetails" ref="slideOverComponent">
        <template v-slot:header>
            <h2 class="font-medium text-base mr-auto">
                {{ $t("systemAccountDetails") }} : {{ systemAccountDetails.coinName }} ({{ systemAccountDetails.asset }})
            </h2>
        </template>
        <template v-slot:body>
            <div class="flex flex-col justify-center gap-y divide-y divida-gray-300">
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">CREATED:</dt>
                    <dd>{{ $h.formatDateFromUnix(systemAccountDetails.dateCreated, 'DD/MM/YYYY') }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">BALANCE:</dt>
                    <dd>{{ systemAccountDetails.balance }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">COIN NAME:</dt>
                    <dd>{{ systemAccountDetails.coinName }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">MEMO:</dt>
                    <dd>{{ systemAccountDetails.memo }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">WITHDRAWAL TYPE:</dt>
                    <dd>{{ systemAccountDetails.withdrawalType }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">STATUS:</dt>
                    <dd>{{ systemAccountDetails.status }}</dd>
                </dl>
            </div>
            <div class="w-full flex mt-6">
                <div class="w-full flex flex-wrap justify-center items-center lg:justify-end">
                    <SuccessButtonOutline :text="$t('withdrawals')" @click="startWithdrawalProcess" class="mr-2 mb-2"/>
                    <ApproveButton :text="$t('withdraw')" @click="startWithdrawalProcess" />
                </div>
            </div>
        </template>
    </SlideOver>
    <!-- END: Details slide over -->
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import xlsx from "xlsx";
import { TabulatorFull as Tabulator } from "tabulator-tables";
import PageTitle from "@/components/core/PageTitle.vue";
import { useSystemAccountsStore } from "../../stores/systemAccounts";
import { useGlobalStore } from "../../stores/global";
import { helper as $h } from "@/utils/helper";
import SuccessButtonOutline from "@/components/core/SuccessButtonOutline.vue";
import { useRouter } from "vue-router";
import SlideOver from "@/components/core/SlideOver.vue";
import ApproveButton from "@/components/core/ApproveButton.vue";

// Import the stores
const systemAccountsStore = useSystemAccountsStore();
const globalStore = useGlobalStore();

const router = useRouter();

// Declare the variables
const systemAccountsList = ref([]);
const tableRef = ref();
const tabulator = ref();
const systemAccountDetails = ref({});
const showSystemAccountDetails = ref(false);
const slideOverComponent = ref(null);
const actionType = ref(null); // Use this to control approval and rejection. 1 for approve, 0 for reject.

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
        data: systemAccountsList.value,
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
                title: "DATE CREATED",
                minWidth: 200,
                responsive: 0,
                field: "dateCreated",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
                formatter: function (cell) {
                    return $h.formatDateFromUnix(cell.getValue(), 'DD/MM/YYYY')
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
                title: "NAME",
                minWidth: 200,
                responsive: 0,
                field: "coinName",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "FIAT EQUIVALENT",
                minWidth: 200,
                responsive: 0,
                field: "fiatEquivalent",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "ADDRESS",
                minWidth: 400,
                responsive: 0,
                field: "address",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "MEMO",
                minWidth: 200,
                responsive: 0,
                field: "memo",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "REFERENCE",
                minWidth: 200,
                responsive: 0,
                field: "reference",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "STATUS",
                minWidth: 200,
                responsive: 0,
                field: "status",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
            },
            {
                title: "APPROVED BY",
                minWidth: 200,
                responsive: 0,
                field: "approvedBy",
                hozAlign: "left",
                vertAlign: "middle",
                headerHozAlign: "left",
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

// watch(
//     computed(() => systemAccountsList.value),
//     () => {
//         tabulator.value.setData(systemAccountsList.value);
//     }
// );

const hideSystemAccountDetails = () => {
    showSystemAccountDetails.value = false;
};

// Actions
const startWithdrawalProcess = async () => {
    actionType.value = 1;
    await globalStore.showApprovalPinModal(true);
};

const completeWithdrawalProcess = async () => {
    await systemAccountsStore.approveSystemWithdrawal({
        id: systemAccountDetails.value.id,
        pin: approvalPin.value
    });
    globalStore.clearApprovalPin();
    slideOverComponent.value.hideSlideOver()
};

watch(() => approvalPin.value, () => {
    // Watch to see if Approval Pin is received, then proceed to perform necessary action.
    if (approvalPin.value && actionType.value === 1) {
        completeWithdrawalProcess();
    }
});


onMounted(async () => {
    systemAccountsList.value = await systemAccountsStore.getSystemAccountWithdrawals();
    initTabulator();
    reInitOnResizeWindow();

    tabulator.value.on("rowClick", async function (e, row) {
        systemAccountDetails.value = await systemAccountsStore.approveSystemWithdrawal(
            row._row.data.id
        );
        showSystemAccountDetails.value = true;
    });
});

const goToCreate = () => {
    router.push({ name: "createSystemAccount" });
};
</script>
