import {
  beforeEach,
  describe,
  expect,
  it,
} from 'vitest'

import {
  clearStoredAccessToken,
  getStoredAccessToken,
  setStoredAccessToken,
} from '@/lib/tokenStorage'

const ACCESS_TOKEN_KEY = 'aico_access_token'

describe('tokenStorage', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  it('returns null when no token exists', () => {
    expect(
      getStoredAccessToken(),
    ).toBeNull()
  })

  it('stores and retrieves the access token', () => {
    setStoredAccessToken('access-token')

    expect(
      localStorage.getItem(ACCESS_TOKEN_KEY),
    ).toBe('access-token')

    expect(
      getStoredAccessToken(),
    ).toBe('access-token')
  })

  it('clears the access token', () => {
    setStoredAccessToken('access-token')

    clearStoredAccessToken()

    expect(
      localStorage.getItem(ACCESS_TOKEN_KEY),
    ).toBeNull()

    expect(
      getStoredAccessToken(),
    ).toBeNull()
  })
})
