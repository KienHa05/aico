import { useNavigate } from 'react-router-dom'
import { EmptyState } from '@/components/ui'

function NotFoundPage() {
  const navigate = useNavigate()

  return (
    <div className="flex flex-1 items-center justify-center p-6">
      <EmptyState
        title="404 - Page Not Found"
        description="The page you are looking for does not exist or has been moved."
        actionLabel="Go to Home"
        onAction={() => navigate('/')}
      />
    </div>
  )
}

export default NotFoundPage
