@extends('layouts.app')

@section('title', 'Auditoría de Inicios de Sesión')
@section('header_title', 'Auditoría de Inicios de Sesión')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Registros de Acceso</h2>
            <p class="text-sm text-slate-500 mt-1">Historial de inicios de sesión exitosos en el sistema.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dirección IP</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($audits as $audit)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">{{ $audit->user->name ?? 'Usuario Eliminado' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $audit->ip_address }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $audit->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-slate-400 text-sm">No hay registros de auditoría disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($audits->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
