import { Link, useSearchParams } from 'react-router-dom'

import {
  buttonVariants,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui'

function EmailVerificationPage() {
  const [searchParams] = useSearchParams()
  const status = searchParams.get('status')

  const verified = status === 'success'

  return (
    <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>
            {verified ? 'Email verified' : 'Email verification'}
          </CardTitle>

          <CardDescription>
            {verified
              ? 'Your email address has been successfully verified.'
              : 'Your email verification status could not be determined.'}
          </CardDescription>
        </CardHeader>

        <CardContent className="space-y-6">
          {verified ? (
            <p
              className="rounded-md border border-border bg-muted p-3 text-sm text-foreground"
              role="status"
            >
              Your account email is now verified. You can continue to log
              in.
            </p>
          ) : (
            <p
              className="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive"
              role="alert"
            >
              Please use the verification link sent to your email address.
            </p>
          )}

          <Link
            to="/login"
            className={buttonVariants({
              variant: 'default',
              size: 'default',
              className: 'w-full',
            })}
          >
            Go to login
          </Link>
        </CardContent>
      </Card>
    </div>
  )
}

export default EmailVerificationPage
