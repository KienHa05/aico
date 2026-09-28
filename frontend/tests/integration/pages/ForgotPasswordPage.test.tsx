import {
  screen,
  waitFor,
} from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import {
  describe,
  expect,
  it,
  vi,
} from 'vitest'

import {
  forgotPassword,
} from '@/features/auth/authApi'
import ForgotPasswordPage from '@/pages/ForgotPasswordPage'
import {
  createAxiosError,
  renderWithProviders,
} from '@tests/support/render'

vi.mock('@/features/auth/authApi', () => ({
  forgotPassword: vi.fn(),
}))

vi.mock('@/lib/tokenStorage', () => ({
  getStoredAccessToken:
    vi.fn(() => null),
  setStoredAccessToken: vi.fn(),
  clearStoredAccessToken: vi.fn(),
}))

const forgotPasswordMock =
  vi.mocked(forgotPassword)

describe('ForgotPasswordPage', () => {
  it('validates the email before submitting', async () => {
    const user = userEvent.setup()

    renderWithProviders(
      <ForgotPasswordPage />,
      {
        initialEntries: ['/forgot-password'],
      },
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Send reset link',
      }),
    )

    expect(
      screen.getByText('Email is required.'),
    ).toBeInTheDocument()

    expect(
      forgotPasswordMock,
    ).not.toHaveBeenCalled()
  })

  it('shows the backend success message', async () => {
    const user = userEvent.setup()

    forgotPasswordMock.mockResolvedValue({
      success: true,
      message:
        'If your email address exists in our system, you will receive a password reset link shortly.',
      data: {},
    })

    renderWithProviders(
      <ForgotPasswordPage />,
      {
        initialEntries: ['/forgot-password'],
      },
    )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Send reset link',
      }),
    )

    await waitFor(() => {
      expect(
        screen.getByRole('status'),
      ).toHaveTextContent(
        'If your email address exists in our system',
      )
    })
  })

  it('displays a throttling error', async () => {
    const user = userEvent.setup()

    forgotPasswordMock.mockRejectedValue(
      createAxiosError(
        {
          message: 'Too many requests.',
        },
        429,
      ),
    )

    renderWithProviders(
      <ForgotPasswordPage />,
      {
        initialEntries: ['/forgot-password'],
      },
    )

    await user.type(
      screen.getByLabelText('Email'),
      'user@example.com',
    )

    await user.click(
      screen.getByRole('button', {
        name: 'Send reset link',
      }),
    )

    expect(
      await screen.findByRole('alert'),
    ).toHaveTextContent(
      'Too many requests.',
    )
  })
})
