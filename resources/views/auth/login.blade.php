<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#F5F7FA] bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:24px_24px] p-4 sm:p-6 lg:p-8 font-sans antialiased">
        
        <div class="max-w-4xl w-full flex rounded-2xl shadow-[0_20px_50px_rgba(11,46,89,0.15)] overflow-hidden bg-white border border-gray-100/80 transition-all duration-300">
            
            <div class="hidden md:flex md:w-1/2 flex-col justify-between p-12 relative overflow-hidden bg-[#071D38]">
                <div class="absolute inset-0 bg-gradient-to-br from-[#071D38] via-[#0A274C] to-[#123F75]"></div>
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-20 -right-10 w-60 h-60 bg-[#2E5E96]/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col items-center text-center my-auto">
                    <div class="bg-white/90 backdrop-blur-md p-4.5 rounded-2xl shadow-[0_10px_25px_rgba(0,0,0,0.1)] mb-8 border border-white/40 transform hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('images/logoJLO.png') }}" alt="Logo Municipalidad JLO" class="h-24 w-auto object-contain">
                    </div>
                    <h1 class="text-2xl font-extrabold text-white mb-2 tracking-wide uppercase text-shadow-sm">
                        Sistema Administrativo
                    </h1>
                    <div class="w-16 h-1 bg-gradient-to-r from-[#2E5E96] to-white/50 rounded-full mb-4"></div>
                    <p class="text-[#d0e1f7] text-base font-medium leading-relaxed max-w-xs">
                        Municipalidad Distrital de <br/>
                        <span class="text-white font-bold text-lg">José Leonardo Ortiz</span>
                    </p>
                </div>
                
                <div class="relative z-10 text-xs text-white/60 font-medium tracking-wide text-center uppercase">
                    &copy; {{ date('Y') }}
                </div>
            </div>

            <div class="w-full md:w-1/2 p-8 sm:p-12 lg:p-14 flex flex-col justify-center bg-white">
                
                <div class="mb-8 md:hidden flex flex-col items-center">
                    <div class="bg-[#F5F7FA] p-3 rounded-xl border border-gray-100 shadow-sm mb-3">
                        <img src="{{ asset('images/logoJLO.png') }}" alt="Logo Municipalidad JLO" class="h-14 object-contain">
                    </div>
                    <span class="text-xs font-bold text-[#071D38] uppercase tracking-wider">Muni JLO</span>
                </div>

                <div class="mb-8 text-center md:text-left">
                    <h2 class="text-3xl font-black text-[#0B2E59] mb-2 tracking-tight text-center">
                        Iniciar Sesión
                    </h2>
                    <p class="text-slate-500 text-sm font-normal">
                        Bienvenido. Ingrese sus credenciales autorizadas.
                    </p>
                </div>

                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-emerald-700 bg-emerald-50 p-3.5 rounded-xl border border-emerald-100 shadow-sm flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#0B2E59] mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-[#123F75] transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                                class="pl-11 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-4 focus:ring-[#123F75]/10 focus:border-[#123F75] sm:text-sm transition-all duration-200 ease-in-out py-3.5 shadow-inner">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#071D38]">
                                Contraseña
                            </label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-[#123F75] transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="pl-11 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-4 focus:ring-[#123F75]/10 focus:border-[#123F75] sm:text-sm transition-all duration-200 ease-in-out py-3.5 shadow-inner">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="flex items-center cursor-pointer group select-none">
                            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-[#123F75] shadow-sm focus:ring-[#123F75] focus:ring-offset-0 h-4 w-4 transition-colors cursor-pointer">
                            <span class="ml-2 text-sm text-slate-600 group-hover:text-slate-900 font-medium transition-colors">Recordar sesión</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-bold text-[#123F75] hover:text-[#071D38] transition-colors duration-200 decoration-2 hover:underline underline-offset-4" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-[0_4px_12px_rgba(11,46,89,0.2)] text-sm font-bold text-white bg-gradient-to-r from-[#071D38] to-[#123F75] hover:from-[#0a274c] hover:to-[#1a5196] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#123F75] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 tracking-wide uppercase">
                            Ingresar al Sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>