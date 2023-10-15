<template>
  <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
      <PageTitle :title="$t('fiatlist')">
        <PrimaryButton :text="$t('createFiat')" @click="goToCreate"/>
      </PageTitle>
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
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import xlsx from "xlsx";
import { TabulatorFull as Tabulator } from "tabulator-tables";
import PageTitle from "@/components/core/PageTitle.vue";
import { useSettingsStore } from "../../stores/settings";
import PrimaryButton from "@/components/core/PrimaryButton.vue";
import { useRouter } from "vue-router";

// Import the stores
const settingsStore = useSettingsStore();

// Declare the variables
const fiatsList = ref([]);
const tableRef = ref();
const tabulator = ref();

const router = useRouter();

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
      data: fiatsList.value,
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
              title: "NAME",
              minWidth: 200,
              responsive: 0,
              field: "name",
              hozAlign: "left",
              vertAlign: "middle",
              headerHozAlign: "left",
          },
          {
              title: "CODE",
              minWidth: 200,
              responsive: 0,
              field: "code",
              hozAlign: "left",
              vertAlign: "middle",
              headerHozAlign: "left",
          },
          {
              title: "SYMBOL",
              minWidth: 200,
              responsive: 0,
              field: "symbol",
              hozAlign: "left",
              vertAlign: "middle",
              headerHozAlign: "left",
          },
          {
              title: "COUNTRY",
              minWidth: 200,
              responsive: 0,
              field: "country",
              vertAlign: "middle",
              hozAlign: "left",
              headerHozAlign: "left",
          },
          {
              title: "BUY RATE",
              minWidth: 200,
              field: "buyRate",
              vertAlign: "middle",
              hozAlign: "left",
              headerHozAlign: "left",
          },
          {
              title: "SELL RATE",
              minWidth: 200,
              responsive: 1,
              field: "fiatEquivalent",
              vertAlign: "middle",
              hozAlign: "left",
              headerHozAlign: "left",
          },
          {
              title: "VERIFIED LIMIT",
              minWidth: 200,
              responsive: 1,
              field: "verifiedLimit",
              vertAlign: "middle",
              hozAlign: "left",
              headerHozAlign: "left",
          },
          {
              title: "UNVERIFIED LIMIT",
              minWidth: 200,
              responsive: 2,
              field: "verifiedLimit",
              vertAlign: "middle",
              hozAlign: "left",
          },
          {
              title: "WITHDRAWAL FEE",
              minWidth: 200,
              responsive: 3,
              field: "withdrawalFee",
              vertAlign: "middle",
              hozAlign: "left",
          },
          {
              title: "CAN WITHDRAW ",
              minWidth: 200,
              responsive: 3,
              field: "canWithdraw",
              vertAlign: "middle",
              hozAlign: "left",
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

const goToCreate = () => {
    router.push({ name: "createFiat" });
};


watch(
    computed(() => fiatsList.value),
    () => {
        initTabulator();
    }
);

onMounted(async () => {
  fiatsList.value = await settingsStore.getFiatList();
  reInitOnResizeWindow();
});
</script>
