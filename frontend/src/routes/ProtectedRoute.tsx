import { Navigate, Outlet, useLocation } from 'react-router-dom'

import { LoadingState } from '@/components/ui'
import { useAppSelector } from '@/store/hooks'

function ProtectedRoute() {
  const location = useLocation()
  const { initialized, status } = useAppSelector((state) => state.auth)

  if (!initialized) {
    return <LoadingState message="Checking authentication..." />
  }

  if (status !== 'authenticated') {
    return (
      <Navigate
        to="/login"
        replace
        state={{ from: location }}
      />
    )
  }

  return <Outlet />
}

export default ProtectedRoute
