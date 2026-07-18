@extends('layouts.admin')

@section('title', 'Reservaciones de Clase')

@section('content')
<div class="space-y-8 animate-fade-in">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('clases.index') }}" class="text-xs text-lime-400 font-bold hover:underline flex items-center gap-1.5 mb-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Volver a Clases & Sesiones
            </a>
            <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">{{ $schedule->gymClass->name }}</h1>
            <p class="text-slate-400 text-xs mt-1">
                Instructor: {{ $schedule->trainer->user->profile->first_name ?? 'Coach' }} {{ $schedule->trainer->user->profile->last_name ?? '' }} | 
                Fecha: {{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m/Y') }} |
                Horario: {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
            </p>
        </div>
    </div>

    <!-- Alerts -->
    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-4 rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-4 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Booking and Info -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Inscribe Manual Card -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="user-plus" class="text-lime-400 w-4 h-4"></i>
                    Inscribir Manual
                </h3>
                
                @php
                    $activeGymId = session('superadmin_gym_id', auth()->user()->gym_id);
                @endphp

                @if($activeGymId === 'all')
                    <div class="mt-4 p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs rounded-xl flex items-start gap-2.5">
                        <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <p class="font-semibold">
                            Estás en la vista de todas las sucursales. Selecciona una sucursal específica en el menú superior para poder registrar inscripciones.
                        </p>
                    </div>
                @else
                    <form action="{{ route('clases.book_client') }}" method="POST" class="mt-4 space-y-4 text-xs font-semibold">
                        @csrf
                        <input type="hidden" name="class_schedule_id" value="{{ $schedule->id }}">
                        
                        <div>
                            <label for="user_id" class="block text-slate-400 uppercase tracking-wider mb-1.5">Seleccionar Atleta</label>
                            <select name="user_id" id="user_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-lime-500/50 cursor-pointer">
                                <option value="" disabled selected>Busca o selecciona un atleta...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">
                                        {{ $client->profile->first_name ?? 'Atleta' }} {{ $client->profile->last_name ?? '' }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-400 hover:to-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-lime-500/10 hover:shadow-lime-500/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3px]"></i>
                            Inscribir Atleta
                        </button>
                    </form>
                @endif
            </div>

            <!-- Capacity Summary Card -->
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-5 shadow-lg space-y-4">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i data-lucide="info" class="text-lime-400 w-4 h-4"></i>
                    Resumen de Capacidad
                </h3>
                <div class="space-y-2.5 text-xs font-semibold text-slate-300">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Capacidad Máxima:</span>
                        <span>{{ $schedule->gymClass->capacity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Cupos Confirmados:</span>
                        <span class="text-emerald-400">{{ $bookings->where('status', 'booked')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Lista de Espera:</span>
                        <span class="text-amber-400">{{ $bookings->where('status', 'waitlisted')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Asistencias Confirmadas:</span>
                        <span class="text-blue-400">{{ $bookings->where('status', 'attended')->count() }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Panel: Bookings Table -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-900/40 border border-slate-800/80 rounded-2xl p-6 shadow-lg">
                <h3 class="font-extrabold text-sm text-slate-100 uppercase tracking-widest border-b border-slate-800 pb-4 mb-4 flex items-center gap-2">
                    <i data-lucide="users-2" class="text-lime-400 w-4 h-4"></i>
                    Lista de Reservas
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 text-xs uppercase font-bold">
                                <th class="py-3 px-4">Atleta</th>
                                <th class="py-3 px-4">Inscripción</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                                <th class="py-3 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-sm">
                            @forelse($bookings as $booking)
                                <tr class="hover:bg-slate-800/10 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $booking->user->profile->profile_photo ?? 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=150&auto=format&fit=crop' }}" class="w-8 h-8 rounded-full object-cover border border-slate-800 shrink-0">
                                            <div class="overflow-hidden">
                                                <span class="block font-bold text-slate-200 truncate">{{ $booking->user->profile->first_name ?? 'Atleta' }} {{ $booking->user->profile->last_name ?? '' }}</span>
                                                <span class="block text-[10px] text-slate-500 truncate">{{ $booking->user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="block text-[11px] text-slate-400 font-semibold">{{ \Carbon\Carbon::parse($booking->booked_at)->format('H:i') }}</span>
                                        <span class="block text-[9px] text-slate-550">{{ \Carbon\Carbon::parse($booking->booked_at)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @php
                                            $statusMap = [
                                                'booked' => 'Confirmado',
                                                'waitlisted' => 'En Espera',
                                                'attended' => 'Asistió',
                                                'cancelled' => 'Cancelado',
                                                'no_show' => 'Falta'
                                            ];
                                            $statusBadge = [
                                                'booked' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                                'waitlisted' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'attended' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                                'cancelled' => 'bg-slate-800/40 text-slate-500 border-slate-800',
                                                'no_show' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-0.5 text-[9px] font-bold border rounded-md {{ $statusBadge[$booking->status] ?? 'bg-slate-950 text-slate-550 border-slate-850' }}">
                                            {{ $statusMap[$booking->status] ?? $booking->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if(in_array($booking->status, ['booked', 'waitlisted']))
                                            <div class="flex justify-end gap-1.5">
                                                <!-- Mark Attended Form -->
                                                <form action="{{ route('clases.update_booking_status', $booking->id) }}" method="POST" class="inline m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="attended">
                                                    <button type="submit" class="px-2 py-1 bg-lime-500/10 hover:bg-lime-500/20 text-lime-400 border border-lime-500/20 hover:border-lime-500/40 text-[10px] font-bold rounded-lg transition-all flex items-center gap-1">
                                                        <i data-lucide="check" class="w-3 h-3 stroke-[2.5px]"></i>
                                                        Marcar Asist.
                                                    </button>
                                                </form>

                                                <!-- Mark Cancelled Form -->
                                                <form action="{{ route('clases.update_booking_status', $booking->id) }}" method="POST" class="inline m-0">
                                                    @csrf
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 hover:border-rose-500/40 text-[10px] font-bold rounded-lg transition-all flex items-center gap-1">
                                                        <i data-lucide="x" class="w-3 h-3 stroke-[2.5px]"></i>
                                                        Cancelar
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-550 italic font-semibold">Completado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-16 text-center text-slate-500">
                                        <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-700 mb-3"></i>
                                        <p class="font-bold">No hay reservaciones registradas para esta sesión.</p>
                                        <p class="text-xs text-slate-550 mt-1">Inscribe a tu primer atleta usando el panel de la izquierda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
