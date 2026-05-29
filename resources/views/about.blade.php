@extends('layouts.frontend')

@section('title', $page->title ?? 'About Us')

@section('content')
<main class="about-page">
	<div class="about-shell">
		@if(isset($sections) && $sections->count())
			@foreach($sections as $section)
				<section class="about-section {{ $section->type }}-wrapper">
					<div class="about-container section-block reveal-up" id="{{ $section->slug ?? 'section-'.$section->id }}">
						
						@if(!in_array($section->type, ['mission', 'vision', 'closing']))
							@if(isset($section->content['kicker']))
								<p class="section-kicker"><span class="dot"></span> {{ $section->content['kicker'] }}</p>
							@endif

							@if(isset($section->content['title']))
								<h2 class="section-heading">{!! $section->content['title'] !!}</h2>
							@endif

							<div class="section-divider"></div>

							@if(isset($section->content['subtext']))
								<p class="section-subtext">{!! $section->content['subtext'] !!}</p>
							@endif
						@endif

						{{-- Type-specific rendering --}}
						@if($section->type === 'mission')
							<p class="section-kicker"><span class="dot"></span> {{ $section->content['kicker'] ?? 'Our Mission' }}</p>
							<div class="section-divider"></div>
							<div class="mission-layout">
								<div class="mission-media reveal-left">
									<div class="media-main shadow-lg" style="background-image: url('{{ $section->content['images']['main'] ?? 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80' }}')"></div>
									<div class="media-thumb thumb-1 shadow-sm" style="background-image: url('{{ $section->content['images']['thumb1'] ?? 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=400' }}')"></div>
									<div class="media-thumb thumb-2 shadow-sm" style="background-image: url('{{ $section->content['images']['thumb2'] ?? 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=400' }}')"></div>
									<div class="media-thumb thumb-3 shadow-sm d-flex align-items-center justify-content-center text-primary fw-bold">
										{{ $section->content['images']['thumb3_text'] ?? 'PT' }}
									</div>
								</div>

								<div class="mission-copy glass-card reveal-right">
									<h2 class="section-heading">{{ $section->content['title'] ?? $page->title }}</h2>
									<div class="divider-line"></div>
									<div class="section-content-wrap">
										{!! $section->content['body'] ?? $page->content !!}
									</div>
									@if(isset($section->content['pills']) && is_array($section->content['pills']))
										<div class="mission-pills">
											@foreach($section->content['pills'] as $pill)
												<span class="mission-pill">{{ $pill }}</span>
											@endforeach
										</div>
									@endif
								</div>
							</div>
						@elseif($section->type === 'features' || $section->type === 'feature')
							<div class="feature-grid">
								@foreach($section->items as $idx => $item)
									<article class="feature-card glass-card reveal-up delay-{{ ($idx % 3) + 1 }}">
										<div class="feature-icon">{!! ($item->meta['icon'] ?? '<i class="bi bi-layers-half"></i>') !!}</div>
										<h3>{{ $item->title }}</h3>
										<p>{!! $item->body !!}</p>
									</article>
								@endforeach
							</div>
						@elseif($section->type === 'explore')
							<div class="explore-grid">
								@foreach($section->items as $idx => $item)
									<article class="explore-card glass-card reveal-up delay-{{ ($idx % 3) + 1 }}">
										<div class="explore-card-icon">{!! ($item->meta['icon'] ?? '<i class="bi bi-folder2-open"></i>') !!}</div>
										<div><h3>{{ $item->title }}</h3><p>{!! $item->body !!}</p></div>
									</article>
								@endforeach
							</div>
						@elseif($section->type === 'vision')
							<p class="section-kicker"><span class="dot"></span> {{ $section->content['kicker'] ?? 'Our Vision' }}</p>
							<div class="section-divider"></div>
							<div class="vision-banner glass-card">
								<h2 class="section-heading" style="text-align:center; margin-bottom: 16px;">{{ $section->content['title'] ?? 'Our Vision' }}</h2>
								<p style="text-align:center; max-width: 800px; margin: 0 auto; color: #64748b; font-size: 1.1rem; line-height: 1.8;">{!! $section->content['body'] ?? '' !!}</p>
							</div>
						@elseif($section->type === 'team')
							<div class="feature-grid mt-4">
								@foreach($section->items as $idx => $item)
									<article class="feature-card glass-card text-center reveal-up delay-{{ ($idx % 3) + 1 }}">
										<div class="team-photo mb-3 mx-auto" style="width: 120px; height: 120px; border-radius: 50%; background: url('{{ $item->meta['photo'] ?? 'https://ui-avatars.com/api/?name='.urlencode($item->title).'&background=random' }}') center/cover;"></div>
										<h3 class="mb-1">{{ $item->title }}</h3>
										<p class="text-primary fw-bold mb-2">{{ $item->meta['designation'] ?? 'Team Member' }}</p>
										<p>{!! $item->body !!}</p>
									</article>
								@endforeach
							</div>
						@elseif($section->type === 'stats')
							<div class="row g-4 mt-4">
								@foreach($section->items as $idx => $item)
									<div class="col-md-3 col-6">
										<div class="glass-card p-4 text-center reveal-up delay-{{ ($idx % 4) + 1 }}">
											<div class="h2 fw-bold text-primary mb-1">{{ $item->title }}</div>
											<div class="small fw-bold text-uppercase text-secondary" style="letter-spacing: 0.1em;">{!! $item->body !!}</div>
										</div>
									</div>
								@endforeach
							</div>
						@elseif($section->type === 'closing')
							<div class="closing-minimal glass-card shadow-lg">
								<div>
									<p class="section-kicker" style="margin-bottom: 10px;"><span class="dot"></span> {{ $section->content['kicker'] ?? 'Final Step' }}</p>
									<h2 class="fw-bold h1">{{ $section->content['title'] ?? 'Learn Smarter. Study with Clarity.' }}</h2>
								</div>
								<div class="closing-actions mt-4 mt-md-0">
									<a href="{{ $section->content['cta_url'] ?? '#' }}" class="cta-button text-decoration-none">{{ $section->content['cta_text'] ?? 'Explore Topics' }}</a>
									<a href="{{ $section->content['cta_secondary_url'] ?? '#' }}" class="cta-button-secondary text-decoration-none">{{ $section->content['cta_secondary_text'] ?? 'Ask a Doubt' }}</a>
								</div>
							</div>
						@else
							{{-- Fallback for generic content --}}
							<div class="generic-content">
								{!! $section->content['body'] ?? ($section->content['html'] ?? '') !!}
								@if($section->items->count())
									<div class="row g-4 mt-4">
										@foreach($section->items as $item)
											<div class="col-md-4">
												<div class="card p-3 h-100"> 
													<h4>{{ $item->title }}</h4>
													<div>{!! $item->body !!}</div>
												</div>
											</div>
										@endforeach
									</div>
								@endif
							</div>
						@endif
					</div>
				</section>
			@endforeach
		@else
			<div class="container text-center py-5">
				<h2>About Us</h2>
				<p>{{ $page->content }}</p>
			</div>
		@endif
	</div>
</main>

@push('styles')
<style>
    /* DEFINITIVE ABOUT PAGE DESIGN FIX */
    .about-page { position: relative; overflow: hidden; background: linear-gradient(180deg, #f8fbff 0%, #f4f9ff 100%); }
    .about-shell { position: relative; z-index: 1; padding-top: 72px; }
    .about-container { max-width: 1280px; margin: 0 auto; width: 100%; padding: 0 24px; }
    .about-section { padding: 100px 0 20px; }
    .section-kicker { display: inline-flex; align-items: center; gap: 10px; font-size: 0.72rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.18em; margin-bottom: 22px; }
    .section-kicker .dot { width: 8px; height: 8px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #38bdf8); box-shadow: 0 0 12px rgba(37,99,235,0.4); }
    .section-divider { width: 100%; height: 1px; margin: 26px 0 36px; background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.2), transparent); }
    
    .mission-layout { display: grid; grid-template-columns: 0.95fr 1.05fr; gap: 48px; align-items: stretch; }
    .mission-media { position: relative; min-height: 560px; border-radius: 28px; overflow: hidden; background: #eef5ff; }
    .media-main { position: absolute; inset: 38px 34px 120px; border-radius: 24px; background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80') center/cover; }
    .media-thumb { position: absolute; bottom: 22px; width: 124px; height: 124px; border-radius: 22px; background-size: cover; border: 2px solid white; box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .thumb-1 { left: 26px; background-image: url('https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=400'); }
    .thumb-2 { left: 164px; background-image: url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=400'); }
    .thumb-3 { left: 302px; background: #2563eb; }

    .mission-copy { padding: 42px; border-radius: 28px; }
    .section-heading { font-family: var(--font-display); font-size: clamp(2.2rem, 4.5vw, 3.4rem); line-height: 1.1; margin: 16px 0 24px; font-weight: 800; color: #0f172a; }
    .section-heading .accent { background: linear-gradient(135deg, #2563eb, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .divider-line { width: 68px; height: 3px; border-radius: 99px; margin-bottom: 24px; background: #2563eb; }
    .section-content-wrap p { color: #64748b; line-height: 1.8; margin-bottom: 1.5rem; }
    
    .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 40px; }
    .feature-card { padding: 32px; border-radius: 24px; transition: transform 0.3s; height: 100%; border: 1px solid rgba(37,99,235,0.06); background: white; }
    .feature-card:hover { transform: translateY(-8px); border-color: rgba(37,99,235,0.15); }
    .feature-icon { width: 52px; height: 52px; display: grid; place-items: center; background: rgba(37,99,235,0.08); color: #2563eb; border-radius: 14px; font-size: 1.5rem; margin-bottom: 20px; }
    .feature-card h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: 12px; }
    .feature-card p { font-size: 0.95rem; color: #64748b; line-height: 1.6; margin: 0; }

    .explore-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-top: 40px; }
    .explore-card { padding: 28px; border-radius: 24px; display: flex; gap: 20px; align-items: flex-start; background: white; border: 1px solid rgba(37,99,235,0.06); }
    .explore-card-icon { width: 48px; height: 48px; min-width: 48px; border-radius: 12px; background: rgba(37,99,235,0.08); color: #2563eb; display: grid; place-items: center; font-size: 1.2rem; }
    .explore-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; }
    
    .vision-banner { padding: 60px 40px; border-radius: 32px; border: 1px solid rgba(59,130,246,0.1); background: rgba(255,255,255,0.6); }
    .closing-minimal { padding: 48px; border-radius: 28px; display: flex; align-items: center; justify-content: space-between; gap: 30px; flex-wrap: wrap; background: #fff; border: 1px solid rgba(59,130,246,0.12); }
    .cta-button { padding: 14px 28px; border-radius: 12px; background: #2563eb; color: #fff; font-weight: 700; box-shadow: 0 10px 25px rgba(37,99,235,0.2); }
    .cta-button-secondary { padding: 13px 28px; border-radius: 12px; background: #fff; color: #0f172a; border: 1px solid rgba(59,130,246,0.15); font-weight: 700; }

    @media (max-width: 1100px) {
        .mission-layout, .feature-grid, .explore-grid { grid-template-columns: 1fr; }
        .mission-media { min-height: 480px; }
    }
    @media (max-width: 768px) {
        .about-section { padding: 60px 0; }
        .mission-copy { padding: 24px; }
        .closing-minimal { padding: 32px; text-align: center; justify-content: center; }
        .thumb-2 { left: 140px; } .thumb-3 { display: none; }
    }
</style>
@endpush
@endsection
