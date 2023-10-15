<template>
  <label :for="id" class="flex flex-col h-44 relative overflow-hidden cursor-pointer">
    <Field :id="id" :name="name" type="file" class="overlayed mt-3" :multiple="multiple" :accept="allowedFiles"
      @change="handleUpload" />
    <div class="h-full flex flex-col justify-center overlayed pointer-events-none rounded-lg bg-white border-2 border-dashed border-gray-200 cursor-pointer">
      <div
        class="h-full flex flex-col pointer-events-none justify-center px-6 py-5 cursor-pointer">
        <div class="text-center">
          <div class="flex flex-col justify-center text-sm leading-6">
            <p class="pl-1"> {{ label }} </p>
            <span v-if="fileTypes" class="text-xs my-2">Accepts: {{ fileTypes }}</span>
          </div>
          <label for="chooseFile"
            class="relative cursor-pointer rounded-md text-sm text-primary hover:text-primary underline mt-2">
            <span>Select File</span>
          </label>
          <template v-if="files.length">
            <div class="flex flex-col justify-center gap-3 mt-3">
              <div v-for="(file, index) in files" :key="'file' + index" class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                  <div class="flex-1">
                    <div class="flex flex-col">
                      <div class="w-full flex gap-2 justify-center items-center">
                        <p class="text-dark text-center text-sm font-medium">
                          {{ file.name }}
                          <span class="text-xs font-medium">
                            ({{ getFileSize(file.size) }})</span>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Vee-validate error -->
                <p class="field-error">
                  <ErrorMessage :name="name" />
                </p>
                <!-- Custom error -->
                <div v-if="errorMessages && errorMessages.length" class="text-danger text-sm">
                  {{ getErrorText(file.name) }}
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </label>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Field, ErrorMessage } from 'vee-validate';

const props = defineProps({
  id: { type: String, default: 'fileUploader' },
  name: {type: String, default: ''},
  multiple: { type: Boolean, default: false },
  allowedFiles: { type: String, default: '*' },
  errorMessages: { type: Array, default: () => [] },
  resetUploader: { type: Boolean, default: false },
  label: { type: String, default: 'Drop your file here or click to upload.' },
  fileTypes: { type: String, default: '' },
})
const emit = defineEmits(['upload'])

const files = ref([])

const uploadInfo = computed(() => {
  return files.value.length === 1
    ? files.value[0].name
    : `${files.value.length} files selected`
})

// handle the file upload event
const handleUpload = (e) => {
  files.value = Array.from(e.target.files) || []
  emitUpload()
}

const emitUpload = () => {
  emit('upload', files.value)
}

const getFileExt = (type) => {
  return type.replace(/^.*[\\\/]/, '') === 'pdf' ? 'pdf' : 'doc'
}

const getFileSize = (size) => {
  let units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB']

  let i = 0

  for (i; size > 1024; i++) {
    size /= 1024
  }

  return parseFloat(size.toFixed(2)) + ' ' + units[i]
}

const getErrorText = (fileName) => {
  const error = props.errorMessages.find(error => error.fileName === fileName)
  return error ? error.message : null;
}

const removeFile = (index) => {
  files.value.splice(index, 1)
  emitUpload()
}

watch(
  () => props.resetUploader,
  (newVal) => {
    if (newVal) {
      files.value = []
    }
  }
)

</script>

<style scoped>
.overlayed {
  @apply absolute h-full top-0 left-0 right-0 bottom-0 w-full block;
}
</style>
