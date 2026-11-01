@extends('layouts.admin')

@section('content')
    <style>
        .response-column { min-width: 250px; width: 28%; }
        .doubt-response-modal .modal-dialog { max-width: 860px; }
        .doubt-response-modal .modal-body { max-height: 72vh; overflow-y: auto; }
        .doubt-context { background: #f8fafc; border-radius: 14px; padding: 18px; }
        .doubt-context .label { color: #64748b; font-size: .75rem; font-weight: 700; text-transform: uppercase; }
        .doubt-context .value { color: #1e293b; white-space: pre-wrap; }
        .doubt-response-modal .response-editor-container,
        .doubt-response-modal .ck-editor,
        .doubt-response-modal .ck-editor__top,
        .doubt-response-modal .ck-toolbar,
        .doubt-response-modal .ck-toolbar__items {
            box-sizing: border-box;
            min-width: 0;
            max-width: 100%;
        }
        .doubt-response-modal .ck-editor { width: 100%; }
        .doubt-response-modal .ck-editor__editable_inline { min-height: 300px; max-height: 65vh; overflow-y: auto; }
        .doubt-response-modal .ck-toolbar__items {
            width: 100%;
            flex-wrap: wrap !important;
            overflow: visible !important;
        }
        .response-editor-readonly .ck-toolbar { display: none; }
        @media (max-width: 575.98px) {
            .doubt-response-modal .modal-dialog { margin: .5rem; }
            .doubt-response-modal .ck-editor__editable_inline { min-height: 240px; max-height: 55vh; }
        }
    </style>

    <div class="mb-4">
        <h2 class="fw-bold">Student Doubts</h2>
        <p class="text-secondary">Review student doubts and send academic responses.</p>
    </div>

    <form method="GET" class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <label class="visually-hidden" for="doubt-search">Search student doubts</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </span>
                        <input
                            type="search"
                            id="doubt-search"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search students, subjects, topics or doubts..."
                            autocomplete="off"
                        >
                    </div>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Search</button>
                    @if(request('search'))
                        <a class="btn btn-outline-secondary" href="{{ route('admin.doubts.index') }}">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">



            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    Please fix the errors and try again.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Year</th>
                        <th>Subject</th>
                        <th>Action</th>
                        <th>Topic</th>
                        <th>Doubt</th>
                        <th>Submitted Date &amp; Time</th>
                        <th>Status</th>
                        <th class="response-column">Response</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($doubts as $doubt)
                        <tr>
                            <td>
                                <strong>{{ $doubt->user?->name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $doubt->user?->email ?? '-' }}</small>
                            </td>

                            <td>{{ $doubt->academicYear?->name ?? '-' }}</td>

                            <td>{{ $doubt->subject?->name ?? '-' }}</td>

                            <td>
                                <form action="{{ route('admin.doubts.destroy', [$doubt, '_listing' => request()->query()]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this doubt?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>

                            <td>{{ $doubt->topic ?: '-' }}</td>

                            <td style="max-width: 260px;">
                                <div class="small text-muted">
                                    {{ $doubt->message }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="small text-muted">
                                    {{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $badgeClass = match($doubt->status) {
                                        'answered' => 'success',
                                        'rejected' => 'danger',
                                        'in_progress' => 'info',
                                        default => 'warning',
                                    };
                                @endphp

                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $doubt->status)) }}
                                </span>
                            </td>



                            <td class="response-column">
                                @if($doubt->answer)
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm response-action"
                                                data-mode="view"
                                                data-id="{{ $doubt->id }}"
                                                data-status="{{ $doubt->status }}"
                                                data-student="{{ $doubt->user?->name ?? 'N/A' }}"
                                                data-subject="{{ $doubt->subject?->name ?? '-' }}"
                                                data-topic="{{ $doubt->topic ?: '-' }}"
                                                data-submission="{{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}"
                                                data-message="{{ base64_encode($doubt->message) }}"
                                                data-answer="{{ base64_encode(\App\Models\Doubt::sanitizeHtml($doubt->answer)) }}">
                                            View Response
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm response-action"
                                                data-mode="edit"
                                                data-id="{{ $doubt->id }}"
                                                data-status="{{ $doubt->status }}"
                                                data-student="{{ $doubt->user?->name ?? 'N/A' }}"
                                                data-subject="{{ $doubt->subject?->name ?? '-' }}"
                                                data-topic="{{ $doubt->topic ?: '-' }}"
                                                data-submission="{{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}"
                                                data-message="{{ base64_encode($doubt->message) }}"
                                                data-answer="{{ base64_encode(\App\Models\Doubt::sanitizeHtml($doubt->answer)) }}">
                                            Edit Response
                                        </button>
                                    </div>
                                    @if($doubt->answeredBy)
                                        <small class="text-muted d-block mt-2">
                                            Answered by {{ $doubt->answeredBy?->name }}
                                            @if($doubt->answered_at)
                                                on {{ $doubt->answered_at->copy()->setTimezone('Asia/Kolkata')->format('d M Y h:i A') }}
                                            @endif
                                        </small>
                                    @endif
                                @else
                                    <button type="button" class="btn btn-primary btn-sm response-action"
                                            data-mode="add"
                                            data-id="{{ $doubt->id }}"
                                            data-status="answered"
                                            data-student="{{ $doubt->user?->name ?? 'N/A' }}"
                                            data-subject="{{ $doubt->subject?->name ?? '-' }}"
                                            data-topic="{{ $doubt->topic ?: '-' }}"
                                            data-submission="{{ $doubt->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '-' }}"
                                            data-message="{{ base64_encode($doubt->message) }}"
                                            data-answer="">
                                        Add Response
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                No doubts found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $doubts->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade doubt-response-modal" id="doubtResponseModal" tabindex="-1"
         aria-labelledby="doubtResponseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="doubtResponseModalLabel">Add Response</h5>
                    <button type="button" class="btn-close" data-response-close aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="doubt-context mb-4">
                        <div class="row g-3">
                            <div class="col-md-6"><div class="label">Student</div><div class="value" id="responseStudent"></div></div>
                            <div class="col-md-6"><div class="label">Subject</div><div class="value" id="responseSubject"></div></div>
                            <div class="col-md-6"><div class="label">Topic</div><div class="value" id="responseTopic"></div></div>
                            <div class="col-md-6"><div class="label">Submitted</div><div class="value" id="responseSubmission"></div></div>
                            <div class="col-12"><div class="label">Doubt</div><div class="value" id="responseMessage"></div></div>
                        </div>
                    </div>
                    <form id="responseModalForm" method="POST">
                        @csrf
                        @method('PUT')
                        @foreach(request()->query() as $key => $value)
                            @if(is_scalar($value))
                                <input type="hidden" name="_listing[{{ $key }}]" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label class="form-label fw-semibold" for="responseStatus">Status</label>
                        <select class="form-select mb-3" id="responseStatus" name="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="answered" selected>Answered</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <label class="form-label fw-semibold" for="modalResponseContent">Response</label>
                        <input type="hidden" name="answer" id="responseAnswer">
                        <div class="response-editor-container" id="responseEditorContainer">
                            <textarea class="form-control" id="modalResponseContent" rows="10" aria-label="Response editor"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-response-close>Cancel</button>
                    <button type="submit" form="responseModalForm" class="btn btn-primary" id="saveResponseButton">Save Response</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
    <script>
        (() => {
            const modalElement = document.getElementById('doubtResponseModal');
            const modal = window.bootstrap?.Modal
                ? new window.bootstrap.Modal(modalElement)
                : {
                    show() {
                        modalElement.style.display = 'block';
                        modalElement.classList.add('show');
                        modalElement.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('modal-open');
                    },
                    hide() {
                        modalElement.style.display = 'none';
                        modalElement.classList.remove('show');
                        modalElement.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('modal-open');
                    }
                };
            const modalContent = document.getElementById('modalResponseContent');
            const editorContainer = document.getElementById('responseEditorContainer');
            const responseForm = document.getElementById('responseModalForm');
            const responseAnswer = document.getElementById('responseAnswer');
            const saveButton = document.getElementById('saveResponseButton');
            let currentMode = 'add';
            let pendingResponseHtml = '';
            let responseEditor;
            let responseEditorPromise;
            let responseEditorIsReadOnly = false;

            const decode = (value) => value ? atob(value) : '';
            const setText = (id, value) => {
                document.getElementById(id).textContent = value || '-';
            };

            const ensureEditor = () => {
                if (responseEditor) return Promise.resolve(responseEditor);
                if (responseEditorPromise) return responseEditorPromise;

                const Editor = window.CKEDITOR?.ClassicEditor;
                if (!Editor) {
                    console.error('Doubt response editor initialization failed: CKEditor 5 super-build did not load.');
                    return Promise.resolve(null);
                }

                const config = {
                    removePlugins: [
                        'RealTimeCollaborativeComments',
                        'RealTimeCollaborativeTrackChanges',
                        'RealTimeCollaborativeRevisionHistory',
                        'PresenceList',
                        'Comments',
                        'TrackChanges',
                        'TrackChangesData',
                        'RevisionHistory',
                        'Users',
                        'CommentsRepository',
                        'WProofreader',
                        'Pagination',
                        'DocumentOutline',
                        'AIAssistant',
                        'AIAssistantUI',
                        'AIAssistantEditing',
                        'AIAdapter',
                        'TableOfContents',
                        'FormatPainter',
                        'Template',
                        'SlashCommand',
                        'PasteFromOfficeEnhanced',
                        'CaseChange',
                        'MathType'
                    ],
                    ckfinder: {
                        uploadUrl: '{{ route("admin.topics.upload_image") }}?_token={{ csrf_token() }}'
                    },
                    toolbar: {
                        items: [
                            'heading', '|',
                            'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                            'bold', 'italic', 'underline', 'strikethrough', 'removeFormat', '|',
                            'link', 'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                            'blockQuote', 'imageUpload', 'insertTable', 'mediaEmbed', '|',
                            'undo', 'redo'
                        ]
                    }
                };

                responseEditorPromise = Editor.create(modalContent, config)
                    .then((editor) => {
                        responseEditor = editor;
                        return editor;
                    })
                    .catch((error) => {
                        responseEditorPromise = null;
                        console.error('Doubt response editor initialization failed:', error);
                        return null;
                    });

                return responseEditorPromise;
            };

            const loadEditorContent = () => {
                ensureEditor().then((editor) => {
                    if (!editor) {
                        modalContent.value = pendingResponseHtml;
                        return;
                    }

                    editor.setData(pendingResponseHtml);
                    if (currentMode === 'view' && !responseEditorIsReadOnly) {
                        editor.enableReadOnlyMode('doubt-response-view');
                        responseEditorIsReadOnly = true;
                    } else if (currentMode !== 'view' && responseEditorIsReadOnly) {
                        editor.disableReadOnlyMode('doubt-response-view');
                        responseEditorIsReadOnly = false;
                    }

                    if (currentMode !== 'view') {
                        editor.editing.view.focus();
                    }
                });
            };

            const closeModal = () => {
                if (typeof modal.hide === 'function') modal.hide();
            };

            document.querySelectorAll('[data-response-close]').forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.querySelectorAll('.response-action').forEach((button) => {
                button.addEventListener('click', () => {
                    currentMode = button.dataset.mode;
                    document.getElementById('doubtResponseModalLabel').textContent =
                        currentMode === 'view' ? 'View Response' :
                        currentMode === 'edit' ? 'Edit Response' : 'Add Response';
                    setText('responseStudent', button.dataset.student);
                    setText('responseSubject', button.dataset.subject);
                    setText('responseTopic', button.dataset.topic);
                    setText('responseSubmission', button.dataset.submission);
                    setText('responseMessage', decode(button.dataset.message));
                    pendingResponseHtml = decode(button.dataset.answer);
                    modalContent.value = pendingResponseHtml;
                    editorContainer.classList.toggle('response-editor-readonly', currentMode === 'view');
                    responseForm.action = `{{ url('/admin/doubts') }}/${button.dataset.id}`;
                    document.getElementById('responseStatus').value =
                        currentMode === 'add' ? 'answered' : (button.dataset.status || 'answered');
                    document.getElementById('responseStatus').disabled = currentMode === 'view';
                    saveButton.classList.toggle('d-none', currentMode === 'view');
                    modal.show();
                    loadEditorContent();
                });
            });

            responseForm.addEventListener('submit', (event) => {
                if (currentMode === 'view') {
                    event.preventDefault();
                    return;
                }

                const responseHtml = responseEditor ? responseEditor.getData().trim() : modalContent.value.trim();
                const responseText = new DOMParser()
                    .parseFromString(responseHtml, 'text/html')
                    .body.textContent.trim();

                if (!responseText) {
                    event.preventDefault();
                    if (responseEditor) responseEditor.editing.view.focus();
                    else modalContent.focus();
                    return;
                }

                responseAnswer.value = responseHtml;
                saveButton.disabled = true;
                saveButton.textContent = 'Saving...';
            });

            modalElement.addEventListener('click', (event) => {
                if (event.target === modalElement) closeModal();
            });
        })();
    </script>
@endpush
