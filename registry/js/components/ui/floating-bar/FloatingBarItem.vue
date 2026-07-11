<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { computed, inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { floatingBarContextKey, floatingBarItemVariants } from '.';

const props = defineProps<{
    href: string;
    label: string;
    icon?: Component;
    activePath?: string;
    class?: HTMLAttributes['class'];
}>();

const context = inject(floatingBarContextKey);

const active = computed(() => {
    const currentPath = context?.currentPath.value ?? '';
    const activePath = props.activePath ?? props.href;

    return (
        currentPath === activePath || currentPath.startsWith(`${activePath}/`)
    );
});

const collapsed = computed(() => context?.collapsed.value && !active.value);
</script>

<template>
    <Link
        :href="href"
        :aria-current="active ? 'page' : undefined"
        :aria-label="label"
        :class="
            cn(
                floatingBarItemVariants({
                    state: collapsed ? 'collapsed' : 'visible',
                }),
                active
                    ? floatingBarItemVariants({ state: 'active' })
                    : floatingBarItemVariants({ state: 'inactive' }),
                props.class,
            )
        "
    >
        <slot name="icon">
            <component v-if="icon" :is="icon" class="size-5 shrink-0" />
        </slot>
        <slot>
            <span class="truncate text-[9px] leading-none font-medium">{{
                label
            }}</span>
        </slot>
    </Link>
</template>
