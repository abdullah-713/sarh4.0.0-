<?php

namespace App\Filament\App\Pages;

use App\Models\WhistleblowerReport;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

/**
 * SarhIndex — صفحة الإبلاغ السري داخل بوابة الموظفين
 *
 * تسمح للموظف بإرسال بلاغ مجهول الهوية ومشفر.
 */
class WhistleblowerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.app.pages.whistleblower';

    protected static ?string $slug = 'whistleblower';

    // ── Form state ──
    public string $category = '';
    public string $severity = 'medium';
    public string $content = '';
    public bool $submitted = false;
    public string $ticketNumber = '';
    public string $anonymousToken = '';
    public string $errorMessage = '';

    public static function getNavigationLabel(): string
    {
        return __('pwa.wb_title');
    }

    public function getTitle(): string
    {
        return __('pwa.wb_title');
    }

    protected function rules(): array
    {
        return [
            'category' => ['required', 'in:fraud,harassment,corruption,safety'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'content'  => ['required', 'min:20'],
        ];
    }

    protected function messages(): array
    {
        return [
            'category.required' => __('pwa.wb_error_category_required'),
            'content.required'  => __('pwa.wb_error_content_required'),
            'content.min'       => __('pwa.wb_error_content_min'),
        ];
    }

    public function submit(): void
    {
        $this->errorMessage = '';

        $this->validate();

        try {
            $ticket = WhistleblowerReport::generateTicketNumber();
            $token  = WhistleblowerReport::generateAnonymousToken();

            WhistleblowerReport::create([
                'ticket_number'     => $ticket,
                'encrypted_content' => encrypt($this->content),
                'category'          => $this->category,
                'severity'          => $this->severity,
                'anonymous_token'   => $token,
                'status'            => 'new',
            ]);

            $this->ticketNumber   = $ticket;
            $this->anonymousToken = $token;
            $this->submitted      = true;

            // Reset form fields
            $this->category = '';
            $this->severity = 'medium';
            $this->content  = '';
        } catch (\Exception $e) {
            Log::error('Whistleblower submit failed', [
                'error'    => $e->getMessage(),
                'category' => $this->category,
            ]);
            $this->errorMessage = __('pwa.wb_error_submit_failed');
        }
    }

    public function resetForm(): void
    {
        $this->submitted      = false;
        $this->ticketNumber   = '';
        $this->anonymousToken = '';
        $this->errorMessage   = '';
        $this->category       = '';
        $this->severity       = 'medium';
        $this->content        = '';
    }
}
