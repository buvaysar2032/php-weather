declare module '*.vue' {
  import type { defineComponent } from 'vue'
  const Component: ReturnType<typeof defineComponent>
  export default Component
}

declare global {
  interface Window {
    ace?: AceAjax
  }
}
