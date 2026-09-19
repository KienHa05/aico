import { useEffect } from 'react'

import { initializeAuthSession } from '@/features/auth/authSession'
import AppRoutes from '@/routes/AppRoutes'
import { useAppDispatch } from '@/store/hooks'

function App() {
  const dispatch = useAppDispatch()

  useEffect(() => {
    void initializeAuthSession(dispatch)
  }, [dispatch])

  return <AppRoutes />
}

export default App
