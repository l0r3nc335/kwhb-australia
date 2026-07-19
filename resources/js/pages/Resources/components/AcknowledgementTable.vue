<template>
<VDialog v-model="showSuccessModal" max-width="400" persistent>
  <VCard class="tw-px-8 tw-pt-10 tw-pb-7 tw-rounded-2xl tw-text-center tw-relative tw-shadow-lg kwbh-success-card">
    <button
      @click="showSuccessModal = false"
      class="tw-absolute tw-top-5 tw-right-5 tw-h-8 tw-w-8 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-white tw-border tw-border-[#111] tw-text-black hover:tw-bg-gray-100 tw-transition"
      aria-label="Close"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
    <div class="tw-flex tw-justify-center tw-mb-4 tw--mt-8">
      <!-- GREEN CHECK ICON -->
      <img src="/assets/images/icons/check-green.svg" alt="Success" class="tw-h-[72px] tw-w-[72px] tw-object-contain" />
    </div>
    <div class="tw-font-bold tw-text-lg tw-text-[#182656] tw-mb-2">{{ successModalMessage }}</div>
    <div class="tw-flex tw-justify-center tw-mt-3 tw-mb-1">
      <VBtn
        style="background-color: #2BC670; color: #fff; font-weight: 700; width:93px;"
        class="tw-rounded tw-shadow-none tw-text-base tw-py-2"
        @click="showSuccessModal = false"
      >OK</VBtn>
    </div>
  </VCard>
</VDialog>  
  <!-- ACKNOWLEDGE MODAL -->
<VDialog v-model="showAcknowledgeModal" persistent max-width="800px">
  <div class="tw-bg-white tw-shadow tw-rounded tw-pb-4 tw-relative" style="max-width: 800px;">
    <!-- Custom Close Button (absolute positioned) -->
    <button
      @click="showAcknowledgeModal = false"
      class="tw-absolute tw-top-5 tw-right-5 tw-h-8 tw-w-8 tw-flex tw-items-center tw-justify-center tw-rounded-full tw-bg-white tw-border tw-border-[#111] tw-text-black hover:tw-bg-gray-100 tw-transition"
      aria-label="Close"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
    <div class="tw-flex tw-justify-between tw-items-center tw-px-5 tw-py-3 tw-bg-gray-100 tw-border-b">
      <div class="font-semibold text-lg">{{ resourceData && resourceData.name ? resourceData.name : 'Resource' }}</div>
    </div>
  <div style="max-height: 70vh; overflow-y: auto;" class="tw-p-5">
    <!-- Images -->
    <img
      v-if="selectedAcknowledgeFile && isImage(selectedAcknowledgeFile)"
      :src="selectedAcknowledgeFile"
      alt="Resource Document"
      style="max-width: 100%; max-height: 60vh; display: block; margin: 0 auto;"
    />

    <!-- Word: Google Docs Viewer -->
    <iframe
      v-else-if="selectedAcknowledgeFile && (isWord(selectedAcknowledgeFile) || isPdf(selectedAcknowledgeFile))"
      :src="getGoogleDocsEmbedUrl(selectedAcknowledgeFile)"
      style="width: 100%; height: 60vh; border: none;"
      @error="canPreview = false"
    />

    <!-- Excel/PowerPoint: Office Online -->
    <iframe
      v-else-if="selectedAcknowledgeFile && isExcelOrPpt(selectedAcknowledgeFile)"
      :src="getOfficeAppsEmbedUrl(selectedAcknowledgeFile)"
      style="width: 100%; height: 60vh; border: none;"
      @error="canPreview = false"
    />

    <!-- Fallback Download -->
    <div v-else-if="selectedAcknowledgeFile">
      <div class="tw-text-center tw-mb-4 tw-text-gray-500">
        Can't preview this file. <br>
        <a
          :href="getServeUrl(selectedAcknowledgeFile)"
          target="_blank"
          class="tw-text-blue-600 tw-underline"
          download
        >
          Download file
        </a>
      </div>
    </div>

    <!-- No file -->
    <div v-else class="tw-text-center tw-p-10 tw-text-gray-400">No file to display.</div>
  </div>

    <div class="tw-flex tw-justify-end tw-gap-3 tw-px-5">
      <VBtn variant="outlined" @click="showAcknowledgeModal = false">Cancel</VBtn>
      <VBtn @click="acknowledgeResource" :loading="acknowledgeLoading">Acknowledged</VBtn>
    </div>
  </div>
</VDialog>


  <!-- PER PAGE SELECT -->
  <div class="app-select me-3 d-flex gap-3 mb-5">
    <AppSelect
      :model-value="itemsPerPage"
      :items="itemsPerPageOptions"
      style="inline-size: 6.25rem;"
      @update:model-value="onItemsPerPageChange"
    />
  </div>

  <!-- DATATABLE -->
  <div class="tw-bg-white tw-shadow">
    <VDataTableServer
      :items-per-page="pagination.per_page"
      :page="pagination.page"
      :items="resources"
      :items-length="pagination.total"
      :headers="headersToUse"
      :sort-by.sync="sortBy"
      @update:options="onOptionsUpdate"
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
                v-if="item.title !== 'Employee' && item.sortable"
                style="display: inline-flex; align-items: center; cursor:pointer;"
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

<template #item="{ item, index }">
  <tr
    class="tw-cursor-pointer hover:tw-bg-orange-100"
    @click="openAcknowledgeModal(item)"
  >
    <!-- Document # -->
    <td>{{ item.id }}</td>
    <!-- Title -->
    <td>{{ item.name }}</td>
    <!-- Super Admin 'Acknowledged By' -->
    <td v-if="isSuperAdminFromAPI">{{ item.acknowledged_by }}</td>
    <!-- Date Added -->
    <td>{{ formatDate(item.created_at, 'DD-MM-YYYY') }}</td>
    <!-- Due Date -->
    <td>{{ formatDateDateOnly(item.due_date, 'DD-MM-YYYY') }}</td>
    <!-- Tags -->
    <td>
      <span
        v-if="item.resource_tag_name"
        :class="`v-chip v-chip--label v-chip--density-default v-chip--size-default ${getTagBackgroundColor(item.resource_tag_name.color)}`"
        :style="`color: ${getTagTextColor(item.resource_tag_name.color)};border-radius: 5px;`"
      >
        {{ item.resource_tag_name.name === 'null' ? '' : item.resource_tag_name.name }}
      </span>
      <span v-else>-</span>
    </td>
    <!-- Acknowledgement -->
    <td>
      <div class="tw-flex tw-justify-center">
        <ResourceAcknowledgementChip :item="item" />
      </div>
    </td>
    <!-- Document Type -->
    <td>
      <div class="tw-flex tw-justify-center tw-w-full tw-relative">
        <div class="tw-relative">
          <v-btn
            color="#FFFFFF"
            variant="outlined"
            class="text-none"
            @click.stop="dlFile(item.extension, item.path)"
          >
            <img :src="getFileImage(item.extension)" style="width: 40px;height: 40px;" alt="">
          </v-btn>
          <span class="tw-text-xs tw-bg-orange-500 tw-text-white tw-rounded tw-text-bold tw-px-1"
                style="position: absolute; right: -10px; bottom: 0;font-size: 0.7rem; font-weight: bold;">{{item.extension}}</span>
        </div>
      </div>
    </td>
    <!-- Action menu -->
    <td>
      <IconBtn>
        <VIcon icon="tabler-dots-vertical" />
        <VMenu
          activator="parent"
          :close-on-content-click="false"
          v-model="menus[index]"
        >
          <VCard style="width: 300px; padding: 16px;">
            <VListItem title="Share with Role" @click="openEditPrivacy(item, index, 'role')" />
            <VListItem title="Share with User" @click="openEditPrivacy(item, index, 'user')" />
            <VListItem title="Copy Link" @click="handleCopyLink(item, index)" />
          </VCard>
        </VMenu>
      </IconBtn>
    </td>
  </tr>
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
              <VBtn variant="tonal" color="default" v-bind="slotProps" :icon="false">
                <VIcon icon="tabler-chevron-left" />
              </VBtn>
            </template>
            <template #next="slotProps">
              <VBtn variant="tonal" color="default" v-bind="slotProps" :icon="false">
                <VIcon icon="tabler-chevron-right" />
              </VBtn>
            </template>
          </VPagination>
        </div>
      </template>
    </VDataTableServer>
  </div>

  <!-- EDIT FORM DIALOG -->
  <VDialog v-model="showEditForm" persistent width="500">
    <div class="tw-bg-white">
      <div class="tw-shadow tw-rounded">
        <div class="tw-flex tw-px-4 tw-py-1 tw-bg-orange-600 tw-text-white">
          <VIcon icon="tabler-files" class="mr-2"></VIcon>
          <div class="tw-flex flex-col tw-justify-center">
            Edit Resources
          </div>
        </div>
          <EditResource
            :resourceData="resourceData"
            :category="category"
            :shareType="resourceData?._shareType"
            @close="showEditForm = false"
            @reload-resources="onReloadResources"
          />
      </div>
    </div>
  </VDialog>

  <!-- VIEW DIALOG -->
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

  <!-- DELETE DIALOG -->
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
import acknowledgementApi from "@/api/acknowledgement/acknowledgementApi";
import resourceApi from "@/api/resources/resourceApi";
import toast from '@/composable/toast.js';
import { useAuthStore } from "@/store/authStore";
import { useEmployeeStore } from '@/store/employeeStore';
import { useRoleStore } from '@/store/roleStore';
import dayjs from 'dayjs';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import _ from 'lodash';
import { VDataTableServer } from 'vuetify/labs/VDataTable';
import EditResource from './EditResourceForm.vue';
import ResourceAcknowledgementChip from './ResourceAcknowledgementChip.vue';
import ResourcePrivacy from './ResourcePrivacy.vue';
import ViewResource from './ViewResourceForm.vue';


dayjs.extend(utc)
dayjs.extend(timezone)

export default {
  components: {
    VDataTableServer,
    ResourceAcknowledgementChip,
    ResourcePrivacy,
    EditResource,
    ViewResource,
  },
  props: {
    resources: { required: true },
    search: { required: false, default: null },
    perPage: { required: false, default: 10 },
    category: { type: [String, null], default: null },
    pagination: { type: Object, required: true },
    itemsPerPageOptions: { type: Array, default: () => [10, 25, 50, 100, 200, 'All'] },
    filter: { // Add this
        type: Object,
        required: false,
        default: () => ({})
    },    
  },
  data() {
    return {
      // dialogs & states
      showAcknowledgeModal: false,
      selectedAcknowledgeFile: '',
      acknowledgeResourceId: null,
      acknowledgeLoading: false,
      canPreview: true,
      showEditForm: false,
      showViewResources: false,
      resourceData: {},
      isEditDrawerVisible: false,
      isResourceDialogVisible: false,
      showDeleteResourceDialog: false,
      isSuperAdminFromAPI: false,

      sortDesc: null,
      sortBy: [{ key: 'id', order: 'asc' }],
      itemsPerPage: this.pagination.per_page,
      menus: {},
      selectedShareOption: {},
      selectedRoles: {},
      selectedEmployees: [],
      showSuccessModal: false,
      successModalMessage: 'You acknowledged a resource',

      // headers: [
      //   { id: 0, title: 'Resource ID', key: 'id', sortable: true },
      //   // { id: 1, title: 'User', key: 'acknowledged_by', sortable: true },
      //     ...(this.isSuperAdminFromAPI ? [{ id: 1, title: 'User', key: 'acknowledged_by', sortable: true }] : []),

      //   { id: 1, title: 'Resource ID', key: 'name', sortable: true },
      //   { id: 5, title: 'Date Added', key: 'created_at', sortable: true },
      //   { id: 11, title: 'Due Date', key: 'due_date', sortable: true },
      //   { id: 6, title: 'Tags', key: 'resource_tag_name', sortable: true },
      //   { id: 7, title: 'Privacy', key: 'is_acknowledged', sortable: true },
      //   { id: 9, title: 'Document Type', key: 'type', sortable: true, align: 'center' },
      //   { id: 10, title: '', key: 'action', sortable: false }
      // ],
    };
  },
  computed: {
  headersToUse() {
    // Only add User column if super admin
    return [
      { id: 0, title: 'Document #', key: 'id', sortable: true },
      { id: 1, title: 'Title', key: 'name', sortable: true },
      ...(this.isSuperAdminFromAPI ? [{ id: 1, title: 'Acknowledged By', key: 'acknowledged_by', sortable: true }] : []),
      { id: 5, title: 'Date Added', key: 'created_at', sortable: true },
      { id: 11, title: 'Due Date', key: 'due_date', sortable: true },
      { id: 6, title: 'Tags', key: 'resource_tag_name', sortable: true },
      { id: 7, title: 'Acknowledgement', key: 'is_acknowledged', sortable: true },
      { id: 9, title: 'Document Type', key: 'type', sortable: true, align: 'center' },
      { id: 10, title: '', key: 'action', sortable: false }
    ];
  },    
  isSuperAdmin() {
    const authStore = useAuthStore();
    // Adjust to match your actual super admin role name
    return (
      authStore.user &&
      authStore.user.role &&
      typeof authStore.user.role.name === 'string' &&
      authStore.user.role.name.trim().toLowerCase() === 'super user'
    );
  },    
    roles() {
      // Use Pinia store directly here as in your script setup
      const store = useRoleStore();
      return store.data?.roles || [];
    },
    employeesWithFullName() {
      const store = useEmployeeStore();
      return (store.data?.employees || []).map(emp => ({
        ...emp,
        fullName: `${emp.first_name ?? ''} ${emp.last_name ?? ''}`
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
    isPreviewableByGoogleDocs(src) {
      return /\.(pdf|ppt|pptx|xls|xlsx)$/i.test(src || '');
    },    
    getFullUrl(relativeUrl) {
      if (/^https?:\/\//.test(relativeUrl)) return relativeUrl;
      return `${window.location.origin}${relativeUrl}`;
    },    
    getServeUrl(filename) {
      // If it's a full URL, return as is.
      if (/^https?:\/\//.test(filename)) return filename;
      // Remove any leading slashes or `public/` if present.
      filename = filename.replace(/^\/?public\/?/, '').replace(/^\/?storage\/?resources\/?/, '');
      // Always return the absolute serve URL
      return `${window.location.origin}/resources/serve/${filename}`;
    },   
    getOriginalStorageUrl(filename) {
      if (/^https?:\/\//.test(filename)) return filename;
      // Remove leading slash
      filename = filename.replace(/^\/?(public|storage\/resources)\/?/, '');
      return `${window.location.origin}/storage/resources/${filename}`;
    },
    onReloadResources() {
      this.showEditForm = false;
      this.loadResources();
    },
    loadFromSearch: _.debounce(function() {
      this.loadResources();
    }, 1000),    
    openEditPrivacy(item, index, type) {
      this.menus[index] = false; // close menu
      this.resourceData = { ...item, _shareType: type }; // add type to resourceData
      this.showEditForm = true;
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
    formatDateDateOnly(dateStr) {
      if (!dateStr) return '-'
      return dayjs(dateStr).tz('Australia/Sydney').format('DD-MM-YYYY')
    },    
    openAcknowledgeModal(item) {
      if (!item.path) return;
      let url = item.path;
      if (url.startsWith('public/')) {
        url = '/storage/' + url.replace(/^public\//, '');
      }
      this.selectedAcknowledgeFile = url;
      this.acknowledgeResourceId = item.id;
      this.resourceData = item; // <---- THIS IS NEEDED!      
      this.showAcknowledgeModal = true;
      this.canPreview = true;
    },
    isImage(src) {
      return /\.(jpg|jpeg|png|gif|bmp|svg)$/i.test(src || '');
    },
    isPdf(src) {
      return /\.pdf$/i.test(src || '');
    },    
    isWord(src) {
      return /\.(doc|docx)$/i.test(src || '');
    },    
    isExcelOrPpt(src) {
      return /\.(xls|xlsx|ppt|pptx)$/i.test(src || '');
    },   
    getOfficeAppsEmbedUrl(fileUrl) {
      const serveUrl = this.getServeUrl(fileUrl);
      return `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(serveUrl)}`;
    },     
    getGoogleDocsEmbedUrl(fileUrl) {
      const serveUrl = this.getOriginalStorageUrl(fileUrl);
      return `https://docs.google.com/gview?url=${encodeURIComponent(serveUrl)}&embedded=true`;
    },    
    // async acknowledgeResource() {
    //   this.acknowledgeLoading = true;
    //   try {
    //     await resourceApi.acknowledgeResource(this.acknowledgeResourceId);
    //     this.showAcknowledgeModal = false;
    //     if (typeof this.loadResources === 'function') this.loadResources();
    //     toast('success', 'Resource acknowledged!');
    //   } catch (e) {
    //     toast('error', 'Failed to acknowledge');
    //   }
    //   this.acknowledgeLoading = false;
    // },

    async acknowledgeResource() {
      this.acknowledgeLoading = true
      try {
        await acknowledgementApi.save({
          resource_id: this.acknowledgeResourceId,
          is_acknowledged: true
        })
        this.showAcknowledgeModal = false
        // Optionally reload resources (if you want the UI to update)
        if (typeof this.loadResources === 'function') this.loadResources()
        this.successModalMessage = 'You acknowledged a resource'
        this.showSuccessModal = true        
        // this.$toast.success('Resource acknowledged!')
      } catch (e) {
        this.$toast.error('Failed to acknowledge')
      }
      this.acknowledgeLoading = false
    },    
    // edit/view/delete dialog triggers
    openDialog(item) {
      this.resourceData = item;
      this.showViewResources = true;
    },
    openEditForm(item) {
      this.resourceData = item;
      this.showEditForm = true;
    },
    openDeleteForm(item) {
      this.showDeleteResourceDialog = true;
      this.resourceData = item;
    },
    async deleteResource() {
      await resourceApi.delete(this.resourceData.id)
        .then(() => {
          this.$toast.success('Resource saved!');
          this.$emit('close');
          this.$emit('reload-resources');
          this.loadResources();
          this.showDeleteResourceDialog = false;
        });
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
    async onItemsPerPageChange(newVal) {
      this.itemsPerPage = newVal;
      this.$emit('update:pagination', { ...this.pagination, per_page: newVal, page: 1 });
      this.loadResources();
    },
    async loadResources() {
      const authStore = useAuthStore();
      const user_role = authStore.user?.role?.id || [];

      // Spread filters into params, so all values get sent!
      const params = {
        category: this.category?.id,
        search: this.search,
        page: this.pagination.page,
        per_page: this.itemsPerPage,
        role_id: user_role,
        sort_by: this.sortBy[0]?.key,
        sort_direction: this.sortBy[0]?.order,
        ...this.filter, // <-- this spreads division, job_title, status, due_date, etc
      };

      console.log("$$$$", params)


      // If due_date is a JS Date, format to yyyy-mm-dd
      if (params.due_date instanceof Date) {
        params.due_date = params.due_date.toISOString().split('T')[0];
      }

      // Remove null/undefined/empty filters so backend is clean
      Object.keys(params).forEach(key => {
        if (params[key] === null || params[key] === undefined || params[key] === '') {
          delete params[key];
        }
      });

      const data = await resourceApi.loadResourcesWithAcknowledgement(params);
      this.isSuperAdminFromAPI = !!data.is_super_admin;

      this.pagination.per_page = data.per_page || this.itemsPerPage;
      this.pagination.total = data.total ?? 0;
      this.$emit('update:resources', data.data);

this.menus = {};
this.resources.forEach((item, idx) => {
  this.menus[idx] = false;
});      
      
    },

    generateRangeText() {
      let pageNumber = this.pagination.page;
      let totalNumberOfRows = this.pagination.total;
      let rowsPerPage = this.pagination.per_page;
      const startIndex = (this.pagination.page - 1) * this.pagination.per_page + 1;
      let endIndex = this.pagination.page * this.pagination.per_page;
      if (endIndex > this.pagination.total) endIndex = this.pagination.total;
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
      this.resources.sort((a, b) => {
        let result = 0;
        let aValue = a[this.sortBy.value];
        let bValue = b[this.sortBy.value];
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
    capitalizeFirst(str) {
      if (str) {
        return str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
      }
      return str;
    },
    getFileImage(extension) {
      const basePath = '/assets/images/icons/';
      const ext = typeof extension === 'string'
        ? extension.toLowerCase().trim()
        : '';
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
    getTextColor(item, value) {
      if (item.status && value == 'resource') {
        switch (this.capitalizeFirst(item.status)) {
          case 'Everyone': case 'Published': case 'Public': return '#2BC670';
          case 'Only Me': return '#F15BB5';
          case 'Draft': return '#808494';
          case 'Archived': return '#F1B434';
          case 'Role': case 'Restricted': case 'User': return '#F9183E';
          default: return '#F15BB5';
        }
      }
      return '#F15BB5';
    },
    getBackgroundColor(item, value) {
      if (item.status && value == 'resource') {
        switch (this.capitalizeFirst(item.status)) {
          case 'Everyone': case 'Published': case 'Public': return '#2BC670';
          case 'Only Me': return '#F15BB5';
          case 'Draft': return '#808494';
          case 'Archived': return '#F1B434';
          case 'Role': case 'Restricted': case 'User': return '#F9183E';
          default: return '#F15BB5';
        }
      }
      return '#F15BB5';
    },
    getTagBackgroundColor(color) {
      switch (color) {
        case 'policy': return 'tw-bg-[#FBB001]';
        case 'payroll': return 'tw-bg-[#9B5DE5]';
        case 'procedure': return 'tw-bg-[#2BC670]';
        case 'process': return 'tw-bg-[#F64300]';
        default: return 'tw-bg-[#FFFFFF]';
      }
    },
    getTagTextColor(color) {
      switch (color) {
        case 'policy': return '#FEF2D6';
        case 'payroll': return '#EFE5FB';
        case 'procedure': return '#DDF6E8';
        case 'process': return '#FEE1D6';
        default: return '#000000';
      }
    },
    getEmployeeProfileImage(employee) {
      // Add your own implementation if you need to show employee avatar
      return employee?.profile_image_url || '/assets/images/default-avatar.png';
    },
    formatDate(date, format) {
      // Use moment.js or dayjs if you wish, or implement as needed
      if (!date) return '';
      // Dummy implementation, replace with your date formatting library
      return date.substring(0, 10).split('-').reverse().join('-');
    },
  },
  watch: {
    filter: {
      deep: true,
      immediate: true,
      handler(val) {
        this.loadResources();
      }
    },
    'pagination.page': {
      handler: _.debounce(function () { this.loadResources(); }, 300)
    },
    search() {
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
};
</script>

<style scoped>
.custom-header {
  color: #333954 !important;
  font-family: Oswald !important;
  font-size: large;
  font-weight: 400 !important;
}
.app-select {
  color: #808494;
}
.align-left { text-align: left !important; }
.align-center { text-align: center !important; }
.align-right { text-align: right !important; }
.custom-width { width: 50px !important; }
:deep(.v-card-title) { font-weight: 900 !important; }
th.custom-header:nth-last-child(2) { width: 120px !important; }
th.custom-header:last-child { width: 60px !important; }
:deep(.text-primary) { color: #001871 !important; }
.kwbh-success-card {
  padding: 90px 50px 20px 50px !important;
}
</style>
