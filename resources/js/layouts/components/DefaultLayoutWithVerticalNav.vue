<script setup>
import navItems from '@/navigation/vertical'
import { useThemeConfig } from '@core/composable/useThemeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'

// import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import Notifications from "@/components/AppNotifications.vue";
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()

const route = useRoute()
const formattedPath = computed(() => {
    const path = route.path.replace('/', '') // Remove "/"
    return path.charAt(0).toUpperCase() + path.slice(1) // Capitalize first letter
})
</script>

<template>
  <VerticalNavLayout  v-if="$route.path !== '/contract'" :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }" >
        <div class="d-flex h-100 align-center" style="inset-block-start: 30px; justify-content: flex-end;">
                <IconBtn
                    v-if="isLessThanOverlayNavBreakpoint(windowWidth)"
                    id="vertical-nav-toggle-btn"
                    class="ms-n3"
                    @click="toggleVerticalOverlayNavActive(true)"
                >
                    <VIcon
                        size="26"
                        icon="tabler-menu-2"
                    />
                </IconBtn>

                <!-- <NavbarThemeSwitcher /> -->
                <NavSearchBar />

                <VSpacer v-if="$route.path !== '/contract'"/>
                <Notifications class="tw-mr-2"></Notifications>
                <UserProfile />
            </div>
    </template>

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }" style="padding: 0px 20px">
      <Transition
        :name="appRouteTransition"
        mode="out-in"
      >
        <Component :is="Component" />
      </Transition>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>

  <VerticalNavLayout
      v-else-if="$route.path === '/contract'"
      :nav-items="navItems"
      style="margin-top: -20px!important;">

      <!-- 👉 Pages -->
        <RouterView v-slot="{ Component }" class="__content">
            <Transition :name="appRouteTransition" mode="out-in">
                <Component :is="Component" />
            </Transition>
        </RouterView>

        <template #footer>
            <Footer />
        </template>
    </VerticalNavLayout>
</template>


<style scoped lang="scss">
@use "@layouts/styles/_variables" as variables;

.__content {
    border: variables.$layout_content_boarder;
}
</style>
