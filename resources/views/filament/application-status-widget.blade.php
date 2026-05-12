<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Statut des candidatures</x-slot>
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach([
                ['label'=>'Validés',  'value'=>$validated,  'color'=>'#22c55e'],
                ['label'=>'En cours', 'value'=>$inProgress, 'color'=>'#3b82f6'],
                ['label'=>'Attente',  'value'=>$pending,    'color'=>'#f59e0b'],
                ['label'=>'Rejetés',  'value'=>$rejected,   'color'=>'#ef4444'],
            ] as $row)
            <div style="display:flex;align-items:center;gap:10px">
                <span style="width:58px;font-size:0.72rem;color:#9ca3af;flex-shrink:0">{{ $row['label'] }}</span>
                <div style="flex:1;height:7px;background:#f3f4f6;border-radius:4px;overflow:hidden">
                    <div style="width:{{ $total > 0 ? round(($row['value'] / $total) * 100) : 0 }}%;height:100%;background:{{ $row['color'] }};border-radius:4px;min-width:{{ $row['value'] > 0 ? '6px' : '0' }}"></div>
                </div>
                <span style="width:18px;text-align:right;font-size:0.75rem;font-weight:600;color:#374151">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
        @if($offres->count() > 0)
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6">
            <p style="font-size:0.82rem;font-weight:500;color:#111827;margin-bottom:12px">Offres actives</p>
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach($offres as $offre)
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:#EEEDFE;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0">💼</div>
                    <div style="flex:1;min-width:0">
                        <p style="font-size:0.78rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $offre->title }}</p>
                        <p style="font-size:0.7rem;color:#9ca3af">{{ $offre->contract_type ?? 'CDI' }} · {{ $offre->location ?? 'Tunis' }}</p>
                    </div>
                    <span style="font-size:0.75rem;color:#6b7280;font-weight:500">{{ $offre->application_progresses_count }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
