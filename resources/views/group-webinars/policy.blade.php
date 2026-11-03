<button type="button" class="btn btn-link p-0 text-start" data-bs-toggle="modal" data-bs-target="#webinarPolicyModal">
    Physiosource Webinar Terms &amp; Conditions and Privacy Policy
</button>

<div class="modal fade" id="webinarPolicyModal" tabindex="-1" aria-labelledby="webinarPolicyModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h2 class="modal-title fs-5 fw-bold" id="webinarPolicyModalTitle">Physiosource Webinar Terms &amp; Conditions and Privacy Policy</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary">Policy version {{ $policyVersion }}</p>
                @foreach($policySections as $heading => $items)
                    <section class="mb-4">
                        <h3 class="h6 fw-bold">{{ $heading }}</h3>
                        @foreach($items as $item)
                            <p class="mb-2">
                                @if($item['label'])<strong>{{ $item['label'] }}</strong> @endif{{ $item['text'] }}
                            </p>
                        @endforeach
                    </section>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
