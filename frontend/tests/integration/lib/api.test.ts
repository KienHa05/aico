import {
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from 'vitest'

type TestRequestConfig = {
  url: string
  headers: Record<string, string>
  _retry?: boolean
  _skipAuthRefresh?: boolean
}

const mocks = vi.hoisted(() => ({
  create: vi.fn(),
  requestUse: vi.fn(),
  responseUse: vi.fn(),
  post: vi.fn(),
  request: vi.fn(),
}))

const fakeApi = Object.assign(
  mocks.request,
  {
    interceptors: {
      request: {
        use: mocks.requestUse,
      },
      response: {
        use: mocks.responseUse,
      },
    },
    post: mocks.post,
  },
)

mocks.create.mockReturnValue(fakeApi)

vi.mock('axios', () => ({
  default: {
    create: mocks.create,
  },
}))

describe('api authentication lifecycle', () => {
  beforeEach(() => {
    localStorage.clear()

    vi.resetModules()

    mocks.create.mockClear()
    mocks.requestUse.mockClear()
    mocks.responseUse.mockClear()
    mocks.post.mockReset()
    mocks.request.mockReset()

    mocks.create.mockReturnValue(fakeApi)
  })

  async function loadInterceptors() {
    await import('@/lib/api')

    const requestFulfilled =
      mocks.requestUse.mock.calls[0][0] as (
        config: {
          headers: Record<string, string>
        },
      ) => {
        headers: Record<string, string>
      }

    const responseRejected =
      mocks.responseUse.mock.calls[0][1] as (
        error: unknown,
      ) => Promise<unknown>

    return {
      requestFulfilled,
      responseRejected,
    }
  }

  it('adds the stored bearer token to requests', async () => {
    localStorage.setItem(
      'aico_access_token',
      'access-token',
    )

    const { requestFulfilled } =
      await loadInterceptors()

    const config = {
      headers: {},
    }

    const result = requestFulfilled(config)

    expect(
      result.headers.Authorization,
    ).toBe('Bearer access-token')
  })

  it('does not add authorization when no token is stored', async () => {
    const { requestFulfilled } =
      await loadInterceptors()

    const config = {
      headers: {},
    }

    const result = requestFulfilled(config)

    expect(
      result.headers.Authorization,
    ).toBeUndefined()
  })

  it('refreshes the token once for concurrent unauthorized requests', async () => {
    localStorage.setItem(
      'aico_access_token',
      'old-token',
    )

    mocks.post.mockResolvedValue({
      data: {
        data: {
          access_token: 'new-token',
        },
      },
    })

    mocks.request.mockResolvedValue({
      data: {
        success: true,
      },
    })

    const { responseRejected } =
      await loadInterceptors()

    const firstConfig: TestRequestConfig = {
      url: '/protected',
      headers: {},
    }

    const secondConfig: TestRequestConfig = {
      url: '/protected',
      headers: {},
    }

    const firstError = {
      response: {
        status: 401,
      },
      config: firstConfig,
    }

    const secondError = {
      response: {
        status: 401,
      },
      config: secondConfig,
    }

    await Promise.all([
      responseRejected(firstError),
      responseRejected(secondError),
    ])

    expect(
      mocks.post,
    ).toHaveBeenCalledTimes(1)

    expect(
      localStorage.getItem(
        'aico_access_token',
      ),
    ).toBe('new-token')

    expect(
      mocks.request,
    ).toHaveBeenCalledTimes(2)

    expect(
      firstConfig.headers.Authorization,
    ).toBe('Bearer new-token')

    expect(
      secondConfig.headers.Authorization,
    ).toBe('Bearer new-token')
  })

  it('does not refresh an auth endpoint after a 401', async () => {
    localStorage.setItem(
      'aico_access_token',
      'access-token',
    )

    const { responseRejected } =
      await loadInterceptors()

    const error = {
      response: {
        status: 401,
      },
      config: {
        url: '/auth/login',
        headers: {},
      } satisfies TestRequestConfig,
    }

    await expect(
      responseRejected(error),
    ).rejects.toBe(error)

    expect(
      mocks.post,
    ).not.toHaveBeenCalled()

    expect(
      mocks.request,
    ).not.toHaveBeenCalled()
  })

  it('clears the stored token when refresh fails', async () => {
    localStorage.setItem(
      'aico_access_token',
      'access-token',
    )

    mocks.post.mockRejectedValue(
      new Error('Refresh failed'),
    )

    const { responseRejected } =
      await loadInterceptors()

    const error = {
      response: {
        status: 401,
      },
      config: {
        url: '/protected',
        headers: {},
      } satisfies TestRequestConfig,
    }

    await expect(
      responseRejected(error),
    ).rejects.toBe(error)

    expect(
      localStorage.getItem(
        'aico_access_token',
      ),
    ).toBeNull()
  })
})
