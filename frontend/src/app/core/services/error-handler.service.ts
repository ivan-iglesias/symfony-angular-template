import { HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class ErrorHandlerService {
  getErrorMessage(error: HttpErrorResponse): string {
    // 1. Errores de Conexión / Timeout (Status 0 o 408)
    if (error.status === 0 || error.status === 408) {
      return 'No se ha podido conectar con el servidor. Revisa tu conexión a internet.';
    }

    // 2. Errores con código de negocio
    const apiCode = error.error?.code;

    const messages: Record<string, string> = {
      INVALID_CREDENTIALS: 'Credenciales incorrectas.',
      ACCOUNT_DISABLED: 'Tu cuenta de Acme ha sido desactivada. Contacta con soporte.',
      ESTABLISHMENT_LOCKED: 'Este establecimiento está bloqueado. Sube de plan para acceder.',
      INSUFFICIENT_STOCK: 'No hay stock suficiente para realizar esta operación.',
      PLAN_LIMIT_REACHED: 'Has alcanzado el límite de tu plan. ¿Quieres subir de nivel?',
    };

    if (apiCode && messages[apiCode]) {
      return messages[apiCode];
    }

    // 3. Errores por bloques de Status HTTP
    if (error.status === 401) return 'Tu sesión ha caducado. Por favor, inicia sesión de nuevo.';
    if (error.status === 403) return 'No tienes permiso para realizar esta acción.';
    if (error.status >= 500) return 'El servidor está experimentando problemas. Inténtalo más tarde.';

    // 4. Fallback por defecto
    return error.error?.message || 'Ha ocurrido un error inesperado.';
  }
}
