<template>
    <VMenu v-model="menu" :close-on-content-click="false" offset-y>
      <template #activator="{ props }">
        <VChip
          label
          size="default"
          variant="plain"
          v-bind="props"
          :style="{
            background: 'transparent',
            border: '2px solid ' + getBackgroundColor(item, 'resource'),
            borderRadius: '5px',
            opacity: '1 !important'
          }"
        >
          <div :style="{ color: getTextColor(item, 'resource'), fontWeight: '500' }">
            {{ capitalizeFirst(displayCardTitle(item.status)) }}
          </div>
        </VChip>
      </template>
    </VMenu>
  </template>
  
<script setup>
  import resourceApi from "@/api/resources/resourceApi";
import { useEmployeeStore } from "@/store/employeeStore";
import { useRoleStore } from '@/store/roleStore';
import { computed, onMounted, ref } from 'vue';

  
  const props = defineProps({
    item: Object,
    getBackgroundColor: Function,
    getTextColor: Function
  })

  const displayCardTitle = (str) =>{
        let status = str;

        if(str === 'user' || str === 'role'){
            status = 'restricted';
        }
        else if(str === 'everyone'){
            status = 'public';
        }

        return status;
  }

  const capitalizeFirst = (str) => {
      if(str === "published"){
        str = "public";
      }
      if (str) {
          return str.split(' ').map(word => {
              return word.charAt(0).toUpperCase() + word.slice(1);
          }).join(' ');
      }

      return str
  }

  const id = computed(() => props.item?.id)
  
  const menu = ref(false)
  const selectedShareOption = ref('role')
  const selectedRoles = ref(null)
  const selectedEmployees = ref([])  // Multi-select result
  
  // 🧠 Pinia store
  const roleStore = useRoleStore()
  const employeeStore = useEmployeeStore()
  const emit = defineEmits(['reload-resources'])

  async function handleArchive() {
    console.log('Archive clicked')

    await resourceApi.updatePrivacy(id.value,{
      status: "archived",
      employees: [],
      roles: []
    })

    emit('reload-resources')    
    menu.value = false;
  }

  async function handleMoveToDraft() {
    console.log('Move to Draft clicked')
    await resourceApi.updatePrivacy(id.value,{
      status: "draft",
      employees: [],
      roles: []
    })  
    emit('reload-resources');
    menu.value = false;
  }

  async function handleCopyLink() {
    console.log('Copy Link clicked', props.item)
    // Example: copy a shareable link to clipboard
    // const link = `${window.location.origin}/resources/${props.item.path}`
    const link = props.item.path
    navigator.clipboard.writeText(link)
    .then(() => {
      console.log('Link copied to clipboard:', link)
    })
    .catch(err => {
      console.error('Failed to copy link:', err)
    })
    emit('reload-resources');
    menu.value = false;    
  }
  
  
  onMounted(async () => {
    if (roleStore.fetchRoles) { 
      await roleStore.fetchRoles()
    }

    // Preload selected roles
    if (props.item.roles && Array.isArray(props.item.roles)) {
      selectedRoles.value = props.item.roles
    }

    // Preload selected employees
    if (props.item.employees && Array.isArray(props.item.employees)) {
      selectedEmployees.value = props.item.employees.map(emp => ({
        ...emp,
        fullName: `${emp.first_name} ${emp.last_name}`
      }))
    }



    if (['role', 'user', 'everyone'].includes(props.item.status)) {
      selectedShareOption.value = props.item.status
    }

  })
  
  // Role list
  const roles = computed(() => roleStore.data?.roles || [])
  
  // 👇 Add fullName field for display in autocomplete
  const employeesWithFullName = computed(() => {
    return (employeeStore.data?.employees || []).map(emp => ({
      ...emp,
      fullName: `${emp.first_name} ${emp.last_name}`
    }))
  })
  
  // Save
  async function save() {
    menu.value = false
    console.log('Selected Employees:', selectedEmployees.value)
    console.log('Selected Roles:', selectedRoles.value)

    await resourceApi.updatePrivacy(id.value,{
      status: selectedShareOption.value,
      employees: selectedEmployees.value,
      roles: selectedRoles.value
    })

    emit('reload-resources')
  }
  
  // Cancel
  function cancelSelection() {
    menu.value = false
    console.log('Selection Cancelled')
  }
  </script>
  
  <style scoped>
  .selected {
    background-color: #e0e0e0;
  }
  
  .employee-list {
    max-height: 100px;
    overflow-y: auto;
  }
  </style>
  