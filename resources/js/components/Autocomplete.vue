<script setup>
const props = defineProps({
  modelValue: String,
})

const emit = defineEmits(['update:model-value', 'change-state'])

const autocompleteInput = ref(null)

const tb = computed(() => {
  return { value: props.modelValue }
})

onMounted(() => {
  const input = autocompleteInput.value
  const autocomplete = new window.google.maps.places.Autocomplete(input, {
    componentRestrictions: { country: ['aus'] },
    fields: ['address_components', 'geometry'],
    types: ['address'],
  })
  autocomplete.addListener('place_changed', function () {
    const place = autocomplete.getPlace()
    let address = ''
    let state = ''
    for (const component of place.address_components) {
      const componentType = component.types[0]
      if (componentType !== 'administrative_area_level_2') {
        if (componentType === 'locality') {
          address += `${component.long_name} `
        } else if (componentType === 'administrative_area_level_1') {
          address += `${component.short_name}, `
          state = component.short_name
        } else if (componentType === 'postal_code') {
          address += `${component.long_name}`
        } else {
          address += `${component.long_name}, `
        }
      }
    }
    emit('update:model-value', address)
    emit('change-state', state)
  })
})
</script>

<template>
  <div class="v-input__control address_input" style="height: 40px">
    <div
      class="v-field v-field--no-label v-field--variant-outlined"
      role="textbox"
    >
      <div class="v-field__overlay"></div>
      <div class="v-field__loader">
        <div
          class="v-progress-linear v-progress-linear--rounded v-progress-linear--rounded-bar v-progress-linear--rounded"
          role="progressbar"
          aria-hidden="true"
          aria-valuemin="0"
          aria-valuemax="100"
          style="top: 0px; height: 0px; left: 50%; transform: translateX(-50%)"
        >
          <!---->
          <div
            class="v-progress-linear__background"
            style="
              background-color: rgb(var(--v-theme-on-surface));
              width: 100%;
            "
          ></div>
          <div class="v-progress-linear__indeterminate">
            <div class="v-progress-linear__indeterminate long bg-primary"></div>
            <div
              class="v-progress-linear__indeterminate short bg-primary"
            ></div>
          </div>
          <!---->
        </div>
      </div>
      <!---->
      <div class="v-field__field" data-no-activator="">
        <!----><label
          class="v-label v-field-label"
          for="app-text-field-Product Name-rwgjm"
          ><!----><!----></label
        ><!---->
        <input
          v-model="tb.value"
          placeholder="Type to search"
          size="1"
          type="text"
          id="app-text-field-Product Name-rwgjm"
          aria-describedby="app-text-field-Product Name-rwgjm-messages"
          class="v-field__input"
          ref="autocompleteInput"
        /><!---->
      </div>
      <!----><!---->
      <div class="v-field__outline">
        <div class="v-field__outline__start"></div>
        <!---->
        <div class="v-field__outline__end"></div>
        <!---->
      </div>
    </div>
  </div>
</template>

<style scoped>
.address_input .v-field__input {
  min-height: 45px !important;
}
</style>
