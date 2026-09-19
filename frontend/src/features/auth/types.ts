export interface AuthUser {
  id: number | string
  name: string
  email: string
  email_verified_at: string | null
}

export interface AuthTokenData {
  access_token: string
  token_type: 'Bearer' | string
  expires_in: number
  user: AuthUser
}

export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface ForgotPasswordRequest {
  email: string
}

export interface ResetPasswordRequest {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export interface GoogleRedirectData {
  redirect_url: string
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export type AuthStatus =
  | 'idle'
  | 'authenticated'
  | 'unauthenticated'
