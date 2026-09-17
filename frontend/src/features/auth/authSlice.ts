import { createSlice, type PayloadAction } from '@reduxjs/toolkit'

import type {
  AuthStatus,
  AuthUser,
} from '@/features/auth/types'
import { getStoredAccessToken } from '@/lib/tokenStorage'

interface AuthState {
  accessToken: string | null
  user: AuthUser | null
  status: AuthStatus
  initialized: boolean
}

const initialState: AuthState = {
  accessToken: getStoredAccessToken(),
  user: null,
  status: 'idle',
  initialized: false,
}

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setCredentials: (
      state,
      action: PayloadAction<{
        accessToken: string
        user: AuthUser
      }>,
    ) => {
      state.accessToken = action.payload.accessToken
      state.user = action.payload.user
      state.status = 'authenticated'
      state.initialized = true
    },

    setAccessToken: (state, action: PayloadAction<string>) => {
      state.accessToken = action.payload
    },

    setUser: (state, action: PayloadAction<AuthUser>) => {
      state.user = action.payload
      state.status = 'authenticated'
      state.initialized = true
    },

    setStatus: (state, action: PayloadAction<AuthStatus>) => {
      state.status = action.payload
    },

    setInitialized: (state, action: PayloadAction<boolean>) => {
      state.initialized = action.payload
    },

    clearAuth: (state) => {
      state.accessToken = null
      state.user = null
      state.status = 'unauthenticated'
      state.initialized = true
    },
  },
})

export const {
  clearAuth,
  setAccessToken,
  setCredentials,
  setInitialized,
  setStatus,
  setUser,
} = authSlice.actions

export default authSlice.reducer
