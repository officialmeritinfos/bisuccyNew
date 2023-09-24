<template>
  <!-- BEGIN: Top Bar -->
  <div class="top-bar">
    <!-- BEGIN: Breadcrumb -->
    <nav aria-label="breadcrumb" class="-intro-x mr-auto hidden sm:flex">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Application</a></li>
        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
      </ol>
    </nav>
    <!-- END: Breadcrumb -->
    <!-- BEGIN: Notifications -->
    <Dropdown class="intro-x mr-auto sm:mr-6">
      <DropdownToggle
        tag="div"
        role="button"
        class="notification notification--bullet cursor-pointer"
      >
        <BellIcon class="notification__icon dark:text-slate-500" />
      </DropdownToggle>
      <DropdownMenu class="notification-content pt-2">
        <DropdownContent tag="div" class="notification-content__box">
          <div class="notification-content__title">Notifications</div>
          <div
            v-for="(faker, fakerKey) in $_.take($f(), 5)"
            :key="fakerKey"
            class="cursor-pointer relative flex items-center"
            :class="{ 'mt-5': fakerKey }"
          >
            <div class="w-12 h-12 flex-none image-fit mr-1">
              <img
                alt="Bisuccy Account"
                class="rounded-full"
                :src="faker.photos[0]"
              />
              <div
                class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white dark:border-darkmode-600"
              ></div>
            </div>
            <div class="ml-2 overflow-hidden">
              <div class="flex items-center">
                <a href="javascript:;" class="font-medium truncate mr-5">{{
                  faker.users[0].name
                }}</a>
                <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">
                  {{ faker.times[0] }}
                </div>
              </div>
              <div class="w-full truncate text-slate-500 mt-0.5">
                {{ faker.news[0].shortContent }}
              </div>
            </div>
          </div>
        </DropdownContent>
      </DropdownMenu>
    </Dropdown>
    <!-- END: Notifications -->
    <!-- BEGIN: Account Menu -->
    <Dropdown class="intro-x w-8 h-8">
      <DropdownToggle
        tag="div"
        role="button"
        class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in"
      >
        <img
          alt="Bisuccy User"
          :src="$f()[9].photos[0]"
        />
      </DropdownToggle>
      <DropdownMenu class="w-56">
        <DropdownContent class="bg-primary text-white">
          <DropdownHeader tag="div" class="!font-normal">
            <div class="font-medium">{{ $f()[0].users[0].name }}</div>
            <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">
              {{ $f()[0].jobs[0] }}
            </div>
          </DropdownHeader>
          <DropdownDivider class="border-white/[0.08]" />
          <DropdownItem class="dropdown-item hover:bg-white/5">
            <UserIcon class="w-4 h-4 mr-2" /> Profile
          </DropdownItem>
          <DropdownItem class="dropdown-item hover:bg-white/5" @click="setMyPin">
            <KeyIcon class="w-4 h-4 mr-2" /> Set Pin
          </DropdownItem>
          <DropdownItem class="dropdown-item hover:bg-white/5">
            <LockIcon class="w-4 h-4 mr-2" /> Reset Password
          </DropdownItem>
          <DropdownItem class="dropdown-item hover:bg-white/5">
            <HelpCircleIcon class="w-4 h-4 mr-2" /> Help
          </DropdownItem>
          <DropdownDivider class="border-white/[0.08]" />
          <DropdownItem class="dropdown-item hover:bg-white/5" @click="logout">
            <ToggleRightIcon class="w-4 h-4 mr-2" /> Logout
          </DropdownItem>
        </DropdownContent>
      </DropdownMenu>
    </Dropdown>
    <!-- END: Account Menu -->
  </div>
  <!-- END: Top Bar -->
</template>

<script setup>
import { ref } from "vue";
import {useGlobalStore} from "../../stores/global";

const searchDropdown = ref(false);
const showSearchDropdown = () => {
  searchDropdown.value = true;
};
const hideSearchDropdown = () => {
  searchDropdown.value = false;
};

const logout = () => {
  useGlobalStore().logMeOut()
}

const setMyPin = () => {
  useGlobalStore().showSetPinModal(true)
}

</script>
