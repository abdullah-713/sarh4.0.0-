<?php

namespace App\Livewire;

use App\Models\WhistleblowerReport;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class WhistleblowerForm extends Component
{
    public string $category = '';
    public string $severity = 'medium';
    public string $content = '';

    public bool $submitted = false;
    public string $ticketNumber = '';
    public string $anonymousToken = '';
    public string $errorMessage = '';

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
            $token = WhistleblowerReport::generateAnonymousToken();

            WhistleblowerReport::create([
                'ticket_number'     => $ticket,
                'encrypted_content' => encrypt($this->content),
                'category'          => $this->category,
                'severity'          => $this->severity,
                'anonymous_token'   => $token,
                'status'            => 'new',
            ]);

            $this->ticketNumber = $ticket;
            $this->anonymousToken = $token;
            $this->submitted = true;

            // Reset form fields
            $this->category = '';
            $this->severity = 'medium';
            $this->content = '';
        } catch (\Exception $e) {
            Log::error('Whistleblower submit failed', [
                'error' => $e->getMessage(),
                'category' => $this->category,
            ]);
            $this->errorMessage = __('pwa.wb_error_submit_failed');
        }
    }

    public function render()
    {
        return view('livewire.whistleblower-form')
            ->layout('layouts.pwa', ['title' => __('pwa.wb_title')]);
    }
}
