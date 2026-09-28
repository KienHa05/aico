import {
  render,
  screen,
} from '@testing-library/react'
import { Provider } from 'react-redux'
import {
  MemoryRouter,
  Route,
  Routes,
} from 'react-router-dom'
import {
  describe,
  expect,
  it,
} from 'vitest'

import type {
  AuthUser,
} from '@/features/auth/types'
import ProtectedRoute from '@/routes/ProtectedRoute'
import {
  createAuthTestStore,
} from '@tests/support/render'

const user: AuthUser = {
  id: 1,
  name: 'Test User',
  email: 'user@example.com',
  email_verified_at: null,
}

function renderRoute(
  auth: Parameters<
    typeof createAuthTestStore
  >[0],
  initialEntry = '/protected',
) {
  const store = createAuthTestStore(auth)

  return {
    store,
    ...render(
      <Provider store={store}>
        <MemoryRouter
          initialEntries={[initialEntry]}
        >
          <Routes>
            <Route element={<ProtectedRoute />}>
              <Route
                path="/protected"
                element={
                  <div>
                    Protected content
                  </div>
                }
              />
            </Route>

            <Route
              path="/login"
              element={<div>Login page</div>}
            />
          </Routes>
        </MemoryRouter>
      </Provider>,
    ),
  }
}

describe('ProtectedRoute', () => {
  it('waits for session initialization', () => {
    renderRoute({
      accessToken: 'access-token',
      user,
      status: 'idle',
      initialized: false,
    })

    expect(
      screen.getByText(
        'Checking authentication...',
      ),
    ).toBeInTheDocument()

    expect(
      screen.queryByText(
        'Protected content',
      ),
    ).not.toBeInTheDocument()
  })

  it('redirects unauthenticated users to login', () => {
    renderRoute({
      accessToken: null,
      user: null,
      status: 'unauthenticated',
      initialized: true,
    })

    expect(
      screen.getByText('Login page'),
    ).toBeInTheDocument()

    expect(
      screen.queryByText(
        'Protected content',
      ),
    ).not.toBeInTheDocument()
  })

  it('renders the protected route for authenticated users', () => {
    renderRoute({
      accessToken: 'access-token',
      user,
      status: 'authenticated',
      initialized: true,
    })

    expect(
      screen.getByText(
        'Protected content',
      ),
    ).toBeInTheDocument()
  })
})
