<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeedbackFormStatus;
use App\Enums\FeedbackQuestionType;
use App\Http\Controllers\Controller;
use App\Models\FeedbackForm;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Services\AuditLogger;
use App\Services\FeedbackFormAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeedbackFormController extends Controller
{
    public function __construct(
        protected FeedbackFormAdminService $forms,
        protected AuditLogger $auditLogger
    ) {}

    public function index(): View
    {
        $items = FeedbackForm::query()->orderByDesc('id')->get();

        return view('admin.feedback.forms.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ku' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $form = $this->forms->createForm($data['title_en'], $data['title_ku'] ?? null, $data['title_ar'] ?? null, $request->user());

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.saved'));
    }

    public function edit(FeedbackForm $form): View
    {
        Gate::authorize('update', $form);
        $version = $form->versions()->orderByDesc('version_number')->first();
        if (! $version) {
            $version = $this->forms->ensureInitialVersion($form);
        }
        $questions = $version->questions()->orderBy('sort_order')->get();

        return view('admin.feedback.forms.edit', compact('form', 'version', 'questions'));
    }

    public function update(Request $request, FeedbackForm $form): RedirectResponse
    {
        Gate::authorize('update', $form);
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ku' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(FeedbackFormStatus::class)],
        ]);
        $oldStatus = $form->status;
        $form->fill([
            'title_en' => $data['title_en'],
            'title_ku' => $data['title_ku'],
            'title_ar' => $data['title_ar'],
            'status' => $data['status'],
        ]);
        $form->save();
        if ($oldStatus !== $form->status) {
            $this->auditLogger->log($request->user(), 'feedback_form.status', $form, ['status' => $form->status->value]);
        }

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.saved'));
    }

    public function publishVersion(Request $request, FeedbackForm $form): RedirectResponse
    {
        Gate::authorize('update', $form);
        $this->forms->publishNewVersion($form, $request->user());

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.version_created'));
    }

    public function updateVersion(Request $request, FeedbackForm $form, FeedbackFormVersion $version): RedirectResponse
    {
        Gate::authorize('update', $form);
        if ($version->feedback_form_id !== $form->id) {
            abort(404);
        }
        $data = $request->validate([
            'accepts_submissions' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
        $this->forms->configureVersionWindow(
            $version,
            (bool) $data['accepts_submissions'],
            $data['starts_at'] ?? null,
            $data['ends_at'] ?? null,
            $request->user()
        );

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.saved'));
    }

    public function storeQuestion(Request $request, FeedbackForm $form, FeedbackFormVersion $version): RedirectResponse
    {
        Gate::authorize('update', $form);
        if ($version->feedback_form_id !== $form->id) {
            abort(404);
        }
        $this->prepareQuestionRequest($request);
        $data = $this->validatedQuestion($request);
        $max = (int) $version->questions()->max('sort_order');
        $data['sort_order'] = $max + 1;
        $this->forms->upsertQuestion($version, $data, null, $request->user());

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.saved'));
    }

    public function updateQuestion(Request $request, FeedbackForm $form, FeedbackFormVersion $version, FeedbackQuestion $question): RedirectResponse
    {
        Gate::authorize('update', $form);
        if ($version->feedback_form_id !== $form->id || $question->feedback_form_version_id !== $version->id) {
            abort(404);
        }
        $this->prepareQuestionRequest($request);
        $data = $this->validatedQuestion($request);
        $this->forms->upsertQuestion($version, $data, $question, $request->user());

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.saved'));
    }

    public function destroyQuestion(Request $request, FeedbackForm $form, FeedbackFormVersion $version, FeedbackQuestion $question): RedirectResponse
    {
        Gate::authorize('update', $form);
        if ($version->feedback_form_id !== $form->id || $question->feedback_form_version_id !== $version->id) {
            abort(404);
        }
        $question->delete();
        $this->auditLogger->log($request->user(), 'feedback_question.deleted', $question);

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.deleted'));
    }

    public function reorderQuestions(Request $request, FeedbackForm $form, FeedbackFormVersion $version): RedirectResponse
    {
        Gate::authorize('update', $form);
        if ($version->feedback_form_id !== $form->id) {
            abort(404);
        }
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:feedback_questions,id'],
        ]);
        $this->forms->reorderQuestions($version, $data['order'], $request->user());

        return redirect()->route('admin.feedback.forms.edit', $form)->with('ok', __('messages.saved'));
    }

    public function preview(FeedbackForm $form): View
    {
        Gate::authorize('update', $form);
        $version = $form->versions()->orderByDesc('version_number')->first();
        abort_if(! $version, 404);
        $questions = $version->questions()->orderBy('sort_order')->get();

        return view('admin.feedback.forms.preview', compact('form', 'version', 'questions'));
    }

    public function destroy(Request $request, FeedbackForm $form): RedirectResponse
    {
        Gate::authorize('delete', $form);
        $this->forms->deleteForm($form, $request->user());

        return redirect()->route('admin.feedback.forms.index')->with('ok', __('messages.deleted'));
    }

    protected function prepareQuestionRequest(Request $request): void
    {
        if ($request->input('type') !== FeedbackQuestionType::MultipleChoice->value) {
            return;
        }
        if (! $request->filled('options_json')) {
            $request->merge(['options' => ['choices' => [
                ['key' => 'opt1', 'en' => 'Option 1'],
                ['key' => 'opt2', 'en' => 'Option 2'],
            ]]]);
        } else {
            $decoded = json_decode((string) $request->input('options_json'), true);
            if (is_array($decoded)) {
                $request->merge(['options' => $decoded]);
            }
        }
    }

    protected function validatedQuestion(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::enum(FeedbackQuestionType::class)],
            'label_en' => ['required', 'string', 'max:500'],
            'label_ku' => ['nullable', 'string', 'max:500'],
            'label_ar' => ['nullable', 'string', 'max:500'],
            'is_required' => ['sometimes', 'boolean'],
            'options' => ['nullable', 'array'],
        ]);
    }
}
