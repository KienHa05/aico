import { zodResolver } from '@hookform/resolvers/zod'
import axios from 'axios'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link } from 'react-router-dom'
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
import { forgotPassword } from '@/features/auth/authApi'

const forgotPasswordSchema = z.object({
  email: z
    .string()
    .min(1, 'Email is required.')
    .email('Enter a valid email address.'),
})

type ForgotPasswordFormValues = z.infer<typeof forgotPasswordSchema>

type ApiErrorResponse = {
  message?: string
  errors?: Record<string, string[]>
}

function ForgotPasswordPage() {
  const [serverError, setServerError] = useState<string | null>(null)
  const [successMessage, setSuccessMessage] = useState<string | null>(null)

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ForgotPasswordFormValues>({
    resolver: zodResolver(forgotPasswordSchema),
    defaultValues: {
      email: '',
    },
  })

  async function onSubmit(
    values: ForgotPasswordFormValues,
  ): Promise<void> {
    setServerError(null)
    setSuccessMessage(null)

    try {
      const response = await forgotPassword(values)

      setSuccessMessage(response.message)
    } catch (error: unknown) {
      const response = axios.isAxiosError<ApiErrorResponse>(error)
        ? error.response
        : undefined

      const emailMessage = response?.data?.errors?.email?.[0]

      if (emailMessage) {
        setError('email', {
          type: 'server',
          message: emailMessage,
        })
      }

      setServerError(
        response?.data?.message ??
        'Unable to send the password reset link. Please try again.',
      )
    }
  }

  return (
    <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Forgot password</CardTitle>

          <CardDescription>
            Enter your email address and we will send you a password reset
            link.
          </CardDescription>
        </CardHeader>

        <CardContent>
          {successMessage && (
            <p
              className="mb-6 rounded-md border border-border bg-muted p-3 text-sm text-foreground"
              role="status"
            >
              {successMessage}
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
                autoComplete="email"
                aria-invalid={Boolean(errors.email)}
                disabled={isSubmitting}
                {...register('email')}
              />

              {errors.email?.message && (
                <p className="text-sm text-destructive">
                  {errors.email.message}
                </p>
              )}
            </div>

            <Button
              className="w-full"
              type="submit"
              disabled={isSubmitting}
            >
              {isSubmitting ? 'Sending...' : 'Send reset link'}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            Remembered your password?{' '}

            <Link
              className="font-medium text-primary hover:underline"
              to="/login"
            >
              Log in
            </Link>
          </p>
        </CardContent>
      </Card>
    </div>
  )
}

export default ForgotPasswordPage
