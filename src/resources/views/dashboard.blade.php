@extends('log-hole::layout')

@section('content')
    <div class="container mx-auto p-6 bg-gray-900 text-white">
        <h1 class="text-3xl font-semibold mb-6 text-white">Log Dashboard</h1>

        <div class="overflow-x-auto">
            <table class="w-full bg-gray-800 border border-gray-700 rounded-lg">
                <thead>
                <tr class="bg-gray-700">
                    <th class="py-3 px-5 border-b border-gray-600 text-left text-gray-400">Level</th>
                    <th class="py-3 px-5 border-b border-gray-600 text-left text-gray-400">Message</th>
                    <th class="py-3 px-5 border-b border-gray-600 text-left text-gray-400">Context</th>
                    <th class="py-3 px-5 border-b border-gray-600 text-left text-gray-400">Logged At</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-700">
                        <td class="py-3 px-5 border-b border-gray-600 text-gray-300">{{ $log->level }}</td>
                        <td class="py-3 px-5 border-b border-gray-600 text-gray-300">{{ $log->message }}</td>
                        <td class="py-3 px-5 border-b border-gray-600 text-gray-300">{{ $log->context }}</td>
                        <td class="py-3 px-5 border-b border-gray-600 text-gray-300">{{ $log->logged_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $logs->links('pagination::tailwind') }} <!-- Custom pagination with Tailwind styles -->
        </div>
    </div>
@endsection
