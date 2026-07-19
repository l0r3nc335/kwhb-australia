<script setup>
import employeeApi from "@/api/employee/employeeApi";
import toast from '@/composable/toast.js';
import { useEmployeeStore } from "@/store/employeeStore";
import moment from "moment";

const upload_file = ref(null)
const notesVal = ref(null)
const notesTotal = ref(null)
const isActive = ref(false)
const loading = ref(false)
const loadingDelete = ref(false)
const dialog = ref(false)
const itemselected = ref('')
const itemsPerPage = ref(5)
const noteSelected = ref('')
const query = reactive({
    search: '',
    subject: '',
    date_filter: '',
    page: 1
})

const form = ref({
    note: '',
    subject: '',
    subject_title: [],
    tag: [],
    file_data: '',
    file_url: '',
})

const categories = [
    'Employee/Personal Details update',
    'Payroll Enquiry',
    'Leave Enquiry',
    'HR/EBA Enquiry',
    'Performance Management/Disciplinary Matters',
    'Professional Development',
    'Recruitment Enquiry',
    'Other',
]

const tags = [
    'Only Me',
    'HR Only',
    'Finance Only'
]

const empData = ref(null)
const employeeStore = useEmployeeStore();
empData.value = employeeStore.data.employee_selected

const editorOptions = ref({
  theme: 'snow',
  modules: {
    toolbar: {
      container: [
        ['bold', 'italic', 'underline',{ list: 'ordered' }, { list: 'bullet' },'link', 'image', 'attachment'],
      ]
    }
  },
});

const close = () => {
    isActive.value = false
    form.value.note = ''
    form.value.subject = ''
    form.value.subject_title = []
    form.value.tag = []
    form.value.file_data = ''
    form.value.file_url = ''
    noteSelected.value = ''
}

const minimize = () => {
    isActive.value = false
}

const createNote = async () => {
  try {
    loading.value = true
    const formData = new FormData()
    formData.append('note', form.value.note)
    formData.append('subject', form.value.subject)
    formData.append('subject_title', form.value.subject_title)
    formData.append('tag', form.value.tag)
    formData.append('user_id', empData.value.user_id)
    formData.append('file', form.value.file_data)

    const response = await employeeApi.add_notes(formData)
    toast('success', response.message)
    loading.value = false
    isActive.value = false
    form.value.note = ''
    form.value.subject = ''
    form.value.subject_title = []
    form.value.tag = []
    notes()
  } catch (error) {
    console.error(error)
  }
}

const updateNote = async () => {
  try {
    loading.value = true
    const formData = new FormData()
    formData.append('note', form.value.note)
    formData.append('subject', form.value.subject)
    formData.append('subject_title', form.value.subject_title)
    formData.append('tag', form.value.tag)
    formData.append('user_id', empData.value.user_id)
    formData.append('id', itemselected.value)
    formData.append('file', form.value.file_data)

    const response = await employeeApi.update_notes(formData)
    toast('success', response.message)
    loading.value = false
    isActive.value = false
    notes()
  } catch (error) {
    console.error(error)
  }
}

const deleteNote = async () => {
    loadingDelete.value = true
    const response = await employeeApi.delete_notes({ id : itemselected.value })
    toast('success', response.message)
    loadingDelete.value = false
    dialog.value = false
    isActive.value = false
    notes()
}

const notes = async () => {
  try {
    const response = await employeeApi.fetch_notes({ id: empData.value.user_id, ...query })
    notesVal.value = response.data
    notesTotal.value = response.total
  } catch (error) {
    console.error(error)
  }
}
watch(
  () => query.search,
  (first, second) => {
    notes()
  },
)
watch(
  () => query.subject,
  (first, second) => {
    notes()
  },
)
watch(
  () => query.date_filter,
  (first, second) => {
    notes()
  },
)
watch(
  () => query.page,
  (first, second) => {
    notes()
  },
)
notes()

const dateFormat = (date) => {
    var dt = moment(new Date(date))
    return dt.format('DD MMM YYYY');
  }

  const tagColor = (tag) => {
    switch (tag) {
        case 'Only Me':
        return {
            bgColor: '#E5E7F1',
            color: '#061E74'
        }
        case 'HR Only':
        return {
            bgColor: '#FEEEF7',
            color: '#F260B7'
        }
        case 'Finance Only':
        return {
            bgColor: '#FCEDE6',
            color: '#E35205'
        }
        default:
        return {
            bgColor: '',
            color: ''
        }
    }
  }

  const showDialogAdd = () => {
    isActive.value = true
    itemselected.value = ''
  }

  const showDialogUpdate = (item) => {
    isActive.value = true
    itemselected.value = item.id
    form.value.note = item.note
    form.value.subject = item.subject
    form.value.subject_title = item.subject_title
    form.value.tag = item.tag
    noteSelected.value = item
  }

  const noteTag = (tag) => {
    if(tag == 'HR Only') {
        return 'HR'
    }else if(tag == 'Finance Only') {
        return 'Finance'
    }
  }

  const generateRangeText = (pageNumber, totalNumberOfRows, rowsPerPage) => {
  const startIndex = (pageNumber - 1) * rowsPerPage + 1
  let endIndex = pageNumber * rowsPerPage
  if (endIndex > totalNumberOfRows) {
    endIndex = totalNumberOfRows
  }

  const rowsOnLastPage = totalNumberOfRows % rowsPerPage || rowsPerPage

  let rangeText
  if (totalNumberOfRows === 0) {
    rangeText = "No entries"
  } else if (pageNumber === 1 && endIndex === totalNumberOfRows) {
    rangeText = `Showing all of ${totalNumberOfRows} entries`
  } else if (pageNumber === 1) {
    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
  } else if (endIndex === totalNumberOfRows) {
    if (rowsOnLastPage === 1) {
      rangeText = `Showing last entry of ${totalNumberOfRows} entries`
    } else {
      rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
    }
  } else {
    rangeText = `Showing ${startIndex} to ${endIndex} of ${totalNumberOfRows} entries`
  }

  return rangeText
}
const onSelectFile = () => {
  const input = upload_file
  const files = input.value.files
  if (files && files[0]) {
    const size = (files[0].size / 1024 / 1024).toFixed(2)
    if (size > 5) {
      toast(
        'error',
        'File size too large. Please select file not exceeding to 5mb'
      )
      return
    }
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.file_data = files[0]
      console.log(files[0])
      form.value.file_url = e.target.result
    }
    reader.readAsDataURL(files[0])
  }
}

const triggerSelectFile = () => {
  upload_file.value.click()
}

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

<template>
    <div class="d-flex justify-space-between">
        <div>
            <span class="font-weight-bold text-h4">Notes</span>
        </div>
        <div style="color: #C0C0C0;" class="d-flex flex-wrap gap-3">
            <AppTextField
                placeholder="Search"
                :model-value="query.search"
                @update:modelValue="(v) => (query.search = v)"
                prepend-inner-icon="tabler-search"
                density="compact"
                style="inline-size: 15rem;"
                clearable
            />
            <AppSelect
                placeholder="Select Subject Category"
                variant="outlined"
                @update:modelValue="(v) => (query.subject = v)"
                style="inline-size: 18em;"
                :items="categories"
                clearable
            />
            <AppDateTimePicker
                placeholder="Select date range"
                v-model="query.date_filter"
                :config="{ mode: 'range', dateFormat: 'd/m/Y' }"
                style="inline-size: 15em;"
            />
        </div>
    </div>
    <div class="mt-8 ml-5 ptext">
        <v-row class="">
            <v-col cols="5">
                <v-row>
                    <v-col cols="6">
                        <span>SUBJECT</span>
                    </v-col>                    
                    <v-col cols="4">
                        <span>NOTE CREATOR</span>
                    </v-col>
                </v-row>
            </v-col>
            <v-col cols="7">
                <v-row>
                    <v-col class="text-center">
                        <span>ATTACHMENT</span>
                    </v-col>
                    <v-col class="text-center">
                        <span>DATE</span>
                    </v-col>
                    <v-col class="text-center">
                        <span>TIME</span>
                    </v-col>
                    <v-col class="text-center">
                        <span>TAG</span>
                    </v-col>
                    <v-col></v-col>
                </v-row>
            </v-col>
        </v-row>
    </div>
    <v-divider class="mt-3 mb-3" :thickness="2"/>
    <div class="text-center" v-if="notesVal && !notesVal.length">
        No data available
    </div>
    <div v-else class="mt-5 ml-5 mb-3 text-subtitle-1"
        v-for="(data, i) in notesVal"
        :key="i"
    >
        <v-row class="">
            <v-col cols="5">
                <v-row class="mb-3">
                    <v-col cols="6">
                        <div>
                            <span>{{ data.subject_title }}</span>
                        </div>
                        <div class="tw-py-2">
                            <span class="font-weight-bold">{{ data.subject }}</span>
                        </div>
                    </v-col>
                    <v-col cols="4">
                        <div>
                            <span>{{ data.user.employee.first_name +" "+ data.user.employee.last_name }}</span>
                        </div>
                        <div class="tw-py-2">
                            <div class="note-desc" v-html="data.note"/>
                        </div>
                    </v-col>                    
                </v-row>
            </v-col>
            <v-col cols="7">
                <v-row>
                    <v-col class="text-center">
                        <a :href="`/storage/attachments/notes/` + data.file" target="_blank" class="tw-text-blue-400 hover:tw-text-blue-600">
                            <VIcon icon="tabler-paperclip" size="sm" class="tw-mr-1"></VIcon>
                            <span>{{ data.file_name}}</span>
                        </a>
                    </v-col>
                    <v-col class="text-center">
                        <span>{{ dateFormat(data.created_at) }}</span>
                    </v-col>
                    <v-col class="text-center">
                        <span>{{ moment(data.created_at, "HH:mm:ss").format('hh:mma') }}</span>
                    </v-col>
                    <v-col class="text-center tw-flex tw-justify-center">
                        <div v-if="data.tag">
                            <VChip
                                label
                                size="default"
                                :style="{
                                    background: tagColor(data.tag).bgColor,
                                    color: tagColor(data.tag).color,
                                    borderRadius: '5px',
                                }"
                            >
                                <span class="font-weight-bold">{{ data.user_id == empData.user_id ? data.tag : noteTag(data.tag) }}</span>
                            </VChip>
                        </div>
                        <div v-else class="tw-border tw-rounded-lg tw-py-1 tw-w-fit tw-px-3">
                            Anyone
                        </div>
                    </v-col>
                    <v-col class="text-left">
                        <VMenu location="end" v-if="data.user_id == empData.user_id">
                            <template #activator="{ props }">
                                <IconBtn v-bind="props">
                                    <VIcon
                                        size="25"
                                        color="#808494"
                                        class="mb-1"
                                        icon="mdi-dots-vertical"
                                    />
                                </IconBtn>
                            </template>
                            <VList>
                                <!-- <VListItem class="hand text-primary" @click="showDialogUpdate(item.raw,true)">
                                    <template #prepend>
                                    <VIcon icon="tabler-eye" />
                                    </template>
                                    <VListItemTitle> View </VListItemTitle>
                                </VListItem> -->

                                <VListItem class="hand" style="color: #E35205" @click="showDialogUpdate(data)">
                                    <template #prepend>
                                        <VIcon icon="tabler-edit" />
                                    </template>
                                    <VListItemTitle> Edit </VListItemTitle>
                                </VListItem>

                                <!-- <VListItem class="hand text-red"  @click="showDialog(item.raw.id)">
                                    <template #prepend>
                                    <VIcon icon="tabler-trash" />
                                    </template>
                                    <VListItemTitle> Deactivate </VListItemTitle>

                                </VListItem> -->
                            </VList>
                        </VMenu>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </div>
    <v-divider class="mt-5 mb-5" :thickness="2"/>
    <div class="d-flex align-center justify-sm-space-between justify-center mb-5" v-if="notesVal">
        <p
        class="text-sm text-disabled mb-0"
        style="color: #999 !important;"
        >
        {{ generateRangeText(query.page, notesTotal, itemsPerPage) }}
        </p>

        <VPagination
        v-if="notesTotal > itemsPerPage"
        v-model="query.page"
        :total-visible="6"
        :length="Math.ceil(notesTotal / itemsPerPage)"
        >
        <template #prev="slotProps">
            <VBtn
            variant="tonal"
            color="default"
            v-bind="slotProps"
            :icon="false"
            class
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
    <div class="float-right">
        <v-dialog persistent width="600" v-model="isActive">
        <template v-slot:activator="{ props }">
            <v-btn prepend-icon="tabler-plus" color="primary" v-bind="props" @click="showDialogAdd" text="Add Note"></v-btn>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <template v-slot:title>
                    <div class="d-flex justify-space-between mb-1 py-2" style="background-color: #EBECEE;">
                        <span class="ml-5 mt-3">{{ typeof itemselected !== 'string' ? 'Update Note' : 'Compose Note' }}</span>

                        <div class="mt-2 mr-2">
                            <IconBtn @click="minimize">
                            <VIcon
                                size="18"
                                icon="tabler-minus"
                            />
                            </IconBtn>
                            <IconBtn @click="close">
                            <VIcon
                                size="18"
                                icon="tabler-x"
                            />
                            </IconBtn>
                        </div>

                    </div>
                </template>
                <div class="d-flex justify-space-between align-center mr-2">
                    <span class="text-subtitle-1 ml-5">Subject:</span>
                    <v-text-field v-model="form.subject" variant="solo" flat placeholder="Subject"></v-text-field>
                    <div class="mb-1 mr-3">
                        <v-select
                            placeholder="Subject Category"
                            variant="outlined"
                            :model-value="form.subject_title"
                            @update:modelValue="(v) => (form.subject_title = v)"
                            :items="categories"
                            style="inline-size: 17rem;"
                            clearable
                        />
                    </div>
                </div>
                <div>
                  <QuillEditor style="height: 200px;" v-model:content="form.note" placeholder="Write your note" :options="editorOptions" contentType="html"/>
                </div>
                <v-divider class="mb-3"></v-divider>
                <!-- <v-card-actions> -->
                    <div class="d-flex justify-space-between mb-3">
                        <div class="d-flex justify-space-between ml-5" style="width: 50%;">
                            <v-btn :disabled="!form.note" :style="{ height: '43px'}" class="mr-3" prepend-icon="tabler-send" color="primary" @click="typeof itemselected !== 'string' ? updateNote() : createNote()" :loading="loading">{{ typeof itemselected !== 'string' ? 'Update' : 'Add' }}</v-btn>

                            <v-select
                                class="mr-1"
                                placeholder="Anyone"
                                variant="outlined"
                                :model-value="form.tag"
                                @update:modelValue="(v) => (form.tag = v)"
                                :items="form.subject_title == 'Performance Management/Disciplinary Matters' ? ['HR Only'] : tags"
                            />

                            <IconBtn v-if="!noteSelected.file && !form.file_url" @click="triggerSelectFile" class="mt-1">
                                <input
                                    ref="upload_file"
                                    type="file"
                                    name="upload_file"
                                    class="upload_file"
                                    accept=""
                                    @input="onSelectFile"
                                    style="display: none;"
                                />

                                <VIcon
                                    size="25"
                                    icon="tabler-paperclip"
                                    color="#828695"
                                />
                            </IconBtn>

                        </div>
                        <div class="d-flex justify-space-between mr-3">
                            <v-dialog v-model="dialog" max-width="400">
                                <v-card prepend-icon="mdi-warning" title="Confirm Delete" text="Are you sure you want to delete this item?">
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn :loading="loadingDelete" @click="deleteNote()" variant="tonal">Yes</v-btn>
                                        <v-btn @click="dialog=false">No</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                            <IconBtn @click="dialog=true" class="mt-1" v-if="typeof itemselected !== 'string'">
                            <VIcon
                                size="25"
                                icon="tabler-trash"
                                color="#828695"
                            />
                            </IconBtn>
                        </div>
                    </div>
                    <div v-if="noteSelected.file || form.file_url" class="mb-2 ml-5">
                        <u v-if="noteSelected.file" class="cursor-pointer" style="color: #5C7FFF;" @click="dlFile()">{{ noteSelected.file_name }}<VIcon icon="tabler-x" size="small" color="#E5E4E6" @click.stop="noteSelected.file = '';form.file_url ='';" @click.prevent class="ml-3"/></u>
                        <u v-else style="color: #5C7FFF;" >{{ form.file_data ? form.file_data?.name : noteSelected.file_name }}<VIcon icon="tabler-x" size="small" color="#E5E4E6" @click.stop="form.file_url =''" @click.prevent class="ml-3"/></u>
                    </div>
                <!-- </v-card-actions> -->
            </v-card>
        </template>
        </v-dialog>
    </div>
</template>

<style scoped>
.ptext {
    font-family: Oswald !important;
    font-size: 18px;
}
:deep(.v-card-item) {
    padding: unset !important;
}
:deep(.text-primary) {
  color: #001871 !important;
}
</style>
