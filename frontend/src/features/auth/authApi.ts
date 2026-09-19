import api from '@/lib/api'

import type {
  ApiResponse,
  AuthTokenData,
  AuthUser,
  ForgotPasswordRequest,
  GoogleRedirectData,
  LoginRequest,
  RegisterRequest,
  ResetPasswordRequest,
} from '@/features/auth/types'

export async function login(
  payload: LoginRequest,
): Promise<ApiResponse<AuthTokenData>> {
  const response = await api.post<ApiResponse<AuthTokenData>>(
    '/auth/login',
    payload,
  )

  return response.data
}

export async function register(
  payload: RegisterRequest,
): Promise<ApiResponse<AuthUser>> {
  const response = await api.post<ApiResponse<AuthUser>>(
    '/auth/register',
    payload,
  )

  return response.data
}

export async function getCurrentUser(): Promise<ApiResponse<AuthUser>> {
  const response = await api.get<ApiResponse<AuthUser>>('/auth/me')

  return response.data
}

export async function logout(): Promise<ApiResponse<Record<string, never>>> {
  const response = await api.post<ApiResponse<Record<string, never>>>(
    '/auth/logout',
  )

  return response.data
}

export async function forgotPassword(
  payload: ForgotPasswordRequest,
): Promise<ApiResponse<Record<string, never>>> {
  const response = await api.post<ApiResponse<Record<string, never>>>(
    '/auth/forgot-password',
    payload,
  )

  return response.data
}

export async function resetPassword(
  payload: ResetPasswordRequest,
): Promise<ApiResponse<Record<string, never>>> {
  const response = await api.post<ApiResponse<Record<string, never>>>(
    '/auth/reset-password',
    payload,
  )

  return response.data
}

export async function getGoogleRedirectUrl(): Promise<
  ApiResponse<GoogleRedirectData>
> {
  const response = await api.get<ApiResponse<GoogleRedirectData>>(
    '/auth/google/redirect',
  )

  return response.data
}
