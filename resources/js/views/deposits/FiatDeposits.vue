<template>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <PageTitle :title="$t('fiatdeposits')" />
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <div class="flex mt-5 sm:mt-0 ml-auto">
                <button
                    id="tabulator-print"
                    class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2"
                    @click="onPrint"
                >
                    <PrinterIcon class="w-4 h-4 mr-2" /> Print
                </button>
                <Dropdown class="w-1/2 sm:w-auto">
                    <DropdownToggle
                        class="btn btn-outline-secondary w-full sm:w-auto"
                    >
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
            <div
                id="tabulator"
                ref="tableRef"
                class="mt-5 table-report table-report--tabulator"
            ></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Details slide over -->

    <SlideOver
        :showSlideOver="showDepositDetails"
        @hide-slide-over="hideDepositDetails"
        ref="slideOverComponent"
    >
        <template v-slot:header>
            <h2 class="font-medium text-base mr-auto">
                {{ $t("fiatdepositdetail") }} : {{ depositDetails.amount }}
            </h2>
        </template>
        <template v-slot:body>
            <div
                class="flex flex-col justify-center gap-y divide-y divida-gray-300"
            >
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">DATE:</dt>
                    <dd>{{ $h.formatDateFromUnix(depositDetails.date, 'DD/MM/YYYY') }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">USER:</dt>
                    <dd>{{ depositDetails.user }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">STATUS:</dt>
                    <dd>{{ depositDetails.status }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">AMOUNT:</dt>
                    <dd>{{ depositDetails.amount }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">AMOUNT PAID:</dt>
                    <dd>{{ depositDetails.amountPaid }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">ACCOUNT NAME:</dt>
                    <dd>{{ depositDetails.accountName }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">
                        ACCOUNT NUMBER:
                    </dt>
                    <dd>{{ depositDetails.accountNumber }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">BANK:</dt>
                    <dd>{{ depositDetails.bank }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">
                        SYSTEM ACCOUNT:
                    </dt>
                    <dd>{{ depositDetails.systemAccount }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">
                        SYSTEM ACCOUNT NAME:
                    </dt>
                    <dd>{{ depositDetails.systemAccountName }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">
                        SYSTEM ACCOUNT BANK:
                    </dt>
                    <dd>{{ depositDetails.systemAccountBank }}</dd>
                </dl>
                <dl class="flex items-center justify-between px-2 py-4">
                    <dt class="text-xs font-medium uppercase">
                        STATUS:
                    </dt>
                    <dd :class="{
                    'text-warning' : depositDetails.status === 'pending approval',
                    'text-danger' : depositDetails.status === 'cancelled',
                    'text-success' : depositDetails.status === 'completed'
                    }">{{ depositDetails.status }}</dd>
                </dl>
            </div>
            <div v-if="depositDetails.status === 'pending approval'" class="w-full flex mt-6">
                <div
                    class="w-full flex flex-wrap justify-center lg:justify-end"
                >
                    <ApproveButton @click="startApprovalProcess" />
                    <RejectButton @click="startRejectionProcess" />
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
import SlideOver from "@/components/core/SlideOver.vue";
import ApproveButton from "@/components/core/ApproveButton.vue";
import RejectButton from "@/components/core/RejectButton.vue";
import { useDepositsStore } from "@/stores/deposits";
import { useGlobalStore } from "@/stores/global";
import { helper as $h } from "@/utils/helper";

// Import the stores
const depositStore = useDepositsStore();
const globalStore = useGlobalStore();

// Declare the variables
const fiatDepositList = computed(() => depositStore.fiatDepositsList);
const approvalPin = computed(() => globalStore.approvalPin);
const actionType = ref(null); // Use this to control approval and rejection. 1 for approve, 0 for reject.
const tableRef = ref();
const tabulator = ref();
const depositDetails = ref({});
const showDepositDetails = ref(false);
const slideOverComponent = ref(null);

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
        data: fiatDepositList.value,
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
                    return $h.formatDateFromUnix(cell.getValue(), 'DD/MM/YYYY')
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
                title: "ACCOUNT NAME",
                minWidth: 200,
                responsive: 0,
                field: "accountName",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "ACCOUNT NUMBER",
                minWidth: 200,
                field: "accountNumber",
                vertAlign: "middle",
                hozAlign: "left",
                headerHozAlign: "left",
            },
            {
                title: "BANK",
                minWidth: 200,
                field: "bank",
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

const hideDepositDetails = () => {
    showDepositDetails.value = false;
};

// Actions
const startApprovalProcess = async () => {
    actionType.value = 1;
    await globalStore.showApprovalPinModal(true);
};

const completeApprovalProcess = async () => {
    await depositStore.approveFiatDeposit({
        id: depositDetails.value.id,
        pin: approvalPin.value
    });
    globalStore.clearApprovalPin();
    slideOverComponent.value.hideSlideOver()
};


const startRejectionProcess = async () => {
    actionType.value = 0;
    await globalStore.showApprovalPinModal(true);
};

const completeRejectionProcess = async () => {
    await depositStore.rejectFiatDeposit({
        id: depositDetails.value.id,
        pin: approvalPin.value
    });
    globalStore.clearApprovalPin();
    slideOverComponent.value.hideSlideOver()
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

watch(
    computed(() => fiatDepositList.value),
    () => {
        initTabulator();
    }
);

watch(
    computed(() => depositDetails.value),
    () => {}
);

onMounted(async () => {
    await depositStore.getFiatDepositList();
    reInitOnResizeWindow();

    tabulator.value.on("rowClick", async function (e, row) {
        depositDetails.value = await depositStore.getFiatDeposit(
            row._row.data.id
        );
        showDepositDetails.value = true;
    });
});
</script>
