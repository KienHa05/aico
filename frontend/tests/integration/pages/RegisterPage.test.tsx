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

import RegisterPage from '@/pages/RegisterPage'
import {
  renderWithProviders,
} from '@tests/support/render'

const registerMock =
  vi.hoisted(() => vi.fn())

vi.mock('@/features/auth/authApi', () => ({
  register: registerMock,
}))

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken:
    vi.fn(() => null),
  setStoredAccessToken: vi.fn(),
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

describe('RegisterPage', () => {
  it('validates required fields on the client', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <RegisterPage />,
      {
        initialEntries: ['/register'],
      },
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Create account',
      }),
    )

    expect(
      screen.getByText('Name is required.'),
    ).toBeInTheDocument()

    expect(
      screen.getByText('Email is required.'),
    ).toBeInTheDocument()

    expect(
      screen.getByText('Password is required.'),
    ).toBeInTheDocument()

    expect(
      screen.getByText(
        'Password confirmation is required.',
      ),
    ).toBeInTheDocument()

    expect(
      registerMock,
    ).not.toHaveBeenCalled()
  })

  it('rejects mismatched passwords before submitting', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <RegisterPage />,
      {
        initialEntries: ['/register'],
      },
    )

    await user.type(
      screen.getByLabelText('Name'),
      'Test User',
    )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.type(
      screen.getByLabelText('Password'),
      'password123',
    )

    await user.type(
      screen.getByLabelText('Confirm password'),
      'different-password',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Create account',
      }),
    )

    expect(
      screen.getByText(
        'Password confirmation does not match.',
      ),
    ).toBeInTheDocument()

    expect(
      registerMock,
    ).not.toHaveBeenCalled()
  })

  it('redirects to login after successful registration', async () => {
    const user = userEvent.setup()

    registerMock.mockResolvedValue({
      success: true,
      message: 'Register successfully.',
      data: {
        id: 1,
        name: 'Test User',
        email: 'user@example.com',
        email_verified_at: null,
      },
    })

    renderWithProviders(
      <>
        <RegisterPage />
        <LocationDisplay />
      </>,
      {
        initialEntries: ['/register'],
      },
    )

    await user.type(
      screen.getByLabelText('Name'),
      'Test User',
    )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.type(
      screen.getByLabelText('Password'),
      'password123',
    )

    await user.type(
      screen.getByLabelText('Confirm password'),
      'password123',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Create account',
      }),
    )

    await waitFor(() => {
      expect(
        screen.getByTestId('location'),
      ).toHaveTextContent('/login')
    })

    expect(
      registerMock,
    ).toHaveBeenCalledWith({
      name: 'Test User',
      email: 'user@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    })
  })

  it('displays server validation errors', async () => {
    const user = userEvent.setup()

    registerMock.mockRejectedValue({
      isAxiosError: true,
      response: {
        status: 422,
        data: {
          message:
            'The email has already been taken.',
          errors: {
            email: [
              'The email has already been taken.',
            ],
          },
        },
      },
    })

    renderWithProviders(
      <RegisterPage />,
      {
        initialEntries: ['/register'],
      },
    )

    await user.type(
      screen.getByLabelText('Name'),
      'Test User',
    )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.type(
      screen.getByLabelText('Password'),
      'password123',
    )

    await user.type(
      screen.getByLabelText('Confirm password'),
      'password123',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Create account',
      }),
    )

    expect(
      await screen.findByRole('alert'),
    ).toHaveTextContent(
      'The email has already been taken.',
    )

    expect(
      screen.getByLabelText('Email'),
    ).toHaveAttribute('aria-invalid', 'true')
  })
})
