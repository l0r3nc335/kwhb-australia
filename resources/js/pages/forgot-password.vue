<script setup>
import { useAuthStore } from "@/store/authStore"
import bg_img from '@images/pages/admin_login.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { VForm } from 'vuetify/components/VForm'

const loading = ref(false)
const { forgot } = useAuthStore()

const username = ref('')
const sent = ref(false)
const notExist = ref(false)

const onSubmit = () => {
  loading.value= true
  forgot({ username: username.value })
    .then(r => {
      if (r) {
      // Handle successful forgot password flow
        sent.value = true
      } else {
        notExist.value = true
      }

      loading.value = false  // Stop the loading indicator regardless of outcome
    })
    .catch(error => {
      console.error('Forgot password request failed:', error)
      loading.value = false  // Stop loading even if there's an error
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
            <div v-if="sent">
              <h2
                class="welcome-title tw-text-[2rem]"
                style="color: #2d4087;"
              >
                <b>Password Reset Sent 📧</b>
              </h2>
              <h3 class="align-center">
                We've sent you an email with instructions to reset your password.
              </h3>
            </div>
            <div v-else>
              <h2
                class="welcome-title tw-text-[2rem]"
                style="color: #2d4087;"
              >
                <b>Forgot Password? 🔒</b>
              </h2>
              <h3 class="align-center">
                Enter your username and we'll send you instructions to reset your password.
              </h3>
            </div>
          </Center>
        </VCardText>

        <VCardText>
          <VForm
            v-if="!sent"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- username -->
              <VCol cols="12">
                <AppTextField
                  v-model="username"
                  autofocus
                  class="input-username"
                  label="Username"
                  type="text"
                />
                <p
                  v-if="notExist"
                  class="error-text pt-2"
                  style="color: red;"
                >
                  Username or Work Email does not exist. Please check and try again.
                </p>
              </VCol>
              
    
              <VCol cols="12">
                <VBtn
                  block
                  :loading="loading"
                  type="submit"
                >
                  Send Reset Link
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
