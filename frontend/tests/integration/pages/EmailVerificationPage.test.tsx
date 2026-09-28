import { screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'

import EmailVerificationPage from '@/pages/EmailVerificationPage'
import {
  renderWithProviders,
} from '@tests/support/render'

describe('EmailVerificationPage', () => {
  it('shows the successful verification state', () => {
    renderWithProviders(
      <EmailVerificationPage />,
      {
        initialEntries: [
          '/email-verification?status=success',
        ],
      },
    )

    expect(
      screen.getByText('Email verified', {
        exact: true,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('status'),
    ).toHaveTextContent(
      'Your account email is now verified.',
    )

    expect(
      screen.getByRole('link', {
        name: 'Go to login',
      }),
    ).toHaveAttribute(
      'href',
      '/login',
    )
  })

  it('shows the fallback state without a successful status', () => {
    renderWithProviders(
      <EmailVerificationPage />,
      {
        initialEntries: [
          '/email-verification',
        ],
      },
    )

    expect(
      screen.getByText('Email verification', {
        exact: true,
      }),
    ).toBeInTheDocument()

    expect(
      screen.getByRole('alert'),
    ).toHaveTextContent(
      'Please use the verification link sent to your email address.',
    )
  })
})
