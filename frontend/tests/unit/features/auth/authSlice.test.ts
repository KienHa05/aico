import { describe, expect, it, vi } from 'vitest'

import reducer, {
  clearAuth,
  setAccessToken,
  setCredentials,
  setInitialized,
  setStatus,
  setUser,
} from '@/features/auth/authSlice'
import type { AuthUser } from '@/features/auth/types'

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken: vi.fn(() => null),
}))

const user: AuthUser = {
  id: 1,
  name: 'Test User',
  email: 'user@example.com',
  email_verified_at: null,
}

describe('authSlice', () => {
  it('uses the expected initial authentication state', () => {
    const state = reducer(undefined, {
      type: '@@init',
    })

    expect(state).toEqual({
      accessToken: null,
      user: null,
      status: 'idle',
      initialized: false,
    })
  })

  it('sets credentials and authenticates the user', () => {
    const state = reducer(
      undefined,
      setCredentials({
        accessToken: 'access-token',
        user,
      }),
    )

    expect(state).toEqual({
      accessToken: 'access-token',
      user,
      status: 'authenticated',
      initialized: true,
    })
  })

  it('updates the access token', () => {
    const currentState = {
      accessToken: 'old-token',
      user,
      status: 'authenticated' as const,
      initialized: true,
    }

    const state = reducer(
      currentState,
      setAccessToken('new-token'),
    )

    expect(state.accessToken).toBe('new-token')
    expect(state.user).toEqual(user)
    expect(state.status).toBe('authenticated')
    expect(state.initialized).toBe(true)
  })

  it('sets the current user and authenticates the session', () => {
    const currentState = {
      accessToken: 'access-token',
      user: null,
      status: 'idle' as const,
      initialized: false,
    }

    const state = reducer(
      currentState,
      setUser(user),
    )

    expect(state.user).toEqual(user)
    expect(state.status).toBe('authenticated')
    expect(state.initialized).toBe(true)
  })

  it('updates the authentication status', () => {
    const currentState = {
      accessToken: 'access-token',
      user,
      status: 'idle' as const,
      initialized: false,
    }

    const state = reducer(
      currentState,
      setStatus('unauthenticated'),
    )

    expect(state.status).toBe('unauthenticated')
    expect(state.accessToken).toBe('access-token')
    expect(state.user).toEqual(user)
    expect(state.initialized).toBe(false)
  })

  it('updates initialization state', () => {
    const initialState = reducer(undefined, {
      type: '@@init',
    })

    const state = reducer(
      initialState,
      setInitialized(true),
    )

    expect(state.initialized).toBe(true)
    expect(state.accessToken).toBeNull()
    expect(state.user).toBeNull()
    expect(state.status).toBe('idle')
  })

  it('clears the authenticated session', () => {
    const currentState = {
      accessToken: 'access-token',
      user,
      status: 'authenticated' as const,
      initialized: true,
    }

    const state = reducer(
      currentState,
      clearAuth(),
    )

    expect(state).toEqual({
      accessToken: null,
      user: null,
      status: 'unauthenticated',
      initialized: true,
    })
  })
})
