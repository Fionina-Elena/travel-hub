import type { Component } from 'vue'
import { PageContext } from 'vike/types'

export { usePageContext }

function usePageContext() {
  return (window).__VITE_PLUGIN_VUE_PAGE_CONTEXT__
}

declare global {
  interface Window {
    __VITE_PLUGIN_VUE_PAGE_CONTEXT__: PageContext
  }
}