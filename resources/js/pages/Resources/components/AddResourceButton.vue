<template>
    <div>
            <VCol cols="12" class="mr-5">
                <div class="d-flex align-center flex-wrap gap-4">
                    <VSpacer/>
                    
                    <v-btn
                        variant="tonal"
                        color="#ffffff"
                        style="background-color: #00196d;"
                        class="text-none font-weight-bold"
                        prepend-icon="tabler-plus"
                        @click="showTagForm=true"
                    >
                    Add Tag
                    </v-btn>
                    <v-btn
                        color="primary"
                        class="text-none"
                        prepend-icon="tabler-plus"
                        @click="showResourceForm=true"
                    >
                    Add Resource
                    </v-btn>
                </div>
            </VCol>

        <VDialog v-model="showResourceForm" persistent width="500">
            <div class="tw-bg-white">
                <div class="tw-shadow tw-rounded">
                    <div class="tw-flex tw-px-4 tw-py-1 tw-bg-orange-600 tw-text-white">
                        <VIcon icon="tabler-files" class="mr-2"></VIcon>
                        <div class="tw-flex flex-col tw-justify-center">
                            Add Resource
                        </div>
                    </div>
                    <ResourceForm :category="category" @close="showResourceForm = false" @reload-resources="showResourceForm = false;$emit('reload-resources')"></ResourceForm>
                </div>
            </div>
        </VDialog>

        <VDialog v-model="showTagForm" persistent width="500">
            <div class="tw-bg-white">
                <div class="tw-shadow tw-rounded">
                    <div class="tw-flex tw-px-4 tw-py-1 tw-bg-orange-600 tw-text-white">
                        <VIcon icon="tabler-tag" class="mr-2"></VIcon>
                        <div class="tw-flex flex-col tw-justify-center">
                            Add Tag
                        </div>
                    </div>
                    <TagForm :category="category" @close="showTagForm = false" @reload-resources="showTagForm = false;$emit('reload-resources')"></TagForm>
                </div>
            </div>
        </VDialog>



    </div>
</template>

<script>
    import ColorSelector from "@/components/selectors/ColorSelector.vue";
import ResourceForm from "@/pages/Resources/components/ResourceForm.vue";
import TagForm from "@/pages/Resources/components/TagForm.vue";

    export default {
        components: {
            ResourceForm,
            TagForm,
            ColorSelector
        },

        props: {
            category: {
                default: null
            }
        },

        data() {
            return {
                showResourceForm: false,
                showTagForm: false
            }
        }
    }
</script>
