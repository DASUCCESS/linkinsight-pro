@extends('installer.layout')

@section('step_number', '2')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Folder Permissions</h2>

    <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-slate-400 text-left border-b border-slate-700">
                <th class="pb-2">Path</th>
                <th class="pb-2 w-32">Writable</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
            @foreach ($results['items'] as $item)
                <tr>
                    <td class="py-2">{{ $item['path'] }}</td>
                    <td class="py-2">
                        @if ($item['writable'])
                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/40">
                                OK
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400 border border-red-500/40">
                                Not writable
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <form action="{{ route('installer.permissions.next') }}" method="POST" class="mt-4 flex justify-between">
        @csrf
        <a href="{{ route('installer.requirements') }}"
           class="px-4 py-2 rounded-full text-sm font-semibold shadow cursor-pointer
                  transition transform duration-200 hover:scale-105
                  bg-slate-800 border border-slate-600">
            Back
        </a>

        <button type="submit"
                class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                       transition transform duration-200 hover:scale-105
                       bg-gradient-to-r from-indigo-500 to-sky-500">
            Continue
        </button>
    </form>
@endsection
