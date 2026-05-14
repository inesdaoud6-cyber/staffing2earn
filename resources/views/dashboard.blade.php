<x-filament-panels::page>
    @vite('resources/css/dashboard.css')
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Welcome Card -->
        <div class="col-span-1 md:col-span-3 p-6 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow text-white">
            <h1 class="text-3xl font-bold mb-2">👋 {{ __('Welcome') }}, {{ $userName }}!</h1>
            <p class="text-lg opacity-90">
                {{ __('This is your personal recruitment space. Start your journey with us today.') }}
            </p>
        </div>

        <!-- Total Applications -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="dashboard-card-title">{{ __('Total Applications') }}</p>
                    <p class="dashboard-card-value" style="color: #667eea;">{{ $totalApplications }}</p>
                </div>
                <div class="dashboard-card-emoji">📋</div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="dashboard-card-title">{{ __('Pending') }}</p>
                    <p class="dashboard-card-value" style="color: #f39c12;">{{ $pendingApplications }}</p>
                </div>
                <div class="dashboard-card-emoji">⏳</div>
            </div>
        </div>

        <!-- Completed Applications -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="dashboard-card-title">{{ __('Completed') }}</p>
                    <p class="dashboard-card-value" style="color: #00b894;">{{ $completedApplications }}</p>
                </div>
                <div class="dashboard-card-emoji">✅</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <a href="/candidate/application-choice" class="btn btn-primary" style="text-align: center; display: block;">
            🚀 {{ __('Start New Application') }}
        </a>
        <a href="/candidate/take-test" class="btn btn-primary" style="text-align: center; display: block;">
            📝 {{ __('Take the Test') }}
        </a>
    </div>

    <!-- Recent Applications -->
    @if($recentApplications->count() > 0)
        <div class="dashboard-card">
            <h2 class="text-xl font-bold mb-4 text-gray-800">📌 {{ __('Recent Applications') }}</h2>
            <div class="overflow-x-auto">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __('Application') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Level') }}</th>
                            <th>{{ __('Score') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentApplications as $app)
                            <tr>
                                <td>
                                    {{ $app->offre ? $app->offre->title ?? 'Free Application' : 'Free Application' }}
                                </td>
                                <td>
                                    @if($app->status === 'pending')
                                        <span class="status-pending">{{ __('Pending') }}</span>
                                    @elseif($app->status === 'in_progress')
                                        <span class="status-in_progress">{{ __('In Progress') }}</span>
                                    @elseif($app->status === 'validated')
                                        <span class="status-validated">{{ __('Validated') }}</span>
                                    @else
                                        <span class="status-rejected">{{ __('Rejected') }}</span>
                                    @endif
                                </td>
                                <td>Level {{ $app->current_level }}</td>
                                <td style="font-weight: 600; color: #667eea;">{{ $app->main_score }}/100</td>
                                <td style="font-size: 0.875rem; color: #999;">
                                    {{ $app->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="dashboard-card" style="text-align: center; padding: 2rem;">
            <p style="color: #999; font-size: 1.1rem;">{{ __('No applications yet. Start your first application') }}!</p>
        </div>
    @endif
</x-filament-panels::page>