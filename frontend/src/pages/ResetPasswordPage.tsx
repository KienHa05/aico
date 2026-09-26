import { zodResolver } from '@hookform/resolvers/zod'
import axios from 'axios'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
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
import { resetPassword } from '@/features/auth/authApi'

const resetPasswordSchema = z
  .object({
    password: z.string().min(1, 'Password is required.'),
    password_confirmation: z
      .string()
      .min(1, 'Password confirmation is required.'),
  })
  .refine(
    (values) => values.password === values.password_confirmation,
    {
      path: ['password_confirmation'],
      message: 'Password confirmation does not match.',
    },
  )

type ResetPasswordFormValues = z.infer<typeof resetPasswordSchema>

type ApiErrorResponse = {
  message?: string
  errors?: Record<string, string[]>
}

function ResetPasswordPage() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [serverError, setServerError] = useState<string | null>(null)

  const token = searchParams.get('token') ?? ''
  const email = searchParams.get('email') ?? ''

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ResetPasswordFormValues>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      password: '',
      password_confirmation: '',
    },
  })

  async function onSubmit(
    values: ResetPasswordFormValues,
  ): Promise<void> {
    setServerError(null)

    try {
      await resetPassword({
        token,
        email,
        ...values,
      })

      navigate('/login', {
        replace: true,
        state: {
          message: 'Password reset successfully. Please log in.',
        },
      })
    } catch (error: unknown) {
      const response = axios.isAxiosError<ApiErrorResponse>(error)
        ? error.response
        : undefined

      const fieldErrors = response?.data?.errors

      if (fieldErrors) {
        const passwordMessage = fieldErrors.password?.[0]
        const confirmationMessage =
          fieldErrors.password_confirmation?.[0]

        if (passwordMessage) {
          setError('password', {
            type: 'server',
            message: passwordMessage,
          })
        }

        if (confirmationMessage) {
          setError('password_confirmation', {
            type: 'server',
            message: confirmationMessage,
          })
        }
      }

      setServerError(
        response?.data?.message ??
        'Unable to reset your password. Please request a new reset link.',
      )
    }
  }

  if (!token || !email) {
    return (
      <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
        <Card className="w-full max-w-md">
          <CardHeader>
            <CardTitle>Invalid reset link</CardTitle>

            <CardDescription>
              This password reset link is missing required information.
            </CardDescription>
          </CardHeader>

          <CardContent>
            <p
              className="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive"
              role="alert"
            >
              Request a new password reset link to continue.
            </p>

            <Link
              className="mt-6 block text-center text-sm font-medium text-primary hover:underline"
              to="/forgot-password"
            >
              Request a new reset link
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
          <CardTitle>Reset password</CardTitle>

          <CardDescription>
            Create a new password for your account.
          </CardDescription>
        </CardHeader>

        <CardContent>
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
                value={email}
                readOnly
                autoComplete="email"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">New password</Label>

              <Input
                id="password"
                type="password"
                autoComplete="new-password"
                aria-invalid={Boolean(errors.password)}
                disabled={isSubmitting}
                {...register('password')}
              />

              {errors.password?.message && (
                <p className="text-sm text-destructive">
                  {errors.password.message}
                </p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="password_confirmation">
                Confirm password
              </Label>

              <Input
                id="password_confirmation"
                type="password"
                autoComplete="new-password"
                aria-invalid={Boolean(errors.password_confirmation)}
                disabled={isSubmitting}
                {...register('password_confirmation')}
              />

              {errors.password_confirmation?.message && (
                <p className="text-sm text-destructive">
                  {errors.password_confirmation.message}
                </p>
              )}
            </div>

            <Button
              className="w-full"
              type="submit"
              disabled={isSubmitting}
            >
              {isSubmitting ? 'Resetting...' : 'Reset password'}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            Back to{' '}

            <Link
              className="font-medium text-primary hover:underline"
              to="/login"
            >
              log in
            </Link>
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

export default ResetPasswordPage
