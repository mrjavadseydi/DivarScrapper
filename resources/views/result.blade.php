<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container flex justify-center mx-auto">
                    <div class="flex flex-col">
                        <div class="w-full">
                            <div class="border-b border-gray-200 shadow">
                                <table>
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-2 text-xs text-gray-500">
                                            ID
                                        </th>
                                        <th class="px-6 py-2 text-xs text-gray-500">
                                            Title
                                        </th>
                                        <th class="px-6 py-2 text-xs text-gray-500">
                                            City
                                        </th>
                                        <th class="px-6 py-2 text-xs text-gray-500">
                                            Status
                                        </th>
                                        <th class="px-6 py-2 text-xs text-gray-500">
                                            Request Date
                                        </th>

                                        <th class="px-6 py-2 text-xs text-gray-500">
                                            Download
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                    @foreach($scrapes as $scrape)
                                        <tr class="whitespace-nowrap">
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{$scrape->id}}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{$scrape->title}}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-500">{{$scrape->city}}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-500">
                                                    @if($scrape->status==1)
                                                        done
                                                    @else
                                                        in queue
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{$scrape->created_at}}
                                            </td>

                                            <td class="px-6 py-4">
                                                <a href="{{route('download',$scrape->id)}}" class="px-4 py-1 text-sm text-white bg-red-400 rounded">Download</a>
                                            </td>
                                        </tr>
                                    @endforeach



                                    </tbody>
                                </table>
                                {{$scrapes->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
