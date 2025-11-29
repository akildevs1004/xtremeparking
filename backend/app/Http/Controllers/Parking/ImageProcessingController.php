<?php

namespace App\Http\Controllers\Parking;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class ImageProcessingController extends Controller
{

    public function processOcrText($inputString)

    {

        // $inputString = "Camera Info.:Camera 01 Device No. :PCOOOO001 Capture Time:09-03-2025 13:12:10 Plate No. 55942 Vehicle Color:White Vehicle Type:Bus Vehicle Brand:Audi Moving Direction:Reverse Validity:96% Country/Region:SHJ Plate Color:White Plate Size :ShortPlate Type:Private Province :Unknown Category: Camera No. :Camera O01";
        $fields = [
            ['names' => ['Camera Info.:'], 'key' => 'camera_info'],
            ['names' => ['Device No.:', 'Device No. :'], 'key' => 'device_no'],
            ['names' => ['Capture Time:', 'Capture Time :'], 'key' => 'capture_time'],
            ['names' => ['Plate No.:',  'Plate No. :', 'Plate No.'], 'key' => 'plate_no'],
            ['names' => ['Vehicle Color:'], 'key' => 'vehicle_color'],
            ['names' => ['Vehicle Type:'], 'key' => 'vehicle_type'],
            ['names' => ['Vehicle Brand:'], 'key' => 'vehicle_brand'],
            ['names' => ['Moving Direction:'], 'key' => 'moving_direction'],
            ['names' => ['Validity:', 'Yalidity:'], 'key' => 'validity'], // Multiple names
            ['names' => ['Country/Region:'], 'key' => 'country_region'],
            ['names' => ['Plate Color:'], 'key' => 'plate_color'],
            ['names' => ['Plate Size:'], 'key' => 'plate_size'],
            ['names' => ['Plate Type:'], 'key' => 'plate_type'],
            ['names' => ['Province:', 'Province :'], 'key' => 'province'],
            ['names' => ['Category:'], 'key' => 'category'],
            ['names' => ['Camera No.', 'Camera No. :'], 'key' => 'camera_number']

        ];

        $result = [];

        for ($i = 0; $i < count($fields) - 1; $i++) {
            $currentField = $fields[$i];
            $nextField = $fields[$i + 1];

            $result[$currentField['key']] = $this->extractBetweenFields(
                $inputString,
                $currentField['names'],
                $nextField['names']
            );
        }

        // Handle the last field
        $lastField = end($fields);
        $result[$lastField['key']] = $this->extractAfterField($inputString, $lastField['names']);

        $return["parsed"] = $result;

        $return["raw"] = $inputString;


        return $return;
    }

    protected function extractBetweenFields($inputString, $currentFieldNames, $nextFieldNames)
    {
        $actualCurrentField = $this->findActualFieldName($inputString, $currentFieldNames);
        $actualNextField = $this->findActualFieldName($inputString, $nextFieldNames);

        if ($actualCurrentField && $actualNextField) {
            $startPos = stripos($inputString, $actualCurrentField);
            $endPos = stripos($inputString, $actualNextField);

            if ($startPos !== false && $endPos !== false && $endPos > $startPos) {
                $startPos += strlen($actualCurrentField);
                $length = $endPos - $startPos;

                return trim(substr($inputString, $startPos, $length));
            }
        }

        return null;
    }

    protected function extractAfterField($inputString, $fieldNames)
    {
        $actualField = $this->findActualFieldName($inputString, $fieldNames);

        if ($actualField) {
            $startPos = stripos($inputString, $actualField);
            if ($startPos !== false) {
                $startPos += strlen($actualField);
                return trim(substr($inputString, $startPos));
            }
        }

        return null;
    }

    protected function findActualFieldName($inputString, $fieldNames)
    {
        foreach ($fieldNames as $fieldName) {
            if (stripos($inputString, $fieldName) !== false) {
                return $fieldName;
            }
        }
        return null;
    }
    /*
    function normalizeOcrFootertest(string $raw): array
    {
        // 1) Basic cleanup
        $text = preg_replace("/[^\S\r\n]+/", " ", $raw);      // collapse spaces
        $text = preg_replace("/[|\\\\`~^]+/", " ", $text);     // strip pipes/backslashes, etc.
        $text = preg_replace("/[“”\"’]/", "", $text);          // strip quotes
        $text = preg_replace("/\s+\n/", "\n", $text);          // trim line ends
        $text = preg_replace("/\n{2,}/", "\n", $text);         // collapse blank lines

        // 2) Fix common OCR mistakes
        $replacements = [
            // 'BAW' => 'BMW',
            'VYalidity' => 'Validity',
            'Camer a' => 'Camera',
            'Cam era' => 'Camera',
            'Capt ure' => 'Capture',
            'Dev ice' => 'Device',
            'Reve rse' => 'Reverse',
            'Plate Typ e' => 'Plate Type',
            'Plate Typ' => 'Plate Type',
            'Plate Siz e' => 'Plate Size',
        ];
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // 3) Unwrap hyphenated / wrapped words that broke across lines
        $text = preg_replace("/(\w)-\n(\w)/", "$1$2", $text);  // join split-words like “Vehi-\ncle”
        $text = preg_replace("/\n(?=[a-z])/", " ", $text);     // join if next line starts lowercase
        $text = preg_replace("/\s{2,}/", " ", $text);

        // 4) Bring everything to one line to split reliably


        // 5) Map of field labels to regex capture
        $fields = [
            'device_no'       => '/Device No\.?:\s*([A-Za-z0-9 _-]+)/i',
            'capture_time'    => '/Capture Time:?\s*([0-9]{2}-[0-9]{2}-[0-9]{4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2})/i',
            'capture_time'    => '/Capture Time :?\s*([0-9]{2}-[0-9]{2}-[0-9]{4}\s+[0-9]{2}:[0-9]{2}:[0-9]{2})/i',


            'plate_no'        => '/Plate No\.?:\s*([A-Za-z0-9-]+)/i',
            'plate_no'        => '/Plate No. :?:\s*([A-Za-z0-9-]+)/i',



            'vehicle_color'   => '/Vehicle Color:?\s*([A-Za-z]+)/i',
            'vehicle_type'    => '/Vehicle Type:?\s*([A-Za-z]+)/i',
            'vehicle_brand'   => '/Vehicle Brand:?\s*([A-Za-z0-9]+)/i',
            'moving_direction' => '/Moving Direction:?\s*([A-Za-z]+)/i',
            'validity'        => '/Validity:?\s*([0-9]{1,3})%/i',
            'country_region'  => '/Country\/Region:?\s*([A-Za-z]+)/i',
            'plate_color'     => '/Plate Color:?\s*([A-Za-z]+)/i',
            'plate_size'      => '/Plate Size:?\s*([A-Za-z]+)/i',
            'plate_type'      => '/Plate Type:?\s*([A-Za-z]+)/i',
            'plate_type'      => '/Plate Typee:?\s*([A-Za-z]+)/i',
            'category'      => '/Category:?\s*([A-Za-z]+)/i',




            'province'        => '/Province:?\s*([A-Za-z]+)/i',
            'camera_no'       => '/Camera No\.?:\s*([A-Za-z0-9-]*)/i',
            // 'camera_no'       => '/Camera No \.?:\s*([A-Za-z0-9-]*)/i',

        ];

        $out = [];
        foreach ($fields as $key => $rx) {
            if (preg_match($rx, $oneLine, $m)) {
                $out[$key] = trim($m[1]);
            }
        }

        // Optional post-fixes
        if (!empty($out['vehicle_brand']) && strtoupper($out['vehicle_brand']) === 'BAW') {
            $out['vehicle_brand'] = 'BMW';
        }
        if (isset($out['validity'])) {
            $out['validity'] = (int)$out['validity'];
        }
        if (isset($out['device_no'])) {
            $out['device_no'] =    str_replace('Capture Time', '', $out['device_no']);
            $out['device_no'] =    trim($out['device_no']);
        }
        return [
            'raw'      => $raw,
            'clean'    => trim($text),
            'parsed'   => $out,
            'one_line' => $oneLine,
        ];
    }*/
}
