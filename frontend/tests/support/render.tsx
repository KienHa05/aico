import type {
  PropsWithChildren,
  ReactElement,
} from 'react'

import {
  render,
  type RenderOptions,
} from '@testing-library/react'
import { configureStore } from '@reduxjs/toolkit'
import { Provider } from 'react-redux'
import { MemoryRouter } from 'react-router-dom'

import authReducer from '@/features/auth/authSlice'
import type { AuthUser } from '@/features/auth/types'

type AuthState = ReturnType<typeof authReducer>

type RenderWithProvidersOptions = Omit<
  RenderOptions,
  'wrapper'
> & {
  preloadedAuth?: Partial<AuthState>
  initialEntries?: string[]
}

const defaultAuthState: AuthState = {
  accessToken: null,
  user: null,
  status: 'unauthenticated',
  initialized: true,
}

export function createAuthTestStore(
  preloadedAuth: Partial<AuthState> = {},
) {
  return configureStore({
    reducer: {
      auth: authReducer,
    },
    preloadedState: {
      auth: {
        ...defaultAuthState,
        ...preloadedAuth,
      },
    },
  })
}

export function createTestUser(
  overrides: Partial<AuthUser> = {},
): AuthUser {
  return {
    id: 1,
    name: 'Test User',
    email: 'user@example.com',
    email_verified_at: null,
    ...overrides,
  }
}

export function renderWithProviders(
  ui: ReactElement,
  {
    preloadedAuth,
    initialEntries = ['/'],
    ...renderOptions
  }: RenderWithProvidersOptions = {},
) {
  const store = createAuthTestStore(preloadedAuth)

  function Wrapper({
    children,
  }: PropsWithChildren) {
    return (
      <Provider store={store}>
        <MemoryRouter initialEntries={initialEntries}>
          {children}
        </MemoryRouter>
      </Provider>
    )
  }

  return {
    store,
    ...render(ui, {
      wrapper: Wrapper,
      ...renderOptions,
    }),
  }
}

export function createAxiosError<T>(
  data: T,
  status: number,
): {
  isAxiosError: true
  response: {
    status: number
    data: T
  }
} {
  return {
    isAxiosError: true,
    response: {
      status,
      data,
    },
  }
}
