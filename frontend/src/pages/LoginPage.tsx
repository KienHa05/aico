import { zodResolver } from '@hookform/resolvers/zod'
import axios from 'axios'
import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { z } from 'zod'

import {
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Input,
  Label,
} from '@/components/ui'
import {
  getGoogleRedirectUrl,
  login,
} from '@/features/auth/authApi'
import { setCredentials } from '@/features/auth/authSlice'
import { setStoredAccessToken } from '@/lib/tokenStorage'
import { useAppDispatch } from '@/store/hooks'

const loginSchema = z.object({
  email: z
    .string()
    .min(1, 'Email is required.')
    .email('Enter a valid email address.'),
  password: z.string().min(1, 'Password is required.'),
})

type LoginFormValues = z.infer<typeof loginSchema>

type ApiErrorResponse = {
  message?: string
  errors?: Record<string, string[]>
}

function LoginPage() {
  const dispatch = useAppDispatch()
  const location = useLocation()
  const navigate = useNavigate()
  const [serverError, setServerError] = useState<string | null>(null)
  const [isGoogleSubmitting, setIsGoogleSubmitting] = useState(false)

  const locationState = location.state as
    | { message?: string }
    | null

  const registrationMessage = locationState?.message ?? null

  const {
    register: registerField,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  })

  useEffect(() => {
    if (registrationMessage) {
      navigate(location.pathname, {
        replace: true,
        state: null,
      })
    }
  }, [location.pathname, navigate, registrationMessage])

  async function onSubmit(values: LoginFormValues): Promise<void> {
    setServerError(null)

    try {
      const response = await login(values)
      const { access_token: accessToken, user } = response.data

      setStoredAccessToken(accessToken)

      dispatch(
        setCredentials({
          accessToken,
          user,
        }),
      )

      navigate('/')
    } catch (error: unknown) {
      const response = axios.isAxiosError<ApiErrorResponse>(error)
        ? error.response
        : undefined

      const fieldErrors = response?.data?.errors

      if (fieldErrors) {
        for (const [field, messages] of Object.entries(fieldErrors)) {
          const message = messages[0]

          if (
            (field === 'email' || field === 'password') &&
            message
          ) {
            setError(field, {
              type: 'server',
              message,
            })
          }
        }
      }

      setServerError(
        response?.data?.message ??
        'Unable to log in. Please try again.',
      )
    }
  }

  async function handleGoogleSignIn(): Promise<void> {
    setServerError(null)
    setIsGoogleSubmitting(true)

    try {
      const response = await getGoogleRedirectUrl()

      window.location.assign(response.data.redirect_url)
    } catch (error: unknown) {
      const response = axios.isAxiosError<ApiErrorResponse>(error)
        ? error.response
        : undefined

      setServerError(
        response?.data?.message ??
        'Unable to start Google sign-in. Please try again.',
      )

      setIsGoogleSubmitting(false)
    }
  }

  const isSubmittingForm = isSubmitting || isGoogleSubmitting

  return (
    <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Log in</CardTitle>

          <CardDescription>
            Sign in to your AICO Platform account.
          </CardDescription>
        </CardHeader>

        <CardContent>
          {registrationMessage && (
            <p
              className="mb-6 rounded-md border border-border bg-muted p-3 text-sm text-foreground"
              role="status"
            >
              {registrationMessage}
            </p>
          )}

          {serverError && (
            <p
              className="mb-6 rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive"
              role="alert"
            >
              {serverError}
            </p>
          )}

          <form
            className="space-y-5"
            onSubmit={handleSubmit(onSubmit)}
            noValidate
          >
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>

              <Input
                id="email"
                type="email"
                autoComplete="username"
                aria-invalid={Boolean(errors.email)}
                disabled={isSubmittingForm}
                {...registerField('email')}
              />

              {errors.email?.message && (
                <p className="text-sm text-destructive">
                  {errors.email.message}
                </p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>

              <Input
                id="password"
                type="password"
                autoComplete="current-password"
                aria-invalid={Boolean(errors.password)}
                disabled={isSubmittingForm}
                {...registerField('password')}
              />

              {errors.password?.message && (
                <p className="text-sm text-destructive">
                  {errors.password.message}
                </p>
              )}

              <div className="text-right">
                <Link
                  className="text-sm font-medium text-primary hover:underline"
                  to="/forgot-password"
                >
                  Forgot your password?
                </Link>
              </div>
            </div>

            <Button
              className="w-full"
              type="submit"
              disabled={isSubmittingForm}
            >
              {isSubmitting
                ? 'Logging in...'
                : 'Log in'}
            </Button>
          </form>

          <div className="my-6 flex items-center gap-3">
            <div className="h-px flex-1 bg-border" />

            <span className="text-xs text-muted-foreground">
              OR
            </span>

            <div className="h-px flex-1 bg-border" />
          </div>

          <Button
            className="w-full"
            type="button"
            variant="outline"
            disabled={isSubmittingForm}
            onClick={() => {
              void handleGoogleSignIn()
            }}
          >
            {isGoogleSubmitting
              ? 'Connecting to Google...'
              : 'Continue with Google'}
          </Button>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            Don't have an account?{' '}

            <Link
              className="font-medium text-primary hover:underline"
              to="/register"
            >
              Create an account
            </Link>
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

export default LoginPage
