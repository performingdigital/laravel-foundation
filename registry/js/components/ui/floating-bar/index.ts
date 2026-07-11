import type { VariantProps } from 'class-variance-authority';
import type { ComputedRef, InjectionKey, Ref } from 'vue';
import { cva } from 'class-variance-authority';

export type FloatingBarContext = {
    collapsed: Ref<boolean>;
    currentPath: ComputedRef<string>;
};

export const floatingBarContextKey = Symbol(
    'floatingBarContext',
) as InjectionKey<FloatingBarContext>;

export { default as FloatingBar } from './FloatingBar.vue';
export { default as FloatingBarItem } from './FloatingBarItem.vue';

export const floatingBarItemVariants = cva(
    'relative flex h-14 flex-col items-center justify-center gap-0.5 overflow-hidden rounded-full transition-[width,margin,opacity] duration-[450ms] ease-[cubic-bezier(0.22,1,0.36,1)] focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none',
    {
        variants: {
            variant: {
                default: '',
            },
            state: {
                active: 'bg-primary text-primary-foreground',
                inactive: 'text-muted-foreground hover:text-foreground',
                collapsed: 'ml-0 w-0 opacity-0',
                visible: 'ml-0.5 w-14 opacity-100 first:ml-0',
            },
        },
        defaultVariants: {
            variant: 'default',
            state: 'inactive',
        },
    },
);

export type FloatingBarItemVariants = VariantProps<
    typeof floatingBarItemVariants
>;
