<x-guest>
    <div class="mb-6 text-center">
        <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-lock text-yellow-600 text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Acceso Restringido</h2>
        <p class="text-sm text-gray-600">
            Se requiere contraseña maestra para 
            @if(strpos($redirect, 'register') !== false)
                crear usuarios
            @elseif(strpos($redirect, 'forgot-password') !== false)
                recuperar contraseña
            @else
                acceder a esta sección
            @endif
        </p>
    </div>

    <form method="POST" action="{{ route('master-password.verify') }}">
        @csrf
        <input type="hidden" name="redirect" value="{{ $redirect }}">

        <!-- Master Password -->
        <div>
            <x-input-label for="master_password" value="Contraseña Maestra *" />
            <div class="relative mt-1">
                <x-text-input 
                    id="master_password" 
                    class="block w-full pr-10" 
                    type="password" 
                    name="master_password" 
                    required 
                    autofocus 
                    placeholder="Ingrese la contraseña maestra"
                />
                <button 
                    type="button" 
                    onclick="togglePassword()" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('master_password')" class="mt-2" />
        </div>

        <!-- Info adicional -->
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                <p class="text-xs text-blue-700">
                    Solo el administrador del sistema posee la contraseña maestra. Si no la conoces, contacta al administrador.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Volver al inicio
            </a>

            <x-primary-button>
                <i class="fas fa-check mr-2"></i>
                Verificar
            </x-primary-button>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('master_password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</x-guest>