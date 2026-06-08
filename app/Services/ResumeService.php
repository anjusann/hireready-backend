<?php

namespace App\Services;

use App\Enums\ResumeFileType;
use App\Enums\SubscriptionPlan;
use App\Models\Resume;
use App\Models\User;
use App\Repositories\Interfaces\ResumeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResumeService extends BaseService
{
    private const FREE_RESUME_LIMIT = 5;

    private const PRO_RESUME_LIMIT = 20;

    public function __construct(
        ResumeRepositoryInterface $resumeRepository,
        protected TextExtractionService $textExtractionService,
    ) {
        parent::__construct($resumeRepository);
    }

    /**
     * @return array{resume: Resume, warning: string|null}
     */
    public function upload(User $user, UploadedFile $file, ?string $title = null): array
    {
        $this->ensureWithinResumeLimit($user);

        $fileType = $this->resolveFileType($file);
        $filename = Str::uuid()->toString().'.'.$fileType->value;
        $directory = 'resumes/'.$user->id;
        $storedPath = $file->storeAs($directory, $filename, 'local');
        $absolutePath = Storage::disk('local')->path($storedPath);

        $extractedText = $this->textExtractionService->tryExtract($absolutePath, $fileType);
        $warning = null;

        if ($extractedText === null) {
            $warning = 'Text extraction failed. Resume saved without extracted content.';
        }

        $isFirstResume = $this->repository->countForUser($user->id) === 0;

        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        $resume = $repository->create([
            'user_id' => $user->id,
            'title' => $title ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_path' => $storedPath,
            'file_type' => $fileType,
            'file_size' => $file->getSize(),
            'extracted_text' => $extractedText,
            'is_primary' => $isFirstResume,
        ]);

        return [
            'resume' => $resume,
            'warning' => $warning,
        ];
    }

    public function listForUser(User $user): Collection
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        return $repository->getAllForUser($user->id);
    }

    public function getForUser(User $user, int $id): Resume
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        return $repository->findForUserOrFail($id, $user->id);
    }

    public function updateForUser(User $user, int $id, array $data): Resume
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        $resume = $repository->findForUserOrFail($id, $user->id);

        return $repository->updateModel($resume, $data);
    }

    public function deleteForUser(User $user, int $id): void
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        $resume = $repository->findForUserOrFail($id, $user->id);

        Storage::disk('local')->delete($resume->file_path);

        $resume->delete();
    }

    public function setPrimaryForUser(User $user, int $id): Resume
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        $resume = $repository->findForUserOrFail($id, $user->id);

        $repository->clearPrimaryForUser($user->id);

        return $repository->updateModel($resume, ['is_primary' => true]);
    }

    protected function ensureWithinResumeLimit(User $user): void
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = $this->repository;

        $limit = $user->subscription_plan === SubscriptionPlan::Pro
            ? self::PRO_RESUME_LIMIT
            : self::FREE_RESUME_LIMIT;

        if ($repository->countForUser($user->id) >= $limit) {
            throw ValidationException::withMessages([
                'file' => ["You have reached the maximum of {$limit} resumes for your subscription plan."],
            ]);
        }
    }

    protected function resolveFileType(UploadedFile $file): ResumeFileType
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf' => ResumeFileType::Pdf,
            'docx' => ResumeFileType::Docx,
            default => throw ValidationException::withMessages([
                'file' => ['Unsupported file type. Only PDF and DOCX files are allowed.'],
            ]),
        };
    }
}
