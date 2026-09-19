import { getCurrentUser } from '@/features/auth/authApi'
import {
  clearAuth,
  setAccessToken,
  setCredentials,
  setInitialized,
  setStatus,
} from '@/features/auth/authSlice'
import {
  clearStoredAccessToken,
  getStoredAccessToken,
} from '@/lib/tokenStorage'
import type { AppDispatch } from '@/store'

let initializationPromise: Promise<void> | null = null

export function initializeAuthSession(dispatch: AppDispatch): Promise<void> {
  if (!initializationPromise) {
    initializationPromise = initialize(dispatch)
  }

  return initializationPromise
}

async function initialize(dispatch: AppDispatch): Promise<void> {
  const accessToken = getStoredAccessToken()

  if (!accessToken) {
    dispatch(setStatus('unauthenticated'))
    dispatch(setInitialized(true))
    return
  }

  dispatch(setStatus('idle'))
  dispatch(setAccessToken(accessToken))

  try {
    const response = await getCurrentUser()
    const currentAccessToken = getStoredAccessToken()

    if (!currentAccessToken) {
      dispatch(clearAuth())
      return
    }

    dispatch(
      setCredentials({
        accessToken: currentAccessToken,
        user: response.data,
      }),
    )
  } catch {
    clearStoredAccessToken()
    dispatch(clearAuth())
  }
}
