<template>
  <div class="py-5 md:py-0 -mx-3 px-3 sm:-mx-8 sm:px-8 bg-black/[0.15] dark:bg-transparent">
    <MobileMenu />
    <div class="flex mt-[4.7rem] md:mt-0 overflow-hidden">
      <!-- BEGIN: Side Menu -->
      <nav class="side-nav">
        <router-link :to="{ name: 'dashboard' }" tag="a" class="intro-x flex items-center pl-5 pt-4 mt-3">
          <img alt="Bisuccy" class="h-10" src="@/assets/images/logo-white.png" />
          <!-- <span class="hidden xl:block text-white text-lg ml-3"> Bisuccy </span> -->
        </router-link>
        <div class="side-nav__devider my-6"></div>
        <ul>
          <!-- BEGIN: First Child -->
          <template v-for="(menu, menuKey) in formattedMenu">
            <li v-if="menu == 'devider'" :key="menu + menuKey" class="side-nav__devider my-6"></li>
            <li v-else :key="menu + menuKey">
              <SideMenuTooltip tag="a" :content="menu.title" :href="
                menu.subMenu
                  ? 'javascript:;'
                  : router.resolve({ name: menu.pageName }).path
              " class="side-menu" :class="{
                  'side-menu--active': menu.active,
                  'side-menu--open': menu.activeDropdown,
                }" @click="linkTo(menu, router, $event)">
                <div class="side-menu__icon">
                  <component :is="menu.icon" />
                </div>
                <div class="side-menu__title">
                  {{ menu.title }}
                  <div v-if="menu.subMenu" class="side-menu__sub-icon"
                    :class="{ 'transform rotate-180': menu.activeDropdown }">
                    <ChevronDownIcon />
                  </div>
                </div>
              </SideMenuTooltip>
              <!-- BEGIN: Second Child -->
              <transition @enter="enter" @leave="leave">
                <ul v-if="menu.subMenu && menu.activeDropdown">
                  <li v-for="(subMenu, subMenuKey) in menu.subMenu" :key="subMenuKey">
                    <SideMenuTooltip tag="a" :content="subMenu.title" :href="
                      subMenu.subMenu
                        ? 'javascript:;'
                        : router.resolve({ name: subMenu.pageName }).path
                    " class="side-menu" :class="{ 'side-menu--active': subMenu.active }"
                      @click="linkTo(subMenu, router, $event)">
                      <div class="side-menu__icon">
                        <component :is="subMenu.icon" />
                      </div>
                      <div class="side-menu__title">
                        {{ subMenu.title }}
                        <div v-if="subMenu.subMenu" class="side-menu__sub-icon" :class="{
                          'transform rotate-180': subMenu.activeDropdown,
                        }">
                          <ChevronDownIcon />
                        </div>
                      </div>
                    </SideMenuTooltip>
                    <!-- BEGIN: Third Child -->
                    <transition @enter="enter" @leave="leave">
                      <ul v-if="subMenu.subMenu && subMenu.activeDropdown">
                        <li v-for="(
                              lastSubMenu, lastSubMenuKey
                            ) in subMenu.subMenu" :key="lastSubMenuKey">
                          <SideMenuTooltip tag="a" :content="lastSubMenu.title" :href="
                            lastSubMenu.subMenu
                              ? 'javascript:;'
                              : router.resolve({ name: lastSubMenu.pageName })
                                .path
                          " class="side-menu" :class="{ 'side-menu--active': lastSubMenu.active }"
                            @click="linkTo(lastSubMenu, router, $event)">
                            <div class="side-menu__icon">
                              <ZapIcon />
                            </div>
                            <div class="side-menu__title">
                              {{ lastSubMenu.title }}
                            </div>
                          </SideMenuTooltip>
                        </li>
                      </ul>
                    </transition>
                    <!-- END: Third Child -->
                  </li>
                </ul>
              </transition>
              <!-- END: Second Child -->
            </li>
          </template>
          <!-- END: First Child -->
        </ul>
      </nav>
      <!-- END: Side Menu -->
      <!-- BEGIN: Content -->
      <div class="content">
        <TopBar />
        <div class="relative">
          <slot />
          <SetPinModal />
          <ApprovalPinModal />
          <ErrorNotification />
          <SuccessNotification />
          <template v-if="loading">
            <LoadingScreen />
          </template>
        </div>
      </div>
      <!-- END: Content -->
    </div>
  </div>
</template>
  
<script setup>
import { computed, onMounted, provide, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { helper as $h } from "@/utils/helper";
import { useSideMenuStore } from "@/stores/side-menu";
import TopBar from "@/components/top-bar/Main.vue";
import MobileMenu from "@/components/mobile-menu/Main.vue";
import LoadingScreen from "@/components/core/LoadingScreen.vue";
import SideMenuTooltip from "@/components/side-menu-tooltip/Main.vue";
import SetPinModal from "@/components/core/SetPinModal.vue";
import ApprovalPinModal from "@/components/core/ApprovalPinModal.vue";
import { linkTo, nestedMenu, enter, leave } from "./index";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useDashboardStore } from "@/stores/dashboard";
import { useGlobalStore } from "@/stores/global";
import ErrorNotification from "@/components/core/ErrorNotification.vue";
import SuccessNotification from "@/components/core/SuccessNotification.vue";

const route = useRoute();
const router = useRouter();
const formattedMenu = ref([]);
const sideMenuStore = useSideMenuStore();
const sideMenu = computed(() => nestedMenu(sideMenuStore.menu, route));

const dashboardStore = useDashboardStore();
const globalStore = useGlobalStore();

const loading = computed(() => globalStore.loading);

provide("forceActiveMenu", (pageName) => {
  route.forceActiveMenu = pageName;
  formattedMenu.value = $h.toRaw(sideMenu.value);
});

watch(
  computed(() => route.path),
  () => {
    delete route.forceActiveMenu;
    formattedMenu.value = $h.toRaw(sideMenu.value);
  }
);

onMounted(() => {
  dom("body").removeClass("error-page").removeClass("login").addClass("main");
  formattedMenu.value = $h.toRaw(sideMenu.value);

  // Call necessary APIs on mount of the layout.
  dashboardStore.getAdminDetails()
});
</script>
  