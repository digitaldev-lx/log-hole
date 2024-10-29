@extends('log-hole::layout')

@section('content')
    <div class="container mx-auto p-4 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4 text-gray-200">Log Dashboard</h1>

        <!-- Search Input -->
        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Search logs..." class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" onkeyup="searchTable()">
        </div>

        <!-- Log Table -->
        <div class="overflow-x-auto">
            <table id="logsTable" class="min-w-full bg-white border border-gray-200">
                <thead>
                <tr class="text-xl bg-gray-50 text-gray-900 uppercase text-xs">
                    <th class="py-2 px-4 border-b text-left">Level</th>
                    <th class="py-2 px-4 border-b text-left">Message</th>
                    <th class="py-2 px-4 border-b text-left">Context</th>
                    <th class="py-2 px-4 border-b text-left">Logged At</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="py-2 px-4 border-b text-gray-200">{{ $log->level }}</td>
                        <td class="py-2 px-4 border-b text-gray-200">{{ $log->message }}</td>
                        <td class="py-2 px-4 border-b text-gray-200">{{ $log->context }}</td>
                        <td class="py-2 px-4 border-b text-gray-200">{{ $log->logged_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>

    <script>
        function searchTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.getElementById("logsTable").getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
                let levelCell = rows[i].getElementsByTagName("td")[0]; // Level column
                let messageCell = rows[i].getElementsByTagName("td")[1]; // Message column
                let found = false;

                // Check if the search input is in the Level or Message column
                if (
                    (levelCell && levelCell.innerHTML.toLowerCase().includes(input)) ||
                    (messageCell && messageCell.innerHTML.toLowerCase().includes(input))
                ) {
                    found = true;
                }

                // Show or hide the row based on the search result
                rows[i].style.display = found ? "" : "none";
            }
        }

    </script>
@endsection
