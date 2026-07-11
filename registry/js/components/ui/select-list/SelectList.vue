<script setup lang="ts">
import { useQuery } from '@tanstack/vue-query';
import { CheckIcon, ChevronDownIcon } from '@lucide/vue';
import { refDebounced } from '@vueuse/core';
import { computed, ref, useId, watch } from 'vue';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';

type OptionId = string | number;
type SelectListValue = OptionId | OptionId[] | null;

type Option = {
    id: OptionId;
    name: string;
    reference?: string | null;
};

const props = withDefaults(
    defineProps<{
        endpoint: string;
        modelValue: SelectListValue;
        multiple?: boolean;
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        disabled?: boolean;
    }>(),
    {
        multiple: false,
        placeholder: 'Select...',
        searchPlaceholder: 'Search...',
        emptyText: 'No items found.',
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: SelectListValue];
    change: [value: Option | Option[] | null];
}>();

const id = useId();
const open = ref(false);
const query = ref('');
const debouncedQuery = refDebounced(query, 300);
const selectedIds = computed<OptionId[]>(() => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue;
    }

    return props.modelValue === null ? [] : [props.modelValue];
});
const selectedKey = computed(() =>
    selectedIds.value.map(String).sort().join(','),
);
const originalSelection = ref(selectedKey.value);

const { data, error, isFetching, isPending } = useQuery<Option[]>({
    queryKey: [
        'select-list',
        computed(() => props.endpoint),
        debouncedQuery,
        selectedKey,
    ],
    queryFn: async ({ signal }) => {
        const parameters = new URLSearchParams({
            search: debouncedQuery.value,
            ids: selectedIds.value.join(','),
        });
        const separator = props.endpoint.includes('?') ? '&' : '?';
        const response = await fetch(
            `${props.endpoint}${separator}${parameters}`,
            {
                headers: { Accept: 'application/json' },
                signal,
            },
        );

        if (!response.ok) {
            throw new Error(`Unable to load options (${response.status}).`);
        }

        const payload = (await response.json()) as
            { data?: Option[] } | Option[];

        return Array.isArray(payload) ? payload : (payload.data ?? []);
    },
});

const options = computed(() => data.value ?? []);
const selectedOptions = computed(() =>
    selectedIds.value
        .map((selectedId) =>
            options.value.find((option) => sameId(option.id, selectedId)),
        )
        .filter((option): option is Option => option !== undefined),
);
const selectedOption = computed(() => selectedOptions.value[0] ?? null);

function sameId(first: OptionId, second: OptionId): boolean {
    return String(first) === String(second);
}

function isSelected(option: Option): boolean {
    return selectedIds.value.some((selectedId) =>
        sameId(selectedId, option.id),
    );
}

function select(option: Option): void {
    if (props.multiple) {
        const value = isSelected(option)
            ? selectedIds.value.filter(
                  (selectedId) => !sameId(selectedId, option.id),
              )
            : [...selectedIds.value, option.id];

        emit('update:modelValue', value);

        return;
    }

    emit('update:modelValue', isSelected(option) ? null : option.id);
    open.value = false;
}

watch(open, (isOpen) => {
    if (isOpen) {
        originalSelection.value = selectedKey.value;

        return;
    }

    if (selectedKey.value === originalSelection.value) {
        return;
    }

    originalSelection.value = selectedKey.value;
    emit(
        'change',
        props.multiple ? selectedOptions.value : selectedOption.value,
    );
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :aria-busy="isFetching"
                :disabled="disabled"
                class="w-full justify-between border-input bg-background px-3 font-normal outline-offset-0 focus-visible:outline-[3px] dark:bg-input/30"
            >
                <span class="flex min-w-0 flex-1 items-center gap-2">
                    <Spinner v-if="isPending || isFetching" class="shrink-0" />

                    <span
                        v-if="multiple && selectedOptions.length > 0"
                        class="flex min-w-0 items-center gap-1"
                    >
                        <Badge
                            v-for="option in selectedOptions.slice(0, 2)"
                            :key="option.id"
                            class="max-w-32 truncate"
                        >
                            {{ option.name }}
                        </Badge>
                        <span
                            v-if="selectedOptions.length > 2"
                            class="text-sm text-muted-foreground"
                        >
                            +{{ selectedOptions.length - 2 }}
                        </span>
                    </span>

                    <span
                        v-else-if="!multiple && selectedOption"
                        class="truncate"
                    >
                        {{ selectedOption.name }}
                    </span>

                    <span v-else class="truncate text-muted-foreground">
                        {{ placeholder }}
                    </span>
                </span>

                <ChevronDownIcon
                    class="size-4 shrink-0 text-muted-foreground/80"
                    aria-hidden="true"
                />
            </Button>
        </PopoverTrigger>

        <PopoverContent
            class="w-full min-w-[var(--reka-popper-anchor-width)] border-input p-0"
            align="start"
        >
            <Command :filter="() => true">
                <CommandInput
                    v-model="query"
                    class="w-full"
                    :placeholder="searchPlaceholder"
                />
                <CommandList>
                    <CommandEmpty>
                        {{ error instanceof Error ? error.message : emptyText }}
                    </CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="option.id"
                            :value="String(option.id)"
                            @select="select(option)"
                        >
                            <span
                                :class="
                                    cn(
                                        'mr-2 flex size-4 items-center justify-center rounded-sm border border-primary',
                                        isSelected(option)
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-transparent',
                                    )
                                "
                            >
                                <CheckIcon class="size-3" aria-hidden="true" />
                            </span>
                            <span class="truncate">{{ option.name }}</span>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
