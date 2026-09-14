import * as React from 'react'

import { cn } from '@/lib/utils'

import { Button } from '@/components/ui/button'

interface ErrorStateProps
  extends React.HTMLAttributes<HTMLDivElement> {
  title?: string
  message?: string
  onRetry?: () => void
  retryLabel?: string
}

const ErrorState = React.forwardRef<
  HTMLDivElement,
  ErrorStateProps
>(
  (
    {
      title = 'Something went wrong',
      message = 'We could not complete your request. Please try again.',
      onRetry,
      retryLabel = 'Try again',
      className,
      ...props
    },
    ref,
  ) => {
    return (
      <div
        ref={ref}
        role="alert"
        className={cn(
          'flex min-h-40 flex-col items-center justify-center gap-3 px-4 text-center',
          className,
        )}
        {...props}
      >
        <div
          aria-hidden="true"
          className="flex h-10 w-10 items-center justify-center rounded-full bg-destructive/10 text-destructive"
        >
          !
        </div>

        <div className="space-y-1">
          <h2 className="text-sm font-semibold text-foreground">
            {title}
          </h2>

          <p className="max-w-md text-sm text-muted-foreground">
            {message}
          </p>
        </div>

        {onRetry ? (
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={onRetry}
          >
            {retryLabel}
          </Button>
        ) : null}
      </div>
    )
  },
)

ErrorState.displayName = 'ErrorState'

export { ErrorState }
