import * as React from 'react'

import { cn } from '@/lib/utils'

interface LoadingStateProps
  extends React.HTMLAttributes<HTMLDivElement> {
  message?: string
}

const LoadingState = React.forwardRef<
  HTMLDivElement,
  LoadingStateProps
>(
  (
    {
      message = 'Loading...',
      className,
      ...props
    },
    ref,
  ) => {
    return (
      <div
        ref={ref}
        role="status"
        aria-live="polite"
        className={cn(
          'flex min-h-40 flex-col items-center justify-center gap-3 text-center',
          className,
        )}
        {...props}
      >
        <span
          aria-hidden="true"
          className="h-6 w-6 animate-spin rounded-full border-2 border-muted border-t-primary"
        />

        <p className="text-sm text-muted-foreground">
          {message}
        </p>

        <span className="sr-only">
          {message}
        </span>
      </div>
    )
  },
)

LoadingState.displayName = 'LoadingState'

export { LoadingState }
