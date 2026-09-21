import { zodResolver } from '@hookform/resolvers/zod'
import axios from 'axios'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
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
import { register as registerAccount } from '@/features/auth/authApi'

const registerSchema = z
  .object({
    name: z
      .string()
      .min(1, 'Name is required.')
      .max(255, 'Name must not exceed 255 characters.'),

    email: z
      .string()
      .min(1, 'Email is required.')
      .email('Enter a valid email address.'),

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

type RegisterFormValues = z.infer<typeof registerSchema>

type ApiErrorResponse = {
  message?: string
  errors?: Record<string, string[]>
}

function RegisterPage() {
  const navigate = useNavigate()
  const [serverError, setServerError] = useState<string | null>(null)

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
  })

  async function onSubmit(
    values: RegisterFormValues,
  ): Promise<void> {
    setServerError(null)

    try {
      await registerAccount(values)

      navigate('/login', {
        state: {
          message:
            'Registration successful. Please log in with your new account.',
        },
      })
    } catch (error: unknown) {
      const response = axios.isAxiosError<ApiErrorResponse>(error)
        ? error.response
        : undefined

      const fieldErrors = response?.data?.errors

      if (fieldErrors) {
        for (const [field, messages] of Object.entries(fieldErrors)) {
          const message = messages[0]

          if (
            (
              field === 'name' ||
              field === 'email' ||
              field === 'password' ||
              field === 'password_confirmation'
            ) &&
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
        'Unable to create your account. Please try again.',
      )
    }
  }

  return (
    <div className="flex min-h-[calc(100vh-8rem)] items-center justify-center px-6 py-12">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle>Create account</CardTitle>

          <CardDescription>
            Create your AICO Platform account.
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
              <Label htmlFor="name">Name</Label>

              <Input
                id="name"
                type="text"
                autoComplete="name"
                aria-invalid={Boolean(errors.name)}
                disabled={isSubmitting}
                {...register('name')}
              />

              {errors.name?.message && (
                <p className="text-sm text-destructive">
                  {errors.name.message}
                </p>
              )}
            </div>

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

            <div className="space-y-2">
              <Label htmlFor="password">Password</Label>

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
              {isSubmitting
                ? 'Creating account...'
                : 'Create account'}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-muted-foreground">
            Already have an account?{' '}

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

export default RegisterPage
