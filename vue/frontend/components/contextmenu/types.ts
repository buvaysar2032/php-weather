import { IconName } from '@fortawesome/fontawesome-svg-core'

export interface Coordinates {
  x: number
  y: number
}

export interface MenuItem {
  label: string
  onClick: () => void
  icon?: IconName
  variant?: 'success' | 'danger' | 'warning' | 'info' | 'light' | 'dark'
  children?: MenuItem[]
  disabled?: boolean
}

export type IContextMenu = (_x: number, _y: number, _items: MenuItem[]) => void
