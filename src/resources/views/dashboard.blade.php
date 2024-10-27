@extends('log-hole::layout')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Log Dashboard</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b text-left text-gray-600">Level</th>
                    <th class="py-2 px-4 border-b text-left text-gray-600">Message</th>
                    <th class="py-2 px-4 border-b text-left text-gray-600">Context</th>
                    <th class="py-2 px-4 border-b text-left text-gray-600">Logged At</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $log->level }}</td>
                        <td class="py-2 px-4 border-b">{{ $log->message }}</td>
                        <td class="py-2 px-4 border-b">{{ $log->context }}</td>
                        <td class="py-2 px-4 border-b">{{ $log->logged_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $logs->links() }} <!-- Links de paginação -->
    </div>
@endsection
