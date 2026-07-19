<script setup>
import ScrollToTop from '@core/components/ScrollToTop.vue';
import { useThemeConfig } from '@core/composable/useThemeConfig';
import { hexToRgb } from '@layouts/utils';
import { useTheme } from 'vuetify';

const {
  syncInitialLoaderTheme,
  syncVuetifyThemeWithTheme: syncConfigThemeWithVuetifyTheme,
  isAppRtl,
  handleSkinChanges,
} = useThemeConfig()

const { global } = useTheme()

// ℹ️ Sync current theme with initial loader theme
syncInitialLoaderTheme()
syncConfigThemeWithVuetifyTheme()
handleSkinChanges()
</script>

<template>
  <VLocaleProvider :rtl="isAppRtl">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView/>
      <ScrollToTop />
    </VApp>
  </VLocaleProvider>
</template>

<style lang="scss">
.v-overlay-container {
  .v-overlay {
    .app-inner-list {
      .v-list {
        .v-list-item--active {
          .v-list-item__content
          {
            color: white;
          }
        }
      }
    }
  }
}

.layout-horizontal-nav .nav-link:not(.sub-item) a.router-link-active, .layout-horizontal-nav .nav-group:not(.sub-item).active > .popper-triggerer > .nav-group-label, .layout-nav-type-vertical .layout-vertical-nav .nav-link > .nav-link-dark {
    background: #e35205 !important;
}
</style>
