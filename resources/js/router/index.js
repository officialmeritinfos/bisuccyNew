import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "@/views/dashboard/Main.vue";
import Profile from "@/views/dashboard/profile.vue";

// DEPOSITS
import FiatDeposits from "@/views/deposits/FiatDeposits.vue";
import Deposits from "@/views/deposits/Main.vue";
import CryptoDeposits from "@/views/deposits/CryptoDeposits.vue";

// WITHDRAWALS 
import FiatWithdrawals from "@/views/withdrawals/FiatWithdrawals.vue";
import CryptoWithdrawals from "@/views/withdrawals/CryptoWithdrawals.vue";

// PURCHASES
import Purchases from "@/views/purchases/index.vue";

// SALES
import Sales from "@/views/sales/index.vue";

// SWAPS
import Swaps from "@/views/swaps/index.vue";

// SETTINGS 
import FiatList from "@/views/settings/FiatList.vue";
import CreateFiat from "@/views/settings/CreateFiat.vue";

// USERS
import Users from "@/views/users/index.vue";
import Wallets from "@/views/users/wallets.vue";
import Banks from "@/views/users/banks.vue";
import UserProfile from "@/views/users/user/index.vue";
import UserWithdrawals from "@/views/users/user/withdrawals.vue";
import UserDeposits from "@/views/users/user/deposits.vue";
import UserSwaps from "@/views/users/user/swaps.vue";
import UserPurchases from "@/views/users/user/purchases.vue";
import UserSales from "@/views/users/user/sales.vue";
import UserSignals from "@/views/users/user/signals.vue";
import UserFiatWithdrawals from "@/views/users/user/fiat-withdrawals.vue";
import UserBanks from "@/views/users/user/banks.vue";
import UserReferrals from "@/views/users/user/referrals.vue";
import UserVerification from "@/views/users/user/verification.vue";


// SIGNALS
import Signals from "@/views/signals/index.vue";
import CreateSignal from "@/views/signals/create.vue";


// SIGNALS
import Messages from "@/views/messages/index.vue";
import CreateMessage from "@/views/messages/create.vue";

// NOTIFICATIONS
import Notifications from "@/views/notifications/index.vue";
import CreateNotification from "@/views/notifications/create.vue";

// SYSTEM ACCOUNTS
import SystemAccounts from "@/views/system-accounts/index.vue";
import SystemAccountsWithdrawals from "@/views/system-accounts/withdrawals.vue"; 
import CreateSystemAccountsWithdrawals from "@/views/system-accounts/withdraw.vue"; 
import SystemFiatAccounts from "@/views/system-accounts/fiats.vue";
import CreateSystemFiatAccount from "@/views/system-accounts/create-fiat.vue";


// STAFF
import Staff from "@/views/staff/index.vue";
import CreateStaff from "@/views/staff/create.vue";
import Roles from "@/views/staff/roles/index.vue";
import CreateRole from "@/views/staff/roles/create.vue";


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
        path: "/dashboard/profile",
        name: "profile",
        component: Profile,
    },
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
    {
        path: "/fiats",
        name: "fiatList",
        component: FiatList,
    },
    {
        path: "/fiats/create",
        name: "createFiat",
        component: CreateFiat,
    },
    {
        path: "/purchases",
        name: "purchases",
        component: Purchases,
    },
    {
        path: "/sales",
        name: "sales",
        component: Sales,
    },
    {
        path: "/swaps",
        name: "swaps",
        component: Swaps,
    },
    {
        path: "/users",
        name: "users",
        component: Users,
    },
    {
        path: "/users-wallets",
        name: "wallets",
        component: Wallets,
    },
    {
        path: "/users-banks",
        name: "banks",
        component: Banks,
    },
    {
        path: "/users/withdrawals/:id",
        name: "userWithdrawals",
        component: UserWithdrawals,
    },
    {
        path: "/users/deposits/:id",
        name: "userDeposits",
        component: UserDeposits
    },
    {
        path: "/users/purchases/:id",
        name: "userPurchases",
        component: UserPurchases
    },
    {
        path: "/users/swaps/:id",
        name: "userSwaps",
        component: UserSwaps
    },
    {
        path: "/users/sales/:id",
        name: "userSales",
        component: UserSales
    },
    {
        path: "/users/signal-payments/:id",
        name: "userSignals",
        component: UserSignals
    },
    {
        path: "/users/fiat-withdrawals/:id",
        name: "userFiatWithdrawals",
        component: UserFiatWithdrawals
    },
    {
        path: "/users/banks/:id",
        name: "userBanks",
        component: UserBanks
    },
    {
        path: "/users/referrals/:id",
        name: "userReferrals",
        component: UserReferrals
    },
    {
        path: "/users/documents/:id",
        name: "userVerification",
        component: UserVerification
    },
    {
        path: "/users/:id",
        name: "userProfile",
        component: UserProfile
    },
    {
        path: "/signals",
        name: "signals",
        component: Signals,
    },
    {
        path: "/signals/create",
        name: "createSignal",
        component: CreateSignal,
    },
    {
        path: "/messages",
        name: "messages",
        component: Messages,
    },
    {
        path: "/messages/create",
        name: "createMessage",
        component: CreateMessage,
    },
    {
        path: "/notifications",
        name: "notifications",
        component: Notifications,
    },
    // {
    //     path: "/notifications/create",
    //     name: "createNotification",
    //     component: CreateNotification,
    // },
    {
        path: "/system-accounts",
        name: "systemAccounts",
        component: SystemAccounts,
    },
    {
        path: "/system-accounts/withdrawals",
        name: "systemAccountWithdrawals",
        component: SystemAccountsWithdrawals,
    },
    {
        path: "/system-accounts/withdrawals/create/:id",
        name: "createSystemAccountWithdrawals",
        component: CreateSystemAccountsWithdrawals,
    },
    {
        path: "/system-fiat-accounts",
        name: "systemFiatAccounts",
        component: SystemFiatAccounts,
    },
    {
        path: "/system-fiat-accounts/create",
        name: "createSystemFiatAccount",
        component: CreateSystemFiatAccount,
    },
    {
        path: "/staff",
        name: "staff",
        component: Staff,
    },
    {
        path: "/staff/create",
        name: "createStaff",
        component: CreateStaff,
    },
    {
        path: "/permissions",
        name: "roles",
        component: Roles,
    },
    {
        path: "/permissions/create",
        name: "createRole",
        component: CreateRole,
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
