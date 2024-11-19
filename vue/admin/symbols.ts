import { InjectionKey } from 'vue'
import { AxiosStatic } from 'axios'

export const AxiosKey: InjectionKey<AxiosStatic> = Symbol('axios')
