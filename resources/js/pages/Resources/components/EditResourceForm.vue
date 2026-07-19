<template>
    <div class="tw-flex tw-flex-col tw-px-4 tw-rounded">
        <div class="tw-flex tw-justify-between py-2">
            <div class="tw-rounded tw-flex tw-border tw-border-orange-400 tw-rounded-t">
                <div @click="type = 1" class="px-3 py-1 tw-flex tw-flex-col tw-justify-center tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white" :class="[type === 1 ? 'tw-bg-orange-400' : '']">
                    <VIcon icon="tabler-files" size="small" :color="type === 1 ? 'white' : 'black'"></VIcon>
                </div>
                <div @click="type = 2" class="px-3 py-1 tw-flex-col tw-justify-center tw-cursor-pointer hover:tw-bg-orange-500 hover:tw-text-white" :class="[type === 2 ? 'tw-bg-orange-400' : '']">
                    <VIcon icon="tabler-link" size="small" :color="type === 2 ? 'white' : 'black'"></VIcon>
                </div>
            </div>
        </div>
        <div class="tw-flex tw-py-1 tw-mb-2">
            <VTextField v-model="name" placeholder="Title" size="sm"></VTextField>
        </div>
        <template v-if="type === 1">
          <VFileInput
            v-model="file"
            size="small"
            prepend-inner-icon="tabler-file"
            :prepend-icon="false"
            label="Select a file..."
          />
          <a
            v-if="path"
            :href="`/storage/${path.replace('public/', '')}`"
            target="_blank"
            class="tw-text-blue-400 hover:tw-text-blue-600"
          >
            <span>{{ path.replace('public/resources/', '') }}</span>
          </a>
        </template>
        <template v-else>
            <VTextField v-model="link" placeholder="HTTP Link..." prepend-inner-icon="tabler-link"></VTextField>
        </template>
        <div v-if="acknowledgements.length > 0" class="tw-mt-3 tw-mb-1 tw-flex">
            <VTextField type="date" label="Acknowledgement Deadline" prepend-inner-icon="tabler-calendar"></VTextField>
        </div>
        <div class="tw-py-3">
            <VTextarea v-model="description" placeholder="Description..."></VTextarea>
        </div>
        <div class="tw-mt-2 tw-mb-1 tw-flex tw-w-full">
          <TagSelector
              :resource="resourceData"
              :multiple="false"
              :label="'Tags'"
              @selected="selectTags"
              v-model="tags"
          />
        </div>
        <div class="tw-mt-2 tw-mb-2 tw-flex tw-items-center">
            <input
                type="checkbox"
                id="acknowledgement-required"
                v-model="acknowledgement_required"
                class="tw-mr-2"
            />
            <label for="acknowledgement-required" class="tw-cursor-pointer">
                Requires Acknowledgment
            </label>
        </div>
        <div class="tw-mb-2 tw-flex tw-flex-col">
            <label for="due-date" class="tw-block tw-text-gray-800 tw-text-sm tw-mb-1">Due Date</label>
            <VueDatePicker
                :min-date="new Date()"
                v-model="due_date"
                auto-apply
                format="dd/MM/yyyy"
                :enable-time-picker="false"
                value-type="date"
                teleport="body"
                id="due-date"
                placeholder="Date"
            />
        </div>
        <div class="tw-mb-2 tw-flex tw-flex-col">
            <label for="privacySelect" class="tw-block tw-text-gray-800 tw-text-sm tw-mb-1">Share with</label>
            <AppSelect
                id="privacySelect"
                v-model="privacy"
                :items="privacyOptions"
                item-title="label"
                item-value="id"
                density="compact"
                size="sm"
                class="tw-w-full"
            >
                <template #selection="{ props, item }">
                    <div class="tw-flex tw-px-2 tw-py-1 tw-cursor-pointer" v-bind="props">
                        <VIcon :icon="item.raw.icon" class="tw-mr-2"></VIcon>
                        <span>{{item.raw.label}}</span>
                    </div>
                </template>
                <template #item="{ props, item }">
                    <div class="tw-flex tw-px-2 tw-py-1 tw-cursor-pointer hover:tw-bg-gray-100" v-bind="props">
                        <VIcon :icon="item.raw.icon" class="tw-mr-2"></VIcon>
                        <span>{{item.raw.label}}</span>
                    </div>
                </template>
            </AppSelect>
            <div v-if="privacy == 2" class="tw-mt-2">
                <label class="tw-block tw-text-gray-700 tw-text-xs tw-mb-1">Select Users</label>
                <EmployeeSelector v-model="selectedUsers" multiple placeholder="Select users..." />
            </div>
            <div v-else-if="privacy == 5" class="tw-mt-2">
                <label class="tw-block tw-text-gray-700 tw-text-xs tw-mb-1">Select Roles</label>
                <AppSelect
                    v-model="selectedRoles"
                    :items="roleOptions"
                    item-title="label"
                    item-value="id"
                    density="compact"
                    size="sm"
                    multiple
                    placeholder="Select roles..."
                    class="tw-w-full"
                />
            </div>
        </div>
        <div class="tw-flex tw-justify-start tw-py-3">
            <VBtn class="tw-mr-2" :disabled="!formValidated" @click="saveResource">Save</VBtn>
            <VBtn color="secondary" @click="$emit('close')">Cancel</VBtn>
        </div>
    </div>
</template>

<script>
import resourceApi from "@/api/resources/resourceApi";
import EmployeeSelector from "@/components/selectors/EmployeeSelector.vue";
import TagSelector from "@/components/selectors/TagSelector.vue";
import { useRoleStore } from '@/store/roleStore';
import { mapState } from 'pinia';

export default {
    components: {
        EmployeeSelector,
        TagSelector
    },
    props: {
        category: { default: null },
        resourceData: { default: null },
        allTags: { // <-- pass this from parent!
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            type: 1,
            name: null,
            file: null,
            link: null,
            due_date: null,
            description: '',
            tags: {},
            privacy: 1,
            path: null,
            acknowledgements: [],
            acknowledgement_required: false,
            privacyOptions: [
                { id: 1, label: 'With Everyone', icon: 'tabler-eye' },
                { id: 2, label: 'With User', icon: 'tabler-lock' },
                { id: 4, label: 'Archive', icon: 'tabler-archive' },
                { id: 5, label: 'With Role', icon: 'tabler-users-group' },
            ],
            selectedUsers: [],
            selectedRoles: [],
        }
    },
    computed: {
        ...mapState(useRoleStore, { userRoles: 'data' }),
        roleOptions() {
            return (this.userRoles?.roles || []).map(r => ({
                id: r.id,
                label: r.name
            }));
        },
        formValidated() {
            if (this.type === 1) return this.name && (this.file || this.resourceData?.path);
            return this.name && this.link;
        }
    },
    mounted() {
      if (this.resourceData) {
        this.type = this.resourceData.type ?? 1;
        this.name = this.resourceData.name ?? '';
        this.file = null;
        this.link = this.resourceData.type === 2 ? (this.resourceData.path ?? '') : '';
        this.path = this.resourceData.path ?? null; // <-- populate here
        this.description = this.resourceData.description ?? '';
        this.due_date = this.resourceData.due_date ? this.parseDate(this.resourceData.due_date) : null;
        // Preselect tag from allTags by id (match resource_tag_name.id)
        if (this.resourceData && this.resourceData._shareType) {
            if (this.resourceData._shareType === 'role') {
            this.privacy = 5;
            } else if (this.resourceData._shareType === 'user') {
            this.privacy = 2;
            }
            delete this.resourceData._shareType;
        } else {
            switch ((this.resourceData.status || '').toLowerCase()) {
            case 'everyone': this.privacy = 1; break;
            case 'user': this.privacy = 2; break;
            case 'only me': this.privacy = 3; break;
            case 'archive': this.privacy = 4; break;
            case 'role': this.privacy = 5; break;
            default: this.privacy = 1;
            }
        }
        this.selectedUsers = Array.isArray(this.resourceData.employees)
          ? this.resourceData.employees.map(emp => ({
              name: (emp.first_name || '') + ' ' + (emp.last_name || ''),
              user_id: emp.user_id || emp.id
            }))
          : [];
        this.selectedRoles = Array.isArray(this.resourceData.roles)
          ? this.resourceData.roles.map(role =>
              typeof role === 'object' ? { id: role.id, label: role.name } : { id: role, label: '' })
          : [];
        this.acknowledgement_required = !!this.resourceData.acknowledgement_required;
      }
      const roleStore = useRoleStore();
      if (roleStore.fetchRoles) roleStore.fetchRoles();
    },

    watch: {
      file(newVal) {
        if (newVal && newVal.length > 0) {
          this.path = null;
        }
      },
      allTags(newTags) {
        if (this.resourceData && this.resourceData.resource_tag_name && newTags.length > 0) {
          const tagId = this.resourceData.resource_tag_name.id;
          const foundTag = newTags.find(t => t.id === tagId);
          this.tags = foundTag || null;
        }
      }
    },
    methods: {
        selectTags(tags) {
            this.tags = tags;
        },
        parseDate(str) {
            if (!str) return null;
            if (typeof str === 'string' && /^\d{2}-\d{2}-\d{4}$/.test(str)) {
                const [dd, mm, yyyy] = str.split('-');
                return new Date(`${yyyy}-${mm}-${dd}T00:00:00`);
            }
            return new Date(str);
        },
        async saveResource() {
            let form = new FormData();
            let file = this.file?.length > 0 ? this.file[0] : null;
            form.append('type', this.type);
            form.append('name', this.name);
            form.append('description', this.description);
            form.append('file', file);
            form.append('link', this.link);
            form.append('category', this.category?.id);
            form.append('tagid', this.tags?.id);
            form.append('privacy', this.privacy);
            form.append('acknowledgement_required', this.acknowledgement_required ? 1 : 0);
            if (this.privacy == 2 && this.selectedUsers.length > 0) {
                form.append('users', JSON.stringify(this.selectedUsers.map(u => u.user_id || u.id || u)));
            }
            if (this.privacy == 5 && this.selectedRoles.length > 0) {
                form.append('roles', JSON.stringify(this.selectedRoles.map(r => r.id || r)));
            }
            let dueDateStr = '';
            if (this.due_date) {
                let d = this.due_date;
                if (typeof d === 'string' && !d.match(/^\d{2}-\d{2}-\d{4}$/)) d = new Date(d);
                if (d instanceof Date && !isNaN(d)) {
                    const dd = String(d.getDate()).padStart(2, '0');
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const yyyy = d.getFullYear();
                    dueDateStr = `${dd}-${mm}-${yyyy}`;
                } else if (typeof d === 'string' && d.match(/^\d{2}-\d{2}-\d{4}$/)) {
                    dueDateStr = d;
                }
            }
            form.append('due_date', dueDateStr);
            await resourceApi.update(form, this.resourceData.id);
            this.$toast.success('Resource updated!');
            this.$emit('close');
            this.$emit('reload-resources');
        }
    }
}
</script>

<style>
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
