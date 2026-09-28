import { describe, expect, it, vi } from 'vitest'

import {
  forgotPassword,
  getCurrentUser,
  getGoogleRedirectUrl,
  login,
  logout,
  register,
  resetPassword,
} from '@/features/auth/authApi'

const apiMock = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn(),
}))

vi.mock('@/lib/api', () => ({
  default: apiMock,
}))

describe('authApi', () => {
  it('sends login credentials to the login endpoint', async () => {
    const response = {
      success: true,
      message: 'Login successful.',
      data: {
        access_token: 'access-token',
        token_type: 'Bearer',
        expires_in: 3600,
        user: {
          id: 1,
          name: 'Test User',
          email: 'user@example.com',
          email_verified_at: null,
        },
      },
    }

    apiMock.post.mockResolvedValue({
      data: response,
    })

    await expect(
      login({
        email: 'user@example.com',
        password: 'password123',
      }),
    ).resolves.toEqual(response)

    expect(apiMock.post).toHaveBeenCalledWith(
      '/auth/login',
      {
        email: 'user@example.com',
        password: 'password123',
      },
    )
  })

  it('sends registration data to the register endpoint', async () => {
    const response = {
      success: true,
      message: 'Register successfully.',
      data: {
        id: 1,
        name: 'Test User',
        email: 'user@example.com',
        email_verified_at: null,
      },
    }

    apiMock.post.mockResolvedValue({
      data: response,
    })

    await expect(
      register({
        name: 'Test User',
        email: 'user@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }),
    ).resolves.toEqual(response)

    expect(apiMock.post).toHaveBeenCalledWith(
      '/auth/register',
      {
        name: 'Test User',
        email: 'user@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      },
    )
  })

  it('requests the current authenticated user', async () => {
    const response = {
      success: true,
      message:
        'Authenticated user retrieved successfully.',
      data: {
        id: 1,
        name: 'Test User',
        email: 'user@example.com',
        email_verified_at: null,
      },
    }

    apiMock.get.mockResolvedValue({
      data: response,
    })

    await expect(
      getCurrentUser(),
    ).resolves.toEqual(response)

    expect(apiMock.get).toHaveBeenCalledWith(
      '/auth/me',
    )
  })

  it('logs out through the logout endpoint', async () => {
    const response = {
      success: true,
      message: 'Logout successful.',
      data: {},
    }

    apiMock.post.mockResolvedValue({
      data: response,
    })

    await expect(
      logout(),
    ).resolves.toEqual(response)

    expect(apiMock.post).toHaveBeenCalledWith(
      '/auth/logout',
    )
  })

  it('sends a forgot-password request', async () => {
    const response = {
      success: true,
      message:
        'If your email address exists in our system, you will receive a password reset link shortly.',
      data: {},
    }

    apiMock.post.mockResolvedValue({
      data: response,
    })

    await expect(
      forgotPassword({
        email: 'user@example.com',
      }),
    ).resolves.toEqual(response)

    expect(apiMock.post).toHaveBeenCalledWith(
      '/auth/forgot-password',
      {
        email: 'user@example.com',
      },
    )
  })

  it('sends a password-reset request', async () => {
    const response = {
      success: true,
      message: 'Password reset successfully.',
      data: {},
    }

    apiMock.post.mockResolvedValue({
      data: response,
    })

    await expect(
      resetPassword({
        token: 'reset-token',
        email: 'user@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }),
    ).resolves.toEqual(response)

    expect(apiMock.post).toHaveBeenCalledWith(
      '/auth/reset-password',
      {
        token: 'reset-token',
        email: 'user@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      },
    )
  })

  it('requests the Google OAuth redirect URL', async () => {
    const response = {
      success: true,
      message:
        'Google OAuth redirect URL generated successfully.',
      data: {
        redirect_url:
          'https://accounts.google.com/o/oauth2/auth?...',
      },
    }

    apiMock.get.mockResolvedValue({
      data: response,
    })

    await expect(
      getGoogleRedirectUrl(),
    ).resolves.toEqual(response)

    expect(apiMock.get).toHaveBeenCalledWith(
      '/auth/google/redirect',
    )
  })

  it('propagates API errors without swallowing them', async () => {
    const error = new Error('Request failed')

    apiMock.post.mockRejectedValue(error)

    await expect(
      login({
        email: 'user@example.com',
        password: 'password123',
      }),
    ).rejects.toBe(error)
  })
})
