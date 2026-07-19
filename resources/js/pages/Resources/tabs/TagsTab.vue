<template>
    <div class="tw-flex tw-flex-col">
        <TagTable :resources="resources" :pagination="pagination"></TagTable>
    </div>
</template>

<script>
// APIs
import resourceApi from "@/api/resources/resourceApi";
// Components
import AddResourceButton from "@/pages/Resources/components/AddResourceButton.vue";
import AddCategoryButton from "@/pages/Resources/components/AddCategoryButton.vue";
import TagTable from "@/pages/Resources/components/TagTable.vue";

export default {
    components: {
        TagTable,
        AddResourceButton,
        AddCategoryButton
    },

    props: {
        selectedCategory: {
            default: null
        }
    },

    data() {
        return {
            loading: false,
            resources: [],
            categories: [],
            category: null,
            selectedResource: null,
            form: {},
            pagination: {
                page: 1,
                per_page: 10,
                total: 0
            }
        }
    },

    mounted() {
        this.loadAll()
    },

    methods: {
        loadAll() {
            this.loadResources()
        },

        async loadCategories() {
            this.categories = await resourceApi.loadCategories()
        },

        async loadResources() {
            let data = await resourceApi.loadTags()
            console.log("hello load resources ....")
            //this.resources = data.data
            this.pagination.per_page = data.per_page
            this.pagination.page = data.current_page

        },

        async save() {
            await resourceApi.save(this.form)
        },

        async delete() {
            await resourceApi.delete(this.selectedResource?.id)
        }
    },

    watch: {
        category() {
            this.loadResources()
            this.$emit('selected-category', this.category)
        },

        selectedCategory() {
            if (!this.selectedCategory) {
                this.category = null
            }
        }
    }
}
</script>

<style>

</style>
