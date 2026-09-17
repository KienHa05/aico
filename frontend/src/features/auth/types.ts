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

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export type AuthStatus =
  | 'idle'
  | 'authenticated'
  | 'unauthenticated'
