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

import {
  resetPassword,
} from '@/features/auth/authApi'
import ResetPasswordPage from '@/pages/ResetPasswordPage'
import {
  createAxiosError,
  renderWithProviders,
} from '@tests/support/render'

vi.mock('@/features/auth/authApi', () => ({
  resetPassword: vi.fn(),
}))

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken:
    vi.fn(() => null),
  setStoredAccessToken: vi.fn(),
  clearStoredAccessToken: vi.fn(),
}))

const resetPasswordMock =
  vi.mocked(resetPassword)

function LocationDisplay() {
  const location = useLocation()

  return (
    <output data-testid="location">
      {location.pathname}
    </output>
  )
}

describe('ResetPasswordPage', () => {
  it('rejects an incomplete reset link', () => {
    renderWithProviders(
      <ResetPasswordPage />,
      {
        initialEntries: ['/reset-password'],
      },
    )

    expect(
      screen.getByText('Invalid reset link', {
        exact: true,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('link', {
        name: 'Request a new reset link',
      }),
    ).toHaveAttribute(
      'href',
      '/forgot-password',
    )
  })

  it('submits token and email from the reset link', async () => {
    const user = userEvent.setup()

    resetPasswordMock.mockResolvedValue({
      success: true,
      message: 'Password reset successfully.',
      data: {},
    })

    renderWithProviders(
      <>
        <ResetPasswordPage />
        <LocationDisplay />
      </>,
      {
        initialEntries: [
          '/reset-password?token=reset-token&email=user%40example.com',
        ],
      },
    )

    expect(
      screen.getByLabelText('Email'),
    ).toHaveValue('user@example.com')

    await user.type(
      screen.getByLabelText('New password'),
      'password123',
    )

    await user.type(
      screen.getByLabelText('Confirm password'),
      'password123',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Reset password',
      }),
    )

    await waitFor(() => {
      expect(
        screen.getByTestId('location'),
      ).toHaveTextContent('/login')
    })

    expect(
      resetPasswordMock,
    ).toHaveBeenCalledWith({
      token: 'reset-token',
      email: 'user@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    })
  })

  it('displays backend password validation errors', async () => {
    const user = userEvent.setup()

    resetPasswordMock.mockRejectedValue(
      createAxiosError(
        {
          message:
            'The password field must be at least 8 characters.',
          errors: {
            password: [
              'The password field must be at least 8 characters.',
            ],
          },
        },
        422,
      ),
    )

    renderWithProviders(
      <ResetPasswordPage />,
      {
        initialEntries: [
          '/reset-password?token=reset-token&email=user%40example.com',
        ],
      },
    )

    await user.type(
      screen.getByLabelText('New password'),
      'short',
    )

    await user.type(
      screen.getByLabelText('Confirm password'),
      'short',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Reset password',
      }),
    )

    expect(
      await screen.findByRole('alert'),
    ).toHaveTextContent(
      'The password field must be at least 8 characters.',
    )

    expect(
      screen.getByLabelText('New password'),
    ).toHaveAttribute('aria-invalid', 'true')
  })
})
