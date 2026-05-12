<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="p-4 rounded-lg" style="background-color: #581c87;">
            <h2 class="text-xl font-bold text-white">
                🎯 Level {{ $this->currentLevel }} — {{ __('Questions') }}
            </h2>
            <p style="color: #d8b4fe;">
                {{ __('Answer all questions then click "Submit"') }}
            </p>
        </div>

        @foreach($this->getQuestions() as $question)
            <div class="p-6 rounded-lg shadow" style="background-color: #1f2937;">
                <p class="text-white font-semibold mb-4">
                    {{ $loop->iteration }}. {{ $question->question_fr }}
                </p>

                {{-- Radio --}}
                @if($question->component === 'radio' && $question->possible_answers)
                    <div class="space-y-2">
                        @foreach($question->possible_answers as $answer)
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="answers.{{ $question->id }}" value="{{ $answer }}" />
                                <span style="color: #d1d5db;">{{ $answer }}</span>
                            </label>
                        @endforeach
                    </div>

                @elseif($question->component === 'text')
                    <textarea wire:model="answers.{{ $question->id }}" rows="3" placeholder="Your answer..."
                        style="width:100%; padding:12px; background-color:#374151; color:white; border:1px solid #4b5563; border-radius:8px;"></textarea>

                @elseif($question->component === 'date')
                    <input type="date" wire:model="answers.{{ $question->id }}"
                        style="padding:12px; background-color:#374151; color:white; border:1px solid #4b5563; border-radius:8px;" />

                @elseif($question->component === 'photo')
                    <input type="file" wire:model="answers.{{ $question->id }}" accept="image/*" style="color:white;" />

                @elseif($question->component === 'list')
                    <select wire:model="answers.{{ $question->id }}"
                        style="width:100%; padding:12px; background-color:#374151; color:white; border:1px solid #4b5563; border-radius:8px;">
                        <option value="">-- Choose --</option>
                        @if($question->possible_answers)
                            @foreach($question->possible_answers as $answer)
                                <option value="{{ $answer }}" style="background-color:#374151; color:white;">
                                    {{ $answer }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                @endif
            </div>
        @endforeach

        {{-- Buttons --}}
        <div class="flex gap-4 pb-6">
            <button wire:click="saveAnswers"
                style="padding:12px 24px; background-color:#4b5563; color:white; border-radius:8px; font-weight:bold;">
                💾 {{ __('Save') }}
            </button>
            <button wire:click="submitLevel"
                style="padding:12px 24px; background-color:#7c3aed; color:white; border-radius:8px; font-weight:bold;">
                ✅ {{ __('Submit Level') }} {{ $this->currentLevel }}
            </button>
        </div>

    </div>
</x-filament-panels::page>