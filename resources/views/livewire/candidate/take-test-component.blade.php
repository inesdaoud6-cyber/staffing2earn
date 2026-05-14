<div>
    {{-- Auto-save toutes les 30s en mode test --}}
    @if($pageStatus === 'take_test')
    <div wire:poll.30000ms="autoSave" style="display:none;"></div>
    @endif

    {{-- Flash message --}}
    @if($flashMessage)
    <div x-data="{ show: true }"
         x-init="setTimeout(() => { show = false; $wire.clearFlash(); }, 3000)"
         x-show="show"
         x-transition
         style="padding:0.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-weight:600;font-size:0.9rem;
         {{ $flashType === 'success' ? 'background:#f0fdf4;color:#16a34a;border:1px solid #86efac;' : 'background:#fffbeb;color:#92400e;border:1px solid #fcd34d;' }}">
        {{ $flashMessage }}
    </div>
    @endif

    @if($pageStatus === 'no_application')
        <div style="text-align:center;padding:3rem;background:#f8f9ff;border-radius:14px;border:1px solid #ede9fe;">
            <div style="font-size:3rem;margin-bottom:1rem;">📋</div>
            <h2 style="font-size:1.25rem;font-weight:800;color:#1a1a8c;margin-bottom:0.5rem;">Aucune candidature active</h2>
            <p style="color:#6b7280;margin-bottom:1rem;">Vous n'avez pas encore de candidature avec un test associé.</p>
            <a href="/candidate/choix-candidature"
               style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.7rem 1.5rem;border-radius:10px;font-weight:700;color:white;background:linear-gradient(135deg,#1a1a8c,#3730a3);text-decoration:none;">
                🚀 {{ __('New application') }}
            </a>
        </div>

    @elseif($pageStatus === 'waiting_admin')
        <div style="text-align:center;padding:3rem;background:#fffbeb;border:1px solid #fcd34d;border-radius:14px;">
            <div style="font-size:3rem;margin-bottom:1rem;">⏳</div>
            <h2 style="font-size:1.25rem;font-weight:800;color:#92400e;margin-bottom:0.5rem;">En attente de validation</h2>
            <p style="color:#78350f;">L'administrateur doit valider votre candidature avant que vous puissiez passer le test.</p>
        </div>

    @elseif($pageStatus === 'waiting_level_validation')
        <div style="text-align:center;padding:3rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;">
            <div style="font-size:3rem;margin-bottom:1rem;">🎯</div>
            <h2 style="font-size:1.25rem;font-weight:800;color:#1e40af;margin-bottom:0.5rem;">Niveau {{ $currentLevel }} soumis</h2>
            <p style="color:#1d4ed8;">Vos réponses ont été enregistrées. En attente de validation de l'administrateur.</p>
        </div>

    @elseif($pageStatus === 'all_validated')
        <div style="text-align:center;padding:3rem;background:#f0fdf4;border:1px solid #86efac;border-radius:14px;">
            <div style="font-size:3rem;margin-bottom:1rem;">🏆</div>
            <h2 style="font-size:1.25rem;font-weight:800;color:#065f46;margin-bottom:0.5rem;">Félicitations !</h2>
            <p style="color:#047857;">Votre candidature a été entièrement validée !</p>
        </div>

    @elseif($pageStatus === 'test')
        <div style="background:linear-gradient(135deg,#1a1a8c,#3730a3);border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.6rem;">
                <div>
                    <h2 style="font-size:1.1rem;font-weight:800;color:white;">🎯 Niveau {{ $currentLevel }} — Questions</h2>
                    <p style="color:#c7d2fe;font-size:0.85rem;">Répondez à toutes les questions puis soumettez</p>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.75rem;font-weight:800;color:white;">{{ $answeredCount }}/{{ $totalQuestions }}</div>
                    <div style="font-size:0.72rem;color:#a5b4fc;">réponses</div>
                </div>
            </div>
            <div style="background:rgba(255,255,255,0.2);border-radius:999px;height:6px;overflow:hidden;">
                <div style="background:white;height:100%;border-radius:999px;transition:width 0.3s;width:{{ $totalQuestions > 0 ? round($answeredCount / $totalQuestions * 100) : 0 }}%;"></div>
            </div>
        </div>

        @foreach($questions as $question)
        <div style="background:white;border-radius:14px;padding:1.5rem;margin-bottom:1rem;border:1px solid #ede9fe;box-shadow:0 2px 8px rgba(26,26,140,0.05);">
            <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:1rem;">
                <span style="flex-shrink:0;width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#1a1a8c,#3730a3);color:white;font-size:0.8rem;font-weight:800;display:flex;align-items:center;justify-content:center;">
                    {{ $loop->iteration }}
                </span>
                <p style="font-weight:700;color:#1a1a8c;font-size:1rem;line-height:1.4;">
                    {{ app()->getLocale() === 'ar' && $question->question_ar
                        ? $question->question_ar
                        : (app()->getLocale() === 'en' && $question->question_en
                            ? $question->question_en
                            : $question->question_fr) }}
                    @if($question->obligatory)
                    <span style="color:#dc2626;font-weight:900;">*</span>
                    @endif
                </p>
            </div>

            @if($question->component === 'radio' && $question->possible_answers)
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    @foreach($question->possible_answers as $answer)
                    <label style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;border:1.5px solid {{ isset($answers[$question->id]) && $answers[$question->id] === $answer ? '#1a1a8c' : '#e5e7eb' }};border-radius:10px;cursor:pointer;background:{{ isset($answers[$question->id]) && $answers[$question->id] === $answer ? '#f0f1ff' : 'white' }};transition:all 0.15s;">
                        <input type="radio"
                            wire:model.live="answers.{{ $question->id }}"
                            value="{{ $answer }}"
                            style="accent-color:#1a1a8c;width:16px;height:16px;flex-shrink:0;">
                        <span style="color:#374151;font-size:0.95rem;">{{ $answer }}</span>
                    </label>
                    @endforeach
                </div>

            @elseif($question->component === 'text')
                <textarea wire:model.live.debounce.500ms="answers.{{ $question->id }}"
                    rows="4"
                    placeholder="Votre réponse..."
                    style="width:100%;padding:0.75rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:0.95rem;color:#374151;resize:vertical;outline:none;font-family:inherit;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#1a1a8c'"
                    onblur="this.style.borderColor='#e5e7eb'"></textarea>

            @elseif($question->component === 'date')
                <input type="date"
                    wire:model.live="answers.{{ $question->id }}"
                    style="padding:0.7rem 0.9rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:0.95rem;color:#374151;width:100%;outline:none;box-sizing:border-box;">

            @elseif($question->component === 'list' && $question->possible_answers)
                <select wire:model.live="answers.{{ $question->id }}"
                    style="width:100%;padding:0.7rem 0.9rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:0.95rem;color:#374151;background:white;outline:none;box-sizing:border-box;">
                    <option value="">-- Choisir --</option>
                    @foreach($question->possible_answers as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        @endforeach

        <div style="display:flex;gap:1rem;padding-bottom:1.5rem;">
            <button wire:click="saveAnswers"
                wire:loading.attr="disabled"
                wire:target="saveAnswers"
                style="display:flex;align-items:center;gap:0.5rem;padding:0.8rem 1.5rem;border-radius:10px;border:1.5px solid #e5e7eb;background:white;color:#374151;font-weight:700;cursor:pointer;">
                <span wire:loading.remove wire:target="saveAnswers">💾 {{ __('Save') }}</span>
                <span wire:loading wire:target="saveAnswers">⏳...</span>
            </button>

            <button wire:click="submitLevel"
                wire:loading.attr="disabled"
                wire:target="submitLevel"
                style="display:flex;align-items:center;gap:0.5rem;padding:0.8rem 1.75rem;border-radius:10px;border:none;background:linear-gradient(135deg,#1a1a8c,#3730a3);color:white;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(26,26,140,0.3);">
                <span wire:loading.remove wire:target="submitLevel">✅ Soumettre Niveau {{ $currentLevel }}</span>
                <span wire:loading wire:target="submitLevel">⏳ Envoi...</span>
            </button>
        </div>
    @endif
</div>