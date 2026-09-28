import {
  screen,
  waitFor,
} from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { useLocation } from 'react-router-dom'
import {
  describe,
  expect,
  it,
  vi,
} from 'vitest'

import LoginPage from '@/pages/LoginPage'
import {
  renderWithProviders,
} from '@tests/support/render'

const mocks = vi.hoisted(() => ({
  login: vi.fn(),
  getGoogleRedirectUrl: vi.fn(),
  setStoredAccessToken: vi.fn(),
}))

vi.mock('@/features/auth/authApi', () => ({
  login: mocks.login,
  getGoogleRedirectUrl:
    mocks.getGoogleRedirectUrl,
}))

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken:
    vi.fn(() => null),
  setStoredAccessToken:
    mocks.setStoredAccessToken,
  clearStoredAccessToken: vi.fn(),
}))

function LocationDisplay() {
  const location = useLocation()

  return (
    <output data-testid="location">
      {location.pathname}
    </output>
  )
}

describe('LoginPage', () => {
  it('renders the login form and recovery link', () => {
    renderWithProviders(
      <LoginPage />,
      {
        initialEntries: ['/login'],
      },
    )

    expect(
      screen.getByText(
        'Sign in to your AICO Platform account.',
        { exact: true },
      ),
    ).toBeInTheDocument()

    expect(
      screen.getByLabelText('Email'),
    ).toBeInTheDocument()

    expect(
      screen.getByLabelText('Password'),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('link', {
        name: 'Forgot your password?',
      }),
    ).toHaveAttribute(
      'href',
      '/forgot-password',
    )

    expect(
      screen.getByRole('button', {
        name: 'Continue with Google',
      }),
    ).toBeInTheDocument()
  })

  it('shows client validation errors without calling the API', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <LoginPage />,
      {
        initialEntries: ['/login'],
      },
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Log in',
      }),
    )

    expect(
      screen.getByText('Email is required.'),
    ).toBeInTheDocument()

    expect(
      screen.getByText('Password is required.'),
    ).toBeInTheDocument()

    expect(
      mocks.login,
    ).not.toHaveBeenCalled()
  })

  it('authenticates the user after successful login', async () => {
    const user = userEvent.setup()

    const authUser = {
      id: 1,
      name: 'Test User',
      email: 'user@example.com',
      email_verified_at: null,
    }

    mocks.login.mockResolvedValue({
      success: true,
      message: 'Login successful.',
      data: {
        access_token: 'access-token',
        token_type: 'Bearer',
        expires_in: 3600,
        user: authUser,
      },
    })

    const { store } =
      renderWithProviders(
        <>
          <LoginPage />
          <LocationDisplay />
        </>,
        {
          initialEntries: ['/login'],
        },
      )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.type(
      screen.getByLabelText('Password'),
      'password123',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Log in',
      }),
    )

    await waitFor(() => {
      expect(
        screen.getByTestId('location'),
      ).toHaveTextContent('/')
    })

    expect(mocks.login).toHaveBeenCalledWith({
      email: 'user@example.com',
      password: 'password123',
    })

    expect(
      mocks.setStoredAccessToken,
    ).toHaveBeenCalledWith(
      'access-token',
    )

    expect(
      store.getState().auth.accessToken,
    ).toBe('access-token')

    expect(
      store.getState().auth.user,
    ).toEqual(authUser)

    expect(
      store.getState().auth.status,
    ).toBe('authenticated')
  })

  it('displays a server authentication error', async () => {
    const user = userEvent.setup()

    mocks.login.mockRejectedValue({
      isAxiosError: true,
      response: {
        status: 401,
        data: {
          message: 'Invalid credentials.',
        },
      },
    })

    renderWithProviders(
      <LoginPage />,
      {
        initialEntries: ['/login'],
      },
    )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.type(
      screen.getByLabelText('Password'),
      'wrong-password',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Log in',
      }),
    )

    expect(
      await screen.findByRole('alert'),
    ).toHaveTextContent(
      'Invalid credentials.',
    )
  })
})
