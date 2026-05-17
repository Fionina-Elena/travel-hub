import { createSSRApp } from 'vue'
import { PageShell } from './PageShell'
import { setPageContext } from './usePageContext'

export { render }

function render(pageContext) {
  const { Page, pageProps } = pageContext
  const app = createSSRApp(Page, pageProps)
  setPageContext(app, pageContext)
  return app
}