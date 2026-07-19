<script setup>
import { useAuthStore } from "@/store/authStore"
import bg_img from '@images/pages/admin_login.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { useToast } from "vue-toast-notification"
import { VForm } from 'vuetify/components/VForm'

const props = defineProps({
  token: {
    type: String, // Define the type as String
    required: true, // Make the prop required
  },
})

const toast = useToast({
  position: 'top-right',
})


const hide = ref(false)
const loading = ref(false)
const { reset } = useAuthStore()

const password = ref('')
const confirmPassword = ref('')
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

const onSubmit = () => {
  loading.value= true

  debugger
  if (password.value.length === 0) {
    toast.info('Kindly enter your new password in the field..')
    loading.value = false

    return
  }

  if (passwordsMatch.value  ) {
    reset({ password: password.value, token: props.token })
      .then(r => {
        if (r) {
        
          toast.success(`Password reset successful! You will be redirected in 3 seconds.`)
          setTimeout(() => {
            window.location.href = '/login'  // Replace with your login page URL
          }, 3000) // 3000ms = 3 seconds

        } else {
          toast.error('The token has expired! Please initiate the forgot password process again.')
        }
      })
      
  } 

  loading.value = false
  
}

// Computed properties for password strength and matching check
const passwordStrengthClass = computed(() => {
  if (password.value.length >= 8 && /\d/.test(password.value) && /[A-Z]/.test(password.value)) {
    return 'text-success' // strong password
  } else if (password.value.length >= 6) {
    return 'text-warning' // moderate password
  } else {
    return 'text-error' // weak password
  }
})

const passwordStrengthText = computed(() => {
  if (password.value.length >= 8 && /\d/.test(password.value) && /[A-Z]/.test(password.value)) {
    return 'Strong'
  } else if (password.value.length >= 6) {
    return 'Moderate'
  } else {
    return 'Weak'
  }
})

const passwordsMatch = computed(() => {
  return password.value === confirmPassword.value
})

// Methods to toggle password visibility
const togglePasswordVisibility = () => {
  isPasswordVisible.value = !isPasswordVisible.value
}

const toggleConfirmPasswordVisibility = () => {
  isConfirmPasswordVisible.value = !isConfirmPasswordVisible.value
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
              <b>Enter your new password 🔒</b>
            </h2>
            <h3 class="align-center">
              Create a strong password to secure your account. 
            </h3>
          </Center>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <!-- New Password Field -->
                <AppTextField
                  v-model="password"
                  class="input-password"
                  label="New Password"
                  placeholder="******"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                >
                  <template #append-inner>
                    <VIcon
                      class="cursor-pointer"
                      @click="togglePasswordVisibility"
                    >
                      {{ isPasswordVisible ? 'mdi-eye-off' : 'mdi-eye' }}
                    </VIcon>
                  </template>
                </AppTextField>

                <!-- Password Strength Indicator -->
                <p
                  v-if="password"
                  :class="passwordStrengthClass + ' pt-1 pb-1'"
                >
                  Password strength: {{ passwordStrengthText }}
                </p>

                <!-- Confirm Password Field -->
                <AppTextField
                  v-model="confirmPassword"
                  class="input-password pt-2"
                  label="Confirm Password"
                  placeholder="******"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                >
                  <template #append-inner>
                    <VIcon
                      class="cursor-pointer"
                      @click="toggleConfirmPasswordVisibility"
                    >
                      {{ isConfirmPasswordVisible ? 'mdi-eye-off' : 'mdi-eye' }}
                    </VIcon>
                  </template>
                </AppTextField>

                <!-- Password Mismatch Warning -->
                <p
                  v-if="confirmPassword && !passwordsMatch"
                  class="text-error pt-1"
                >
                  Passwords do not match!
                </p>
              </VCol>

              <VCol cols="12">
                <VBtn
                  block
                  :loading="loading"
                  type="submit"
                >
                  Reset Password
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
