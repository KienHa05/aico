import { useEffect, useRef, useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'

import {
  buttonVariants,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui'
import { getCurrentUser } from '@/features/auth/authApi'
import { clearAuth, setCredentials } from '@/features/auth/authSlice'
import {
  clearStoredAccessToken,
  setStoredAccessToken,
} from '@/lib/tokenStorage'
import { useAppDispatch } from '@/store/hooks'

type GoogleOAuthCallback =
  | {
    status: 'success'
    accessToken: string
  }
  | {
    status: 'error'
    message: string
  }
  | {
    status: 'missing'
    message: string
  }

function readGoogleOAuthCallback(
  search: string,
  hash: string,
): GoogleOAuthCallback {
  const searchParams = new URLSearchParams(search)
  const error = searchParams.get('error')

  if (error) {
    return {
      status: 'error',
      message: 'Unable to sign in with Google. Please try again.',
    }
  }

  const hashParams = new URLSearchParams(
    hash.replace(/^#/, ''),
  )
  const accessToken = hashParams.get('access_token')

  if (!accessToken?.trim()) {
    return {
      status: 'missing',
      message:
        'The Google authentication response is missing an access token.',
    }
  }

  return {
    status: 'success',
    accessToken,
  }
}

function clearOAuthCallbackUrl(): void {
  window.history.replaceState(
    null,
    document.title,
    `${window.location.pathname}${window.location.search}`,
  )
}

function GoogleOAuthCallbackPage() {
  const dispatch = useAppDispatch()
  const location = useLocation()
  const navigate = useNavigate()
  const processedRef = useRef(false)
  const [sessionError, setSessionError] = useState<string | null>(
    null,
  )

  const callback = readGoogleOAuthCallback(
    location.search,
    location.hash,
  )

  const callbackError =
    callback.status === 'success'
      ? null
      : callback.message

  const errorMessage = sessionError ?? callbackError

  useEffect(() => {
    if (processedRef.current) {
      return
    }

    const currentCallback = readGoogleOAuthCallback(
      location.search,
      location.hash,
    )

    if (currentCallback.status !== 'success') {
      return
    }

    processedRef.current = true
    clearOAuthCallbackUrl()

    const { accessToken } = currentCallback

    async function initializeGoogleSession(): Promise<void> {
      setStoredAccessToken(accessToken)

      try {
        const response = await getCurrentUser()

        dispatch(
          setCredentials({
            accessToken,
            user: response.data,
          }),
        )

        navigate('/', { replace: true })
      } catch {
        clearStoredAccessToken()
        dispatch(clearAuth())

        setSessionError(
          'Google sign-in could not be completed. Please try again.',
        )
      }
    }

    void initializeGoogleSession()
  }, [dispatch, location.hash, location.search, navigate])

  if (errorMessage) {
    return (
      <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
        <Card className="w-full max-w-md">
          <CardHeader>
            <CardTitle>Google sign-in failed</CardTitle>

            <CardDescription>
              We could not complete your Google authentication.
            </CardDescription>
          </CardHeader>

          <CardContent className="space-y-6">
            <p
              className="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive"
              role="alert"
            >
              {errorMessage}
            </p>

            <Link
              to="/login"
              className={buttonVariants({
                variant: 'default',
                size: 'default',
                className: 'w-full',
              })}
            >
              Return to login
            </Link>
          </CardContent>
        </Card>
      </div>
    )
  }

  return (
    <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Signing in with Google</CardTitle>

          <CardDescription>
            Please wait while we finish setting up your session.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <p
            className="rounded-md border border-border bg-muted p-3 text-sm text-foreground"
            role="status"
          >
            Authenticating your Google account...
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

export default GoogleOAuthCallbackPage
