import {
  ApplicationConfig,
  importProvidersFrom,
  provideBrowserGlobalErrorListeners,
} from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { errorInterceptor } from './core/interceptors/error-interceptor';
import { mockInterceptor } from './core/interceptors/mock-interceptor';
import { environment } from '../environments/environment';
import { authInterceptor } from './core/interceptors/auth-interceptor';
import { resilienceInterceptor } from './core/interceptors/resilience-interceptor';
import { cacheInterceptor } from './core/interceptors/cache-interceptor-interceptor';

import { providePrimeNG } from 'primeng/config';
import { definePreset } from '@primeng/themes';
import Aura from '@primeng/themes/aura';

import {
  LucideAngularModule,
  Warehouse,
  Package,
  Users,
  Settings,
  LogOut,
  Bell,
  CircleAlert,
} from 'lucide-angular';

const isDev = !environment.production;

// 1. Define tu paleta de colores de marca
const customPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '{blue.50}',
      100: '{blue.100}',
      200: '{blue.200}',
      300: '{blue.300}',
      400: '{blue.400}',
      500: '{blue.500}', // Este será tu azul principal
      600: '{blue.600}',
      700: '{blue.700}',
      800: '{blue.800}',
      900: '{blue.900}',
      950: '{blue.950}',
    },
  },
});

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideRouter(routes),
    provideHttpClient(
      withInterceptors([
        cacheInterceptor,
        authInterceptor,
        errorInterceptor,
        resilienceInterceptor,
        ...(isDev ? [mockInterceptor] : []),
      ]),
    ),
    providePrimeNG({
      theme: {
        preset: customPreset,
        options: {
          darkModeSelector: '.my-app-dark',
          cssLayer: false,
        },
      },
    }),
    importProvidersFrom(
      LucideAngularModule.pick({
        Warehouse,
        Package,
        Users,
        Settings,
        LogOut,
        Bell,
        CircleAlert,
      }),
    ),
  ],
};
