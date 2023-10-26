<template>
    <div class="w-48">
        <AppButton
            class="btn btn-primary mb-2 flex w-full align-middle"
            aria-haspopup="true"
            :aria-expanded="isOpen"
            @click="toggleState"
        >
            <span class="mr-2 inline-block"> {{ comboLabelText }} </span>
            <i class="ri-arrow-down-line"></i>
        </AppButton>

        <transition name="slide-fade">
            <div v-show="isOpen">
                <div v-show="useSearch">
                    <!-- search input -->
                    <label for="input-group-search" class="sr-only"
                        >Search</label
                    >
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <i class="ri-search-line" aria-hidden="true"></i>
                        </div>
                        <AppInputText
                            id="input-group-search"
                            ref="searchInputRef"
                            v-model="searchOptionText"
                            role="searchbox"
                            aria-autocomplete="list"
                            type="text"
                            class="pl-10"
                            :placeholder="searchPlaceholder"
                            @keypress.enter="validateOptionHighlighted"
                            @keydown="handleArrowKeys"
                            @keydown.esc="toggleState"
                        />
                    </div>
                </div>

                <!-- combo options -->
                <ul class="mt-2 p-1 shadow" role="listbox">
                    <li
                        v-for="(option, index) in filteredOptions"
                        :key="option.value"
                        role="option"
                        :aria-selected="index === highlightedIndex"
                    >
                        <a
                            href="#"
                            class="block px-4 py-2 text-sm hover:bg-skin-neutral-3 hover:text-skin-neutral-12"
                            :class="{
                                'bg-skin-neutral-3 text-skin-neutral-12':
                                    index === highlightedIndex
                            }"
                            @click="updateModelValue(option)"
                        >
                            {{ option.label }}
                        </a>
                    </li>
                </ul>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    modelValue: {
        type: [Object, null],
        required: true
    },
    comboLabel: {
        type: String,
        default: 'Dropdown search'
    },
    useSearch: {
        type: Boolean,
        default: true
    },
    searchPlaceholder: {
        type: String,
        default: 'Search'
    },
    options: {
        type: Array,
        default: () => []
    }
})

onMounted(() => {
    isOpen.value && (highlightedIndex.value = 0)
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

const searchInputRef = ref(null)
const toggleState = () => {
    isOpen.value = !isOpen.value

    highlightedIndex.value = 0

    window.setTimeout(() => {
        if (isOpen.value) {
            searchOptionText.value = ''
            searchInputRef.value.focusInput()
        }
    }, 100)
}

const updateModelValue = (option) => {
    emit('update:modelValue', option)
    toggleState()
}

const searchOptionText = ref('')

const filteredOptions = computed(() => {
    if (searchOptionText.value) {
        return props.options.filter((option) =>
            option.label
                .toLowerCase()
                .includes(searchOptionText.value.toLowerCase())
        )
    } else {
        return props.options
    }
})

const validateOptionHighlighted = () => {
    if (filteredOptions.value[highlightedIndex.value]) {
        updateModelValue(filteredOptions.value[highlightedIndex.value])
    }
}

const highlightedIndex = ref(0)

const handleArrowKeys = (event) => {
    switch (event.key) {
        case 'ArrowUp':
            if (highlightedIndex.value > 0) {
                highlightedIndex.value--
            }
            break
        case 'ArrowDown':
            if (highlightedIndex.value < filteredOptions.value.length - 1) {
                highlightedIndex.value++
            }
            break
        default:
            break
    }
}

const comboLabelText = computed(() => {
    if (props.modelValue) {
        return props.modelValue.label
    } else {
        return props.comboLabel
    }
})
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    @apply transition-all duration-200 ease-in;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    @apply -translate-y-2 opacity-0;
}
</style>