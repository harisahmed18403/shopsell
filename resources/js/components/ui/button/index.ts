import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300/40 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0",
  {
    variants: {
      variant: {
        default: "bg-rose-500 text-white shadow hover:bg-rose-400",
        destructive:
          "bg-rose-500 text-white shadow-sm hover:bg-rose-400",
        outline:
          "border border-white/10 bg-white/5 text-slate-100 shadow-sm hover:bg-white/10",
        secondary:
          "bg-slate-800 text-white shadow-sm hover:bg-slate-700",
        ghost: "text-slate-200 hover:bg-white/10 hover:text-white",
        link: "text-rose-300 underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-9 px-4 py-2",
        "xs": "h-7 rounded px-2",
        "sm": "h-8 rounded-md px-3 text-xs",
        "lg": "h-10 rounded-md px-8",
        "icon": "h-9 w-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
