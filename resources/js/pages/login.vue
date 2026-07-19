<script setup>
import { useAuthStore } from "@/store/authStore"
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import bg_img from '@images/flash-logo.png'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { VForm } from 'vuetify/components/VForm'

const loading = ref(false)
const { login } = useAuthStore()

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
const isPasswordVisible = ref(false)
const refVForm = ref()
const username = ref('')
const password = ref('')
const rememberMe = ref(false)

const onSubmit = () => {
  loading.value= true
  login({ username: username.value, password: password.value })
    .then(() => {
      loading.value = false
    })
    .catch(error => {
      loading.value = false
    })

}
</script>

<template>
  <VRow style=" height: 80%;">
    <VCol
      cols="6"
      style="  height: 100%;"
      lg="6"
    >
      <VImg
        :src="bg_img"
        style=""
        class="bg_img"
      />
    </VCol>

    <VCol
      cols="6"
      lg="4"
      style=""
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="700"
        width="600px"
        class=""
      >
        <VCardText>
          <VNodeRenderer class="mb-6" />
          <Center>
            <h2
              class="welcome-title tw-text-[2rem]"
              style="color: #2d4087;"
            >
              <b>Welcome to Flash</b>
            </h2>
            <h3 class="align-center">
              Please sign in
            </h3>
          </Center>
        </VCardText>



        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="username"
                  class="input-username"
                  label="Username"
                  placeholder="John Doe"
                  type="text"
                  autofocus
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <div class="tw-relative">
                  <AppTextField
                    v-model="password"
                    class="input-password"
                    label="Password"
                    placeholder="**************"
                    :type="isPasswordVisible ? 'text' : 'password'"
                    @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  />
                  <div style="position: absolute; top: 0; right: 0;">
                    <a
                      class="text-primary ms-2 mb-1"
                      href="/forgot-password"
                    >
                      Forgot Password?
                    </a>
                  </div>
                </div>


                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                  <VCheckbox
                    v-model="rememberMe"
                    label="Remember me"
                  />
                </div>

                <VBtn
                  block
                  :loading="loading"
                  type="submit"
                >
                  Sign in
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.bg_img {
  block-size: 100%;
  inline-size: 100%;
}

.welcome-title {
  color: #001871;
}
</style>

<style lang="scss">
    @use "@core-scss/template/pages/page-auth.scss";

    .input-username label,
    .input-password label {
      color: #2d4087 !important;
      font-size: 1rem !important;
    }
</style>

<route lang="yaml">
meta:
  layout: blank
</route>
