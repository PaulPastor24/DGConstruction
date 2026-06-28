'use client'

import * as React from 'react'
import {
  FluentProvider,
  webLightTheme,
  webDarkTheme,
  SSRProvider,
  RendererProvider,
  createDOMRenderer,
  renderToStyleElements,
} from '@fluentui/react-components'
import { useServerInsertedHTML } from 'next/navigation'

type ThemeMode = 'light' | 'dark'

const ThemeModeContext = React.createContext<{
  mode: ThemeMode
  setMode: (mode: ThemeMode) => void
}>({ mode: 'light', setMode: () => {} })

export function useThemeMode() {
  return React.useContext(ThemeModeContext)
}

export function Providers({ children }: { children: React.ReactNode }) {
  const [renderer] = React.useState(() => createDOMRenderer())
  const didRenderRef = React.useRef(false)
  const [mode, setMode] = React.useState<ThemeMode>('light')

  useServerInsertedHTML(() => {
    if (didRenderRef.current) {
      return
    }
    didRenderRef.current = true
    return <>{renderToStyleElements(renderer)}</>
  })

  return (
    <RendererProvider renderer={renderer}>
      <SSRProvider>
        <ThemeModeContext.Provider value={{ mode, setMode }}>
          <FluentProvider
            theme={mode === 'light' ? webLightTheme : webDarkTheme}
            id="__fluent-root"
          >
            {children}
          </FluentProvider>
        </ThemeModeContext.Provider>
      </SSRProvider>
    </RendererProvider>
  )
}
