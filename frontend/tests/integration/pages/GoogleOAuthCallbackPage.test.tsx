import {
  screen,
  waitFor,
} from '@testing-library/react'
import { useLocation } from 'react-router-dom'
import {
  describe,
  expect,
  it,
  vi,
} from 'vitest'

import {
  getCurrentUser,
} from '@/features/auth/authApi'
import GoogleOAuthCallbackPage from '@/pages/GoogleOAuthCallbackPage'
import {
  renderWithProviders,
} from '@tests/support/render'

const mocks = vi.hoisted(() => ({
  getCurrentUser: vi.fn(),
  setStoredAccessToken: vi.fn(),
  clearStoredAccessToken: vi.fn(),
}))

vi.mock('@/features/auth/authApi', () => ({
  getCurrentUser:
    mocks.getCurrentUser,
}))

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken:
    vi.fn(() => null),
  setStoredAccessToken:
    mocks.setStoredAccessToken,
  clearStoredAccessToken:
    mocks.clearStoredAccessToken,
}))

const getCurrentUserMock =
  vi.mocked(getCurrentUser)

function LocationDisplay() {
  const location = useLocation()

  return (
    <output data-testid="location">
      {location.pathname}
    </output>
  )
}

describe('GoogleOAuthCallbackPage', () => {
  it('establishes a session from the OAuth token', async () => {
    const authUser = {
      id: 1,
      name: 'Google User',
      email: 'google@example.com',
      email_verified_at:
        '2026-09-27T00:00:00.000Z',
    }

    getCurrentUserMock.mockResolvedValue({
      success: true,
      message:
        'Authenticated user retrieved successfully.',
      data: authUser,
    })

    window.history.pushState(
      null,
      '',
      '/auth/google/callback#access_token=google-token',
    )

    const { store } =
      renderWithProviders(
        <>
          <GoogleOAuthCallbackPage />
          <LocationDisplay />
        </>,
        {
          initialEntries: [
            '/auth/google/callback#access_token=google-token',
          ],
        },
      )

    await waitFor(() => {
      expect(
        screen.getByTestId('location'),
      ).toHaveTextContent('/')
    })

    expect(
      mocks.setStoredAccessToken,
    ).toHaveBeenCalledWith(
      'google-token',
    )

    expect(
      getCurrentUserMock,
    ).toHaveBeenCalledTimes(1)

    expect(
      store.getState().auth.accessToken,
    ).toBe('google-token')

    expect(
      store.getState().auth.user,
    ).toEqual(authUser)

    expect(
      store.getState().auth.status,
    ).toBe('authenticated')

    expect(
      window.location.hash,
    ).toBe('')
  })

  it('shows an error when Google returns an error', () => {
    window.history.pushState(
      null,
      '',
      '/auth/google/callback?error=google_auth_failed',
    )

    renderWithProviders(
      <GoogleOAuthCallbackPage />,
      {
        initialEntries: [
          '/auth/google/callback?error=google_auth_failed',
        ],
      },
    )

    expect(
      screen.getByText('Google sign-in failed', {
        exact: true,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('alert'),
    ).toHaveTextContent(
      'Unable to sign in with Google.',
    )

    expect(
      getCurrentUserMock,
    ).not.toHaveBeenCalled()
  })

  it('clears the token when session initialization fails', async () => {
    getCurrentUserMock.mockRejectedValue(
      new Error('Unauthorized'),
    )

    window.history.pushState(
      null,
      '',
      '/auth/google/callback#access_token=google-token',
    )

    renderWithProviders(
      <GoogleOAuthCallbackPage />,
      {
        initialEntries: [
          '/auth/google/callback#access_token=google-token',
        ],
      },
    )

    expect(
      await screen.findByRole('alert'),
    ).toHaveTextContent(
      'Google sign-in could not be completed.',
    )

    expect(
      mocks.clearStoredAccessToken,
    ).toHaveBeenCalledTimes(1)
  })
})
