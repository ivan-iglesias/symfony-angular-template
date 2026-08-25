import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { AuthService } from '../../core/services/auth-service';

@Component({
  selector: 'app-dashboard',
  template: `
    <div class="dashboard">
      <header>
        <h1>Acme</h1>
        <span>Operario: {{ username() }}</span>
      </header>
      <main>
        <p>Bienvenido al inventario. Aquí listaremos los productos pronto.</p>
      </main>
    </div>
  `,
  styleUrl: 'dashboard.component.scss',
  imports: [],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DashboardComponent {
  private readonly authService = inject(AuthService);

  protected readonly username = this.authService.username;
}
