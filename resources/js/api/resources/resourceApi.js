import axios from '@axios';
import { useToast } from "vue-toast-notification";
const $toast = useToast({
    position: 'top-right'
})

export default {
    async delete(id) {
        await axios.delete('/api/v1/resources/' + id)
    },

    async loadCategories() {
        let categories = []

        await axios.get('/api/v1/resources/categories')
            .then(res => {
                categories = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching resource categories.')
                console.log(error.response)
            })

        return categories
    },

    async loadResources(params) {
        console.log("PPPP", params)
        let resources = []
        await axios.get('/api/v1/resources', {
            params: {
                category: params.category,
                searchtext: params.search,
                page: params.page,
                per_page: params.per_page,
                role_id: params.role_id,
                sort_by: params.sort_by,
                sort_direction: params.sort_direction,
                ...params // includes extra filters
            },
            headers: { 'Cache-Control': 'no-cache' }
        })
        .then(res => {
            resources = res.data || []
        })
        .catch(err => {
            $toast.error(err.response?.data?.message || 'Something went wrong while fetching resources.')
            console.log(err.response)
        })
        return resources
    },

    async loadResourcesWithAcknowledgement(
        categoryId,
        searchText = "",
        page = 1,
        per_page = 10,
        role_id = 0,
        sort_by = null,
        sort_direction = null
    ) {
        let resources = []

        await axios.get('/api/v1/resources/acknowledgement-required', {
            params: {
                category: categoryId,
                searchtext: searchText,
                page: page,
                per_page: per_page,
                role_id: role_id,
                sort_by: sort_by,
                sort_direction: sort_direction
            }
        })
            .then(res => {
                resources = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching resources.')
                console.log(error.response)
            })

        return resources
    },    

    async saveCategory(form) {
        await axios.post('/api/v1/resources/categories', form)
    },

    async save(params) {
        await axios.post('/api/v1/resources', params)
    },

    async updatePrivacy(id, data){
        console.log("resource ID", id);
        console.log("data", data)
        await axios.post(`/api/v1/resources/update_privacy/${id}`, data)
    },

    async update(params, id) {
        await axios.post(`/api/v1/resources/update/${id}`, params)
    },

    async saveTag(form) {
        await axios.post('/api/v1/resources/tags', form)
    },

    async getTagsByResourceId(resourceId) {
        let tags = []
    
        await axios.get(`/api/v1/resources/${resourceId}/tags`)
            .then(res => {
                tags = res.data || []
            })
            .catch(err => {
                $toast.error(err.response?.data?.message || 'Something went wrong while fetching resource tags.')
                console.error(err.response)
            })
    

        return tags
    },
        
    async loadTags() {
        let tags = []

        await axios.get('/api/v1/resources/tags')
            .then(res => {
                tags = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching tags.')
                console.log(error.response)
            })

        return tags
    },
    async fetchAllTags() {
        let categories = []

        await axios.get('/api/v1/resources/fetchTags')
            .then(res => {
                categories = res.data || []
            })
            .catch(err => {
                $toast.error(err.response.data.message || 'Something went wrong while fetching resource categories.')
                console.log(error.response)
            })

        return categories
    },

}
