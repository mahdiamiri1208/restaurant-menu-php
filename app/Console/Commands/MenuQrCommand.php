<?php

namespace App\Console\Commands;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Console\Command;

class MenuQrCommand extends Command
{
    protected $signature = 'menu:qr';

    protected $description = 'Generate printable SVG and PNG QR codes for PUBLIC_URL';

    public function handle(): int
    {
        $url = trim((string) config('menu.public_url'));
        $parts = parse_url($url);

        if (
            ! filter_var($url, FILTER_VALIDATE_URL)
            || ! is_array($parts)
            || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true)
            || blank($parts['host'] ?? null)
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            $this->error('PUBLIC_URL must be an absolute HTTP or HTTPS URL without embedded credentials.');

            return self::FAILURE;
        }

        if (($parts['path'] ?? '') !== '/menu') {
            $url = $parts['scheme'].'://'.$parts['host'];

            if (isset($parts['port'])) {
                $url .= ':'.$parts['port'];
            }

            $url .= '/menu';

            if (isset($parts['query'])) {
                $url .= '?'.$parts['query'];
            }

            if (isset($parts['fragment'])) {
                $url .= '#'.$parts['fragment'];
            }
        }

        if (! extension_loaded('gd') || ! function_exists('imagepng')) {
            $this->error('The GD extension is required to generate the PNG QR code.');

            return self::FAILURE;
        }

        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 1196,
            margin: 2,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $svgPath = base_path('menu-qr.svg');
        $pngPath = base_path('menu-qr.png');

        (new SvgWriter())->write($qrCode)->saveToFile($svgPath);
        (new PngWriter())->write($qrCode)->saveToFile($pngPath);

        $this->info("Generated QR codes for {$url}:");
        $this->line($svgPath);
        $this->line($pngPath);

        return self::SUCCESS;
    }
}
