import { defineStore } from "pinia";

export const useSideMenuStore = defineStore("sideMenu", {
    state: () => ({
        menu: [
            {
                icon: "HomeIcon",
                pageName: "dashboard",
                title: "Dashboard",
                // subMenu: [{
                //   icon: "HomeIcon",
                //   pageName: "side-menu-page-1",
                //   title: "Page 1",
                // },]
            },
            {
                icon: "BanknoteIcon",
                pageName: "fiatDeposits",
                title: "Fiat",
                subMenu: [
                    {
                        icon: "TrendingDownIcon",
                        pageName: "fiatDeposits",
                        title: "Deposit",
                    },
                    {
                        icon: "TrendingUpIcon",
                        pageName: "fiatWithdrawals",
                        title: "Withdrawal",
                    },
                    {
                        icon: "SettingsIcon",
                        pageName: "fiatList",
                        title: "Fiat settings",
                    },
                ],
            },
            {
                icon: "BitcoinIcon",
                pageName: "crypto",
                title: "Crypto",
                subMenu: [
                    {
                        icon: "TrendingDownIcon",
                        pageName: "cryptoDeposits",
                        title: "Deposit",
                    },
                    {
                        icon: "TrendingUpIcon",
                        pageName: "cryptoWithdrawals",
                        title: "Withdrawal",
                    },
                    {
                        icon: "BriefcaseIcon",
                        pageName: "purchases",
                        title: "Purchases",
                    },
                    {
                        icon: "BarChart2Icon",
                        pageName: "sales",
                        title: "Sales",
                    },
                    {
                        icon: "RefreshCcwIcon",
                        pageName: "swaps",
                        title: "Swaps",
                    },
                ],
            },
            {
                icon: "UsersIcon",
                pageName: "userMgt",
                title: "Users Mgt.",
                subMenu: [
                    {
                        icon: "UserIcon",
                        pageName: "users",
                        title: "Users",
                    },
                    {
                        icon: "WalletIcon",
                        pageName: "wallets",
                        title: "Users Wallet",
                    },
                    {
                        icon: "LandmarkIcon",
                        pageName: "banks",
                        title: "Users Bank",
                    },
                ],
            },
            {
                icon: "SignalIcon",
                pageName: "signals",
                title: "Signals",
            },
            {
                icon: "MailIcon",
                pageName: "messages",
                title: "Messaging",
            },
            {
                icon: "BellIcon",
                pageName: "notifications",
                title: "Notifications",
            },
            {
                icon: "Settings2Icon",
                pageName: "settings",
                title: "System Crypto Acct",
            },
            {
                icon: "SlidersIcon",
                pageName: "settings",
                title: "System Fiat Acct",
            },
            {
                icon: "BoxIcon",
                pageName: "settings",
                title: "Profile",
                subMenu: [
                    {
                        icon: "UserCheckIcon",
                        pageName: "settings",
                        title: "Admin Details",
                    },
                    {
                        icon: "EyeOffIcon",
                        pageName: "settings",
                        title: "Password change",
                    },
                    {
                        icon: "KeyIcon",
                        pageName: "settings",
                        title: "Set account Pin",
                    },
                    {
                        icon: "SlidersIcon",
                        pageName: "settings",
                        title: "General settings",
                    },
                    {
                        icon: "UserPlusIcon",
                        pageName: "settings",
                        title: "Staff",
                    },
                    {
                        icon: "TargetIcon",
                        pageName: "settings",
                        title: "Permission module",
                    },
                ],
            },
        ],
    }),
});
