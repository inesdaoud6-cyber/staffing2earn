<x-filament-panels::page>
    @vite('resources/css/dashboard.css')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Card: My Profile -->
        <div class="p-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow text-white">
            <h3 class="text-lg font-bold mb-2">👤 {{ __('My Profile') }}</h3>
            <p class="text-sm mb-4">{{ $candidateName }}</p>
            <p class="text-xs opacity-75">{{__('Email')}}: {{ auth()->user()->email }}</p>
            <a href="#"
                class="mt-4 inline-block px-4 py-2 bg-white text-blue-600 rounded font-semibold hover:bg-gray-100">
                {{__('View Profile')}}
            </a>
        </div>

        <!-- Card: My Applications -->
        <div class="p-6 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow text-white">
            <h3 class="text-lg font-bold mb-2">📝 {{ __('My Applications') }}</h3>
            <p class="text-3xl font-bold">{{ $totalApplications }}</p>
            <p class="text-xs opacity-75 mt-2">{{__('applications submitted')}}</p>
            <a href="/candidate/my-applications"
                class="mt-4 inline-block px-4 py-2 bg-white text-purple-600 rounded font-semibold hover:bg-gray-100">
                {{__('View All')}}
            </a>
        </div>

        <!-- Card: My Test Results -->
        <div class="p-6 bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow text-white">
            <h3 class="text-lg font-bold mb-2">📊 {{ __('Test Results') }}</h3>
            <p class="text-3xl font-bold">{{ $averageScore }}%</p>
            <p class="text-xs opacity-75 mt-2">{{__('average score')}}</p>
            <a href="/candidate/test-results"
                class="mt-4 inline-block px-4 py-2 bg-white text-green-600 rounded font-semibold hover:bg-gray-100">
                {{__('View Results')}}
            </a>
        </div>
    </div>

    <!-- Applications List -->
    <div class="mt-8 dashboard-card">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">📋 {{__('All Applications')}}</h2>

        @if($applications->count() > 0)
            <div class="overflow-x-auto">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __('Position') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Level') }}</th>
                            <th>{{ __('Score') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $app)
                            <tr>
                                <td class="font-medium">
                                    {{ $app->offre ? $app->offre->title ?? 'Free Application' : 'Free Application' }}
                                </td>
                                <td>
                                    @if($app->status === 'pending')
                                        <span class="status-pending">{{__('Pending')}}</span>
                                    @elseif($app->status === 'in_progress')
                                        <span class="status-in_progress">{{__('In Progress')}}</span>
                                    @elseif($app->status === 'validated')
                                        <span class="status-validated">{{__('Validated')}}</span>
                                    @else
                                        <span class="status-rejected">{{__('Rejected')}}</span>
                                    @endif
                                </td>
                                <td>Level {{ $app->current_level }}</td>
                                <td style="font-weight: 600; color: #667eea;">{{ $app->main_score }}/100</td>
                                <td style="font-size: 0.875rem; color: #999;">
                                    {{ $app->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <a href="/candidate/application/{{ $app->id }}/view"
                                        class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{__('View')}} →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 2rem; text-align: center; background: #f5f5f5; border-radius: 8px;">
                <p style="color: #999; font-size: 1.1rem;">{{__('You haven\'t started any applications yet.')}}</p>
                <a href="/candidate/application-choice"
                    class="mt-4 inline-block px-6 py-2 bg-purple-500 text-white rounded font-semibold hover:bg-purple-600">
                    {{__('Start Your First Application')}}
                </a>
            </div>
        @endif
    </div>
</x-filament-panels::page>