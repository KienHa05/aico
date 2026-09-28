import { describe, expect, it, vi } from 'vitest'

import {
  clearAuth,
  setAccessToken,
  setCredentials,
  setInitialized,
  setStatus,
} from '@/features/auth/authSlice'
import type { AppDispatch } from '@/store'

const mocks = vi.hoisted(() => ({
  getCurrentUser: vi.fn(),
  getStoredAccessToken: vi.fn(),
  clearStoredAccessToken: vi.fn(),
}))

vi.mock('@/features/auth/authApi', () => ({
  getCurrentUser: mocks.getCurrentUser,
}))

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken:
    mocks.getStoredAccessToken,
  clearStoredAccessToken:
    mocks.clearStoredAccessToken,
}))

describe('authSession', () => {
  async function loadSession() {
    vi.resetModules()

    return import('@/features/auth/authSession')
  }

  it('marks the session unauthenticated when no token exists', async () => {
    mocks.getStoredAccessToken.mockReturnValue(null)

    const { initializeAuthSession } =
      await loadSession()

    const dispatchMock = vi.fn()
    const dispatch =
      dispatchMock as unknown as AppDispatch

    await initializeAuthSession(dispatch)

    expect(
      mocks.getCurrentUser,
    ).not.toHaveBeenCalled()

    expect(dispatchMock).toHaveBeenNthCalledWith(
      1,
      setStatus('unauthenticated'),
    )

    expect(dispatchMock).toHaveBeenNthCalledWith(
      2,
      setInitialized(true),
    )
  })

  it('restores an authenticated session from the stored token', async () => {
    const user = {
      id: 1,
      name: 'Test User',
      email: 'user@example.com',
      email_verified_at: null,
    }

    mocks.getStoredAccessToken.mockReturnValue(
      'access-token',
    )

    mocks.getCurrentUser.mockResolvedValue({
      success: true,
      message:
        'Authenticated user retrieved successfully.',
      data: user,
    })

    const { initializeAuthSession } =
      await loadSession()

    const dispatchMock = vi.fn()
    const dispatch =
      dispatchMock as unknown as AppDispatch

    await initializeAuthSession(dispatch)

    expect(
      mocks.getCurrentUser,
    ).toHaveBeenCalledTimes(1)

    expect(dispatchMock).toHaveBeenNthCalledWith(
      1,
      setStatus('idle'),
    )

    expect(dispatchMock).toHaveBeenNthCalledWith(
      2,
      setAccessToken('access-token'),
    )

    expect(dispatchMock).toHaveBeenNthCalledWith(
      3,
      setCredentials({
        accessToken: 'access-token',
        user,
      }),
    )
  })

  it('clears the session when /auth/me fails', async () => {
    mocks.getStoredAccessToken.mockReturnValue(
      'access-token',
    )

    mocks.getCurrentUser.mockRejectedValue(
      new Error('Unauthorized'),
    )

    const { initializeAuthSession } =
      await loadSession()

    const dispatchMock = vi.fn()
    const dispatch =
      dispatchMock as unknown as AppDispatch

    await initializeAuthSession(dispatch)

    expect(
      mocks.clearStoredAccessToken,
    ).toHaveBeenCalledTimes(1)

    expect(dispatchMock).toHaveBeenCalledWith(
      clearAuth(),
    )
  })

  it('deduplicates concurrent initialization calls', async () => {
    mocks.getStoredAccessToken.mockReturnValue(
      'access-token',
    )

    let resolveCurrentUser:
      | ((value: unknown) => void)
      | undefined

    mocks.getCurrentUser.mockReturnValue(
      new Promise((resolve) => {
        resolveCurrentUser = resolve
      }),
    )

    const { initializeAuthSession } =
      await loadSession()

    const dispatchMock = vi.fn()
    const dispatch =
      dispatchMock as unknown as AppDispatch

    const firstCall =
      initializeAuthSession(dispatch)
    const secondCall =
      initializeAuthSession(dispatch)

    expect(firstCall).toBe(secondCall)
    expect(
      mocks.getCurrentUser,
    ).toHaveBeenCalledTimes(1)

    resolveCurrentUser?.({
      success: true,
      message:
        'Authenticated user retrieved successfully.',
      data: {
        id: 1,
        name: 'Test User',
        email: 'user@example.com',
        email_verified_at: null,
      },
    })

    await Promise.all([
      firstCall,
      secondCall,
    ])
  })
})
