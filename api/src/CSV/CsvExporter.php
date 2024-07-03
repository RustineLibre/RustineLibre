<?php

declare(strict_types=1);

namespace App\CSV;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class CsvExporter
{
    public function export(array $data, string $filename): Response
    {
        $serializer = new Serializer(encoders: [new CsvEncoder()]);
        $response = new Response($serializer->encode($data, CsvEncoder::FORMAT));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }
}
