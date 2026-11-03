@if(($examPrepSettings['exam_prep_enabled'] ?? true) && ($examPrepCards ?? collect())->isNotEmpty())
    <section class="section exam-section" id="exam-aid">
        <div class="section-container">
            <div class="section-header reveal-up">
            @if($examPrepSettings['exam_prep_tag'])
                <span class="section-tag">{{ $examPrepSettings['exam_prep_tag'] }}</span>
            @endif
            <h2 class="section-title">
                {{ $examPrepSettings['exam_prep_title'] ?: 'Exam Aid Center' }}
                @if($examPrepSettings['exam_prep_title_highlight'])
                    <span class="text-gradient">{{ $examPrepSettings['exam_prep_title_highlight'] }}</span>
                @endif
            </h2>
            @if($examPrepSettings['exam_prep_subtitle'])
                <p class="section-subtitle">{{ $examPrepSettings['exam_prep_subtitle'] }}</p>
            @endif
            </div>

            <div class="exam-grid reveal-stagger">
                @foreach($examPrepCards as $card)
                    <div class="exam-card {{ $card->is_large ? 'exam-card-large' : '' }}">
                        <div class="exam-card-bg" @unless($card->is_large) style="--ec-color:#004AAD" @endunless></div>
                        <div class="exam-icon"><span class="ui-icon ui-icon-{{ $card->icon }}"></span></div>
                        <h3>{{ $card->title }}</h3>
                        @if($card->description)
                            <p>{{ $card->description }}</p>
                        @endif

                        @if($card->is_large)
                            <div class="exam-stats">
                                <div class="exam-stats">
                                    <div>
                                        <span class="es-num">{{ number_format($examTopicsCount ?? 0) }}+</span>
                                        <span class="es-label">Topics</span>
                                    </div>
                                    <div>
                                        <span class="es-num">{{ number_format($examSubjectsCount ?? 0) }}+</span>
                                        <span class="es-label">Subjects</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <a href="{{ $card->link_url }}" class="exam-btn" style="text-decoration: none; display: inline-block; text-align: center;">
                            {{ $card->button_text }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
