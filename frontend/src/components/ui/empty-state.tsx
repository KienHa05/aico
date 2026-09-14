import * as React from 'react'

import { cn } from '@/lib/utils'

import { Button } from '@/components/ui/button'

interface EmptyStateProps
  extends React.HTMLAttributes<HTMLDivElement> {
  title?: string
  description?: string
  actionLabel?: string
  onAction?: () => void
}

const EmptyState = React.forwardRef<
  HTMLDivElement,
  EmptyStateProps
>(
  (
    {
      title = 'No data available',
      description = 'There is nothing to display here yet.',
      actionLabel,
      onAction,
      className,
      ...props
    },
    ref,
  ) => {
    return (
      <div
        ref={ref}
        className={cn(
          'flex min-h-40 flex-col items-center justify-center gap-3 px-4 text-center',
          className,
        )}
        {...props}
      >
        <div
          aria-hidden="true"
          className="flex h-10 w-10 items-center justify-center rounded-full bg-muted text-muted-foreground"
        >
          —
        </div>

        <div className="space-y-1">
          <h2 className="text-sm font-semibold text-foreground">
            {title}
          </h2>

          <p className="max-w-md text-sm text-muted-foreground">
            {description}
          </p>
        </div>

        {actionLabel && onAction ? (
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={onAction}
          >
            {actionLabel}
          </Button>
        ) : null}
      </div>
    )
  },
)

EmptyState.displayName = 'EmptyState'

export { EmptyState }
