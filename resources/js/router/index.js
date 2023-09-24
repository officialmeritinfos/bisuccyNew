import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "@/views/dashboard/Main.vue";

// DEPOSITS
import FiatDeposits from "@/views/deposits/FiatDeposits.vue";
import Deposits from "@/views/deposits/Main.vue";
import CryptoDeposits from "@/views/deposits/CryptoDeposits.vue";

// WITHDRAWALS 
import FiatWithdrawals from "@/views/withdrawals/FiatWithdrawals.vue";
import CryptoWithdrawals from "@/views/withdrawals/CryptoWithdrawals.vue";

const routes = [
    {
        path: "/dashboard",
        name: "dashboard",
        component: Dashboard,
    },
    // {
    //     path: "/deposits",
    //     name: "deposits",
    //     component: Deposits,
    // },
    {
        path: "/fiat-deposits",
        name: "fiatDeposits",
        component: FiatDeposits,
    },
    {
        path: "/fiat-withdrawals",
        name: "fiatWithdrawals",
        component: FiatWithdrawals,
    },
    {
        path: "/deposits",
        name: "cryptoDeposits",
        component: CryptoDeposits,
    },
    {
        path: "/withdrawals",
        name: "cryptoWithdrawals",
        component: CryptoWithdrawals,
    },
    {
        path: "/settings",
        name: "settings",
        component: Deposits,
    },
];

const adminPrefix = window.appModule.adminPrefix;
function makeBaseUrl() {
    return `${adminPrefix}`;
}

const router = createRouter({
    history: createWebHistory(makeBaseUrl()),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return savedPosition || { left: 0, top: 0 };
    },
});

export default router;
