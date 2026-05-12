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

        {{-- Timer --}}
        @if($this->timeLimitSeconds)
            <div
                x-data="{
                    remaining: {{ $this->timeRemainingSeconds }},
                    interval: null,
                    get minutes() { return Math.floor(this.remaining / 60) },
                    get seconds() { return this.remaining % 60 },
                    get isUrgent() { return this.remaining <= 60 },
                    start() {
                        this.interval = setInterval(() => {
                            if (this.remaining > 0) {
                                this.remaining--;
                            } else {
                                clearInterval(this.interval);
                                $dispatch('timer-expired');
                                $wire.dispatch('timer-expired');
                            }
                        }, 1000);
                    }
                }"
                x-init="start()"
                class="p-4 rounded-lg flex items-center gap-3"
                :style="isUrgent ? 'background-color:#7f1d1d;' : 'background-color:#1f2937;'"
            >
                <span class="text-2xl">⏱️</span>
                <div>
                    <p class="text-white font-bold text-lg">
                        <span x-text="String(minutes).padStart(2,'0')">00</span>:<span x-text="String(seconds).padStart(2,'0')">00</span>
                    </p>
                    <p :style="isUrgent ? 'color:#fca5a5;' : 'color:#9ca3af;'" class="text-sm">
                        <span x-show="!isUrgent">Time remaining</span>
                        <span x-show="isUrgent">⚠️ Less than 1 minute!</span>
                    </p>
                </div>
            </div>
        @endif

        {{-- Time expired message --}}
        @if($this->timeExpired)
            <div class="p-4 rounded-lg bg-red-900 text-white font-bold text-center">
                ⏰ Time is up! Your answers have been submitted automatically.
            </div>
        @endif

        {{-- Level awaiting approval --}}
        @if(in_array($this->application?->level_status, ['awaiting_approval']))
            <div class="p-6 rounded-lg bg-yellow-900 text-yellow-100 text-center">
                <p class="text-xl font-bold mb-2">⏳ Awaiting Admin Approval</p>
                <p>Your level {{ $this->currentLevel }} has been submitted. Please wait for the admin to review and approve before proceeding to the next level.</p>
            </div>
        @else

        {{-- Questions --}}
        @foreach($this->getQuestions() as $question)
            <div class="p-6 rounded-lg shadow" style="background-color: #1f2937;">
                <p class="text-white font-semibold mb-4">
                    {{ $loop->iteration }}. {{ $question->question_fr }}
                    @if($question->max_note > 0)
                        <span style="color:#a78bfa; font-size:0.85em;">({{ $question->max_note }} pts)</span>
                    @endif
                </p>

                @if($question->component === 'radio' && $question->possible_answers)
                    <div class="space-y-2">
                        @foreach($question->possible_answers as $answer)
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" wire:model="answers.{{ $question->id }}" value="{{ $answer }}" @if($this->timeExpired) disabled @endif />
                                <span style="color: #d1d5db;">{{ $answer }}</span>
                            </label>
                        @endforeach
                    </div>

                @elseif($question->component === 'text')
                    <textarea wire:model="answers.{{ $question->id }}" rows="3" placeholder="Your answer..."
                        @if($this->timeExpired) disabled @endif
                        style="width:100%; padding:12px; background-color:#374151; color:white; border:1px solid #4b5563; border-radius:8px;"></textarea>

                @elseif($question->component === 'date')
                    <input type="date" wire:model="answers.{{ $question->id }}"
                        @if($this->timeExpired) disabled @endif
                        style="padding:12px; background-color:#374151; color:white; border:1px solid #4b5563; border-radius:8px;" />

                @elseif($question->component === 'photo')
                    <input type="file" wire:model="answers.{{ $question->id }}" accept="image/*"
                        @if($this->timeExpired) disabled @endif
                        style="color:white;" />

                @elseif($question->component === 'list')
                    <select wire:model="answers.{{ $question->id }}"
                        @if($this->timeExpired) disabled @endif
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
        @if(!$this->timeExpired)
        <div class="flex gap-4 pb-6">
            <button wire:click="saveAnswers"
                style="padding:12px 24px; background-color:#4b5563; color:white; border-radius:8px; font-weight:bold;">
                💾 {{ __('Save') }}
            </button>
            <button wire:click="submitLevel"
                wire:confirm="Are you sure you want to submit level {{ $this->currentLevel }}? You cannot go back."
                style="padding:12px 24px; background-color:#7c3aed; color:white; border-radius:8px; font-weight:bold;">
                ✅ {{ __('Submit Level') }} {{ $this->currentLevel }}
            </button>
        </div>
        @endif

        @endif {{-- end awaiting_approval check --}}

        {{-- Score Section --}}
        @if($this->levelScore > 0 || $this->showScore)
            <div class="p-6 rounded-lg" style="background-color:#1e3a5f;">
                <h3 class="text-white font-bold text-lg mb-3">📊 Your Score — Level {{ $this->currentLevel }}</h3>
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-purple-300">{{ $this->levelScore }}</p>
                        <p style="color:#9ca3af;">/ {{ $this->levelMaxScore }} pts</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-300">{{ $this->levelScorePercent }}%</p>
                        <p style="color:#9ca3af;">Score</p>
                    </div>
                </div>

                {{-- Score visibility toggle --}}
                <div class="mt-4 flex items-center gap-3">
                    @php $candidate = auth()->user()->candidate; @endphp
                    <button wire:click="toggleScoreVisibility"
                        style="padding:8px 18px; border-radius:8px; font-weight:bold; background-color: {{ $candidate?->score_visibility ? '#065f46' : '#374151' }}; color:white;">
                        @if($candidate?->score_visibility)
                            👁️ Score is Public — Click to make Private
                        @else
                            🔒 Score is Private — Click to make Public
                        @endif
                    </button>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
