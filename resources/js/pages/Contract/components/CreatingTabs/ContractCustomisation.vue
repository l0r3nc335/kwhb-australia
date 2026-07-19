<script>
  import contractApi from "@/api/contract/contractApi";

  export default {
    props: {
      selectedTemplate: {
        type: Object,
        required: true,
      },
      employee: {
        type: Object,
        required: true,
      },
    },
    data() {
      return {
        customisationData: {},
        items: [],
        contractTitle: '', 
        selectedEmploymentType: null,
        selectedVaccinations: [],
        vaccinationOptions: [
          'Covid-19', 'Measles, mumps & rubella (MMR)', 'Whooping cough',
          'Hep B', 'Meningococcal ACWY'
        ],
        imagePreview: null,
      };
    },
    watch: {
      contractTitle(newTitle) {
        console.log('Updated Contract Title:', newTitle);
      },
      selectedEmploymentType(newEmploymentType) {
        console.log('Selected Employment Type:', newEmploymentType);
      }
    },
    created() {
      if (this.selectedTemplate && this.selectedTemplate.templateFields) {
        this.selectedTemplate.templateFields.forEach((field) => {
          this.customisationData[field.key_name] = {
            id: field.id,
            value: ""
          };
        });

        // Handle applicant name initialization (firstName + lastName)
        const applicantNameField = this.selectedTemplate.templateFields.find(field => field.key_name === "applicant_name");
        if (applicantNameField) {
          this.customisationData["applicant_name"].value = `${this.employee.firstName} ${this.employee.lastName}`;
        }

        // Handle applicant email initialization
        const applicantEmailField = this.selectedTemplate.templateFields.find(field => field.key_name === "applicant_email");
        if (applicantEmailField) {
          this.customisationData["applicant_email"].value = this.employee.email;
        }

        console.log("Initialized Customisation Data:", this.customisationData);
      }
    },
    methods: {
      updateCustomisationData() {
        console.log(this.contractTitle);
        this.$emit('updateContractTitle', this.contractTitle);
        this.$emit('updateEmploymentType', this.selectedEmploymentType);

        if (this.selectedVaccinations.length > 0) {
          this.customisationData['vaccinations'].value = this.selectedVaccinations;
        }

        console.log("Current Customisation Data:", this.customisationData);
        this.$emit("updateCustomisationData", this.customisationData);
      },

      async fetchEmployment() {
        try {
          const response = await contractApi.fetchEmployments();
          this.items = response.map(item => ({
            id: item.id,
            name: item.name
          }));
        } catch (error) {
          console.error('Error fetching employment data:', error);
        }
      },

      getInputType(field) {
        switch (field.type) {
          case 'date':
            return 'date';
          case 'number':
            return 'number';
          case 'email':
            return 'email';
          case 'file':
            return 'file';
          case 'select':
            return 'select';
          case 'text':
          default:
            return 'text';
        }
      },

      handleImageUpload(event, fieldKey) {
        const file = event.target.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.imagePreview = e.target.result;
            this.customisationData[fieldKey] = {
              id: this.customisationData[fieldKey].id,
              value: file
            };
          };
          reader.readAsDataURL(file);
        } else {
          this.imagePreview = null;
        }
      }
    },

    mounted() {
      this.fetchEmployment();
      console.log('Selected employee:', this.employee);
    },
  };
</script>

<template>
  <div>
    <h2 class="text-h2 font-weight-medium">Contract Customisation</h2>
    <p class="mb-4 mt-4">
      Select or enter any additional information that will be included in your Contract
    </p>

    <div class="form-group contract-title">
      <label for="title">Title</label>
      <input type="text" class="form-control title" placeholder="Enter Contract Title" v-model="contractTitle" @input="updateCustomisationData"/>
    </div>

    <div class="form-row">
      <div class="col-12 col-md-6">
        <label for="Employment Type">Employment Type</label>
        <div class="select-wrapper">
          <select
            v-model="selectedEmploymentType"
            class="form-control custom-select"
            style="width: 400px;"
            @change="updateCustomisationData">
            <option value="" disabled>Select Employment Type</option>
            <option v-for="item in items" :key="item.id" :value="item.id" style="color: black;">
              {{ item.name }}
            </option>
          </select>
        </div>
      </div>

      <div
        v-for="(field, index) in selectedTemplate.templateFields"
        :key="field.id"
        class="form-group col-12 col-md-6">
        <label :for="field.key_name">{{ field.name }}</label>



        <template v-if="field.type === 'select'">
          <select
            v-model="customisationData[field.key_name].value"
            class="form-control"
            :id="field.key_name"
            :placeholder="'Select ' + field.name"
            required
            @change="updateCustomisationData"
          >
            <option value="" disabled>Select {{ field.name }}</option>
            <option v-if="field.name === 'Vaccinations'" v-for="option in vaccinationOptions" :key="option" :value="option" style="color: black;">
              {{ option }}
            </option>
            <option v-for="option in field.options" :key="option.id" :value="option.id">
              {{ option.name }}
            </option>
          </select>
        </template>

        <template v-else-if="field.type === 'file'">
          <input
            type="file"
            class="form-control"
            @change="handleImageUpload($event, field.key_name)"
            :id="field.key_name"
            accept="image/*"
          />
          <div v-if="imagePreview" class="mt-3">
            <img :src="imagePreview" alt="Image Preview" class="img-thumbnail" style="max-width: 100%; max-height: 200px;"/>
          </div>
        </template>

        <template v-else>
          <input
            :type="getInputType(field)"
            class="form-control"
            v-model="customisationData[field.key_name].value"
            :id="field.key_name"
            :placeholder="'Enter ' + field.name"
            required
            @input="updateCustomisationData"
          />
        </template>

      </div>
    </div>
  </div>
</template>

<style scoped>
  .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    padding: 30px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-control {
    border: 1px solid gray;
    padding: 10px;
    border-radius: 10px;
    width: 400px;
  }

  .col-12 {
    flex: 1 1 100%;
  }

  .col-md-6 {
    flex: 1 1 45%;
  }

  .mt-4 {
    margin-top: 20px;
  }

  .data-entry {
    margin-bottom: 10px;
  }

  .title {
    width: 80%;
  }

  .contract-title {
    margin: 0px 30px;
  }
</style>
