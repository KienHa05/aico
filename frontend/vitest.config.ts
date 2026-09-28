import { fileURLToPath } from 'node:url'

import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@tests': fileURLToPath(
        new URL('./tests/', import.meta.url),
      ),
      '@': fileURLToPath(
        new URL('./src/', import.meta.url),
      ),
    },
  },
  test: {
    environment: 'jsdom',
    environmentOptions: {
      jsdom: {
        url: 'http://localhost/',
      },
    },
    setupFiles: ['./tests/setup.ts'],
    include: [
      'tests/**/*.{test,spec}.{ts,tsx}',
    ],
    clearMocks: true,
    mockReset: true,
    restoreMocks: true,
  },
})
