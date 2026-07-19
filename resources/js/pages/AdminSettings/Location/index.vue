<template>
  <div>
    <div class="tw-flex tw-justify-between">
      <p class="font-weight-bold text-h2 mb-3">Locations</p>
      <VBtn prepend-icon="tabler-circle-plus-filled" @click="showAddDialog = true">
        Add Location
      </VBtn>
    </div>

    <VDialog v-model="showAddDialog" max-width="500" persistent scrollable>
      <VCard>
        <VCardTitle class="tw-bg-orange-500 tw-text-white tw-font-bold tw-py-2 tw-px-4">
          Add Location
        </VCardTitle>

        <div class="tw-px-4 tw-rounded">
          <!-- Name -->
          <div class="tw-flex tw-py-1 tw-mb-2">
            <AppTextField
              v-model="form.name"
              :rules="[requiredValidator]"
              label="Location Name"
            />
          </div>

          <!-- Divisions multi-select (no Select All) -->
          <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">
              Divisions
            </label>
            <VSelect
              v-model="form.division_ids"
              :items="divisions"
              item-title="name"
              item-value="id"
              multiple
              chips
              clearable
              placeholder="All"
              :rules="[]"
              hide-details="auto"
              hide-label
              color="white"
            />
          </div>

          <!-- Valid From -->
          <div class="tw-flex tw-py-1 tw-mb-2 tw-flex-col">
            <label class="tw-font-medium tw-mb-1 tw-text-[13px] tw-text-[#000]">
              Valid From
            </label>
            <VueDatePicker
              :min-date="new Date()"
              v-model="form.valid_from"
              auto-apply
              format="dd/MM/yyyy"
              :enable-time-picker="false"
              teleport="body"
            />
          </div>

          <!-- Footer -->
          <div class="tw-flex tw-justify-end tw-py-3">
            <VBtn class="tw-mr-2" color="secondary" @click="showAddDialog = false">
              Cancel
            </VBtn>
            <VBtn
              color="success"
              :disabled="!form.name || !form.valid_from"
              @click="addRecord"
            >
              Save
            </VBtn>
          </div>
        </div>
      </VCard>
    </VDialog>

    <VRow>
      <VCol cols="12">
        <LocationTable ref="locationTableRef" />
      </VCol>
    </VRow>
  </div>
</template>

<script>
import DivisionApi from "@/api/division/divisionApi";
import LocationApi from "@/api/location/locationApi";
import LocationTable from "@/pages/AdminSettings/Location/LocationTable.vue";

export default {
  components: { LocationTable },
  data() {
    return {
      showAddDialog: false,
      divisions: [],
      form: {
        name: null,
        division_ids: [],    // leave empty to mean “all divisions”
        valid_from: null,
      },
    };
  },
  mounted() {
    this.loadDivisions();
  },
  methods: {
    async loadDivisions() {
      const res = await DivisionApi.fetchDivisions();
      this.divisions = Array.isArray(res.data) ? res.data : res;
    },

    async addRecord() {
      // format date as YYYY-MM-DD
      if (this.form.valid_from) {
        const d = new Date(this.form.valid_from);
        this.form.valid_from = d.toISOString().split("T")[0];
      }

      // if user selected none, treat as “all”
      const isDivisionAll = this.form.division_ids.length === 0 ? 1 : 0;

      const payload = {
        name: this.form.name,
        valid_from: this.form.valid_from,
        division_ids: this.form.division_ids,    // may be []
        is_division_all: isDivisionAll,
      };

      LocationApi.addLocation(payload)
        .then((res) => {
          console.log("@@@@",res)
          if(!res){
            this.$toast.error(
              err?.response?.data?.errors?.name?.[0] ||
              err?.response?.data?.message ||
              "Failed to add location"
            );
          }
          else{
            this.$toast.success("Location added successfully");

          }

        })



      this.showAddDialog = false;
      this.$refs.locationTableRef.loadItems();

      // reset the form
      this.form = { name: null, division_ids: [], valid_from: null };
    },
  },
};
</script>
