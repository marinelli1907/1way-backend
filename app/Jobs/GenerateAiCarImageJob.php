<?php

namespace App\Jobs;

use App\Models\AiCarImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateAiCarImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $jobId
    ) {}

    public function handle(): void
    {
        $record = AiCarImage::find($this->jobId);
        if (!$record) {
            return;
        }

        try {
            $record->update(['status' => AiCarImage::STATUS_RUNNING]);

            $relPath = 'ai-cars/' . $this->jobId . '.png';
            $fullPath = Storage::disk('public')->path($relPath);

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $this->generatePlaceholderPng(
                $fullPath,
                trim(($record->make ?? '') . ' ' . ($record->model ?? '') . ' ' . ($record->color ?? '')) ?: 'Car'
            );

            $url = Storage::disk('public')->url($relPath);

            $record->update([
                'status' => AiCarImage::STATUS_DONE,
                'image_path' => $relPath,
                'image_url' => $url,
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('GenerateAiCarImageJob failed: ' . $e->getMessage());
            $record->update([
                'status' => AiCarImage::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function generatePlaceholderPng(string $path, string $label): void
    {
        $w = 400;
        $h = 300;
        $img = @imagecreatetruecolor($w, $h);
        if ($img === false) {
            throw new \RuntimeException('GD imagecreatetruecolor failed');
        }

        $bg = imagecolorallocate($img, 240, 240, 240);
        $text = imagecolorallocate($img, 60, 60, 60);
        imagefill($img, 0, 0, $bg);

        $label = strlen($label) > 40 ? substr($label, 0, 37) . '...' : $label;
        imagestring($img, 5, (int) max(10, ($w - strlen($label) * 8) / 2), (int) (($h - 20) / 2), $label, $text);

        imagepng($img, $path);
        imagedestroy($img);
    }
}
