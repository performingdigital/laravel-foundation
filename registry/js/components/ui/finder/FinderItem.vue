<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import { computed, useSlots } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    name: string;
    meta?: string | null;
    href?: string | null;
    selected?: boolean;
    selectable?: boolean;
    disabled?: boolean;
    class?: HTMLAttributes['class'];
    contentClass?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    click: [event: MouseEvent];
    'update:selected': [selected: boolean];
}>();

const slots = useSlots();
const hasActions = computed(() => !!slots.actions);
const contentClasses = computed(() =>
    cn(
        'h-auto w-full justify-start p-3 text-left',
        props.selectable ? 'pl-12' : '',
        hasActions.value ? 'pr-12' : '',
        props.disabled ? 'cursor-default hover:bg-background' : '',
        props.contentClass,
    ),
);
</script>

<template>
    <div
        :class="
            cn(
                'group relative',
                selected
                    ? 'rounded-md ring-2 ring-primary ring-offset-2 ring-offset-background'
                    : '',
                props.class,
            )
        "
    >
        <div
            v-if="selectable"
            class="absolute top-2 left-2 z-10 rounded-md bg-background/90 p-1 shadow-sm"
            @click.stop
            @mousedown.stop
        >
            <Checkbox
                :model-value="selected"
                :aria-label="`Select ${name}`"
                @update:model-value="emit('update:selected', $event === true)"
            />
        </div>

        <Button
            v-if="href"
            as-child
            variant="outline"
            :class="contentClasses"
            :disabled="disabled"
        >
            <Link
                :href="href"
                class="flex min-w-0 items-center gap-3"
                v-bind="$attrs"
            >
                <slot name="icon" />

                <div class="min-w-0 flex-1 overflow-hidden">
                    <div class="truncate text-sm font-medium">{{ name }}</div>
                    <div
                        v-if="meta"
                        class="truncate text-xs font-normal text-muted-foreground"
                    >
                        {{ meta }}
                    </div>
                </div>
            </Link>
        </Button>

        <Button
            v-else
            type="button"
            variant="outline"
            :class="contentClasses"
            :disabled="disabled"
            v-bind="$attrs"
            @click="emit('click', $event)"
        >
            <div class="flex min-w-0 items-center gap-3">
                <slot name="icon" />

                <div class="min-w-0 flex-1 overflow-hidden">
                    <div class="truncate text-sm font-medium">{{ name }}</div>
                    <div
                        v-if="meta"
                        class="truncate text-xs font-normal text-muted-foreground"
                    >
                        {{ meta }}
                    </div>
                </div>
            </div>
        </Button>

        <div
            v-if="hasActions"
            class="absolute top-1/2 right-2 -translate-y-1/2"
        >
            <slot name="actions" />
        </div>
    </div>
</template>
