<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLicenseNotification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public License $license,
        public string $type,
        public array $recipients,
        public array $data = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            foreach ($this->recipients as $recipient) {
                $this->sendNotification($recipient);
            }

            Log::info('Notificación de licencia enviada exitosamente', [
                'license_code' => $this->license->license_code,
                'type' => $this->type,
                'recipients_count' => count($this->recipients)
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando notificación de licencia', [
                'license_code' => $this->license->license_code,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Enviar notificación a un destinatario específico
     */
    protected function sendNotification(string $recipient): void
    {
        $subject = $this->getSubject();
        $template = $this->getTemplate();
        $data = $this->getTemplateData();

        Mail::send($template, $data, function ($message) use ($recipient, $subject) {
            $message->to($recipient)->subject($subject);
        });
    }

    /**
     * Obtener el asunto del email según el tipo
     */
    protected function getSubject(): string
    {
        return match ($this->type) {
            'code_generated' => "Nuevo código de licencia generado - {$this->license->license_code}",
            'expiration_warning' => "⚠️ Licencia expirará en {$this->data['days_remaining']} días - Sistema de Inventario",
            'expired' => "🚨 Licencia expirada - {$this->license->license_code}",
            default => "Notificación de licencia - {$this->license->license_code}"
        };
    }

    /**
     * Obtener el template según el tipo
     */
    protected function getTemplate(): string
    {
        return match ($this->type) {
            'code_generated' => 'emails.license-code',
            'expiration_warning' => 'emails.license-expiration',
            'expired' => 'emails.license-expired',
            default => 'emails.license-notification'
        };
    }

    /**
     * Obtener los datos para el template
     */
    protected function getTemplateData(): array
    {
        $baseData = [
            'license' => $this->license,
            'encryptedCode' => $this->license->encrypted_code,
        ];

        return array_merge($baseData, $this->data);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de notificación de licencia falló', [
            'license_code' => $this->license->license_code,
            'type' => $this->type,
            'recipients' => $this->recipients,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
