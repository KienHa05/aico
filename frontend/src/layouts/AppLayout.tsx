import { Link, Outlet } from 'react-router-dom'

function AppLayout() {
  return (
    <div className="flex min-h-screen flex-col bg-gray-50 text-gray-900">
      <header className="border-b border-gray-200 bg-white">
        <div className="mx-auto flex min-h-16 w-full max-w-7xl items-center px-6">
          <Link
            to="/"
            className="text-lg font-bold tracking-tight text-gray-900"
          >
            AICO Platform
          </Link>
        </div>
      </header>

      <main className="flex-1">
        <Outlet />
      </main>

      <footer className="border-t border-gray-200 bg-white">
        <div className="mx-auto flex w-full max-w-7xl items-center px-6 py-4 text-sm text-gray-500">
          AICO Platform
        </div>
      </footer>
    </div>
  )
}

export default AppLayout
