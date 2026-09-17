import axios, {
  type AxiosError,
  type InternalAxiosRequestConfig,
} from 'axios'

import {
  clearStoredAccessToken,
  getStoredAccessToken,
  setStoredAccessToken,
} from '@/lib/tokenStorage'

const api = axios.create({
  baseURL:
    import.meta.env.VITE_API_BASE_URL ??
    'http://127.0.0.1:8000/api/v1',
  timeout: 10000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

type AuthRequestConfig = InternalAxiosRequestConfig & {
  _retry?: boolean
  _skipAuthRefresh?: boolean
}

const AUTH_ENDPOINTS_WITHOUT_REFRESH = new Set([
  '/auth/login',
  '/auth/register',
  '/auth/forgot-password',
  '/auth/reset-password',
  '/auth/refresh',
])

let refreshPromise: Promise<string> | null = null

function shouldSkipRefresh(config: AuthRequestConfig): boolean {
  if (config._skipAuthRefresh) {
    return true
  }

  const requestUrl = config.url ?? ''

  return AUTH_ENDPOINTS_WITHOUT_REFRESH.has(requestUrl)
}

api.interceptors.request.use((config) => {
  const token = getStoredAccessToken()

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

async function refreshAccessToken(): Promise<string> {
  if (!refreshPromise) {
    const currentToken = getStoredAccessToken()

    if (!currentToken) {
      throw new Error('No access token available for refresh.')
    }

    refreshPromise = api
      .post(
        '/auth/refresh',
        null,
        {
          headers: {
            Authorization: `Bearer ${currentToken}`,
          },
          _skipAuthRefresh: true,
        } as AuthRequestConfig,
      )
      .then((response) => {
        const token = response.data.data.access_token

        setStoredAccessToken(token)

        return token
      })
      .catch((error: unknown) => {
        clearStoredAccessToken()

        throw error
      })
      .finally(() => {
        refreshPromise = null
      })
  }

  return refreshPromise
}

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as AuthRequestConfig | undefined

    if (
      error.response?.status !== 401 ||
      !originalRequest ||
      originalRequest._retry ||
      shouldSkipRefresh(originalRequest) ||
      !getStoredAccessToken()
    ) {
      return Promise.reject(error)
    }

    originalRequest._retry = true

    try {
      const token = await refreshAccessToken()

      originalRequest.headers.Authorization = `Bearer ${token}`

      return api(originalRequest)
    } catch {
      return Promise.reject(error)
    }
  },
)

export default api
