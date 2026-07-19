<template>
  <div class="app-select me-3 d-flex gap-3 mb-5">
    <AppSelect
      :model-value="itemsPerPage"
      :items="itemsPerPageOptions"
      style="inline-size: 6.25rem;"
      @update:model-value="onItemsPerPageChange"
    />
  </div>
  <div class="tw-bg-white tw-shadow">
    <VDataTableServer
      :items-per-page="pagination.per_page"
      :page="pagination.page"
      :items="resources"
      :items-length="pagination.total"
      :headers="headers"
    >
      <template #headers="{ headers }">
        <tr>
          <template v-for="column in headers" :key="column.id">
            <th
              v-for="item in column"
              :key="item.title"
              :class="`custom-header align-${item.align}`"
            >
              <span
                v-if="item.title != 'Employee' && item.sortable"
                style="display: inline-flex; align-items: center;"
                @click="toggleSort(item)"
              >
                <span style="white-space: nowrap;">{{ item.title }}</span>
                <span v-if="isSorted(item)">
                  <VIcon v-if="['desc','descending'].includes(sortBy[0].order)" style="font-size: medium;">tabler-chevron-down</VIcon>
                  <VIcon v-else style="font-size: medium;">tabler-chevron-up</VIcon>
                </span>
                <VIcon v-else style="font-size: medium;">tabler-selector</VIcon>
              </span>
              <span v-else style="text-align: center;">
                <b>{{ item.title }}</b>
              </span>
            </th>
          </template>
        </tr>
      </template>

      <template #item.description="{item}">
        <div>{{ item.description === 'null' ? '' : item.description }}</div>
      </template>

      <template #item.created_by="{item}">
        <div class="tw-items-center">
          <div class="tw-items-center">
            {{ item.created_by && item.created_by.employee
              ? (item.created_by.employee.first_name || '') + ' ' + (item.created_by.employee.last_name || '')
              : ''
            }}
          </div>
        </div>
      </template>
      <template #item.created_at="{item}">
        <div>{{ formatDate(item.created_at, 'DD-MM-YYYY') }}</div>
      </template>

        <template #item.resource_tag_name="{item}">
        <div>
            <span
            v-if="item.resource_tag_name && item.resource_tag_name.color"
            :class="`v-chip v-chip--label v-chip--density-default v-chip--size-default ${getTagBackgroundColor(item.resource_tag_name.color)}`"
            :style="`color: ${getTagTextColor(item.resource_tag_name.color)};border-radius: 5px;`"
            >
            {{ item.resource_tag_name.name === 'null' ? '' : item.resource_tag_name.name }}
            </span>
            <span v-else>-</span>
        </div>
        </template>


      <template #item.status="{item}">
        <ResourcePrivacy
          :item="item"
          :getBackgroundColor="getBackgroundColor"
          :getTextColor="getTextColor"
          @reload-resources="loadResources"
        />
      </template>

      <template #item.type="{item}">
        <div class="tw-flex tw-justify-center tw-w-full tw-relative">
          <div class="tw-relative">
            <v-btn
              color="#FFFFFF"
              variant="outlined"
              class="text-none"
              @click="dlFile(item.extension, item.path)"
            >
              <img :src="getFileImage(item.extension)" style="width: 40px;height: 40px;" alt="">
            </v-btn>
            <span class="tw-text-xs tw-bg-orange-500 tw-text-white tw-rounded tw-text-bold tw-px-1"
              style="position: absolute; right: -10px; bottom: 0;font-size: 0.7rem; font-weight: bold;">{{item.extension}}</span>
          </div>
        </div>
      </template>

      <template #item.action="{ item }">
        <IconBtn>
          <VIcon icon="tabler-dots-vertical" />
          <VMenu
            activator="parent"
            :close-on-content-click="false"
            v-model="menus[item.id]"
          >
            <VCard style="width: 300px; padding: 16px;">
              <VListItem title="Share with Role" @click="openEditPrivacy(item, 'role')" />
              <VListItem title="Share with User" @click="openEditPrivacy(item, 'user')" />
              <VListItem title="Copy Link" @click="handleCopyLink(item)" />
            </VCard>
          </VMenu>
        </IconBtn>
      </template>

      <template #bottom>
        <VDivider />
        <div class="d-flex align-center justify-sm-space-between justify-center flex-wrap gap-3 pa-5 pt-3">
          <p class="text-sm text-disabled mb-0" style="color: #999 !important;">
            {{ generateRangeText() }}
          </p>
          <VPagination
            v-if="pagination.total > pagination.per_page"
            v-model="pagination.page"
            :total-visible="6"
            :length="Math.ceil(pagination.total / pagination.per_page)"
          >
            <template #prev="slotProps">
              <VBtn
                variant="tonal"
                color="default"
                v-bind="slotProps"
                :icon="false"
              >
                <VIcon icon="tabler-chevron-left" />
              </VBtn>
            </template>
            <template #next="slotProps">
              <VBtn
                variant="tonal"
                color="default"
                v-bind="slotProps"
                :icon="false"
              >
                <VIcon icon="tabler-chevron-right" />
              </VBtn>
            </template>
          </VPagination>
        </div>
      </template>
    </VDataTableServer>
  </div>

  <VDialog v-model="showEditForm" persistent width="500">
    <div class="tw-bg-white">
      <div class="tw-shadow tw-rounded">
        <div class="tw-flex tw-px-4 tw-py-1 tw-bg-orange-600 tw-text-white">
          <VIcon icon="tabler-files" class="mr-2"></VIcon>
          <div class="tw-flex flex-col tw-justify-center">
            Edit Resources
          </div>
        </div>
        <EditResource :resourceData="resourceData" :category="category" @close="showEditForm = false" @reload-resources="onReloadResources"></EditResource>
      </div>
    </div>
  </VDialog>

  <VDialog v-model="showViewResources" persistent width="500">
    <div class="tw-bg-white">
      <div class="tw-shadow tw-rounded">
        <div class="tw-flex tw-px-4 tw-py-1 tw-bg-orange-600 tw-text-white">
          <VIcon icon="tabler-files" class="mr-2"></VIcon>
          <div class="tw-flex flex-col tw-justify-center">
            View Resources
          </div>
        </div>
        <ViewResource :resourceData="resourceData" :category="category" @close="showViewResources = false" @reload-resources="showViewResources = false;$emit('reload-resources')"></ViewResource>
      </div>
    </div>
  </VDialog>

  <VDialog v-model="showDeleteResourceDialog" width="400">
    <DialogCloseBtn @click="showDeleteResourceDialog = false" />
    <div class="tw-bg-white tw-rounded-lg  tw-flex tw-flex-col">
      <div class="tw-px-10 tw-py-6 tw-mt-4  tw-flex"><VIcon icon="tabler-trash" class=" tw-text-red-500 tw-mr-3"></VIcon>Are you sure you want to delete this resource request? This cannot be undone.</div>
      <div class="tw-flex tw-justify-end tw-p-4">
        <VBtn @click="deleteResource">Confirm</VBtn>
      </div>
    </div>
  </VDialog>
</template>

<script>
import resourceApi from "@/api/resources/resourceApi";
import toast from '@/composable/toast.js';
import { useAuthStore } from "@/store/authStore";
import { useEmployeeStore } from '@/store/employeeStore';
import { useRoleStore } from '@/store/roleStore';
import _ from 'lodash';
import { mapState } from "pinia";
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import EditResource from './EditResourceForm.vue';
import ResourcePrivacy from './ResourcePrivacy.vue';
import ViewResource from './ViewResourceForm.vue';

export default {
  components: {
    VDataTableServer,
    EditResource,
    ResourcePrivacy,
    ViewResource,
  },
  props: {
    filter: { type: Object, required: false, default: () => ({}) },
    resources: { required: true },
    search: { required: false, default: null },
    perPage: { required: false, default: 10 },
    category: { type: [String, null], default: null },
    pagination: { type: Object, required: true },
    itemsPerPageOptions: { type: Array, default: () => [10, 25, 50, 100, 200, 'All'] }
  },
  data() {
    return {
      headers: [
        { id: 0, title: 'Document #', key: 'id', sortable: true },
        { id: 1, title: 'Title', key: 'name', sortable: true },
        { id: 2, title: 'Description', key: 'description', sortable: true },
        { id: 4, title: 'Created By', key: 'created_by', sortable: true },
        { id: 5, title: 'Date Added', key: 'created_at', sortable: true },
        { id: 6, title: 'Tags', key: 'resource_tag_name', sortable: true },
        { id: 7, title: 'Privacy', key: 'status', sortable: true },
        { id: 8, title: 'Document Type', key: 'type', sortable: true, align: 'center' },
        { id: 9, title: '', key: 'action', sortable: false }
      ],
      showDeleteResourceDialog: false,
      resourceData: {},
      showEditForm: false,
      showViewResources: false,
      sortDesc: null,
      sortBy: [{ key: 'id', order: 'asc' }],
      itemsPerPage: this.pagination.per_page,
      menus: {},
      selectedShareOption: {},
      selectedRoles: {},
      selectedEmployees: [],
    }
  },
  computed: {
    ...mapState(useRoleStore, { rolesData: 'data' }),
    ...mapState(useEmployeeStore, { employeesData: 'data' }),
    roles() {
      return this.rolesData?.roles || [];
    },
    employeesWithFullName() {
      return (this.employeesData?.employees || []).map(emp => ({
        ...emp,
        fullName: `${emp.first_name} ${emp.last_name}`
      }));
    }
  },
  mounted() {
    const roleStore = useRoleStore();
    const employeeStore = useEmployeeStore();
    if (roleStore.fetchRoles) roleStore.fetchRoles();
    if (employeeStore.fetchEmployees) employeeStore.fetchEmployees();
    this.loadResources();
  },
  methods: {
    onReloadResources() {
      this.showEditForm = false;
      this.loadResources();
    },    
  openEditPrivacy(item, shareType = null) {
    this.menus[item.id] = false;
    // Make a shallow copy to avoid polluting the real item
    this.resourceData = { ...item };
    if (shareType) {
      this.resourceData._shareType = shareType;
    }
    this.showEditForm = true;
  },  
    dlFile(extension, link) {
      if (extension === null) {
        link = (link.indexOf('://') === -1) ? 'http://' + link : link;
      }
      if (extension !== null) {
        link = link.replace("public/", "");
        link = "/storage/" + link;
      }
      window.open(link, '_blank');
    },
    editResource(item) {
      this.resourceData = item;
      this.showEditForm = true;
      console.log(item);
    },
    openDialog(item) {
      this.resourceData = item;
      this.showViewResources = true;
    },
    openEditForm(item) {
      this.resourceData = item;
      this.showEditForm = true;
    },
    getFileImage(extension) {
      const basePath = '/assets/images/icons/';
      const ext = typeof extension === 'string' ? extension.toLowerCase().trim() : '';
      switch (ext) {
        case 'mp3': case 'wav': case 'ogg': return basePath + 'audio.png';
        case 'mp4': case 'avi': case 'mov': case 'webm': case 'mkv': return basePath + 'video.png';
        case 'jpg': case 'jpeg': case 'png': case 'gif': case 'bmp': case 'svg': return basePath + 'image.png';
        case 'doc': case 'docx': return basePath + 'word.png';
        case 'pdf': return basePath + 'pdf.png';
        case 'xls': case 'xlsx': case 'csv': return basePath + 'excel.png';
        case 'ppt': case 'pptx': return basePath + 'powerpoint.png';
        case 'zip': case 'rar': case '7z': case 'tar': case 'gz': return basePath + 'compressed.png';
        case '': return basePath + 'link.png';
        default: return basePath + 'file.png';
      }
    },
    setShareOption(id, option, item) {
      this.selectedShareOption[id] = option;
      this.selectedRoles[id] = item.roles || [];
      this.selectedEmployees[id] = item.employees || [];
    },
    async handleArchive(item) {
      await resourceApi.updatePrivacy(item.id, {
        status: "archived",
        employees: [],
        roles: []
      });
      this.menus[item.id] = false;
      this.loadResources();
    },
    async handleMoveToDraft(item) {
      await resourceApi.updatePrivacy(item.id, {
        status: "draft",
        employees: [],
        roles: []
      });
      this.menus[item.id] = false;
      this.loadResources();
    },
    async handleCopyLink(item) {
      try {
        await navigator.clipboard.writeText(item.path);
      } catch (err) {
        console.error("Copy failed", err);
      }
      this.menus[item.id] = false;
      toast('success', 'Copied to Clipboard');
    },
    async save(item) {
      await resourceApi.updatePrivacy(item.id, {
        status: this.selectedShareOption[item.id],
        employees: this.selectedEmployees[item.id],
        roles: this.selectedRoles[item.id]
      });
      this.menus[item.id] = false;
      this.loadResources();
    },
    cancelSelection(item) {
      this.menus[item.id] = false;
    },
    loadFromSearch: _.debounce(function() {
      this.loadResources();
    }, 1000),
    async onItemsPerPageChange(newVal) {
      this.itemsPerPage = newVal;
      this.$emit('update:pagination', { ...this.pagination, per_page: newVal, page: 1 });
      this.loadResources();
    },
    async loadResources() {
      const authStore = useAuthStore();
      const user_role = authStore.user?.role?.id || [];
      let sortBy = this.sortBy && this.sortBy.length > 0 ? this.sortBy[0].key : 'id';
      let sortDirection = this.sortBy && this.sortBy.length > 0 ? this.sortBy[0].order : 'asc';

      const params = {
        category: this.category?.id,
        search: this.search,
        page: this.pagination.page,
        per_page: this.itemsPerPage,
        role_id: user_role,
        sort_by: this.sortBy[0]?.key,
        sort_direction: this.sortBy[0]?.order,
        ...this.filter
      };
      console.log("REEESs", params, this.search)
      const data = await resourceApi.loadResources(params);

      this.pagination.per_page = this.itemsPerPage;
      this.pagination.total = data.total ?? 0;
      this.$emit('update:resources', data.data);
    },
    openDeleteForm(item) {
      this.showDeleteResourceDialog = true;
      this.resourceData = item;
    },
    async deleteResource() {
      await resourceApi.delete(this.resourceData.id)
        .then((success) => {
          this.$toast.success('Resource saved!');
          this.$emit('close');
          this.$emit('reload-resources');
          this.loadResources();
          this.showDeleteResourceDialog = false;
        });
    },
    generateRangeText() {
      let pageNumber = this.pagination.page;
      let totalNumberOfRows = this.pagination.total;
      let rowsPerPage = this.pagination.per_page;
      const startIndex = (pageNumber - 1) * rowsPerPage + 1;
      let endIndex = pageNumber * rowsPerPage;
      if (endIndex > totalNumberOfRows) endIndex = totalNumberOfRows;
      const rowsOnLastPage = totalNumberOfRows % rowsPerPage || rowsPerPage;
      let rangeText;
      if (totalNumberOfRows === 0) rangeText = "No entries";
      else if (rowsPerPage === -1) rangeText = `Showing all of ${totalNumberOfRows} entries`;
      else if (pageNumber === 1 && endIndex === totalNumberOfRows) rangeText = `Showing all of ${totalNumberOfRows} entries`;
      else if (pageNumber === 1) rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`;
      else if (endIndex === totalNumberOfRows) {
        if (rowsOnLastPage === 1) rangeText = `Showing last entry of ${totalNumberOfRows} entries`;
        else rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`;
      } else rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`;
      return rangeText;
    },
    sortItems() {
      if (!this.sortBy) return;
      let sortKey = this.sortBy[0].key;
      this.resources.sort((a, b) => {
        let result = 0;
        let aValue = a[sortKey];
        let bValue = b[sortKey];
        if (aValue === undefined || aValue === null || aValue === '') return this.sortDesc ? 1 : -1;
        if (bValue === undefined || bValue === null || bValue === '') return this.sortDesc ? -1 : 1;
        if (typeof aValue === 'string') aValue = aValue.toLowerCase();
        if (typeof bValue === 'string') bValue = bValue.toLowerCase();
        if (typeof aValue === 'number' && typeof bValue === 'number') result = aValue - bValue;
        else {
          if (aValue < bValue) result = -1;
          else if (aValue > bValue) result = 1;
        }
        return this.sortDesc ? -result : result;
      });
    },
    toggleSort(column) {
      if (this.sortBy[0]?.key === column.key) {
        this.sortBy[0].order = this.sortBy[0].order === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortBy = [{ key: column.key, order: 'asc' }];
      }
      this.loadResources();
    },
    isSorted(column) {
      return this.sortBy[0]?.key === column.key;
    },
    onOptionsUpdate(options) {
      if (options.sortBy && options.sortBy.length > 0) {
        this.sortBy = options.sortBy;
      }
      if (options.page) {
        this.pagination.page = options.page;
      }
      if (options.itemsPerPage) {
        this.itemsPerPage = options.itemsPerPage;
      }
      this.loadResources();
    },
    capitalizeFirst(str) {
      if (str) {
        return str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
      }
      return str;
    },
    getTextColor(item, value) {
      if (item.status && value == 'resource') {
        switch (this.capitalizeFirst(item.status)) {
          case 'Everyone':
          case 'Published':
          case 'Public':
            return '#2BC670';
          case 'Only Me':
            return '#F15BB5';
          case 'Draft':
            return '#808494';
          case 'Archived':
            return '#F1B434';
          case 'Role':
          case 'Restricted':
          case 'User':
            return '#F9183E';
          default:
            return '#F15BB5';
        }
      }
      return '#F15BB5';
    },
    getBackgroundColor(item, value) {
      if (item.status && value == 'resource') {
        switch (this.capitalizeFirst(item.status)) {
          case 'Everyone':
          case 'Published':
          case 'Public':
            return '#2BC670';
          case 'Only Me':
            return '#F15BB5';
          case 'Draft':
            return '#808494';
          case 'Archived':
            return '#F1B434';
          case 'Role':
          case 'Restricted':
          case 'User':
            return '#F9183E';
          default:
            return '#F15BB5';
        }
      }
      return '#F15BB5';
    },
    getTagBackgroundColor(color) {
      switch (color) {
        case 'policy':
          return 'tw-bg-[#FBB001]';
        case 'payroll':
          return 'tw-bg-[#9B5DE5]';
        case 'procedure':
          return 'tw-bg-[#2BC670]';
        case 'process':
          return 'tw-bg-[#F64300]';
        default:
          return 'tw-bg-[#FFFFFF]';
      }
    },
    getTagTextColor(color) {
      switch (color) {
        case 'policy':
          return '#FEF2D6';
        case 'payroll':
          return '#EFE5FB';
        case 'procedure':
          return '#DDF6E8';
        case 'process':
          return '#FEE1D6';
        default:
          return '#000000';
      }
    },
    getEmployeeProfileImage(employee) {
      return employee?.profile_image_url || '/assets/images/default-avatar.png';
    },
    formatDate(date, format) {
      if (!date) return '';
      return date.substring(0, 10).split('-').reverse().join('-');
    },
  },
  watch: {
    filter: {
      deep: true,
      immediate: true,
      handler() {
        this.loadResources();
      }
    },
    'pagination.page': {
      handler: _.debounce(function () {
        this.loadResources();
      }, 300)
    },
    search() {
      console.log("This search", this.search)
      this.loadFromSearch();
    },
    perPage() {
      this.loadResources();
    },
    menus: {
      handler(newVal) {
        for (const id in newVal) {
          if (newVal[id]) {
            const item = this.resources.find(d => d.id === Number(id));
            if (item) {
              this.selectedEmployees[id] = (item.employees || []).map(emp => ({
                ...emp,
                fullName: `${emp.first_name} ${emp.last_name}`
              }));
              this.selectedRoles[id] = item.roles || [];
              this.selectedShareOption[id] = item.status;
            }
          }
        }
      },
      deep: true,
    }
  }
}
</script>

<style scoped>
.custom-header {
  color: #333954 !important;
  font-family: Oswald !important;
  font-size: large;
  font-weight: 400 !important;
}
.app-select { color: #808494; }
.align-left { text-align: left !important; }
.align-center { text-align: center !important; }
.align-right { text-align: right !important; }
.custom-width { width: 50px !important; }
:deep(.v-card-title) { font-weight: 900 !important; }
th.custom-header:nth-last-child(2) { width: 120px !important; }
th.custom-header:last-child { width: 60px !important; }
:deep(.text-primary) { color: #001871 !important; }

</style>
