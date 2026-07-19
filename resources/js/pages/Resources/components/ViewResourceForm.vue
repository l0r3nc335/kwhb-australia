<template>
    <div class="tw-flex tw-flex-col tw-px-4 tw-rounded">
        <div class="tw-flex tw-justify-between py-2">
            <div class="tw-rounded tw-flex tw-border tw-border-orange-400 tw-rounded-t">
                <div @click="resourceData.type = 1" class="px-3 py-1 tw-flex tw-flex-col tw-justify-center tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white" :class="[resourceData.type === 1 ? 'tw-bg-orange-400' : '']">
                    <VIcon icon="tabler-files" size="small" :color="resourceData.type === 1 ? 'white' : 'black'"></VIcon>
                </div>
                <div @click="resourceData.type = 2"  class="px-3 py-1 tw-flex-col tw-justify-center tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white" :class="[resourceData.type === 2 ? 'tw-bg-orange-400' : '']">
                    <VIcon icon="tabler-link" size="small" :color="resourceData.type === 2 ? 'white' : 'black'"></VIcon>
                </div>
            </div>
            <div>
                <AppSelect  :disabled="true" id="privacySelect" v-model="resourceData.privacy" :items="privacyOptions" item-title="label" item-value="id" density="compact" size="sm">
                    <template #selection="{ props, item }">
                        <div class="tw-flex tw-px-2 tw-py-1 tw-cursor-pointer " v-bind="props">
                            <VIcon :icon="item.raw.icon" class="tw-mr-2"></VIcon>
                            <span>{{item.raw.label}}</span>
                        </div>
                    </template>
                    <template #item="{ props, item }">
                        <div class="tw-flex tw-px-2 tw-py-1 tw-cursor-pointer hover:tw-bg-gray-100" v-bind="props" :disabled="true">
                            <VIcon :icon="item.raw.icon" class="tw-mr-2"></VIcon>
                            <span>{{item.raw.label}}</span>
                        </div>
                    </template>
                </AppSelect>
            </div>
        </div>
        <div class="tw-flex tw-py-1 tw-mb-2">
            <VTextField v-model="resourceData.name" placeholder="Title" size="sm" :disabled="true"></VTextField>
        </div>
        <template v-if="resourceData.type === 1">
                        <a :href="`/storage/` + resourceData.path.replace('public/', '')" target="_blank" class="tw-text-blue-400 hover:tw-text-blue-600">
                            <span>{{ resourceData.path.replace('public/resources/', '')}}</span>
                        </a>
        </template>
        <template v-else>
            <VTextField v-model="resourceData.path" placeholder="HTTP Link..." prepend-inner-icon="tabler-link"  :disabled="true"></VTextField>
        </template>
        <!-- <div class="tw-mt-2 tw-mb-1 tw-flex tw-w-full">
            <EmployeeSelector @selected="selectEmployees" :multiple="true" :label="'Acknowledgements'"  :disabled="true" ></EmployeeSelector>
        </div> -->
        <div v-if="acknowledgements.length > 0" class="tw-mt-3 tw-mb-1 tw-flex">
            <VTextField type="date" label="Acknowledgement Deadline" prepend-inner-icon="tabler-calendar"  :disabled="true"></VTextField>
        </div>
        <div class="tw-py-3">
            <VTextarea v-model="resourceData.description"  placeholder="Description..."  :disabled="true"></VTextarea>
        </div>
    <div class="tw-mt-2 tw-mb-1 tw-flex tw-w-full">
            <TagSelector v-model="resourceData.resource_tag_name" @selected="selectTags" :multiple="false" :label="'Tags'"  :disabled="true"></TagSelector>
        </div>
        <div class="tw-flex tw-justify-end tw-py-3">
            <VBtn class="tw-mr-2" color="secondary" @click="$emit('close')">Cancel</VBtn>
        </div>
    </div>
</template>

<script setup>

const dlFile = () => {
    const fileName = noteSelected.value.file_name;

    const downloadUrl =  `${import.meta.env.VITE_API_BASE_URL}/api/notes/pdf/${noteSelected.value.file}`
    fetch(downloadUrl)
    .then(response => response.blob())
    .then(blob => {
      const blobWithFileName = new Blob([blob], { type: 'application/pdf' });

      const url = window.URL.createObjectURL(blobWithFileName);

      const anchor = document.createElement('a');
      anchor.href = url;
      anchor.download = fileName;
      anchor.style.display = 'none';

      document.body.appendChild(anchor);

      anchor.click();

      setTimeout(() => {
          window.URL.revokeObjectURL(url);
          document.body.removeChild(anchor);
      }, 2000);
    })
}

</script>

<script>
    import resourceApi from "@/api/resources/resourceApi";
    import EmployeeSelector from "@/components/selectors/EmployeeSelector.vue";
    import TagSelector from "@/components/selectors/TagSelector.vue";

    export default {
        components: {
            EmployeeSelector,
            TagSelector
        },

        props: {
            category: {
                default: null
            },
            resourceData: {
               default: ''
              },
        },

        data() {
            return {
                type: 1, // 1 = file, 2 = link,
                name: null,
                file: null,
                link: null,
                description: '',
                privacy: 1, // 1 - Public, 2 - Restricted, 3 - Only Me, 4 - archived
                acknowledgements: [],
                privacyOptions: [
                    {
                        id: 1,
                        label: 'Public',
                        icon: 'tabler-eye'
                    },
                    {
                        id: 2,
                        label: 'Restricted',
                        icon: 'tabler-lock'
                    },
                    {
                        id: 3,
                        label: 'Only Me',
                        icon: 'tabler-user'
                    },
                    {
                        id: 4,
                        label: 'Archive',
                        icon: 'tabler-archive'
                    },
                ]
            }
        },

        computed: {
            formValidated() {
                if (this.type === 1) {
                    return this.name && this.file
                }

                return this.name && this.link
            }
        },

        methods: {
            selectEmployees(employees) {
                this.acknowledgements = employees
            },

            resourceClose() {
                this.$emit('close')
                this.$emit('reload-resources')

            },
            resetForm() {
                this.name = null
                this.type = 1
                this.link = null
                this.file = null
                this.description = null
                this.acknowledgements = []
                this.privacy = 1
            },

            async saveResource() {
                let form = new FormData();
                let file = this.file?.length > 0 ? this.file[0] : null

                form.append('type', this.type)
                form.append('name', this.name)
                form.append('description', this.description)
                form.append('file', file)
                form.append('link', this.link)
                form.append('category', this.category?.id)

                this.resourceData.category = this.category;

                await resourceApi.save(form)
                this.resetForm()

                this.$toast.success('Resource saved!')
                this.$emit('close')
                this.$emit('reload-resources')
            }
            
        }
    }
</script>

<style >
#privacySelect .v-select .v-field .v-text-field__prefix, .v-select .v-field .v-text-field__suffix, .v-select .v-field .v-field__input, .v-select .v-field.v-field {
    padding: 0 3px !important;
}
input[type='date']::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}
</style>
