<template>
    <div class="tw-mb-6">
        <VBtn prepend-icon="tabler-plus" @click="showResourceForm=true" color="kwhb">Add Category</VBtn>
        <VDialog v-model="showResourceForm" persistent width="500">
            <div class="tw-bg-white">
                <div class="tw-shadow tw-rounded">
                    <div class="tw-flex tw-px-4 tw-py-1 tw-bg-orange-600 tw-text-white">
                        <VIcon icon="tabler-files" class="mr-2"></VIcon>
                        <div class="tw-flex flex-col tw-justify-center">
                            Add Category
                        </div>
                    </div>
                    <ResourceCategoryForm @close="showResourceForm = false" @reload-categories="$emit('reload-categories')"></ResourceCategoryForm>
                </div>
            </div>
        </VDialog>
    </div>
</template>

<script>
import ResourceCategoryForm from "@/pages/Resources/components/ResourceCategoryForm.vue";

export default {
    components: {
        ResourceCategoryForm
    },

    data() {
        return {
            showResourceForm: false
        }
    }
}
</script>
