<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Apellido1 -->
        <div class="mt-4">
            <x-input-label for="apellido1" :value="__('Primer Apellido')" />
            <x-text-input id="apellido1" class="block mt-1 w-full" type="text" name="apellido1" :value="old('apellido1')" required autocomplete="apellido1" />
            <x-input-error :messages="$errors->get('apellido1')" class="mt-2" />
        </div>

        <!-- Apellido2 -->
        <div class="mt-4">
            <x-input-label for="apellido2" :value="__('Segundo Apellido')" />
            <x-text-input id="apellido2" class="block mt-1 w-full" type="text" name="apellido2" :value="old('apellido2')" autocomplete="apellido2" />
            <x-input-error :messages="$errors->get('apellido2')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Tipo de Documento -->
        <div class="mt-4">
            <x-input-label for="tipo_documento" :value="__('Tipo de Documento')" />
            <select id="tipo_documento" name="tipo_documento" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">Seleccionar tipo de documento</option>
                @foreach($availableDocumentTypes as $type)
                    <option value="{{ $type }}" {{ old('tipo_documento') === $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
        </div>

        <!-- Número de Documento -->
        <div class="mt-4">
            <x-input-label for="numero_documento" :value="__('Número de Documento')" />
            <x-text-input id="numero_documento" class="block mt-1 w-full" type="text" name="numero_documento" :value="old('numero_documento')" required maxlength="20" placeholder="Ingrese su número de documento" />
            <x-input-error :messages="$errors->get('numero_documento')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
